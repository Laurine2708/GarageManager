<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Check, Save } from '@lucide/vue';
import { Toaster } from '@/components/ui/sonner';
import garageImage from '../../assets/images/accueil.jpg';
import gearLogo from '../../assets/images/rouage.png';

type UserRecord = {
    id: number;
    firstName: string;
    lastName: string;
    telephone: string | null;
    email: string | null;
    login: string;
    houseNumber: string;
    streetName: string;
    postalCode: string;
    city: string;
    userRole: string;
};

const props = defineProps<{
    role: string;
    mode: 'create' | 'edit' | 'profile' | 'view';
    user: UserRecord | null;
}>();

const form = useForm({
    firstName: props.user?.firstName ?? '',
    lastName: props.user?.lastName ?? '',
    telephone: props.user?.telephone ?? '',
    email: props.user?.email ?? '',
    login: props.user?.login ?? '',
    houseNumber: props.user?.houseNumber ?? '',
    streetName: props.user?.streetName ?? '',
    postalCode: props.user?.postalCode ?? '',
    city: props.user?.city ?? '',
    userRole: props.user?.userRole ?? 'client',
    password: '',
    password_confirmation: '',
});
const successDialog = ref<HTMLDialogElement | null>(null);

// Indique si le formulaire sert à modifier une fiche utilisateur existante.
const isEditing = computed(() => props.mode === 'edit');
// Indique si le formulaire concerne le profil de la personne actuellement connectée.
const isProfile = computed(() => props.mode === 'profile');
// Indique si la page affiche une fiche en lecture seule, sans permettre son envoi.
const isViewing = computed(() => props.mode === 'view');
const roleLabels: Record<string, string> = {
    client: 'Profil Client',
    mecanicien: 'Profil Mécanicien',
    administrateur: 'Profil Admin',
};
const formRoleLabels: Record<string, string> = {
    client: 'Client',
    mecanicien: 'Mécanicien',
    administrateur: 'Administrateur',
};
const roleLabel = roleLabels[props.role] ?? '';
// Construit le menu latéral selon le rôle connecté afin de ne proposer que les rubriques pertinentes pour ce rôle.
const navItems = computed(() => {
    if (props.role === 'client') {
        return ['Vue d’ensemble', 'Mes véhicules', 'Historique d’interventions', 'Mon profil'];
    }

    if (props.role === 'mecanicien') {
        return ['Vue d’ensemble', 'Utilisateurs', 'Véhicules', 'Interventions', 'Mon profil'];
    }

    return ['Vue d’ensemble', 'Utilisateurs', 'Véhicules', 'Rendez-vous', 'Interventions', 'Pièces', 'Tarifs MO', 'Mon profil'];
});
const roleNouns: Record<string, string> = {
    client: 'client',
    mecanicien: 'mécanicien',
    administrateur: 'administrateur',
};
// Génère le titre selon l'action en cours et, pour une fiche, selon le rôle enregistré de l'utilisateur plutôt que le rôle modifiable du formulaire.
const pageTitle = computed(() => isEditing.value
    ? `Modifier une fiche ${roleNouns[props.user?.userRole ?? ''] ?? 'utilisateur'} :`
    : isViewing.value
        ? `Détails de la fiche ${roleNouns[props.user?.userRole ?? ''] ?? 'utilisateur'} :`
        : isProfile.value ? 'Modifier mon profil :' : 'Ajouter un utilisateur :');
// Choisit le titre de la fenêtre de confirmation selon qu'une nouvelle fiche vient d'être créée ou qu'une donnée existante a été modifiée.
const successTitle = computed(() => props.mode === 'create' ? 'Création validée' : 'Modifications validées');
// Fournit le texte de confirmation adapté à la création d'utilisateur, à la modification du profil personnel ou à la modification d'une autre fiche.
const successMessage = computed(() => {
    if (props.mode === 'create') return 'Le profil utilisateur a été créé avec succès.';
    if (isProfile.value) return 'Votre profil a été mis à jour avec succès.';

    return 'La fiche utilisateur a été modifiée avec succès.';
});
// Définit la destination du bouton de retour et de la fermeture de confirmation : vue d'ensemble pour un profil, liste des utilisateurs pour une fiche.
const returnHref = computed(() => isProfile.value ? '/dashboard' : '/utilisateurs');
const imageStyle = {
    '--garage-image': `url(${garageImage})`,
} as Record<string, string>;

// Envoie les données au point d'entrée correspondant au mode de page et n'affiche la confirmation qu'après une réponse serveur réussie.
function submit(): void {
    if (isViewing.value) return;

    if (isProfile.value) {
        form.put('/mon-profil', {
            // Après la mise à jour du profil personnel, ouvre la confirmation verte au lieu de l'afficher avant la réponse du serveur.
            onSuccess: () => successDialog.value?.showModal(),
        });
        return;
    }

    if (isEditing.value && props.user) {
        form.put(`/utilisateurs/${props.user.id}`, {
            // Ouvre la confirmation uniquement si la modification de cette fiche a été acceptée.
            onSuccess: () => successDialog.value?.showModal(),
        });
        return;
    }

    form.post('/utilisateurs', {
        // Ouvre la confirmation uniquement si la création de la nouvelle fiche a réussi.
        onSuccess: () => successDialog.value?.showModal(),
    });
}

// Ferme la confirmation après succès, puis renvoie l'utilisateur vers la page prévue pour son rôle et le mode traité.
function closeSuccessDialog(): void {
    successDialog.value?.close();

    if (
        (props.role === 'administrateur' && (isEditing.value || props.mode === 'create'))
        || (isProfile.value && ['client', 'mecanicien', 'administrateur'].includes(props.role))
    ) {
        router.visit(returnHref.value);
    }
}
</script>

<template>
    <Head :title="isViewing ? 'Détails utilisateur' : isProfile ? 'Mon profil' : isEditing ? 'Modifier un utilisateur' : 'Ajouter un utilisateur'" />
    <Toaster />

    <div class="user-form-shell" :style="imageStyle">
        <aside class="sidebar">
            <Link href="/dashboard" class="brand">
                <img :src="gearLogo" alt="" />
                <span>GarageManager</span>
            </Link>

            <nav aria-label="Navigation principale">
                <template v-for="item in navItems" :key="item">
                    <!-- Le lien conserve un accès direct au dashboard depuis les formulaires. -->
                    <Link v-if="item === 'Vue d’ensemble'" href="/dashboard" class="nav-item">
                        {{ item }}
                    </Link>
                    <Link v-else-if="item === 'Utilisateurs'" href="/utilisateurs" class="nav-item">
                        {{ item }}
                    </Link>
                    <Link
                        v-else-if="item === 'Mon profil' && ['client', 'mecanicien', 'administrateur'].includes(props.role)"
                        href="/mon-profil"
                        class="nav-item"
                        :class="{ active: isProfile }"
                        :aria-current="isProfile ? 'page' : undefined"
                    >
                        {{ item }}
                    </Link>
                    <span v-else class="nav-item" :class="{ active: isProfile && item === 'Mon profil' }">
                        {{ item }}
                    </span>
                </template>
            </nav>

            <div class="sidebar-footer">
                <span class="role-label">{{ roleLabel }}</span>
                <Link href="/logout" method="post" as="button" class="logout-button">
                    Déconnexion
                </Link>
            </div>
        </aside>

        <main class="form-main">
            <h1>{{ pageTitle }}</h1>

            <form class="user-form" @submit.prevent="submit">
                <div class="field-grid">
                    <label class="form-field">
                        <span>Prénom :</span>
                        <input v-model="form.firstName" autocomplete="given-name" :readonly="isViewing" required />
                        <small v-if="form.errors.firstName">{{ form.errors.firstName }}</small>
                    </label>
                    <label class="form-field">
                        <span>Nom :</span>
                        <input v-model="form.lastName" autocomplete="family-name" :readonly="isViewing" required />
                        <small v-if="form.errors.lastName">{{ form.errors.lastName }}</small>
                    </label>
                    <label class="form-field">
                        <span>Téléphone :</span>
                        <input v-model="form.telephone" type="tel" autocomplete="tel" :readonly="isViewing" />
                        <small v-if="form.errors.telephone">{{ form.errors.telephone }}</small>
                    </label>
                    <label class="form-field">
                        <span>Mail :</span>
                        <input v-model="form.email" type="email" autocomplete="email" placeholder="test@exemple.com" :readonly="isViewing" :required="props.mode === 'create'" />
                        <small v-if="form.errors.email">{{ form.errors.email }}</small>
                    </label>
                    <label class="form-field">
                        <span>Login :</span>
                        <input v-model="form.login" autocomplete="username" :readonly="isViewing" required />
                        <small v-if="form.errors.login">{{ form.errors.login }}</small>
                    </label>

                    <div class="section-label">Adresse :</div>
                    <label class="form-field">
                        <span>N° de rue :</span>
                        <input v-model="form.houseNumber" autocomplete="address-line1" :readonly="isViewing" required />
                        <small v-if="form.errors.houseNumber">{{ form.errors.houseNumber }}</small>
                    </label>
                    <label class="form-field">
                        <span>Nom de rue :</span>
                        <input v-model="form.streetName" autocomplete="address-line2" :readonly="isViewing" required />
                        <small v-if="form.errors.streetName">{{ form.errors.streetName }}</small>
                    </label>
                    <label class="form-field">
                        <span>Code postal :</span>
                        <input v-model="form.postalCode" inputmode="numeric" autocomplete="postal-code" :readonly="isViewing" required />
                        <small v-if="form.errors.postalCode">{{ form.errors.postalCode }}</small>
                    </label>
                    <label class="form-field">
                        <span>Ville :</span>
                        <input v-model="form.city" autocomplete="address-level2" :readonly="isViewing" required />
                        <small v-if="form.errors.city">{{ form.errors.city }}</small>
                    </label>
                    <label v-if="!isProfile" class="form-field">
                        <span>Rôle :</span>
                        <span v-if="isViewing" class="field-value">
                            {{ formRoleLabels[form.userRole] ?? form.userRole }}
                        </span>
                        <select v-else v-model="form.userRole" required>
                            <option value="client">Client</option>
                            <option value="mecanicien">Mécanicien</option>
                            <option value="administrateur">Administrateur</option>
                        </select>
                        <small v-if="form.errors.userRole">{{ form.errors.userRole }}</small>
                    </label>
                    <!-- Le mot de passe reste modifiable uniquement dans le profil personnel. -->
                    <label v-if="isProfile" class="form-field">
                        <span>Mot de passe (facultatif) :</span>
                        <input
                            v-model="form.password"
                            type="password"
                            autocomplete="new-password"
                        />
                        <small v-if="form.errors.password">{{ form.errors.password }}</small>
                    </label>
                    <label v-if="isProfile" class="form-field">
                        <span>Confirmer le mot de passe :</span>
                        <input v-model="form.password_confirmation" type="password" autocomplete="new-password" />
                        <small v-if="form.errors.password_confirmation">{{ form.errors.password_confirmation }}</small>
                    </label>
                </div>

                <div class="form-actions">
                    <button v-if="!isViewing" type="submit" class="form-button save-button" :disabled="form.processing">
                        <Save :size="16" aria-hidden="true" />
                        <span>{{ form.processing ? 'Enregistrement...' : 'Enregistrer' }}</span>
                    </button>
                    <Link :href="returnHref" class="form-button return-button">
                        <ArrowLeft :size="16" aria-hidden="true" />
                        <span>Retour</span>
                    </Link>
                </div>
            </form>
        </main>

        <dialog ref="successDialog" class="success-dialog" aria-labelledby="success-title">
            <div class="success-content">
                <span class="success-icon"><Check :size="24" aria-hidden="true" /></span>
                <h2 id="success-title">{{ successTitle }}</h2>
                <p>{{ successMessage }}</p>
                <button type="button" class="form-button success-close" autofocus @click="closeSuccessDialog">
                    Fermer
                </button>
            </div>
        </dialog>
    </div>
</template>

<style scoped>
.user-form-shell {
    display: flex;
    min-height: 100svh;
    background: #c9cdd1;
    color: #252a2e;
    font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
}

.sidebar {
    --blue-overlay: linear-gradient(rgb(210 225 237 / 88%), rgb(210 225 237 / 90%));
    position: sticky;
    top: 0;
    display: flex;
    width: 208px;
    height: 100svh;
    flex: 0 0 208px;
    flex-direction: column;
    background-image: var(--blue-overlay), var(--garage-image);
    background-position: center;
    background-size: cover;
}

.brand {
    display: flex;
    min-height: 54px;
    align-items: center;
    gap: 8px;
    padding: 0 15px;
    border-bottom: 1px solid rgb(69 92 108 / 18%);
    color: inherit;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
}

.brand img {
    width: 24px;
    height: 24px;
    object-fit: contain;
}

.sidebar nav {
    display: flex;
    flex-direction: column;
    gap: 2px;
    padding: 25px 10px;
}

.nav-item {
    padding: 9px 10px;
    color: #35434c;
    font-size: 12px;
    text-decoration: none;
}

.nav-item.active {
    background: rgb(255 255 255 / 62%);
    color: #152b3a;
    font-weight: 600;
}

.sidebar-footer {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-top: auto;
    padding: 14px 12px 18px;
    text-align: center;
    font-size: 11px;
}

.role-label {
    padding-bottom: 11px;
    border-bottom: 1px solid rgb(69 92 108 / 25%);
    font-size: 11px;
    font-weight: 600;
}

.logout-button {
    min-height: 32px;
    border: 1px solid rgb(69 92 108 / 30%);
    background: rgb(255 255 255 / 60%);
    color: #252a2e;
    cursor: pointer;
    font: inherit;
}

.form-main {
    width: min(100%, 1180px);
    margin: 0 auto;
    padding: 30px 40px 48px;
}

.form-main h1 {
    margin: 0 0 40px;
    text-align: center;
    font-size: 21px;
    font-weight: 600;
}

.user-form {
    display: flex;
    min-height: calc(100svh - 120px);
    flex-direction: column;
    justify-content: space-between;
}

.field-grid {
    display: grid;
    width: min(100%, 780px);
    grid-template-columns: repeat(2, minmax(0, 1fr));
    column-gap: 36px;
    row-gap: 20px;
    margin: 0 auto;
}

.form-field {
    display: flex;
    min-width: 0;
    flex-direction: column;
    gap: 6px;
    font-size: 13px;
}

.form-field input,
.form-field select,
.field-value {
    display: flex;
    width: 100%;
    height: 36px;
    align-items: center;
    padding: 0 10px;
    border: 1px solid transparent;
    border-radius: 0;
    background: #dfe1e3;
    color: #252a2e;
    font: inherit;
    font-size: 12px;
}

.form-field select {
    cursor: pointer;
}

.field-value {
    background: #dfe1e3;
}

.form-field input:focus,
.form-field select:focus {
    border-color: #3e657e;
    outline: 2px solid rgb(62 101 126 / 16%);
}

.form-field input::placeholder {
    color: #777d81;
    font-style: italic;
}

.form-field small {
    color: #a33a35;
    font-size: 11px;
}

.section-label {
    grid-column: 1 / -1;
    margin: 0 0 -12px;
    font-size: 13px;
}

.form-actions {
    display: flex;
    justify-content: center;
    gap: 60px;
    margin: 40px auto 0;
}

.form-button {
    display: inline-flex;
    min-width: 124px;
    min-height: 42px;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 0 16px;
    border: 0;
    border-radius: 2px;
    cursor: pointer;
    font: inherit;
    font-size: 13px;
    text-decoration: none;
    transition: background-color 140ms ease, transform 140ms ease;
}

.save-button {
    background: #3e657e;
    color: #fff;
}

.save-button:hover:not(:disabled) {
    background: #2f536a;
    transform: translateY(-1px);
}

.save-button:disabled {
    cursor: wait;
    opacity: 0.7;
}

.return-button {
    background: #dfe1e3;
    color: #252a2e;
}

.return-button:hover {
    background: #cdd2d5;
}

.form-button:focus-visible {
    outline: 2px solid #193b50;
    outline-offset: 3px;
}

.success-dialog {
    position: fixed;
    inset: 0;
    width: min(92vw, 420px);
    margin: auto;
    padding: 0;
    border: 1px solid #c5d8c8;
    border-radius: 5px;
    background: #fff;
    color: #252a2e;
    box-shadow: 0 18px 50px rgb(20 36 47 / 28%);
}

.edit-confirmation-dialog {
    position: fixed;
    inset: 0;
    width: min(92vw, 440px);
    margin: auto;
    padding: 0;
    border: 1px solid #b9c4ca;
    border-radius: 4px;
    background: #f7f8f8;
    color: #252a2e;
    box-shadow: 0 18px 50px rgb(20 36 47 / 28%);
}

.edit-confirmation-dialog::backdrop {
    background: rgb(20 31 38 / 48%);
    backdrop-filter: blur(2px);
}

.edit-confirmation-content {
    padding: 24px;
}

.edit-confirmation-content h2 {
    margin: 0 0 8px;
    font-size: 18px;
}

.edit-confirmation-content p {
    margin: 0;
    color: #53616a;
    font-size: 13px;
}

.edit-confirmation-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 22px;
}

.success-dialog::backdrop {
    background: rgb(20 31 38 / 48%);
    backdrop-filter: blur(2px);
}

.success-content {
    display: flex;
    align-items: center;
    flex-direction: column;
    padding: 30px 24px 24px;
    text-align: center;
}

.success-icon {
    display: grid;
    width: 48px;
    height: 48px;
    place-items: center;
    border-radius: 50%;
    background: #e5f4e8;
    color: #278348;
}

.success-content h2 {
    margin: 16px 0 6px;
    font-size: 18px;
}

.success-content p {
    margin: 0;
    color: #53616a;
    font-size: 13px;
}

.success-close {
    margin-top: 22px;
    background: #278348;
    color: #fff;
}

.success-close:hover {
    background: #1f6e3b;
}

@media (max-width: 760px) {
    .user-form-shell {
        flex-direction: column;
    }

    .sidebar {
        position: static;
        width: 100%;
        height: auto;
        flex: 0 0 auto;
    }

    .sidebar nav {
        flex-direction: row;
        gap: 3px;
        overflow-x: auto;
        padding: 8px 10px;
    }

    .nav-item {
        flex: 0 0 auto;
        padding: 8px;
        font-size: 11px;
    }

    .sidebar-footer {
        display: none;
    }

    .form-main {
        padding: 25px 20px 36px;
    }

    .form-main h1 {
        margin-bottom: 28px;
    }

    .user-form {
        min-height: auto;
    }

    .form-actions {
        gap: 16px;
        margin-top: 32px;
    }
}

@media (max-width: 520px) {
    .field-grid {
        grid-template-columns: minmax(0, 1fr);
        row-gap: 14px;
    }

    .section-label {
        grid-column: auto;
        margin: 10px 0 -6px;
    }

    .form-actions {
        justify-content: space-between;
    }
}
</style>