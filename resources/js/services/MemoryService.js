import axios from 'axios';

// Erstelle einen allgemeinen API-Client
const apiClient = axios.create({
  baseURL: 'http://localhost:8000/api', // Laravel-Backend
  headers: { 'Content-Type': 'application/json' },
});

// Karten-Daten vom Backend abrufen
export const getCards = async () => {
  const response = await apiClient.get('/cards');
  return response.data;
};