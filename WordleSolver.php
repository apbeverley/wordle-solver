<?php

declare(strict_types=1);

class WordleSolver
{
    public function __construct(
        private array                  $wordList,
        private readonly WordleChecker $wordle
    )
    {
    }

    public function play(string $guessWord, string $randomWord): string
    {
        $guessAttempt = 0;

        while ($guessWord !== $randomWord) {
            if (empty($guessedWords)) {
                $guessedWords = [$guessWord];
            } else {
                // get the next best guess based on what is most common among the remaining words
                $guessWord = $this->getNextBestGuess();

                $guessedWords[] = $guessWord;
            }

            $guessWordCheckResults = $this->wordle->checkGuess($guessWord, $randomWord);

            //format the results into an array
            $formatGuessResults = $this->formatGuessResults($guessWordCheckResults);

            // remove words we know it can't be from the list
            $this->wordList = $this->excludeWordsFromList($formatGuessResults);

            // check if this guess is the correct word before attempting to guess again
            if ($guessWord === $randomWord) {
                break;
            }

            $guessAttempt++;

            if ($guessAttempt === 6) {
                return "You lose! The word was `$randomWord`. You tried `" . implode('`, `', $guessedWords) . "`";
            }
        }

        return "You win! The word was: `$randomWord` it took you $guessAttempt attempts.";
    }

    private function getNextBestGuess(): string
    {
        $positionalFrequencies = [];

        // Calculate how often each letter appears in each specific position
        foreach ($this->wordList as $word) {
            $letters = str_split($word);
            foreach ($letters as $position => $letter) {
                $positionalFrequencies[$position][$letter] = ($positionalFrequencies[$position][$letter] ?? 0) + 1;
            }
        }

        // Score words based on the positional frequencies of their letters
        $wordScores = [];
        foreach ($this->wordList as $word) {
            $score = 0;
            $letters = str_split($word);

            foreach ($letters as $position => $letter) {
                // Add points based on how common this letter is in this exact spot
                $score += $positionalFrequencies[$position][$letter];
            }

            $wordScores[$word] = $score;
        }

        // If all words have the same score, return a random word from the list
        $allSame = count(array_unique($wordScores)) === 1;

        if ($allSame) {
            return array_rand($wordScores);
        }

        // Sort the array from highest score to lowest score
        arsort($wordScores);

        return array_key_first($wordScores);
    }

    private function excludeWordsFromList(array $guessResults): array
    {
        // Gather all letters we KNOW are in the word in the correct position or not
        $knownLetters = array_merge(
            array_values($guessResults['correct'] ?? []),
            array_values($guessResults['wrong_position'] ?? [])
        );

        // Count how many times each known letter MUST appear
        $knownCounts = array_count_values($knownLetters);

        $absent = $guessResults['absent'] ?? [];

        // Letters that are truly absent (they appear exactly 0 times)
        $strictAbsent = array_diff($absent, array_keys($knownCounts));
        $absentString = implode('', $strictAbsent);

        // Letters that are in both 'absent' AND 'known' lists.
        // We now know the EXACT maximum number of times this letter can appear.
        $cappedLetters = array_intersect($absent, array_keys($knownCounts));

        return array_filter($this->wordList, static function ($word) use ($guessResults, $absentString, $cappedLetters, $knownCounts) {
            // RULE: Completely Absent Letters (0 occurrences)
            if ($absentString !== '' && strpbrk($word, $absentString) !== false) {
                return false;
            }

            // RULE: Capped Duplicate Letters
            if (array_any($cappedLetters, fn($letter) => substr_count($word, $letter) > $knownCounts[$letter])) {
                return false;
            }

            // RULE: Minimum Known Letters
            // If the game revealed two 'O's, the candidate word MUST have at least two 'O's.
            if (array_any($knownCounts, fn($count, $letter) => substr_count($word, $letter) < $count)) {
                return false;
            }

            // RULE: Correct Position (Green)
            // The word MUST have the exact letter at the exact position.
            if (!empty($guessResults['correct'])) {
                foreach ($guessResults['correct'] as $position => $letter) {
                    if (!isset($word[$position]) || $word[$position] !== $letter) {
                        return false;
                    }
                }
            }

            // RULE: Wrong Position Letter
            // The word MUST contain the letter, but MUST NOT have it at this specific position.
            if (!empty($guessResults['wrong_position'])) {
                foreach ($guessResults['wrong_position'] as $position => $letter) {
                    // Must contain the letter somewhere
                    if (str_contains($word, $letter) === false) {
                        return false;
                    }
                    // Must NOT be at the guessed position
                    if (isset($word[$position]) && $word[$position] === $letter) {
                        return false;
                    }
                }
            }

            // If the word survives all that, keep it!
            return true;
        });
    }

    private function formatGuessResults(array $guessWordCheckResults): array
    {
        $wordAttemptResults = [];

        foreach ($guessWordCheckResults as $pos => $result) {
            if ($result['status'] === 'correct') {
                $wordAttemptResults['correct'][$pos] = $result['letter'];
            }

            if ($result['status'] === 'wrong_position') {
                $wordAttemptResults['wrong_position'][$pos] = $result['letter'];
            }

            if ($result['status'] === 'absent') {
                $wordAttemptResults['absent'][$pos] = $result['letter'];
            }
        }

        return $wordAttemptResults;
    }
}
