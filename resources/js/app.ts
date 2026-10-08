import { createInertiaApp } from '@inertiajs/vue3';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Démarre l'application Inertia et configure les valeurs communes utilisées par toutes les pages.
void createInertiaApp({
    // Reçoit le titre fourni par la page courante, lui ajoute le nom de l'application et utilise ce dernier seul si aucun titre n'est défini.
    title: (title) => (title ? `${title} - ${appName}` : appName),
    // Choisit le ou les layouts en fonction du nom de la page : certaines vues occupent tout l'écran, l'authentification et les paramètres ont leur propre structure, et les autres pages utilisent le layout principal.
    layout: (name) => {
        switch (true) {
            case name === 'Welcome':
            case name === 'Dashboard':
            case name === 'Users':
            case name === 'UserForm':
            case name === 'Appointments':
            case name === 'AppointmentForm':
            case name === 'Vehicles':
            case name === 'VehicleForm':
            case name === 'auth/Login':
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    progress: {
        color: '#4B5563',
    },
});

// Applique au chargement le thème enregistré dans le navigateur, ou celui du système si aucun choix n'a été sauvegardé, puis suit les changements du thème système.
initializeTheme();

// Enregistre un écouteur des événements flash Inertia; lorsqu'une réponse serveur contient un toast, affiche son message avec le type indiqué (succès, erreur, etc.).
initializeFlashToast();
