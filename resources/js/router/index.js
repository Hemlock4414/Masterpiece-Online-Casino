import { createRouter, createWebHistory } from "vue-router";

import HomeView from "../views/HomeView.vue";

import { storeToRefs } from "pinia";
import { useAuthStore } from "@/store/AuthStore";


const router = createRouter({
    history: createWebHistory("/"),
    routes: [
        {
            path: "/",
            name: "home",
            component: HomeView,
        },
        {
            // Hier brauchen wir keine Meta-Informationen, da diese Route für nicht authentifizierte User zugänglich sein soll.
            path: "/einloggen",
            name: "login",
            component: () => import("../views/LoginView.vue"),
        },
        {
            // Hier brauchen wir keine Meta-Informationen, da diese Route für nicht authentifizierte User zugänglich sein soll.
            path: "/registrieren",
            name: "register",
            component: () => import("../views/RegisterView.vue"),
        },
        {
            // Hier brauchen wir keine Meta-Informationen, da diese Route für nicht authentifizierte User zugänglich sein soll.
            path: "/spielerschutz",
            name: "fair-gaming",
            component: () => import("../views/FairGamingView.vue"),
        },
        {
            // Hier brauchen wir keine Meta-Informationen, da diese Route für nicht authentifizierte User zugänglich sein soll.
            path: "/datenschutz",
            name: "privacy",
            component: () => import("../views/PrivacyView.vue"),
        },
        {
            // Hier brauchen wir keine Meta-Informationen, da diese Route für nicht authentifizierte User zugänglich sein soll.
            path: "/agb",
            name: "terms",
            component: () => import("../views/TermsView.vue"),
        },
        {
            // Hier brauchen wir keine Meta-Informationen, da diese Route für nicht authentifizierte User zugänglich sein soll.
            path: "/kontakt",
            name: "contact",
            component: () => import("../views/ContactView.vue"),
        },
        {
            path: "/profil",
            name: "profile",
            component: () => import("../views/ProfileView.vue"),
            meta: { requiresAuth: true },
        },
        {
            path: "/memory/play",
            name: "memory",
            component: () => import("../views/MemoryView.vue"),
        },
        {
            path: "/passwort-vergessen",
            name: "password-reset",
            component: () => import("../views/PasswordResetView.vue"),
        },
        {
            path: "/passwort-neu-setzen",
            name: "new-password",
            component: () => import("../views/PasswordNewView.vue"),
        },
        // {
        //     path: "/blackjack/play",
        //     name: "blackjack",
        //     component: () => import("../views/BlackjackView.vue"),
        // },
            // {
            // path: "/dashboard",
            // name: "dashboard",
            // route level code-splitting
            // this generates a separate chunk (Dashboard.[hash].js) for this route
            // which is lazy-loaded when the route is visited.
            // component: () => import("../views/DashboardView.vue"),
            // die Meta-Informationen verwenden wir um den Zugriff zu schützen
            // meta: { requiresAuth: true },
        // },
    ],

});

// navigation guard
router.beforeEach(async (to, from, next) => {
    const { getAuthUser } = useAuthStore();
    const { authUser } = storeToRefs(useAuthStore());
    const reqAuth = to.matched.some((record) => record.meta.requiresAuth);

    if (reqAuth && !authUser.value) {
        await getAuthUser();
        if (!authUser.value) next("/login");
        next();
    } else {
        next(); // make sure to always call next()!
    }
});

export default router;