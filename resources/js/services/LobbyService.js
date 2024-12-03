import { apiClient } from './apiClient';

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
  }
};