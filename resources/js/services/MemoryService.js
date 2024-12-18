import axios from 'axios';

const apiClient = axios.create({
  baseURL: 'http://localhost/api',
  headers: { 'Content-Type': 'application/json' },
});

// Neues Spiel erstellen
export const createGame = async ({ cards_count, theme, guest_id } = {}) => {
  try {
    const requestData = {
      cards_count: cards_count || 16,
      theme: theme || 'emojis'
    };
    
    if (guest_id) {
      requestData.guest_id = parseInt(guest_id);
    }

    console.log('Request Data:', requestData); // Debug
    
    const response = await apiClient.post('/memory-games/create', requestData);
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
export const stopGame = async (gameId, status = 'finished') => {
  try {
    const response = await apiClient.post(`/memory-games/${gameId}/stop`, { status });
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

// Matched Cards aktualisieren
export const updateMatchedCards = async (gameId, cardIds, playerId) => {
  try {
    const response = await apiClient.post(`/memory-games/${gameId}/cards/match`, {
      card_ids: cardIds,
      player_id: playerId
    });
    return response.data;
  } catch (error) {
    console.error('Update Matched Cards Error:', error.response?.data || error);
    throw error;
  }
};

export const nextTurn = async (gameId) => {
  try {
    const response = await apiClient.post(`/memory-games/${gameId}/next-turn`);
    return response.data;
  } catch (error) {
    console.error('Next Turn Error:', error.response?.data || error);
    throw error;
  }
};

// Neue Funktion für Custom Themes
export const getCustomThemes = async () => {
  try {
    const response = await apiClient.get('/memory-games/custom-themes');
    return response.data;
  } catch (error) {
    console.error('Custom Themes Error:', error.response?.data || error);
    throw error;
  }
};