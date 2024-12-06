import apiClient from './apiClient';

export const LobbyService = {
    createLobby: async (challengedId, challengedType, challengedName, gameType) => {
        const response = await apiClient.post('/lobby/create', {
            challenged_id: challengedId,
            challenged_type: challengedType,
            challenged_name: challengedName,
            game_type: gameType
        });
        return response.data;
    },

    updateLobbyStatus: async (lobbyId, status) => {
        return apiClient.put(`/lobby/${lobbyId}/status`, { status });
    },

    getActiveLobby: async (playerId, playerType) => {
        return apiClient.get(`/lobby/active/${playerId}/${playerType}`);
    },

    // Holt die Liste aller aktiven Spieler
    getOnlinePlayers: async () => {
        try {
            const response = await apiClient.get('/lobby/online-players');
            return response.data;
        } catch (error) {
            console.error('Error fetching online players:', error);
            throw error;
        }
    },

    // Sendet eine Spieleinladung
    challengePlayer: async (playerId) => {
        try {
            const response = await apiClient.post(`/lobby/challenge/${playerId}`);
            return response.data;
        } catch (error) {
            console.error('Error challenging player:', error);
            throw error;
        }
    },

    // Aktualisiert den Online-Status eines Spielers
    updateStatus: async (status) => {
        try {
            const response = await apiClient.post('/lobby/status', { status });
            return response.data;
        } catch (error) {
            console.error('Error updating status:', error);
            throw error;
        }
    },
    
    updatePlayerStatus: async (status) => {
        try {
            const guestId = sessionStorage.getItem('memoryGuestId');
            const response = await apiClient.post('/lobby/player-status', {
                player_id: guestId,
                status: status
            });
            return response.data;
        } catch (error) {
            console.error('Error updating player status:', error);
            throw error;
        }
    }
};