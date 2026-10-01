<script setup lang="ts">
import { computed, nextTick, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Plus, Search, Trash2 } from '@lucide/vue';
import { Toaster } from '@/components/ui/sonner';
import garageImage from '../../assets/images/accueil.jpg';
import gearLogo from '../../assets/images/rouage.png';

type User = {
    id_utilisateur: number;
    nom_utilisateur: string;
    prenom_utilisateur: string;
    email_utilisateur: string | null;
    login_utilisateur: string;
    tel_utilisateur: string | null;
    role_utilisateur: string;
};

const props = defineProps<{
    role: string;
    users: User[];
}>();

const search = ref('');
const deleteDialog = ref<HTMLDialogElement | null>(null);
const deleteSuccessDialog = ref<HTMLDialogElement | null>(null);
const userToDelete = ref<User | null>(null);
const deletedUserName = ref('');
const isDeleting = ref(false);
const roleLabels: Record<string, string> = {
    client: 'Profil Client',
    mecanicien: 'Profil Mécanicien',
    administrateur: 'Profil Admin',
};
const userRoleLabels: Record<string, string> = {
    client: 'Client',
    mecanicien: 'Mécanicien',
    administrateur: 'Administrateur',
};
// Prépare les rubriques du menu latéral selon le rôle connecté, pour respecter les accès propres aux clients, mécaniciens et administrateurs.
const navItems = computed(() => {
    if (props.role === 'client') {
        // Mon profil reste non cliquable en attendant la création de sa page.
        return ['Vue d’ensemble', 'Mes véhicules', 'Historique d’interventions', 'Mon profil'];
    }

    if (props.role === 'mecanicien') {
        return ['Vue d’ensemble', 'Utilisateurs', 'Véhicules', 'Interventions', 'Mon profil'];
    }

    return ['Vue d’ensemble', 'Utilisateurs', 'Véhicules', 'Rendez-vous', 'Interventions', 'Pièces', 'Tarifs MO', 'Mon profil'];
});
const roleLabel = roleLabels[props.role] ?? '';

// Convertit le rôle enregistré en libellé lisible, en neutralisant les accents et la casse pour reconnaître les variantes de données.
function displayRole(role: string): string {
    const normalizedRole = role.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase();

    // Un seul libellé lisible est utilisé, même si la valeur stockée varie en casse ou en accents.
    return userRoleLabels[normalizedRole] ?? role.trim();
}

// Recalcule la liste affichée quand la recherche ou les utilisateurs changent, en comparant la requête à tous les champs visibles et identifiants.
const filteredUsers = computed(() => {
    const query = search.value.trim().toLocaleLowerCase();

    if (!query) return props.users;

    // Conserve chaque utilisateur pour lequel au moins une valeur recherchable contient le texte saisi.
    return props.users.filter((user) => [
        user.nom_utilisateur,
        user.prenom_utilisateur,
        user.email_utilisateur ?? '',
        user.login_utilisateur,
        user.tel_utilisateur ?? '',
        user.role_utilisateur,
    ].some((value) => value.toLocaleLowerCase().includes(query)));
});

// Mémorise l'utilisateur choisi puis ouvre la boîte de confirmation après la mise à jour des références Vue dans le DOM.
function requestDelete(user: User): void {
    userToDelete.value = user;
    // Attend le prochain cycle de rendu avant de demander au navigateur d'ouvrir le dialogue natif.
    void nextTick(() => deleteDialog.value?.showModal());
}

// Supprime l'utilisateur sélectionné, affiche ensuite le dialogue de succès et réinitialise l'état de chargement à la fin de la requête.
function deleteUser(): void {
    const user = userToDelete.value;

    if (!user) return;

    isDeleting.value = true;
    deletedUserName.value = `${user.prenom_utilisateur} ${user.nom_utilisateur}`;
    router.delete(`/utilisateurs/${user.id_utilisateur}`, {
        // Après confirmation serveur, ferme la demande de suppression et ouvre le dialogue de réussite.
        onSuccess: () => {
            deleteDialog.value?.close();
            deleteSuccessDialog.value?.showModal();
        },
        // Quel que soit le résultat de la requête, réactive les commandes de la page lorsque la visite Inertia se termine.
        onFinish: () => {
            isDeleting.value = false;
        },
    });
}

const imageStyle = {
    '--garage-image': `url(${garageImage})`,
} as Record<string, string>;
</script>

<template>
    <Head title="Utilisateurs" />
    <Toaster />

    <div class="users-shell" :style="imageStyle">
        <aside class="sidebar">
            <Link href="/dashboard" class="brand">
                <img :src="gearLogo" alt="" />
                <span>GarageManager</span>
            </Link>

            <nav aria-label="Navigation principale">
                <template v-for="item in navItems" :key="item">
                    <!-- Depuis les pages secondaires, ce lien revient à la vue d’ensemble. -->
                    <Link v-if="item === 'Vue d’ensemble'" href="/dashboard" class="nav-item">
                        {{ item }}
                    </Link>
                    <Link
                        v-else-if="item === 'Utilisateurs'"
                        href="/utilisateurs"
                        class="nav-item"
                        :class="{ active: item === 'Utilisateurs' }"
                    >
                        {{ item }}
                    </Link>
                    <Link
                        v-else-if="item === 'Mon profil' && ['client', 'mecanicien', 'administrateur'].includes(props.role)"
                        href="/mon-profil"
                        class="nav-item"
                    >
                        {{ item }}
                    </Link>
                    <span v-else class="nav-item">
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

        <main class="users-main">
            <header class="page-heading">
                <div>
                    <h1>Tableau de bord</h1>
                    <p>Utilisateurs</p>
                </div>
                <Link
                    v-if="props.role === 'administrateur'"
                    href="/utilisateurs/create"
                    class="action-button add-button"
                >
                    <Plus :size="15" aria-hidden="true" />
                    <span>AJOUTER</span>
                </Link>
            </header>

            <form class="search-form" @submit.prevent>
                <input v-model="search" type="search" placeholder="Barre de recherche..." aria-label="Rechercher un utilisateur" />
                <button type="submit" class="action-button">
                    <Search :size="15" aria-hidden="true" />
                    <span>Rechercher</span>
                </button>
            </form>

            <section class="user-list" aria-label="Liste des utilisateurs">
                <article v-for="user in filteredUsers" :key="user.id_utilisateur" class="user-row">
                    <!-- Le lien couvre la fiche; les contrôles au premier plan gardent leur action. -->
                    <Link
                        v-if="['administrateur', 'mecanicien'].includes(props.role)"
                        :href="`/utilisateurs/${user.id_utilisateur}`"
                        class="user-card-link"
                        :aria-label="`Voir les détails de ${user.prenom_utilisateur} ${user.nom_utilisateur}`"
                    />
                    <div class="user-identity">
                        <h2>{{ user.prenom_utilisateur }} {{ user.nom_utilisateur }}</h2>
                        <p>
                            {{ user.email_utilisateur || 'E-mail non renseigné' }}<br />
                            {{ user.tel_utilisateur || 'Téléphone non renseigné' }}
                        </p>
                    </div>
                    <span class="user-role">{{ displayRole(user.role_utilisateur) }}</span>
                    <div v-if="['administrateur', 'mecanicien'].includes(props.role)" class="row-actions">
                        <div v-if="props.role === 'administrateur'" class="row-action-buttons">
                            <Link :href="`/utilisateurs/${user.id_utilisateur}/edit`" class="action-button">
                                <Pencil :size="15" aria-hidden="true" />
                                <span>Modifier</span>
                            </Link>
                            <button type="button" class="action-button" @click="requestDelete(user)">
                                <Trash2 :size="15" aria-hidden="true" />
                                <span>Supprimer</span>
                            </button>
                        </div>
                        <Link :href="`/utilisateurs/${user.id_utilisateur}`" class="details-prompt">
                            Cliquez pour voir le détail
                        </Link>
                    </div>
                </article>
                <p v-if="filteredUsers.length === 0" class="empty-state">Aucun utilisateur trouvé.</p>
            </section>
        </main>

        <dialog
            ref="deleteDialog"
            class="confirmation-dialog"
            aria-labelledby="delete-title"
            aria-describedby="delete-description"
            @close="userToDelete = null"
        >
            <div class="confirmation-content">
                <h2 id="delete-title">Confirmer la suppression</h2>
                <p id="delete-description">
                    Voulez-vous supprimer le compte de
                    <strong v-if="userToDelete">{{ userToDelete.prenom_utilisateur }} {{ userToDelete.nom_utilisateur }}</strong> ?
                    Cette action est définitive.
                </p>
                <div class="dialog-actions">
                    <form method="dialog">
                        <button type="submit" class="action-button">Annuler</button>
                    </form>
                    <button type="button" class="action-button" :disabled="isDeleting" @click="deleteUser">
                        <Trash2 :size="15" aria-hidden="true" />
                        <span>{{ isDeleting ? 'Suppression...' : 'Supprimer' }}</span>
                    </button>
                </div>
            </div>
        </dialog>

        <dialog ref="deleteSuccessDialog" class="confirmation-dialog" aria-labelledby="delete-success-title">
            <div class="confirmation-content">
                <h2 id="delete-success-title">Suppression confirmée</h2>
                <p>Le compte de <strong>{{ deletedUserName }}</strong> a bien été supprimé.</p>
                <div class="dialog-actions">
                    <button type="button" class="action-button" @click="deleteSuccessDialog?.close()">Fermer</button>
                </div>
            </div>
        </dialog>
    </div>
</template>

<style scoped>
.users-shell {
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

.users-main {
    width: min(100%, 1180px);
    margin: 0 auto;
    padding: 30px 34px 48px;
}

.page-heading,
.search-form,
.user-row,
.row-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.page-heading {
    margin-bottom: 25px;
}

.page-heading h1 {
    margin: 0;
    font-size: 23px;
    font-weight: 600;
}

.page-heading p {
    margin: 3px 0 0;
    color: #68737b;
    font-size: 12px;
}

.action-button {
    display: inline-flex;
    min-height: 42px;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 0 16px;
    border: 0;
    border-radius: 3px;
    background: #3e657e;
    box-shadow: 0 2px 5px rgb(34 57 71 / 18%);
    color: #fff;
    cursor: pointer;
    font: inherit;
    font-size: 11px;
    font-weight: 600;
    text-decoration: none;
    transition: background-color 140ms ease, box-shadow 140ms ease, transform 140ms ease;
}

.action-button:hover:not(:disabled) {
    background: #2f536a;
    box-shadow: 0 4px 9px rgb(34 57 71 / 24%);
    transform: translateY(-1px);
}

.action-button:focus-visible {
    outline: 2px solid #193b50;
    outline-offset: 3px;
}

.action-button:active:not(:disabled) {
    box-shadow: 0 1px 3px rgb(34 57 71 / 18%);
    transform: translateY(0);
}

.action-button:disabled {
    cursor: wait;
    opacity: 0.7;
}

.action-button span {
    line-height: 1;
}

.add-button {
    padding: 0 20px;
}

.search-form {
    gap: 12px;
    margin-bottom: 28px;
}

.search-form input {
    width: 100%;
    min-width: 0;
    height: 38px;
    padding: 0 11px;
    border: 0;
    background: #dfe1e3;
    color: inherit;
    font: inherit;
    font-size: 11px;
}

.search-form input::placeholder {
    color: #777d81;
    font-style: italic;
}

.search-form .action-button {
    width: 166px;
    flex: 0 0 166px;
}

.user-list {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.user-row {
    position: relative;
    min-height: 84px;
    gap: 12px;
    padding: 11px 14px;
    background: #dfe1e3;
    cursor: pointer;
}

.user-card-link {
    /* Le lien couvre l’encadré sans recouvrir les actions placées devant lui. */
    position: absolute;
    z-index: 1;
    inset: 0;
}

.user-card-link:focus-visible {
    outline: 2px solid #193b50;
    outline-offset: 3px;
}

.user-row > :not(.user-card-link) {
    position: relative;
    z-index: 2;
    pointer-events: none;
}

.user-identity {
    min-width: 0;
    flex: 1 1 36%;
}

.user-identity h2 {
    margin: 0 0 5px;
    font-size: 16px;
    font-weight: 700;
}

.user-identity p {
    margin: 0;
    overflow-wrap: anywhere;
    color: #68737b;
    font-size: 11px;
    font-style: italic;
}

.user-role {
    flex: 0 1 16%;
    font-size: 14px;
}

.row-actions {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    align-self: flex-end;
    justify-content: flex-end;
    gap: 5px;
    pointer-events: none;
}

.row-action-buttons {
    display: flex;
    gap: 24px;
}

.details-prompt {
    align-self: flex-end;
    color: inherit;
    font-size: 9px;
    font-style: italic;
    text-decoration: none;
    white-space: nowrap;
    pointer-events: auto;
}

.row-actions .action-button {
    pointer-events: auto;
}

.details-prompt:hover {
    text-decoration: underline;
    text-underline-offset: 2px;
}

.row-actions .action-button {
    min-width: 94px;
    padding: 0 12px;
}

.confirmation-dialog {
    /* Le dialogue natif est positionné au centre du viewport, indépendamment du contenu de la page. */
    position: fixed;
    inset: 0;
    margin: auto;
    width: min(92vw, 440px);
    padding: 0;
    border: 1px solid #b9c4ca;
    border-radius: 4px;
    background: #f7f8f8;
    color: #252a2e;
    box-shadow: 0 18px 50px rgb(20 36 47 / 28%);
}

.confirmation-dialog::backdrop {
    background: rgb(20 31 38 / 48%);
    backdrop-filter: blur(2px);
}

.confirmation-content {
    padding: 24px;
}

.confirmation-content h2 {
    margin: 0 0 10px;
    font-size: 18px;
}

.confirmation-content p {
    margin: 0;
    color: #53616a;
    font-size: 13px;
    line-height: 1.6;
}

.dialog-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 24px;
}

.empty-state {
    margin: 0;
    padding: 18px 14px;
    background: #dfe1e3;
    color: #68737b;
    font-size: 12px;
}

@media (max-width: 760px) {
    .users-shell {
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

    .users-main {
        padding: 24px 18px 36px;
    }

    .user-row {
        align-items: flex-start;
        flex-wrap: wrap;
    }

    .row-actions {
        width: 100%;
        gap: 10px;
    }

    .row-action-buttons {
        width: 100%;
        justify-content: flex-end;
        gap: 10px;
    }

    .row-action-buttons .action-button {
        flex: 1;
    }
}

@media (max-width: 480px) {
    .search-form {
        align-items: stretch;
        flex-direction: column;
    }

    .search-form .action-button {
        width: 100%;
        flex-basis: 38px;
    }

    .row-action-buttons .action-button {
        flex: 1;
    }

    .user-role {
        flex-basis: auto;
    }
}
</style>