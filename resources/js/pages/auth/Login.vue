<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import PasswordInput from '@/components/PasswordInput.vue';
import { store } from '@/routes/login';
import garageImage from '../../../assets/images/accueil.jpg';
import gearLogo from '../../../assets/images/rouage.png';

/** Traduit l'erreur standard de Fortify sans remplacer les autres messages de validation. */
const translateLoginError = (message?: string) =>
    message?.toLowerCase().includes('credentials')
        ? 'Identifiant ou mot de passe incorrect.'
        : message;

defineProps<{
    status?: string;
}>();
</script>

<template>
    <Head title="Connexion" />

    <main
        class="login-page"
        :style="{ '--garage-image': `url(${garageImage})` }"
    >
        <section class="login-panel">
            <header class="brand">
                <img :src="gearLogo" alt="" />
                <span>GarageManager</span>
            </header>

            <div class="intro">
                <h1>Gestion d’atelier automobile</h1>
                <p>Suivi des interventions et coordination des équipes en temps réel</p>
            </div>

            <div class="form-block">
                <h2>Connexion</h2>
                <p class="form-subtitle">Accédez à votre espace</p>
                <p v-if="status" class="status-message">{{ status }}</p>

                <Form
                    v-bind="store.form()"
                    v-slot="{ errors, processing }"
                    class="login-form"
                >
                    <!-- Ces noms correspondent aux champs attendus par Fortify et Utilisateur. -->
                    <label for="login_utilisateur">Login</label>
                    <input
                        id="login_utilisateur"
                        name="login_utilisateur"
                        type="text"
                        autocomplete="username"
                        required
                        autofocus
                    />
                    <p v-if="errors.login_utilisateur" class="field-error">
                        {{ translateLoginError(errors.login_utilisateur) }}
                    </p>

                    <label for="password">Mot de passe</label>
                    <PasswordInput
                        id="password"
                        name="password"
                        class="login-password-input"
                        autocomplete="current-password"
                        required
                    />
                    <p v-if="errors.password" class="field-error">
                        {{ errors.password }}
                    </p>

                    <button
                        type="submit"
                        :disabled="processing"
                        data-test="login-button"
                    >
                        {{ processing ? 'Connexion…' : 'Connexion' }}
                    </button>
                </Form>
            </div>
        </section>

        <!-- À relier aux pages légales lorsqu'elles seront disponibles. -->
        <nav class="legal-links" aria-label="Informations légales">
            <a href="#politique-de-confidentialite">Politique de confidentialité</a>
            <a href="#mentions-legales">Mentions légales</a>
        </nav>
    </main>
</template>

<style scoped>
.login-page {
    position: relative;
    display: grid;
    min-height: 100svh;
    place-items: center;
    padding: 24px;
    overflow: hidden;
    color: #20262b;
    font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
    isolation: isolate;
}

.login-page::before {
    position: fixed;
    z-index: -1;
    inset: 0;
    content: '';
    background-image: linear-gradient(rgb(214 229 240 / 78%), rgb(214 229 240 / 78%)), var(--garage-image);
    background-position: center;
    background-size: cover;
}

.login-panel {
    position: relative;
    display: flex;
    width: min(100%, 540px);
    min-height: 620px;
    flex-direction: column;
    align-items: center;
    padding: 26px 32px 34px;
    overflow: hidden;
    background: transparent;
}

.brand {
    position: fixed;
    z-index: 1;
    top: 26px;
    right: 34px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 18px;
}

.brand img {
    width: 27px;
    height: 27px;
    object-fit: contain;
}

.intro {
    margin-top: 50px;
    text-align: center;
}

.intro h1 {
    margin: 0;
    font-size: 24px;
    font-weight: 500;
}

.intro p {
    margin: 14px 0 0;
    font-size: 14px;
}

.form-block {
    width: min(100%, 270px);
    margin-top: 42px;
}

.form-block h2 {
    margin: 0;
    text-align: center;
    font-size: 21px;
    font-weight: 600;
    text-transform: uppercase;
}

.form-subtitle {
    margin: 9px 0 18px;
    text-align: center;
    font-size: 15px;
}

.login-form {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.login-form label {
    margin-top: 4px;
    font-size: 14px;
}

.login-form :deep(input) {
    width: 100%;
    height: 38px;
    padding: 0 10px;
    border: 1px solid #b3c2ce;
    border-radius: 2px;
    background: rgb(255 255 255 / 78%);
    color: #20262b;
    font: inherit;
    outline-color: #477da4;
}

.login-form :deep(.login-password-input) {
    padding-right: 38px;
}

.legal-links {
    position: fixed;
    right: 34px;
    bottom: 20px;
    display: flex;
    gap: 24px;
    font-size: 13px;
}

.legal-links a {
    color: #1673b8;
    text-decoration: none;
}

.legal-links a:hover {
    text-decoration: underline;
}

.login-form button {
    align-self: center;
    min-width: 112px;
    min-height: 38px;
    margin-top: 9px;
    padding: 0 18px;
    border: 1px solid #748999;
    border-radius: 2px;
    background: rgb(255 255 255 / 70%);
    color: #20262b;
    cursor: pointer;
}

.login-form button:hover {
    background: #fff;
}

.login-form button:disabled {
    cursor: wait;
    opacity: 0.7;
}

.field-error,
.status-message {
    margin: 0;
    color: #a33131;
    font-size: 14px;
}

.status-message {
    margin-bottom: 10px;
    text-align: center;
}

@media (max-width: 520px) {
    .login-page {
        padding: 12px;
    }

    .login-panel {
        min-height: min(620px, calc(100svh - 24px));
        padding: 72px 20px 30px;
    }

    .intro {
        margin-top: 22px;
    }

    .brand {
        top: 18px;
        right: 18px;
        font-size: 16px;
    }

    .legal-links {
        right: 12px;
        bottom: 12px;
        left: 12px;
        justify-content: center;
        gap: 16px;
        font-size: 11px;
    }
}
</style>
