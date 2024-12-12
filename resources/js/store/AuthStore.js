import { defineStore } from "pinia";
import axios from "axios";

export const authClient = axios.create({
    baseURL: import.meta.env.VITE_BASE_URL,
    withCredentials: true, // required to handle the CSRF token
});

export const useAuthStore = defineStore("AuthStore", {
    state: () => {
        return {
            user: null,
            guestId: null
        };
    },

    actions: {  
        async register(credentials) {
            try {
                await authClient.get("/sanctum/csrf-cookie");
                const response = await authClient.post("/api/register", credentials);
                return response;
            } catch (error) {
                this.user = null;
                if (error.response && error.response.data && error.response.data.errors) {
                    // Werfe den Fehler weiter, damit er in der Komponente behandelt werden kann
                    throw error;
                }
                throw new Error("Bei der Registrierung ist ein Fehler aufgetreten");
            }
        },
        async login(credentials) {
            try {
                await authClient.get("/sanctum/csrf-cookie");
                // Sende die Anmeldedaten mit dem generischen 'login'-Feld
                const response = await authClient.post("/api/login", {
                    login: credentials.login,
                    password: credentials.password,
                    remember: credentials.remember
                });
                
                // Wenn der Login erfolgreich war, hole die Benutzerdaten
                if (response.status === 200) {
                    await this.getAuthUser();
                }
                
                return response;
            } catch (error) {
                this.user = null;
                throw error;
            }
        },
        async getAuthUser() {
            try {
                await authClient.get("/sanctum/csrf-cookie");
                let response = await authClient.get("/api/user");
                this.user = response.data;
                
                // Gast-ID aus Session wiederherstellen
                if (!this.user) {
                    this.guestId = sessionStorage.getItem('memoryGuestId');
                }
                
                return response;
            } catch (error) {
                this.user = null;
                this.guestId = sessionStorage.getItem('memoryGuestId');
                console.error(error);
            }
        },

        async logout() {
            try {
                await authClient.get("/sanctum/csrf-cookie");
                let response = await authClient.post("/api/logout");
                this.user = null;
                return response;
            } catch (error) {
                this.user = null;
                throw error;
            }
        },
        setGuestId(id) {
            this.guestId = id;
            sessionStorage.setItem('memoryGuestId', id);
        },
    },

    // get Elements from store
    getters: {
        authUser: (state) => state.user,
        currentPlayerId: (state) => state.user?.id || state.guestId
    },
});