<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Game;
use Pusher\Pusher;
use Inertia\Inertia;

class GameController extends Controller
{
    public function create()
    {
            // Find the game
            $game = Game::find(1);

            if (!$game){
                $game = Game::firstOrCreate([
                 'id' => 1,
                 'player_x' => 'Player1',
                 'player_o' => 'Player2',
                 'turn' => 'X',
                 'board' =>'---------'
                ]);
             }

         
            // Fetch the current state of the board
            $board = $this->convertStringToArray($game->board);
            $winner = $this->checkWinner($board);
 
            return Inertia::render('Game', [
                'initialBoard' => $board,
                'initialTurn' => $game->turn, // Determine if X or O should play based on the current turn
                'initialWinner' => $winner
            ]);
      
    }

    private function convertToString($boardArray) {
        // Map null or empty values to '-', and X or O remain unchanged
        return implode('', array_map(function($value) {
            return $value === null ? '-' : $value;
        }, $boardArray));
    }

    private function convertStringToArray($boardString) {
        // Split the string into an array of characters
        $characters = str_split($boardString);
        
        // Convert "-" to null and keep "X" or "0" as is
        return array_map(function($char) {
            return $char === '-' ? null : $char;
        }, $characters);
    }

    public function move(Request $request, $gameId)
    {
        $game = Game::find($gameId);

        if ($game){  
          $board = $game->board;
        }

        $position = $request->input('position');
        $player = $request->input('player');
 
        //if ($board[$position] === null && $player === $game->turn) {
            $board[$position] = $player;

    
            // Check if the game has a winner
            $winner = $this->checkWinner($board);

            if (is_array($board)){
                $game->board = $this->convertToString($board);
            }else{
                $game->board = $board;
            }

            // Update the current player if no winner
            $game->turn = $player === 'X' ? 'O' : 'X';
            
            $game->save();

            if ($winner) {
                // Save the winner and end the game
                $game->winner = $winner;
                $game->is_over = true;  // Add a column to track if the game is over
            }

            // Trigger Pusher event
            $this->triggerPusher($gameId, $board, $game->turn);
        //}

        return Inertia::render('Game', ['game' => $game]);
    }

    protected function triggerPusher($gameId, $board, $turn)
    {
        $options = [
            'cluster' => env('PUSHER_APP_CLUSTER'),
            'useTLS' => true,
        ];
        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            $options
        );

        $data = [
            'board' => $board,
            'turn' => $turn,
        ];
        $pusher->trigger('game-' . $gameId, 'move', $data);
    }


    private function checkWinner($board)
    {
    // Define all possible winning combinations
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

}
