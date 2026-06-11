<?php

declare(strict_types=1);

require_once __DIR__ . '/WordleChecker.php';
require_once __DIR__ . '/WordleSolver.php';

$wordList = file(__DIR__ . '/wordList.csv', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$startingGuessWords = ['slate', 'crane', 'trace', 'audio', 'canoe'];

$randomWord = $wordList[array_rand($wordList)];

$startingGuessWord = $startingGuessWords[array_rand($startingGuessWords)];

$wordleSolver = new WordleSolver($wordList, new WordleChecker());

echo $wordleSolver->play($startingGuessWord, $randomWord);
