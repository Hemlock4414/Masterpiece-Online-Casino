import axios from 'axios';

// Erstelle einen allgemeinen API-Client
const apiClient = axios.create({
  baseURL: 'http://localhost/api', // Laravel-Backend
  headers: { 'Content-Type': 'application/json' },
});

// Neues Spiel erstellen
export const createGame = async (pairs) => {
  const response = await apiClient.post('/memory-games/create', { pairs });
  return response.data;
};

export const startGame = async (gameId) => {
  try {
    const response = await apiClient.post(`/memory-games/${gameId}/start`);
    return response.data;
  } catch (error) {
    console.error('Start Game Error:', error.response?.data || error.message);
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

// Karte aufdecken
export const flipCard = async (gameId, cardId) => {
  console.log('Flipping card:', { gameId, cardId }); // Debug
  const response = await apiClient.post(`/memory-games/${gameId}/cards/flip`, { 
    card_id: cardId 
  });
  return response.data;
};

// Punkte eines Spielers aktualisieren
export const updatePlayerScore = async (gameId, playerId, score) => {
  const response = await apiClient.put(`/memory-games/${gameId}/players/${playerId}`, {
    player_score: score,
  });
  return response.data;
};