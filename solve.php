<?php

declare(strict_types=1);

require_once __DIR__ . '/WordleChecker.php';
require_once __DIR__ . '/WordleSolver.php';

$wordList = file(__DIR__ . '/wordList.csv', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$randomWord = $wordList[array_rand($wordList)];

$startingGuessWords = 'slate';

$wordleSolver = new WordleSolver($wordList, new WordleChecker());

echo $wordleSolver->play($startingGuessWords, $randomWord);
