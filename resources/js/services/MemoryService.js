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
  const response = await apiClient.post(`/memory-games/${gameId}/cards/flip`, { card_id: cardId });
  return response.data;
};