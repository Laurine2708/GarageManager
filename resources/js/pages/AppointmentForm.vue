<script setup lang="ts">
/**
 * Formulaire de création, consultation et modification d'un rendez-vous.
 */
import { computed, ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Check, Save } from '@lucide/vue';
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
    mode: 'create' | 'edit' | 'view';
    appointment: Appointment | null;
    appointmentClients: AppointmentClient[];
}>();

const isViewing = props.mode === 'view';
const isCreating = props.mode === 'create';
const form = useForm({
    clientId: props.appointment ? String(props.appointment.clientId) : '',
    vehicleId: props.appointment ? String(props.appointment.vehicleId) : '',
    appointmentDate: props.appointment?.appointmentDate ?? '',
    reason: props.appointment?.reason ?? '',
    returnTo: isCreating ? 'appointments.create' : 'appointments.edit',
});
const successDialog = ref<HTMLDialogElement | null>(null);
const appointmentVehicles = computed(() =>
    props.appointmentClients.find((client) => client.id === Number(form.clientId))?.vehicles ?? [],
);
const pageTitle = isViewing ? 'Consulter un rendez-vous' : isCreating ? 'Ajouter un rendez-vous' : 'Modifier un rendez-vous';
const imageStyle = {
    '--garage-image': `url(${garageImage})`,
} as Record<string, string>;

/** Enregistre le rendez-vous et affiche une confirmation après la réponse serveur. */
function submit(): void {
    if (isViewing) return;

    const options = {
        onSuccess: () => successDialog.value?.showModal(),
    };

    if (props.appointment) {
        form.put(`/rendez-vous/${props.appointment.id}`, options);
        return;
    }

    form.post('/rendez-vous', options);
}

/** Ferme la confirmation et retourne à la liste des rendez-vous. */
function closeSuccessDialog(): void {
    successDialog.value?.close();
    router.visit('/rendez-vous');
}
</script>

<template>
    <Head :title="pageTitle" />
    <Toaster />

    <div class="appointment-form-shell" :style="imageStyle">
        <aside class="sidebar">
            <Link href="/dashboard" class="brand">
                <img :src="gearLogo" alt="" />
                <span>GarageManager</span>
            </Link>

            <nav aria-label="Navigation principale">
                <Link href="/dashboard" class="nav-item">Vue d’ensemble</Link>
                <Link href="/utilisateurs" class="nav-item">Utilisateurs</Link>
                <Link href="/vehicules" class="nav-item">Véhicules</Link>
                <Link href="/rendez-vous" class="nav-item active" aria-current="page">Rendez-vous</Link>
                <span class="nav-item">Interventions</span>
                <span class="nav-item">Pièces</span>
                <span class="nav-item">Tarifs MO</span>
                <Link href="/mon-profil" class="nav-item">Mon profil</Link>
            </nav>

            <div class="sidebar-footer">
                <span class="role-label">{{ props.role === 'administrateur' ? 'Profil Admin' : props.role }}</span>
                <Link href="/logout" method="post" as="button" class="logout-button">Déconnexion</Link>
            </div>
        </aside>

        <main class="form-main">
            <h1>{{ pageTitle }} :</h1>

            <form class="appointment-form" @submit.prevent="submit">
                <fieldset class="field-grid" :disabled="isViewing">
                    <label class="form-field">
                        <span>Client :</span>
                        <select v-if="!isViewing" v-model="form.clientId" required @change="form.vehicleId = ''">
                            <option value="" disabled>Sélectionner un client</option>
                            <option v-for="client in props.appointmentClients" :key="client.id" :value="String(client.id)">
                                {{ client.name }}
                            </option>
                        </select>
                        <span v-else class="field-value">{{ props.appointment?.clientName }}</span>
                        <small v-if="form.errors.clientId">{{ form.errors.clientId }}</small>
                    </label>
                    <label class="form-field">
                        <span>Véhicule :</span>
                        <select v-if="!isViewing" v-model="form.vehicleId" required :disabled="!form.clientId">
                            <option value="" disabled>Sélectionner un véhicule</option>
                            <option v-for="vehicle in appointmentVehicles" :key="vehicle.id" :value="String(vehicle.id)">
                                {{ vehicle.label }}
                            </option>
                        </select>
                        <span v-else class="field-value">
                            {{ props.appointment?.vehicle }} - {{ props.appointment?.registration }}
                        </span>
                        <small v-if="form.errors.vehicleId">{{ form.errors.vehicleId }}</small>
                    </label>
                    <label class="form-field">
                        <span>Date et heure :</span>
                        <input v-if="!isViewing" v-model="form.appointmentDate" type="datetime-local" required />
                        <span v-else class="field-value">{{ props.appointment?.dateLabel }}</span>
                        <small v-if="form.errors.appointmentDate">{{ form.errors.appointmentDate }}</small>
                    </label>
                    <label class="form-field">
                        <span>Motif :</span>
                        <input v-if="!isViewing" v-model="form.reason" maxlength="255" required />
                        <span v-else class="field-value">{{ props.appointment?.reason }}</span>
                        <small v-if="form.errors.reason">{{ form.errors.reason }}</small>
                    </label>
                </fieldset>

                <p v-if="!isViewing && props.appointmentClients.length === 0" class="appointment-empty">
                    Aucun client avec véhicule n’est disponible.
                </p>

                <div class="form-actions">
                    <button
                        v-if="!isViewing"
                        type="submit"
                        class="form-button save-button"
                        :disabled="form.processing || props.appointmentClients.length === 0"
                    >
                        <Save :size="16" aria-hidden="true" />
                        <span>{{ form.processing ? 'Enregistrement...' : isCreating ? 'Ajouter' : 'Enregistrer' }}</span>
                    </button>
                    <Link href="/rendez-vous" class="form-button return-button">
                        <ArrowLeft :size="16" aria-hidden="true" />
                        <span>Retour</span>
                    </Link>
                </div>
            </form>
        </main>

        <dialog ref="successDialog" class="success-dialog" aria-labelledby="success-title">
            <div class="success-content">
                <span class="success-icon"><Check :size="24" aria-hidden="true" /></span>
                <h2 id="success-title">Confirmation</h2>
                <p>{{ isCreating ? 'Le rendez-vous a été ajouté avec succès.' : 'Le rendez-vous a été modifié avec succès.' }}</p>
                <button type="button" class="form-button success-close" autofocus @click="closeSuccessDialog">
                    Fermer
                </button>
            </div>
        </dialog>
    </div>
</template>

<style scoped>
.appointment-form-shell {
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
.nav-item { padding: 9px 10px; color: #35434c; font-size: 12px; text-decoration: none; transition: background-color 140ms ease, color 140ms ease; }
.nav-item:hover, .nav-item:focus-visible, .nav-item.active { background: rgb(255 255 255 / 62%); color: #152b3a; }
.nav-item.active { font-weight: 600; }
.sidebar-footer { display: flex; flex-direction: column; gap: 10px; margin-top: auto; padding: 14px 12px 18px; text-align: center; font-size: 11px; }
.role-label { padding-bottom: 11px; border-bottom: 1px solid rgb(69 92 108 / 25%); font-size: 11px; font-weight: 600; }
.logout-button { min-height: 32px; border: 1px solid rgb(69 92 108 / 30%); background: rgb(255 255 255 / 60%); color: #252a2e; cursor: pointer; font: inherit; }
.form-main { width: min(100%, 1180px); margin: 0 auto; padding: 30px 40px 48px; }
.form-main h1 { margin: 0 0 40px; text-align: center; font-size: 21px; font-weight: 600; }
.appointment-form { display: flex; min-height: calc(100svh - 120px); flex-direction: column; justify-content: space-between; }
.field-grid { display: grid; width: min(100%, 780px); grid-template-columns: repeat(2, minmax(0, 1fr)); column-gap: 36px; row-gap: 26px; margin: 0 auto; padding: 0; border: 0; }
.form-field { display: flex; min-width: 0; flex-direction: column; gap: 6px; font-size: 13px; }
.form-field input, .form-field select, .field-value { display: flex; width: 100%; height: 36px; align-items: center; padding: 0 10px; border: 1px solid transparent; border-radius: 0; background: #dfe1e3; color: #252a2e; font: inherit; font-size: 12px; }
.form-field input:focus, .form-field select:focus { border-color: #3e657e; outline: 2px solid rgb(62 101 126 / 18%); }
.form-field input:disabled, .form-field select:disabled { color: #41484d; opacity: 1; }
.form-field select { cursor: pointer; }
.form-field small { color: #a22; font-size: 11px; }
.appointment-empty { width: min(100%, 780px); margin: 18px auto 0; color: #53616a; font-size: 12px; }
.form-actions { display: flex; justify-content: center; gap: 70px; margin-top: 48px; }
.form-button { display: inline-flex; min-width: 110px; min-height: 42px; align-items: center; justify-content: center; gap: 8px; padding: 0 16px; border: 0; border-radius: 0; background: #dfe1e3; color: #252a2e; cursor: pointer; font: inherit; font-size: 13px; text-decoration: none; }
.form-button:disabled { cursor: not-allowed; opacity: .55; }
.save-button { background: #3e657e; color: #fff; }
.return-button { min-width: 110px; }
.success-dialog { position: fixed; inset: 0; margin: auto; width: min(92vw, 440px); padding: 0; border: 1px solid #b9c4ca; border-radius: 4px; background: #f7f8f8; color: #252a2e; box-shadow: 0 18px 50px rgb(20 36 47 / 28%); }
.success-dialog::backdrop { background: rgb(20 31 38 / 48%); backdrop-filter: blur(2px); }
.success-content { display: flex; align-items: center; flex-direction: column; padding: 30px 24px 24px; text-align: center; }
.success-icon { display: grid; width: 48px; height: 48px; place-items: center; border-radius: 50%; background: #e5f4e8; color: #278348; }
.success-content h2 { margin: 16px 0 6px; font-size: 18px; }
.success-content p { margin: 0; color: #53616a; font-size: 13px; }
.success-close { margin-top: 22px; background: #278348; color: #fff; }

@media (max-width: 760px) {
    .appointment-form-shell { flex-direction: column; }
    .sidebar { position: static; width: 100%; height: auto; flex: 0 0 auto; }
    .sidebar nav { flex-direction: row; gap: 3px; overflow-x: auto; padding: 8px 10px; }
    .nav-item { flex: 0 0 auto; padding: 8px; font-size: 11px; }
    .sidebar-footer { display: none; }
    .form-main { padding: 24px 18px 36px; }
    .appointment-form { min-height: calc(100svh - 180px); }
}

@media (max-width: 540px) {
    .field-grid { grid-template-columns: minmax(0, 1fr); row-gap: 18px; }
    .form-actions { justify-content: center; gap: 20px; }
}
</style>
