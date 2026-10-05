<script setup lang="ts">
import { computed, nextTick, ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Check, Pencil, Plus, Search, Trash2 } from '@lucide/vue';
import { Toaster } from '@/components/ui/sonner';
import garageImage from '../../assets/images/accueil.jpg';
import gearLogo from '../../assets/images/rouage.png';

type Appointment = {
    id: number;
    clientId: number;
    clientName: string;
    email: string | null;
    telephone: string | null;
    vehicleId: number;
    vehicle: string;
    registration: string;
    appointmentDate: string;
    dateLabel: string;
    reason: string;
};

type AppointmentClient = {
    id: number;
    name: string;
    vehicles: { id: number; label: string }[];
};

const props = defineProps<{
    role: string;
    appointments: Appointment[];
    appointmentClients: AppointmentClient[];
}>();

const search = ref('');
const appointmentDialog = ref<HTMLDialogElement | null>(null);
const detailsDialog = ref<HTMLDialogElement | null>(null);
const deleteDialog = ref<HTMLDialogElement | null>(null);
const successDialog = ref<HTMLDialogElement | null>(null);
const successMessage = ref('');
const appointmentBeingEdited = ref<Appointment | null>(null);
const appointmentToDelete = ref<Appointment | null>(null);
const appointmentForDetails = ref<Appointment | null>(null);
const deleteError = ref('');
const form = useForm({
    clientId: '',
    vehicleId: '',
    appointmentDate: '',
    reason: '',
    returnTo: 'appointments.index',
});

const navItems = ['Vue d’ensemble', 'Utilisateurs', 'Véhicules', 'Rendez-vous', 'Interventions', 'Pièces', 'Tarifs MO', 'Mon profil'];
const roleLabel = 'Profil Admin';
const appointmentVehicles = computed(() =>
    props.appointmentClients.find((client) => client.id === Number(form.clientId))?.vehicles ?? [],
);
const filteredAppointments = computed(() => {
    const query = search.value.trim().toLocaleLowerCase();

    if (!query) return props.appointments;

    return props.appointments.filter((appointment) => [
        appointment.clientName,
        appointment.email ?? '',
        appointment.telephone ?? '',
        appointment.vehicle,
        appointment.registration,
        appointment.dateLabel,
        appointment.reason,
    ].some((value) => value.toLocaleLowerCase().includes(query)));
});
const imageStyle = {
    '--garage-image': `url(${garageImage})`,
} as Record<string, string>;

function openCreateDialog(): void {
    appointmentBeingEdited.value = null;
    form.reset();
    form.clearErrors();
    void nextTick(() => appointmentDialog.value?.showModal());
}

function openEditDialog(appointment: Appointment): void {
    appointmentBeingEdited.value = appointment;
    form.clearErrors();
    form.clientId = String(appointment.clientId);
    form.vehicleId = String(appointment.vehicleId);
    form.appointmentDate = appointment.appointmentDate;
    form.reason = appointment.reason;
    void nextTick(() => appointmentDialog.value?.showModal());
}

function submitAppointment(): void {
    const isCreating = !appointmentBeingEdited.value;
    const options = {
        onSuccess: () => {
            appointmentDialog.value?.close();
            appointmentBeingEdited.value = null;
            form.reset();

            if (isCreating) {
                successMessage.value = 'Le rendez-vous a été ajouté avec succès.';
                successDialog.value?.showModal();
            } else {
                successMessage.value = 'Le rendez-vous a été modifié avec succès.';
                successDialog.value?.showModal();
            }
        },
    };

    if (appointmentBeingEdited.value) {
        form.put(`/rendez-vous/${appointmentBeingEdited.value.id}`, options);
        return;
    }

    form.post('/rendez-vous', options);
}

function openDetailsDialog(appointment: Appointment): void {
    appointmentForDetails.value = appointment;
    void nextTick(() => detailsDialog.value?.showModal());
}

function openDeleteDialog(appointment: Appointment): void {
    appointmentToDelete.value = appointment;
    deleteError.value = '';
    void nextTick(() => deleteDialog.value?.showModal());
}

function deleteAppointment(): void {
    const appointment = appointmentToDelete.value;

    if (!appointment) return;

    router.delete(`/rendez-vous/${appointment.id}`, {
        onSuccess: () => {
            deleteDialog.value?.close();
            appointmentToDelete.value = null;
            successMessage.value = 'Le rendez-vous a été supprimé avec succès.';
            successDialog.value?.showModal();
        },
        onError: (errors) => {
            deleteError.value = errors.appointment ?? 'La suppression du rendez-vous a échoué.';
        },
    });
}
</script>

<template>
    <Head title="Rendez-vous" />
    <Toaster />

    <div class="appointments-shell" :style="imageStyle">
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
                    <Link v-else-if="item === 'Rendez-vous'" href="/rendez-vous" class="nav-item active" aria-current="page">
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
                <Link href="/logout" method="post" as="button" class="logout-button">
                    Déconnexion
                </Link>
            </div>
        </aside>

        <main class="appointments-main">
            <header class="page-heading">
                <div>
                    <h1>Tableau de bord</h1>
                    <p>Rendez-vous</p>
                </div>
                <button type="button" class="action-button add-button" @click="openCreateDialog">
                    <Plus :size="15" aria-hidden="true" />
                    <span>Ajouter</span>
                </button>
            </header>

            <form class="search-form" @submit.prevent>
                <input v-model="search" type="search" placeholder="Barre de recherche..." aria-label="Rechercher un rendez-vous" />
                <button type="submit" class="action-button search-button">
                    <Search :size="15" aria-hidden="true" />
                    <span>Rechercher</span>
                </button>
            </form>

            <section class="appointment-list" aria-label="Liste des rendez-vous">
                <article v-for="appointment in filteredAppointments" :key="appointment.id" class="appointment-row">
                    <button
                        type="button"
                        class="appointment-card-link"
                        :aria-label="`Voir le rendez-vous de ${appointment.clientName} du ${appointment.dateLabel}`"
                        @click="openDetailsDialog(appointment)"
                    />
                    <div class="appointment-client">
                        <h2>{{ appointment.clientName }}</h2>
                        <p>
                            {{ appointment.email || 'Adresse mail non renseignée' }}<br />
                            {{ appointment.telephone || 'Numéro de téléphone non renseigné' }}<br />
                            {{ appointment.vehicle }} · {{ appointment.registration }}
                        </p>
                    </div>
                    <p class="appointment-date">Date du rendez-vous : {{ appointment.dateLabel }}</p>
                    <div class="row-actions">
                        <div class="row-action-buttons">
                            <button type="button" class="action-button" @click.stop="openEditDialog(appointment)">
                                <Pencil :size="15" aria-hidden="true" />
                                <span>Modifier</span>
                            </button>
                            <button type="button" class="action-button" @click.stop="openDeleteDialog(appointment)">
                                <Trash2 :size="15" aria-hidden="true" />
                                <span>Supprimer</span>
                            </button>
                        </div>
                        <span class="details-prompt">Cliquez pour voir le détail</span>
                    </div>
                </article>
                <p v-if="filteredAppointments.length === 0" class="empty-state">
                    {{ search.trim() ? 'Aucun rendez-vous trouvé.' : 'Aucun rendez-vous enregistré.' }}
                </p>
            </section>
        </main>

        <dialog ref="appointmentDialog" class="appointment-dialog" aria-labelledby="appointment-dialog-title">
            <form class="appointment-form" @submit.prevent="submitAppointment">
                <h2 id="appointment-dialog-title">{{ appointmentBeingEdited ? 'Modifier le rendez-vous' : 'Ajouter un rendez-vous' }}</h2>
                <label class="appointment-field">
                    <span>Client :</span>
                    <select v-model="form.clientId" required @change="form.vehicleId = ''">
                        <option value="" disabled>Sélectionner un client</option>
                        <option v-for="client in props.appointmentClients" :key="client.id" :value="String(client.id)">
                            {{ client.name }}
                        </option>
                    </select>
                    <small v-if="form.errors.clientId">{{ form.errors.clientId }}</small>
                </label>
                <label class="appointment-field">
                    <span>Véhicule :</span>
                    <select v-model="form.vehicleId" required :disabled="!form.clientId">
                        <option value="" disabled>Sélectionner un véhicule</option>
                        <option v-for="vehicle in appointmentVehicles" :key="vehicle.id" :value="String(vehicle.id)">
                            {{ vehicle.label }}
                        </option>
                    </select>
                    <small v-if="form.errors.vehicleId">{{ form.errors.vehicleId }}</small>
                </label>
                <label class="appointment-field">
                    <span>Date et heure :</span>
                    <input v-model="form.appointmentDate" type="datetime-local" required />
                    <small v-if="form.errors.appointmentDate">{{ form.errors.appointmentDate }}</small>
                </label>
                <label class="appointment-field">
                    <span>Motif :</span>
                    <input v-model="form.reason" maxlength="255" required />
                    <small v-if="form.errors.reason">{{ form.errors.reason }}</small>
                </label>
                <div v-if="props.appointmentClients.length === 0" class="appointment-empty">
                    Aucun client avec véhicule n’est disponible.
                </div>
                <div class="dialog-actions">
                    <button type="button" class="action-button secondary-button" @click="appointmentDialog?.close()">Annuler</button>
                    <button type="submit" class="action-button" :disabled="form.processing || props.appointmentClients.length === 0">
                        {{ form.processing ? 'Enregistrement...' : appointmentBeingEdited ? 'Enregistrer' : 'Ajouter' }}
                    </button>
                </div>
            </form>
        </dialog>

        <dialog ref="detailsDialog" class="confirmation-dialog" aria-labelledby="details-title">
            <div v-if="appointmentForDetails" class="confirmation-content">
                <h2 id="details-title">Détail du rendez-vous</h2>
                <p><strong>Client :</strong> {{ appointmentForDetails.clientName }}</p>
                <p><strong>Véhicule :</strong> {{ appointmentForDetails.vehicle }} · {{ appointmentForDetails.registration }}</p>
                <p><strong>Date :</strong> {{ appointmentForDetails.dateLabel }}</p>
                <p><strong>Motif :</strong> {{ appointmentForDetails.reason }}</p>
                <div class="dialog-actions">
                    <button type="button" class="action-button" @click="detailsDialog?.close()">Fermer</button>
                </div>
            </div>
        </dialog>

        <dialog ref="deleteDialog" class="confirmation-dialog" aria-labelledby="delete-title">
            <div class="confirmation-content">
                <h2 id="delete-title">Confirmer la suppression</h2>
                <p v-if="appointmentToDelete">
                    Supprimer le rendez-vous de <strong>{{ appointmentToDelete.clientName }}</strong> du {{ appointmentToDelete.dateLabel }} ?
                </p>
                <p v-if="deleteError" class="delete-error">{{ deleteError }}</p>
                <div class="dialog-actions">
                    <button type="button" class="action-button secondary-button" @click="deleteDialog?.close()">Annuler</button>
                    <button type="button" class="action-button danger-button" @click="deleteAppointment">
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
.appointments-shell {
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

.brand img { width: 24px; height: 24px; object-fit: contain; }

.sidebar nav { display: flex; flex-direction: column; gap: 2px; padding: 25px 10px; }

.nav-item {
    padding: 9px 10px;
    color: #35434c;
    font-size: 12px;
    text-decoration: none;
    transition: background-color 140ms ease, color 140ms ease;
}

.nav-item:hover,
.nav-item:focus-visible,
.nav-item.active { background: rgb(255 255 255 / 62%); color: #152b3a; }

.nav-item.active { font-weight: 600; }

.sidebar-footer { display: flex; flex-direction: column; gap: 10px; margin-top: auto; padding: 14px 12px 18px; text-align: center; font-size: 11px; }

.role-label { padding-bottom: 11px; border-bottom: 1px solid rgb(69 92 108 / 25%); font-size: 11px; font-weight: 600; }

.logout-button { min-height: 32px; border: 1px solid rgb(69 92 108 / 30%); background: rgb(255 255 255 / 60%); color: #252a2e; cursor: pointer; font: inherit; }

.appointments-main { width: min(100%, 1180px); margin: 0 auto; padding: 30px 34px 48px; }

.page-heading,
.search-form,
.appointment-row,
.row-actions { display: flex; align-items: center; justify-content: space-between; }

.page-heading { margin-bottom: 25px; }
.page-heading h1 { margin: 0; font-size: 23px; font-weight: 600; }
.page-heading p { margin: 3px 0 0; color: #68737b; font-size: 12px; }

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

.action-button:hover:not(:disabled) { background: #2f536a; box-shadow: 0 4px 9px rgb(34 57 71 / 24%); transform: translateY(-1px); }
.action-button:focus-visible { outline: 2px solid #193b50; outline-offset: 3px; }
.action-button:disabled { cursor: not-allowed; opacity: 0.55; }

.search-form { gap: 12px; margin-bottom: 28px; }
.search-form input { width: 100%; min-width: 0; height: 38px; padding: 0 11px; border: 0; background: #dfe1e3; color: inherit; font: inherit; font-size: 11px; }
.search-form input::placeholder { color: #777d81; font-style: italic; }
.search-button { min-width: 140px; }
.search-form .action-button { width: 166px; flex: 0 0 166px; }

.appointment-list { display: grid; gap: 20px; }

.appointment-row {
    position: relative;
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    min-height: 86px;
    gap: 12px;
    padding: 12px;
    background: #d8dadd;
}

.appointment-card-link { position: absolute; z-index: 1; inset: 0; width: 100%; height: 100%; border: 0; background: transparent; cursor: pointer; }
.appointment-card-link:focus-visible { outline: 2px solid #193b50; outline-offset: 3px; }
.appointment-row > :not(.appointment-card-link) { position: relative; z-index: 2; pointer-events: none; }
.appointment-client { min-width: 0; flex: 1 1 0; }
.appointment-client h2 { overflow-wrap: anywhere; margin: 0 0 2px; font-size: 14px; font-weight: 700; }
.appointment-client p { margin: 0; color: #68737b; font-size: 11px; font-style: italic; line-height: 1.25; }
.appointment-date { align-self: center; margin: 0; color: #53616a; font-size: 11px; text-align: center; }
.row-actions { display: flex; flex-direction: column; align-items: flex-end; justify-content: center; gap: 6px; pointer-events: none; }
.row-action-buttons { display: flex; gap: 10px; }
.row-action-buttons .action-button { min-height: 34px; padding: 0 10px; font-size: 10px; }
.row-action-buttons .action-button { pointer-events: auto; }
.details-prompt { color: #252a2e; font-size: 11px; font-style: italic; }
.empty-state { margin: 0; padding: 18px 12px; color: #68737b; font-size: 13px; }

.appointment-dialog,
.confirmation-dialog { position: fixed; inset: 0; width: min(92vw, 440px); max-height: min(90vh, 720px); margin: auto; padding: 0; overflow: auto; border: 1px solid #b9c4ca; border-radius: 4px; background: #fff; color: #252a2e; box-shadow: 0 18px 50px rgb(20 36 47 / 28%); }
.appointment-dialog::backdrop,
.confirmation-dialog::backdrop { background: rgb(20 31 38 / 48%); backdrop-filter: blur(2px); }
.appointment-form { display: grid; gap: 15px; padding: 24px; }
.appointment-form h2,
.confirmation-content h2 { margin: 0; font-size: 18px; }
.appointment-field { display: flex; min-width: 0; flex-direction: column; gap: 6px; font-size: 12px; }
.appointment-field input,
.appointment-field select { width: 100%; min-height: 38px; padding: 0 9px; border: 1px solid #b9c4ca; background: #fff; color: inherit; font: inherit; }
.appointment-field select { cursor: pointer; }
.appointment-field select:disabled { border-color: #d1d5d8; background: #e7e9ea; color: #858d92; cursor: not-allowed; }
.appointment-field small,
.delete-error { color: #a33a35; }
.appointment-empty { margin: 0; color: #68737b; font-size: 12px; }
.dialog-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 6px; }
.secondary-button { background: #737e85; }
.danger-button { background: #9f3935; }
.confirmation-content { display: grid; gap: 12px; padding: 24px; }
.confirmation-content p { margin: 0; color: #53616a; font-size: 13px; }
.success-dialog { position: fixed; inset: 0; width: min(92vw, 420px); margin: auto; padding: 0; border: 1px solid #c5d8c8; border-radius: 5px; background: #fff; color: #252a2e; box-shadow: 0 18px 50px rgb(20 36 47 / 28%); }
.success-dialog::backdrop { background: rgb(20 31 38 / 48%); backdrop-filter: blur(2px); }
.success-content { display: flex; align-items: center; flex-direction: column; padding: 30px 24px 24px; text-align: center; }
.success-icon { display: grid; width: 48px; height: 48px; place-items: center; border-radius: 50%; background: #e5f4e8; color: #278348; }
.success-content h2 { margin: 16px 0 6px; font-size: 18px; }
.success-content p { margin: 0; color: #53616a; font-size: 13px; }
.success-close { margin-top: 22px; background: #278348; }
.success-close:hover:not(:disabled) { background: #1f6e3b; }

@media (max-width: 760px) {
    .appointments-shell { display: block; }
    .sidebar { position: relative; width: 100%; height: auto; min-height: 0; }
    .sidebar nav { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); padding: 8px; }
    .sidebar-footer { display: none; }
    .appointments-main { padding: 22px 16px 36px; }
    .page-heading { gap: 12px; }
    .search-form { gap: 8px; }
    .search-form input { min-width: 0; }
    .search-button { min-width: 42px; padding: 0 10px; }
    .appointment-row { grid-template-columns: minmax(0, 1fr); gap: 10px; }
    .appointment-date { grid-row: 2; text-align: center; }
    .row-actions { grid-row: 3; align-items: flex-end; }
    .row-action-buttons { flex-wrap: wrap; justify-content: flex-end; }
}
</style>