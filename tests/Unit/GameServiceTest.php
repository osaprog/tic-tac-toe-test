<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Game;
use App\Services\GameService;

class GameServiceTest extends TestCase
{
    protected GameService $gameService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gameService = new GameService();
    }

    public function testConvertToString()
    {
        // Given an array representation of a Tic Tac Toe board
        $boardArray = ['X', 'O', null, null, 'X', null, 'O', 'X', null];

        // When converting to string
        $result = $this->gameService->convertToString($boardArray);

        // Then the string representation should be correct
        $this->assertEquals('XO--X-OX-', $result);
    }

    public function testConvertStringToArray()
    {
        // Given a string representation of a Tic Tac Toe board
        $boardString = 'XOX-OXX--';

        // When converting to array
        $result = $this->gameService->convertStringToArray($boardString);

        // Then the array representation should be correct
        $this->assertEquals(['X', 'O', 'X', null, 'O', 'X', 'X', null, null], $result);
    }

    public function testCheckWinner()
    {
        // Winning case for 'X'
        $boardXWin = ['X', 'X', 'X', null, null, null, null, null, null];
        $winner = $this->gameService->checkWinner($boardXWin);
        $this->assertEquals('X', $winner);

        // Winning case for 'O'
        $boardOWin = ['O', 'O', 'O', null, null, null, null, null, null];
        $winner = $this->gameService->checkWinner($boardOWin);
        $this->assertEquals('O', $winner);

        // No winner case
        $boardNoWin = ['X', 'O', null, null, 'X', null, 'O', null, null];
        $winner = $this->gameService->checkWinner($boardNoWin);
        $this->assertNull($winner);
    }

    public function testResetGame()
    {
        // Create a new game instance
        $game = Game::create([
            'player_x' => 'Player1',
            'player_o' => 'Player2',
            'turn' => 'X',
            'board' => 'XOX-OXX--',
        ]);

        // Reset the game
        $resetGame = $this->gameService->resetGame($game);

        // Check the reset state
        $this->assertEquals('---------', $resetGame->board);
        $this->assertEquals('X', $resetGame->turn); // Assuming the property in the model is 'turn'
    }
}
