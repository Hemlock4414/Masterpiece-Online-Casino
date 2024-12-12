import apiClient from './apiClient';
import { LobbyService } from './LobbyService';

export const createGame = async (pairs = 8, guestId = null) => {
    try {
        const requestData = {
            pairs: pairs
        };
        if (guestId) {
            requestData.guest_id = parseInt(guestId);
        }
        
        const response = await apiClient.post('/memory-games/create', requestData);
        
        // Status-Update wird über Presence Channel gehandhabt
        await LobbyService.updatePlayerStatus('waiting');
        
        return response.data;
    } catch (error) {
        console.error('Create Game Error:', error.response?.data || error);
        throw error;
    }
};

export const startGame = async (gameId) => {
    try {
        const response = await apiClient.post(`/memory-games/${gameId}/start`);
        await LobbyService.updatePlayerStatus('in_game');
        return response.data;
    } catch (error) {
        console.error('Start Game Error:', error.response?.data || error);
        throw error;
    }
};

export const stopGame = async (gameId, status = 'finished') => {
    try {
        const response = await apiClient.post(`/memory-games/${gameId}/stop`, { status });
        await LobbyService.updatePlayerStatus('available');
        return response.data;
    } catch (error) {
        console.error('Stop Game Error:', error.response?.data || error);
        throw error;
    }
};

// Die anderen Methoden bleiben unverändert
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

export const joinGame = async (gameId) => {
    try {
        const response = await apiClient.post(`/memory-games/${gameId}/join`);
        await LobbyService.updatePlayerStatus('waiting');
        return response.data;
    } catch (error) {
        console.error('Join Game Error:', error.response?.data || error);
        throw error;
    }
};