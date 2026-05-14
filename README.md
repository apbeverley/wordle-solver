# Wordle Solver 🟩

A command-line Wordle solver written in PHP. Given a word list, it automatically plays Wordle by progressively eliminating invalid candidates after each guess until the target word is found.

## How It Works

The solver uses a two-step loop each round:

1. **Eliminate** — After each guess, the Wordle engine returns one of three statuses per letter:
    - 🟩 `correct` — Right letter, right position
    - 🟨 `wrong_position` — Right letter, wrong position
    - ⬛ `absent` — Letter not in the word (at this count)

   The solver applies these rules to filter the word list down to only valid remaining candidates.

2. **Guess** — Instead of guessing randomly, the solver calculates the **positional frequency** of every remaining letter. It scores and selects the word that is mathematically most likely to reveal new information. (If multiple words share the highest score, one is selected at random).

*Note: The starting guess is always `slate`, chosen for its strong letter coverage.*

## Requirements

- PHP **8.4+**
- A `wordList.csv` file in the same directory as the script (one five-letter word per line)

## Usage

```bash
php .\solve.php
```

The solver picks a random target word from `wordList.csv` and attempts to solve it, printing the result to stdout.

**Example output:**

```
You win! The word was: `crane` it took you 4 attempts.
```

## Project Structure

```
.
├── solve.php           # Main script entry point
├── WordleChecker.php   # Wordle Checker (engine)
├── WordleSolver.php    # Wordle Solver 
└── wordList.csv        # Word list (one five-letter word per line)
```

## Classes

### `WordleSolver`

Orchestrates the game loop. On each turn it:

- Submits the current guess to the `Wordle` engine
- Formats the result
- Filters the word list down to valid candidates
- Calculates the optimal next guess based on letter frequency distribution among the remaining words (breaking ties randomly if multiple words share the top score).

### `Wordle`

A self-contained guess checker. Given a guess and a target word, it returns a per-letter result array handling the edge
case of duplicate letters correctly — a letter is only marked `wrong_position` as many times as it genuinely appears in
the target.

## Limitations

- Limited to **6 attempts**, matching standard Wordle rules
- Only supports **5-letter words**
- Solver state is not preserved between games, each run is independent

## Potential Improvements

- Experiment with alternative starting words or opening sequences
- Accept a target word or starting guess as a CLI argument
- Track and report solve statistics across multiple runs
