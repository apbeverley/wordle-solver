<?php

declare(strict_types=1);

class WordleChecker
{
    public function checkGuess(string $guess, string $todayWord): array
    {
        $guessArray = str_split(strtolower($guess));
        $todayWord = strtolower($todayWord);

        $results = array_fill(0, 5, null);
        $remainingTarget = str_split($todayWord);

        $results = $this->findAllExactLetterMatches($guessArray, $todayWord, $results, $remainingTarget);

        return $this->findAllWrongOrAbsentLetters($guessArray, $results, $remainingTarget);
    }

    private function findAllExactLetterMatches(
        array  $guessLetters,
        string $todayWord,
        array  $results,
        array  &$remainingTarget
    ): array
    {
        foreach ($guessLetters as $i => $letter) {
            if ($letter === $todayWord[$i]) {
                $results[$i] = [
                    'letter' => $letter,
                    'status' => 'correct'
                ];
                $remainingTarget[$i] = null;
            }
        }

        return $results;
    }

    private function findAllWrongOrAbsentLetters(array $guessLetters, array $results, array $remainingTarget): array
    {
        foreach ($guessLetters as $i => $letter) {
            if ($results[$i] !== null) {
                continue;
            }

            $pos = array_search($letter, $remainingTarget, true);

            if ($pos !== false) {
                $results[$i] = [
                    'letter' => $letter,
                    'status' => 'wrong_position'
                ];

                $remainingTarget[$pos] = null;
            } else {
                $results[$i] = [
                    'letter' => $letter,
                    'status' => 'absent'
                ];
            }
        }

        return $results;
    }
}