import axios from 'axios';

const apiClient = axios.create({
  baseURL: 'http://localhost/api',
  headers: { 'Content-Type': 'application/json' },
});

// Neues Spiel erstellen
export const createGame = async (pairs = 8) => {
  try {
    const response = await apiClient.post('/memory-games/create', { pairs });
    return response.data;
  } catch (error) {
    console.error('Create Game Error:', error.response?.data || error);
    throw error;
  }
};

// Spiel starten
export const startGame = async (gameId) => {
  try {
    const response = await apiClient.post(`/memory-games/${gameId}/start`);
    return response.data;
  } catch (error) {
    console.error('Start Game Error:', error.response?.data || error);
    throw error;
  }
};

// Spiel beenden
export const stopGame = async (gameId) => {
  try {
    const response = await apiClient.post(`/memory-games/${gameId}/stop`);
    return response.data;
  } catch (error) {
    console.error('Stop Game Error:', error.response?.data || error);
    throw error;
  }
};

// Punkte eines Spielers aktualisieren
export const updatePlayerScore = async (gameId, playerId, score) => {
  try {
    const response = await apiClient.put(`/memory-games/${gameId}/players/${playerId}`, {
      player_score: score,
    });
    return response.data;
  } catch (error) {
    console.error('Update Score Error:', error.response?.data || error);
    throw error;
  }
};