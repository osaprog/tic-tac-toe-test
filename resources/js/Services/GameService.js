import { Inertia } from '@inertiajs/inertia';

const GameService = {
    createGame() {
        return Inertia.post('/games/create');
    },

    makeMove(gameId, position, player) {
        return Inertia.post(`/games/${gameId}/move`, {
            position,
            player,
        });
    },
};

export default GameService;
