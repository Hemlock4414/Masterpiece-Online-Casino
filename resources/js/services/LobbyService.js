import { useAuthStore } from "@/store/AuthStore";
import apiClient from './apiClient';

export const LobbyService = {
    getCurrentPlayerId: () => {
        const authStore = useAuthStore();
        if (authStore.user?.id) {
            return authStore.user.id;
        }
        return sessionStorage.getItem('memoryGuestId');
    },

    updatePlayerStatus: async (status) => {
        try {
            const playerId = LobbyService.getCurrentPlayerId();
            const response = await apiClient.post('/lobby/player-status', {
                player_id: playerId,
                status: status
            });
            console.log('Status updated:', response.data);
            return response.data;
        } catch (error) {
            console.error('Error updating status:', error);
            throw error;
        }
    },

    updateLobbyStatus: async (lobbyId, status) => {
        try {
            const response = await apiClient.post(`/lobby/status/${lobbyId}`, { status });
            console.log('Lobby status updated:', response.data);
            return response.data;
        } catch (error) {
            console.error('Error updating lobby status:', error);
            throw error;
        }
    },

    getOnlinePlayers: async () => {
        try {
            const response = await apiClient.get('/lobby/online-players');
            console.log('Online players:', response.data);
            return response.data;
        } catch (error) {
            console.error('Error fetching online players:', error);
            throw error;
        }
    },

    challengePlayer: async (playerId) => {
        try {
            const challenger = LobbyService.getCurrentPlayerId();
            const response = await apiClient.post(`/lobby/challenge/${playerId}`, {
                challenger_id: challenger
            });
            console.log('Challenge sent:', response.data);
            return response.data;
        } catch (error) {
            console.error('Error challenging player:', error);
            throw error;
        }
    }
};