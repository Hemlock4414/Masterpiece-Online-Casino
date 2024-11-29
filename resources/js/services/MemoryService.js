import axios from 'axios';

// Erstelle einen allgemeinen API-Client
const apiClient = axios.create({
  baseURL: 'http://localhost/api', // Laravel-Backend
  headers: { 'Content-Type': 'application/json' },
});

// Error Handler hinzufügen
const handleError = (error) => {
  const errorMessage = error.response?.data?.error || error.message;
  console.error('API Error:', errorMessage);
  throw new Error(errorMessage);
};

// Neues Spiel erstellen
export const createGame = async (pairs = 8) => {
  try {
      const response = await apiClient.post('/memory-games/create', { pairs });
      return response.data;
  } catch (error) {
      handleError(error);
  }
};

// Spiel starten
export const startGame = async (gameId) => {
  try {
    const response = await apiClient.post(`/memory-games/${gameId}/start`);
    console.log('Start game response:', response.data); // Debug
    return response.data;
  } catch (error) {
    console.error('Start Game Error:', error.response?.data || error);
    throw error;
  }
};

// Spiel laden
export const getGame = async (gameId) => {
  const response = await apiClient.get(`/memory-games/${gameId}`);
  return response.data;
};

// Spiel beenden
export const stopGame = async (gameId) => {
  const response = await apiClient.post(`/memory-games/${gameId}/stop`);
  return response.data;
};

// Karten-Daten vom Backend abrufen
export const getCards = async () => {
  const response = await apiClient.get('/cards');
  return response.data;
};

export const flipCard = async (gameId, cardId, playerId) => {
  try {
    console.log('Flipping card:', { gameId, cardId, playerId }); // Debug
    const response = await apiClient.post(`/memory-games/${gameId}/cards/flip`, { 
      card_id: cardId,
      player_id: playerId 
    });
    console.log('Flip response:', response.data); // Debug
    return response.data;
  } catch (error) {
    console.error('Flip error:', error.response?.data || error);
    throw error;
  }
};

// Punkte eines Spielers aktualisieren
export const updatePlayerScore = async (gameId, playerId, score) => {
  const response = await apiClient.put(`/memory-games/${gameId}/players/${playerId}`, {
    player_score: score,
  });
  return response.data;
};

export const resetCard = async (gameId, cardId) => {
  try {
    const response = await apiClient.post(`/memory-games/${gameId}/cards/${cardId}/reset`);
    return response.data;
  } catch (error) {
    console.error('Reset card error:', error.response?.data || error);
    throw error;
  }
};
