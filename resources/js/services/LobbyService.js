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
            // Broadcast über Presence Channel wird vom Server gehandhabt
            return response.data;
        } catch (error) {
            console.error('Error updating status:', error);
            throw error;
        }
    },

    updateLobbyStatus: async (lobbyId, status) => {
        try {
            const response = await apiClient.post(`/lobby/status/${lobbyId}`, { status });
            return response.data;
        } catch (error) {
            console.error('Error updating lobby status:', error);
            throw error;
        }
    },

    // Diese Methode wird nicht mehr benötigt, da Presence Channel die Online-Spieler verwaltet
    getOnlinePlayers: async () => {
        console.warn('getOnlinePlayers is deprecated. Use Presence Channel instead.');
        return [];
    },

    challengePlayer: async (playerId) => {
        try {
            const challenger = LobbyService.getCurrentPlayerId();
            const response = await apiClient.post(`/lobby/challenge/${playerId}`, {
                challenger_id: challenger
            });
            return response.data;
        } catch (error) {
            console.error('Error challenging player:', error);
            throw error;
        }
    },

    // Neue Methode für Presence Channel Authentication
    getPresenceChannelAuth: async () => {
        try {
            const response = await apiClient.post('/broadcasting/auth', {
                channel_name: 'presence-game.lobby'
            });
            return response.data;
        } catch (error) {
            console.error('Error getting presence channel auth:', error);
            throw error;
        }
    }
};