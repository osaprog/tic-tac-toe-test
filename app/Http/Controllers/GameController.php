<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Game;
use Pusher\Pusher;
use Inertia\Inertia;
use App\Services\GameService; // Import the GameService

class GameController extends Controller
{
    protected $gameService;

    public function __construct(GameService $gameService)
    {
        $this->gameService = $gameService; // Inject GameService
    }

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
                'board' => '---------'
            ]);
        }

        // Fetch the current state of the board
        $board = $this->gameService->convertStringToArray($game->board);
        $winner = $this->gameService->checkWinner($board);

        return Inertia::render('Game', [
            'initialBoard' => $board,
            'initialTurn' => $game->turn,
            'initialWinner' => $winner
        ]);
    }

    public function reset(Request $request, $gameId)
    {
        $game = Game::findOrFail($gameId);
        $this->gameService->resetGame($game);

        return $this->create(); // Re-render the game view
    }

    public function move(Request $request, $gameId)
    {
        $game = Game::findOrFail($gameId);

        $board = $this->gameService->convertStringToArray($game->board);
        $position = $request->input('position');
        $player = $request->input('player');

        if ($board[$position] === null && $player === $game->turn) {
            $board[$position] = $player;

            // Check if the game has a winner
            $winner = $this->gameService->checkWinner($board);

            $game->board = $this->gameService->convertToString($board);
            $game->turn = $player === 'X' ? 'O' : 'X';

            $game->save();

            if ($winner) {
                $game->winner = $winner;
                $game->is_over = true;  // Add a column to track if the game is over
            }
            
            // Trigger Pusher event
            $this->triggerPusher($gameId, $board, $game->turn);
        }

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
}
