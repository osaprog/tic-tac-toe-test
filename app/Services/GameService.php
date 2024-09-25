<?php
namespace App\Services;

use App\Models\Game;

class GameService
{
    public function convertToString($boardArray)
    {
        return implode('', array_map(function($value) {
            return $value === null ? '-' : $value;
        }, $boardArray));
    }

    public function convertStringToArray($boardString)
    {
        $characters = str_split($boardString);
        return array_map(function($char) {
            return $char === '-' ? null : $char;
        }, $characters);
    }

    public function checkWinner($board)
    {
        $winningCombinations = [
            [0, 1, 2], [3, 4, 5], [6, 7, 8], // Rows
            [0, 3, 6], [1, 4, 7], [2, 5, 8], // Columns
            [0, 4, 8], [2, 4, 6],            // Diagonals
        ];

        foreach ($winningCombinations as $combination) {
            [$a, $b, $c] = $combination;
            if ($board[$a] && $board[$a] === $board[$b] && $board[$a] === $board[$c]) {
                return $board[$a];  // Return 'X' or 'O' as the winner
            }
        }

        return null; // No winner yet
    }

    public function resetGame(Game $game)
    {
        $game->board = $this->convertToString(array_fill(0, 9, null));
        $game->turn = 'X';
        $game->save();
        return $game;
    }
}
