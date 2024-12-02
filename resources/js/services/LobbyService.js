import { apiClient } from './apiClient';

export const LobbyService = {
  getOnlinePlayers: async () => {
    const response = await apiClient.get('/lobby/players');
    return response.data;
  },

  updateStatus: async (status) => {
    return apiClient.post('/lobby/status', { status });
  },

  challengePlayer: async (playerId) => {
    return apiClient.post(`/lobby/challenge/${playerId}`);
  }
};