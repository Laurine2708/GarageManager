<script setup lang="ts">
/**
 * Présente les véhicules accessibles au rôle courant et leurs actions.
 * @prop role Rôle de l’utilisateur connecté.
 * @prop vehicles Véhicules affichés.
 * @prop clients Clients disponibles pour l’attribution.
 */
import { computed, nextTick, ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Check, Pencil, Plus, Search, Trash2 } from '@lucide/vue';
import { Toaster } from '@/components/ui/sonner';
import garageImage from '../../assets/images/accueil.jpg';
import gearLogo from '../../assets/images/rouage.png';

type Vehicle = {
    id: number;
    brand: string;
    model: string;
    registration: string;
    year: string;
    firstRegistration: string;
    engine: string;
    vin: string;
    engineCode: string;
    owners: { id: number; name: string }[];
};

const props = defineProps<{
    role: string;
    vehicles: Vehicle[];
    clients: { id: number; name: string }[];
}>();

const search = ref('');
const formDialog = ref<HTMLDialogElement | null>(null);
const deleteDialog = ref<HTMLDialogElement | null>(null);
const successDialog = ref<HTMLDialogElement | null>(null);
const successMessage = ref('');
const editingVehicle = ref<Vehicle | null>(null);
const vehicleToDelete = ref<Vehicle | null>(null);
const deleteError = ref('');
const form = useForm({
    brand: '',
    model: '',
    registration: '',
    firstRegistration: '',
    engine: '',
    vin: '',
    engineCode: '',
    ownerId: '',
});

const roleLabels: Record<string, string> = {
    client: 'Profil Client',
    mecanicien: 'Profil Mécanicien',
    administrateur: 'Profil Admin',
};
/** Rubriques de navigation autorisées pour le rôle courant. */
const navItems = computed(() => props.role === 'client'
    ? ['Vue d’ensemble', 'Mes véhicules', 'Historique d’interventions', 'Mon profil']
    : props.role === 'mecanicien'
        ? ['Vue d’ensemble', 'Utilisateurs', 'Véhicules', 'Interventions', 'Mon profil']
        : ['Vue d’ensemble', 'Utilisateurs', 'Véhicules', 'Rendez-vous', 'Interventions', 'Pièces', 'Tarifs MO', 'Mon profil']);
const roleLabel = roleLabels[props.role] ?? '';
/** Filtre les véhicules sur leurs caractéristiques et les noms de propriétaires. */
const filteredVehicles = computed(() => {
    const query = search.value.trim().toLocaleLowerCase();

    if (!query) return props.vehicles;

    return props.vehicles.filter((vehicle) => [
        vehicle.brand,
        vehicle.model,
        vehicle.registration,
        vehicle.year,
        vehicle.vin,
        ...vehicle.owners.map((owner) => owner.name),
    ].some((value) => value.toLocaleLowerCase().includes(query)));
});
const imageStyle = {
    '--garage-image': `url(${garageImage})`,
} as Record<string, string>;

/** Réinitialise et ouvre le formulaire de création d’un véhicule. */
function openCreateDialog(): void {
    editingVehicle.value = null;
    form.reset();
    form.clearErrors();
    void nextTick(() => formDialog.value?.showModal());
}

/** Crée ou met à jour le véhicule en cours d’édition. */
function submitVehicle(): void {
    const isCreating = !editingVehicle.value;
    const options = {
        onSuccess: () => {
            formDialog.value?.close();
            editingVehicle.value = null;
            form.reset();
            if (isCreating) {
                successMessage.value = 'Le véhicule a été ajouté avec succès.';
                successDialog.value?.showModal();
            }
        },
    };

    if (editingVehicle.value) {
        form.put(`/vehicules/${editingVehicle.value.id}`, options);
        return;
    }

    form.post('/vehicules', options);
}

/** Sélectionne un véhicule et ouvre sa confirmation de suppression. */
function openDeleteDialog(vehicle: Vehicle): void {
    vehicleToDelete.value = vehicle;
    deleteError.value = '';
    void nextTick(() => deleteDialog.value?.showModal());
}

/** Supprime le véhicule sélectionné et présente le résultat. */
function deleteVehicle(): void {
    const vehicle = vehicleToDelete.value;

    if (!vehicle) return;

    router.delete(`/vehicules/${vehicle.id}`, {
        onSuccess: () => {
            deleteDialog.value?.close();
            vehicleToDelete.value = null;
            successMessage.value = 'Le véhicule a été supprimé avec succès.';
            successDialog.value?.showModal();
        },
        onError: (errors) => {
            deleteError.value = errors.vehicle ?? 'La suppression du véhicule a échoué.';
        },
    });
}
</script>

<template>
    <Head title="Véhicules" />
    <Toaster />

    <div class="vehicles-shell" :style="imageStyle">
        <aside class="sidebar">
            <Link href="/dashboard" class="brand">
                <img :src="gearLogo" alt="" />
                <span>GarageManager</span>
            </Link>

            <nav aria-label="Navigation principale">
                <template v-for="item in navItems" :key="item">
                    <Link v-if="item === 'Vue d’ensemble'" href="/dashboard" class="nav-item">
                        {{ item }}
                    </Link>
                    <Link v-else-if="item === 'Utilisateurs'" href="/utilisateurs" class="nav-item">
                        {{ item }}
                    </Link>
                    <Link
                        v-else-if="item === 'Véhicules' || item === 'Mes véhicules'"
                        href="/vehicules"
                        class="nav-item active"
                        aria-current="page"
                    >
                        {{ item }}
                    </Link>
                    <Link v-else-if="item === 'Rendez-vous'" href="/rendez-vous" class="nav-item">
                        {{ item }}
                    </Link>
                    <Link v-else-if="item === 'Mon profil'" href="/mon-profil" class="nav-item">
                        {{ item }}
                    </Link>
                    <span v-else class="nav-item">{{ item }}</span>
                </template>
            </nav>

            <div class="sidebar-footer">
                <span class="role-label">{{ roleLabel }}</span>
                <Link href="/logout" method="post" as="button" class="logout-button">Déconnexion</Link>
            </div>
        </aside>

        <main class="vehicles-main">
            <header class="page-heading">
                <div>
                    <h1>Tableau de bord</h1>
                    <p>Véhicules</p>
                </div>
                <button v-if="props.role === 'administrateur'" type="button" class="action-button add-button" @click="openCreateDialog">
                    <Plus :size="15" aria-hidden="true" />
                    <span>Ajouter</span>
                </button>
            </header>

            <form class="search-form" @submit.prevent>
                <input v-model="search" type="search" placeholder="Barre de recherche..." aria-label="Rechercher un véhicule" />
                <button type="submit" class="action-button">
                    <Search :size="15" aria-hidden="true" />
                    <span>Rechercher</span>
                </button>
            </form>

            <section class="vehicle-list" aria-label="Liste des véhicules">
                <article
                    v-for="vehicle in filteredVehicles"
                    :key="vehicle.id"
                    class="vehicle-row"
                    tabindex="0"
                    @click="router.visit(`/vehicules/${vehicle.id}`)"
                    @keydown.enter.self="router.visit(`/vehicules/${vehicle.id}`)"
                >
                    <div class="vehicle-description">
                        <h2>{{ vehicle.brand }} {{ vehicle.model }}</h2>
                        <p>
                            Année : {{ vehicle.year }}<br />
                            Immatriculation : {{ vehicle.registration }}<br />
                            VIN : {{ vehicle.vin }}
                        </p>
                    </div>
                    <p class="vehicle-owner">
                        {{ vehicle.owners.length ? vehicle.owners.map((owner) => owner.name).join(', ') : 'Propriétaire non renseigné' }}
                    </p>
                    <div v-if="props.role === 'administrateur'" class="vehicle-actions" @click.stop>
                        <Link :href="`/vehicules/${vehicle.id}/edit`" class="action-button">
                            <Pencil :size="14" aria-hidden="true" />
                            <span>Modifier</span>
                        </Link>
                        <button type="button" class="action-button" @click="openDeleteDialog(vehicle)">
                            <Trash2 :size="14" aria-hidden="true" />
                            <span>Supprimer</span>
                        </button>
                    </div>
                    <span class="details-prompt">Voir le détail</span>
                </article>
                <p v-if="!filteredVehicles.length" class="empty-state">
                    {{ search ? 'Aucun véhicule ne correspond à votre recherche.' : 'Aucun véhicule enregistré.' }}
                </p>
            </section>
        </main>

        <dialog ref="formDialog" class="vehicle-dialog">
            <form class="dialog-content" @submit.prevent="submitVehicle">
                <h2>{{ editingVehicle ? 'Modifier un véhicule' : 'Ajouter un véhicule' }}</h2>
                <div class="vehicle-form-grid">
                    <label class="form-field">
                        <span>Marque</span>
                        <input v-model="form.brand" required maxlength="50" />
                        <small v-if="form.errors.brand">{{ form.errors.brand }}</small>
                    </label>
                    <label class="form-field">
                        <span>Modèle</span>
                        <input v-model="form.model" required maxlength="50" />
                        <small v-if="form.errors.model">{{ form.errors.model }}</small>
                    </label>
                    <label class="form-field">
                        <span>Immatriculation</span>
                        <input v-model="form.registration" required maxlength="50" />
                        <small v-if="form.errors.registration">{{ form.errors.registration }}</small>
                    </label>
                    <label class="form-field">
                        <span>Année de mise en circulation</span>
                        <input v-model="form.firstRegistration" type="date" required />
                        <small v-if="form.errors.firstRegistration">{{ form.errors.firstRegistration }}</small>
                    </label>
                    <label class="form-field">
                        <span>Motorisation</span>
                        <input v-model="form.engine" required maxlength="50" />
                        <small v-if="form.errors.engine">{{ form.errors.engine }}</small>
                    </label>
                    <label class="form-field">
                        <span>VIN</span>
                        <input v-model="form.vin" required maxlength="255" />
                        <small v-if="form.errors.vin">{{ form.errors.vin }}</small>
                    </label>
                    <label class="form-field">
                        <span>Code moteur</span>
                        <input v-model="form.engineCode" required maxlength="50" />
                        <small v-if="form.errors.engineCode">{{ form.errors.engineCode }}</small>
                    </label>
                    <label v-if="!editingVehicle" class="form-field">
                        <span>Propriétaire</span>
                        <select v-model="form.ownerId" required>
                            <option value="" disabled>Sélectionner un client</option>
                            <option v-for="client in props.clients" :key="client.id" :value="String(client.id)">
                                {{ client.name }}
                            </option>
                        </select>
                        <small v-if="form.errors.ownerId">{{ form.errors.ownerId }}</small>
                    </label>
                </div>
                <div class="dialog-actions">
                    <button type="button" class="action-button" @click="formDialog?.close()">Annuler</button>
                    <button type="submit" class="action-button primary-button" :disabled="form.processing">
                        {{ form.processing ? 'Enregistrement...' : 'Enregistrer' }}
                    </button>
                </div>
            </form>
        </dialog>

        <dialog
            ref="deleteDialog"
            class="confirmation-dialog"
            aria-labelledby="delete-title"
            aria-describedby="delete-description"
            @close="vehicleToDelete = null"
        >
            <div class="confirmation-content">
                <h2 id="delete-title">Supprimer le véhicule</h2>
                <p id="delete-description">
                    Voulez-vous supprimer
                    <strong v-if="vehicleToDelete">{{ vehicleToDelete.brand }} {{ vehicleToDelete.model }}</strong> ?
                    Cette action est définitive.
                </p>
                <p v-if="deleteError" class="form-error">{{ deleteError }}</p>
                <div class="dialog-actions">
                    <form method="dialog">
                        <button type="submit" class="action-button">Annuler</button>
                    </form>
                    <button type="button" class="action-button danger-button" @click="deleteVehicle">
                        <Trash2 :size="15" aria-hidden="true" />
                        <span>Supprimer</span>
                    </button>
                </div>
            </div>
        </dialog>

        <dialog ref="successDialog" class="success-dialog" aria-labelledby="success-title">
            <div class="success-content">
                <span class="success-icon"><Check :size="24" aria-hidden="true" /></span>
                <h2 id="success-title">Confirmation</h2>
                <p>{{ successMessage }}</p>
                <button type="button" class="action-button success-close" autofocus @click="successDialog?.close()">
                    Fermer
                </button>
            </div>
        </dialog>
    </div>
</template>

<style scoped>
.vehicles-shell {
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
    transition: background-color 140ms ease, color 140ms ease;
}

.nav-item:hover,
.nav-item:focus-visible {
    background: rgb(255 255 255 / 62%);
    color: #152b3a;
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

.vehicles-main {
    width: min(100%, 1180px);
    margin: 0 auto;
    padding: 30px 34px 48px;
}

.page-heading,
.search-form {
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

.vehicle-list {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.vehicle-row {
    position: relative;
    display: grid;
    min-height: 60px;
    grid-template-columns: minmax(0, 1fr) minmax(120px, .8fr) auto;
    grid-template-rows: 1fr auto;
    align-items: center;
    gap: 4px 12px;
    padding: 5px 14px;
    background: #dfe1e3;
    cursor: pointer;
}

.vehicle-row:focus-visible {
    outline: 2px solid #193b50;
    outline-offset: 3px;
}

.vehicle-description h2 {
    margin: 0 0 3px;
    font-size: 15px;
    font-weight: 700;
}

.vehicle-description p {
    margin: 0;
    color: #68737b;
    font-size: 10px;
    font-style: italic;
    line-height: 1.3;
}

.vehicle-owner {
    margin: 0;
    font-size: 14px;
}

.vehicle-actions {
    display: flex;
    align-items: center;
    gap: 24px;
}

.vehicle-actions .action-button {
    min-width: 94px;
    padding: 0 12px;
}

.details-prompt {
    grid-column: 2 / 4;
    justify-self: end;
    color: inherit;
    font-size: 9px;
    font-style: italic;
    text-decoration: none;
    white-space: nowrap;
}

.empty-state {
    margin: 0;
    padding: 18px 14px;
    background: #dfe1e3;
    color: #68737b;
    font-size: 12px;
}

.vehicle-dialog {
    position: fixed;
    inset: 0;
    margin: auto;
    width: min(92vw, 700px);
    max-height: 90vh;
    padding: 0;
    border: 1px solid #b9c4ca;
    border-radius: 4px;
    background: #f7f8f8;
    color: #252a2e;
    box-shadow: 0 18px 50px rgb(20 36 47 / 28%);
}

.vehicle-dialog::backdrop {
    background: rgb(20 31 38 / 48%);
    backdrop-filter: blur(2px);
}

.confirmation-dialog {
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

.confirmation-content .form-error {
    color: #a22;
}

.danger-button {
    background: #9f3935;
}

.danger-button:hover:not(:disabled) {
    background: #812e2a;
}

.success-dialog {
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
}

.success-close:hover:not(:disabled) {
    background: #1f6e3b;
}

.dialog-content {
    padding: 24px;
}

.dialog-content h2 {
    margin: 0 0 20px;
    font-size: 19px;
}

.vehicle-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px 18px;
}

.form-field {
    display: flex;
    min-width: 0;
    flex-direction: column;
    gap: 6px;
    font-size: 12px;
}

.form-field input,
.form-field select {
    height: 36px;
    padding: 0 9px;
    border: 1px solid #c6c8ca;
    background: #fff;
    color: inherit;
    font: inherit;
}

.form-field small,
.form-error {
    color: #a22;
    font-size: 11px;
}

.dialog-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 22px;
}

.confirmation-content .dialog-actions {
    margin-top: 24px;
}

@media (max-width: 760px) {
    .vehicles-shell {
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

    .vehicles-main {
        padding: 24px 18px 36px;
    }
}

@media (max-width: 560px) {
    .vehicle-row {
        grid-template-columns: minmax(0, 1fr) auto;
    }

    .vehicle-owner {
        grid-column: 1;
    }

    .vehicle-actions {
        grid-column: 1 / 3;
        justify-content: flex-start;
    }

    .details-prompt {
        grid-column: 1 / 3;
    }

    .vehicle-form-grid {
        grid-template-columns: minmax(0, 1fr);
    }
}
</style>
