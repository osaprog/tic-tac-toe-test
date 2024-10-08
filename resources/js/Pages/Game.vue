<template>
    <div>
        <h1>Tic Tac Toe</h1>
        <p v-if="winner">  
        <div :style="{ backgroundColor: 'green', color: 'white', padding: '10px' }">
            Winner: {{ winner }}
        </div>
        </p>
        <p v-else>Current Turn: {{ turn }}</p>
        <div class="grid">
            <div v-for="(cell, index) in board" :key="index" @click="makeMove(index)" class="cell">
                {{ cell }}
            </div>
        </div>
        <button @click="resetGame" class="reset-button">Reset Game</button>        
    </div>
</template>

<script>
import { Inertia } from '@inertiajs/inertia';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

export default {
    props: {
        initialBoard: Array,
        initialTurn: String,
        initialWinner: String,
    },
    data() {
        return {
            board: this.initialBoard || Array(9).fill(null),
            turn: this.initialTurn || 'X',
            winner: this.initialWinner || null, // Track if there is a winner
            player: null, // To track if the player is X or O
            gameId: 1,  // Hardcoded here for simplicity, can be dynamic
        };
    },
    computed: {
        moveUrl() {
            return `/games/1/move`;
        },
    },
    mounted() {
        // Automatically assign the player based on the current turn
        this.player = this.turn === 'X' ? 'X' : 'O';

        // Setup Pusher for real-time updates
        //this.setupPusher();
        setInterval(() => {
            window.location.href = '/';
        }, 1000); // 3 sec
    },
    methods: {
        setupPusher() {
            window.Echo = new Echo({
                broadcaster: 'pusher',
                key: import.meta.env.VITE_PUSHER_APP_KEY,
                cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
                encrypted: true,
            });

            window.Echo.channel('game-' + this.gameId)
                .listen('move', (data) => {
                    this.board = data.board;
                    this.turn = data.turn;
                    if (data.winner) {
                        this.winner = data.winner;
                    }                    
                });
        },
        makeMove(index) {

            // Prevent moves if the game is over
                if (this.winner || this.board[index]) {
                    return;
            }

            // Allow move only if it's the player's turn and the cell is empty
            if (!this.board[index] && this.turn === this.player) {
                this.board[index] = this.player;
                this.turn = this.player === 'X' ? 'O' : 'X'; // Toggle turn

                Inertia.post(this.moveUrl, {
                    position: index,
                    player: this.player,
                }, {
                    preserveState: true, // Ensure state is preserved if you have additional props
                    replace: true // Prevent URL from being appended
                });

            }
        },
        resetGame() {
            // Reset the game state
            Inertia.post(`/games/1/reset`, {}, {
                onSuccess: (response) => {
                    // Reset local state after successful response
                    this.board = Array(9).fill(null);
                    this.turn = 'X'; // Reset to starting player
                    this.winner = null; // Reset winner
                },
                preserveState: false, // Ensure state is preserved if you have additional props
            });
        },
    },
};
</script>

<style>
.grid {
    display: grid;
    grid-template-columns: repeat(3, 100px);
    gap: 5px;
}
.cell {
    width: 100px;
    height: 100px;
    display: flex;
    justify-content: center;
    align-items: center;
    border: 1px solid black;
    font-size: 2rem;
    cursor: pointer;
}
.reset-button {
    margin-top: 20px;
    padding: 10px;
    font-size: 1rem;
}
</style>
