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
            profileImage: null,
        };
    },

    actions: {  
        async register(credentials) {
            try {
                await authClient.get("/sanctum/csrf-cookie");
                const response = await authClient.post("/api/register", credentials);
                if (response.status === 201) {
                    await this.getAuthUser();
                }
                return response;
            } catch (error) {
                this.user = null;
                if (error.response && error.response.data && error.response.data.errors) {
                    throw error;
                }
                throw new Error("Bei der Registrierung ist ein Fehler aufgetreten");
            }
        },

        async login(credentials) {
            try {
                await authClient.get("/sanctum/csrf-cookie");
                const response = await authClient.post("/api/login", {
                    login: credentials.login,
                    password: credentials.password,
                    remember: credentials.remember
                });
                
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
                const response = await authClient.get("/api/user");
                this.user = response.data;
                this.profileImage = response.data?.user?.profile_pic_url || null;
                return response;
            } catch (error) {
                this.user = null;
                this.profileImage = null;
                console.error('Fehler beim Laden der Benutzerdaten:', error);
                throw error;
            }
        },

        async logout() {
            try {
                await authClient.get("/sanctum/csrf-cookie");
                const response = await authClient.post("/api/logout");
                this.user = null;
                this.profileImage = null;
                return response;
            } catch (error) {
                this.user = null;
                this.profileImage = null;
                throw error;
            }
        },

        async updateProfilePicture(formData) {
            try {
                await authClient.get("/sanctum/csrf-cookie");
                const response = await authClient.post("/api/user/update/pic", formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    }
                });
                
                if (response.data.profile_pic_url) {
                    this.profileImage = response.data.profile_pic_url;
                }
                
                await this.getAuthUser(); // Aktualisiere Benutzerdaten
                return response;
            } catch (error) {
                console.error('Fehler beim Hochladen des Profilbildes:', error);
                throw error;
            }
        },

        async deleteProfilePicture() {
            try {
                await authClient.get("/sanctum/csrf-cookie");
                const response = await authClient.delete("/api/user/delete/pic");
                this.profileImage = null;
                await this.getAuthUser();
                return response;
            } catch (error) {
                console.error('Fehler beim Löschen des Profilbildes:', error);
                throw error;
            }
        },

        async updateEmail(data) {
            try {
                await authClient.get("/sanctum/csrf-cookie");
                const response = await authClient.post("/api/user/update/email", {
                    current_password: data.current_password,
                    email: data.email
                });
                
                // Aktualisiere den user im Store
                if (this.user && this.user.user) {
                    this.user.user.email = data.email;
                }
                
                await this.getAuthUser(); // Optional als Backup
                return response;
            } catch (error) {
                console.error('Fehlerdetails:', error.response?.data);
                throw error;
            }
        },

        async updatePassword(data) {
            try {
                await authClient.get("/sanctum/csrf-cookie");
                const response = await authClient.post("/api/user/update/password", {
                    current_password: data.current_password,
                    new_password: data.password,
                    new_password_confirmation: data.password_confirmation
                });
                await this.logout();
                return response;
            } catch (error) {
                console.error('Fehler beim Aktualisieren des Passworts:', error);
                throw error;
            }
        },

        async deleteAccount() {
            try {
                await authClient.get("/sanctum/csrf-cookie");
                const response = await authClient.delete("/api/user/delete");
                this.user = null;
                this.profileImage = null;
                return response;
            } catch (error) {
                console.error('Fehler beim Löschen des Kontos:', error);
                throw error;
            }
        }
    },

    getters: {
        authUser: (state) => state.user,
        profilePicUrl: (state) => state.profileImage,
    },
});