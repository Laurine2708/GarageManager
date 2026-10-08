<script setup lang="ts">
/**
 * Affiche ou modifie une fiche véhicule selon le mode reçu.
 * @prop role Rôle de l’utilisateur connecté.
 * @prop mode Consultation ou modification.
 * @prop vehicle Véhicule concerné.
 * @prop clients Clients proposés pour l’attribution.
 */
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Check, Save } from '@lucide/vue';
import { Toaster } from '@/components/ui/sonner';
import garageImage from '../../assets/images/accueil.jpg';
import gearLogo from '../../assets/images/rouage.png';

type Vehicle = {
    id: number;
    brand: string;
    model: string;
    registration: string;
    firstRegistration: string;
    engine: string;
    vin: string;
    engineCode: string;
    ownerId: number | null;
    ownerName: string;
};

const props = defineProps<{
    role: string;
    mode: 'view' | 'edit';
    vehicle: Vehicle;
    clients: { id: number; name: string }[];
}>();

const form = useForm({
    brand: props.vehicle.brand,
    model: props.vehicle.model,
    registration: props.vehicle.registration,
    firstRegistration: props.vehicle.firstRegistration,
    engine: props.vehicle.engine,
    vin: props.vehicle.vin,
    engineCode: props.vehicle.engineCode,
    ownerId: props.vehicle.ownerId === null ? '' : String(props.vehicle.ownerId),
});
const successDialog = ref<HTMLDialogElement | null>(null);
const roleLabels: Record<string, string> = {
    client: 'Profil Client',
    mecanicien: 'Profil Mécanicien',
    administrateur: 'Profil Admin',
};
const navItems = props.role === 'client'
    ? ['Vue d’ensemble', 'Mes véhicules', 'Historique d’interventions', 'Mon profil']
    : props.role === 'mecanicien'
        ? ['Vue d’ensemble', 'Utilisateurs', 'Véhicules', 'Interventions', 'Mon profil']
        : ['Vue d’ensemble', 'Utilisateurs', 'Véhicules', 'Rendez-vous', 'Interventions', 'Pièces', 'Tarifs MO', 'Mon profil'];
const roleLabel = roleLabels[props.role] ?? '';
const isViewing = props.mode === 'view';
const imageStyle = {
    '--garage-image': `url(${garageImage})`,
} as Record<string, string>;

/** Enregistre les modifications si la page n’est pas en lecture seule. */
function submit(): void {
    if (isViewing) return;

    form.put(`/vehicules/${props.vehicle.id}`, {
        onSuccess: () => successDialog.value?.showModal(),
    });
}

/** Ferme la confirmation puis revient à la liste des véhicules. */
function closeSuccessDialog(): void {
    successDialog.value?.close();
    router.visit('/vehicules');
}
</script>

<template>
    <Head :title="isViewing ? 'Détails du véhicule' : 'Modifier un véhicule'" />
    <Toaster />

    <div class="vehicle-form-shell" :style="imageStyle">
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
                <Link href="/logout" method="post" as="button" class="logout-button">
                    Déconnexion
                </Link>
            </div>
        </aside>

        <main class="form-main">
            <h1>{{ isViewing ? 'Consulter une fiche véhicule :' : 'Modifier une fiche véhicule :' }}</h1>

            <form class="vehicle-form" @submit.prevent="submit">
                <div class="field-grid">
                    <label class="form-field">
                        <span>Marque :</span>
                        <input v-model="form.brand" :disabled="isViewing" required maxlength="50" />
                        <small v-if="form.errors.brand">{{ form.errors.brand }}</small>
                    </label>
                    <label class="form-field">
                        <span>Modèle :</span>
                        <input v-model="form.model" :disabled="isViewing" required maxlength="50" />
                        <small v-if="form.errors.model">{{ form.errors.model }}</small>
                    </label>
                    <label class="form-field">
                        <span>Date de mise en circulation :</span>
                        <input v-model="form.firstRegistration" type="date" :disabled="isViewing" required />
                        <small v-if="form.errors.firstRegistration">{{ form.errors.firstRegistration }}</small>
                    </label>
                    <label class="form-field">
                        <span>Immatriculation :</span>
                        <input v-model="form.registration" :disabled="isViewing" required maxlength="50" />
                        <small v-if="form.errors.registration">{{ form.errors.registration }}</small>
                    </label>
                    <label class="form-field">
                        <span>Propriétaire :</span>
                        <select v-if="!isViewing" v-model="form.ownerId" required>
                            <option value="" disabled>Sélectionner un client</option>
                            <option v-for="client in props.clients" :key="client.id" :value="String(client.id)">
                                {{ client.name }}
                            </option>
                        </select>
                        <input v-else :value="props.vehicle.ownerName" disabled />
                        <small v-if="form.errors.ownerId">{{ form.errors.ownerId }}</small>
                    </label>
                    <label class="form-field">
                        <span>Motorisation :</span>
                        <input v-model="form.engine" :disabled="isViewing" required maxlength="50" />
                        <small v-if="form.errors.engine">{{ form.errors.engine }}</small>
                    </label>
                    <label class="form-field">
                        <span>Code moteur :</span>
                        <input v-model="form.engineCode" :disabled="isViewing" required maxlength="50" />
                        <small v-if="form.errors.engineCode">{{ form.errors.engineCode }}</small>
                    </label>
                    <label class="form-field">
                        <span>VIN :</span>
                        <input v-model="form.vin" :disabled="isViewing" required maxlength="255" />
                        <small v-if="form.errors.vin">{{ form.errors.vin }}</small>
                    </label>
                </div>

                <div class="form-actions">
                    <button v-if="!isViewing" type="submit" class="form-button save-button" :disabled="form.processing">
                        <Save :size="16" aria-hidden="true" />
                        <span>{{ form.processing ? 'Enregistrement...' : 'Enregistrer' }}</span>
                    </button>
                    <Link href="/vehicules" class="form-button return-button">
                        <ArrowLeft :size="16" aria-hidden="true" />
                        <span>Retour</span>
                    </Link>
                </div>
            </form>
        </main>

        <dialog ref="successDialog" class="success-dialog" aria-labelledby="success-title">
            <div class="success-content">
                <span class="success-icon"><Check :size="24" aria-hidden="true" /></span>
                <h2 id="success-title">Modifications validées</h2>
                <p>La fiche véhicule a été modifiée avec succès.</p>
                <button type="button" class="form-button success-close" autofocus @click="closeSuccessDialog">
                    Fermer
                </button>
            </div>
        </dialog>
    </div>
</template>

<style scoped>
.vehicle-form-shell {
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
.vehicle-form { display: flex; min-height: calc(100svh - 120px); flex-direction: column; justify-content: space-between; }
.field-grid { display: grid; width: min(100%, 780px); grid-template-columns: repeat(2, minmax(0, 1fr)); column-gap: 36px; row-gap: 26px; margin: 0 auto; }
.form-field { display: flex; min-width: 0; flex-direction: column; gap: 6px; font-size: 13px; }
.form-field input, .form-field select { display: flex; width: 100%; height: 36px; align-items: center; padding: 0 10px; border: 1px solid transparent; border-radius: 0; background: #dfe1e3; color: #252a2e; font: inherit; font-size: 12px; }
.form-field input:focus, .form-field select:focus { border-color: #3e657e; outline: 2px solid rgb(62 101 126 / 18%); }
.form-field input:disabled, .form-field select:disabled { color: #41484d; opacity: 1; }
.form-field small { color: #a22; font-size: 11px; }
.form-actions { display: flex; justify-content: center; gap: 70px; margin-top: 48px; }
.form-button { display: inline-flex; min-width: 110px; min-height: 42px; align-items: center; justify-content: center; gap: 8px; padding: 0 16px; border: 0; border-radius: 0; background: #dfe1e3; color: #252a2e; cursor: pointer; font: inherit; font-size: 13px; text-decoration: none; }
.save-button { background: #dfe1e3; }
.return-button { min-width: 110px; }
.success-dialog { position: fixed; inset: 0; margin: auto; width: min(92vw, 440px); padding: 0; border: 1px solid #b9c4ca; border-radius: 4px; background: #f7f8f8; color: #252a2e; box-shadow: 0 18px 50px rgb(20 36 47 / 28%); }
.success-dialog::backdrop { background: rgb(20 31 38 / 48%); backdrop-filter: blur(2px); }
.success-content { display: flex; align-items: center; flex-direction: column; padding: 30px 24px 24px; text-align: center; }
.success-icon { display: grid; width: 48px; height: 48px; place-items: center; border-radius: 50%; background: #e5f4e8; color: #278348; }
.success-content h2 { margin: 16px 0 6px; font-size: 18px; }
.success-content p { margin: 0; color: #53616a; font-size: 13px; }
.success-close { margin-top: 22px; background: #278348; color: #fff; }
.success-close:hover { background: #1f6e3b; }

@media (max-width: 760px) {
    .vehicle-form-shell { flex-direction: column; }
    .sidebar { position: static; width: 100%; height: auto; flex: 0 0 auto; }
    .sidebar nav { flex-direction: row; gap: 3px; overflow-x: auto; padding: 8px 10px; }
    .nav-item { flex: 0 0 auto; padding: 8px; font-size: 11px; }
    .sidebar-footer { display: none; }
    .form-main { padding: 24px 18px 36px; }
    .vehicle-form { min-height: calc(100svh - 180px); }
}

@media (max-width: 540px) {
    .field-grid { grid-template-columns: minmax(0, 1fr); row-gap: 18px; }
    .form-actions { justify-content: space-between; gap: 12px; }
}
</style>
