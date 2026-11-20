<script setup lang="ts">
/** Configure les tarifs horaires et affiche le coût estimé des interventions ouvertes. */
import { ref, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Toaster } from '@/components/ui/sonner';
import garageImage from '../../assets/images/accueil.jpg';
import gearLogo from '../../assets/images/rouage.png';

type Tariff = {
    id: number;
    label: string;
    amount: number;
};

/** Informations nécessaires pour dater chaque intervention et afficher son tarif estimé. */
type Intervention = {
    id: number;
    description: string;
    status: string | null;
    client: string | null;
    date: string | null;
    departureDate: string | null;
    minutes: number;
    tarif: string | null;
    hourlyRate: number;
    estimatedCost: number;
};

const props = defineProps<{
    role: string;
    tariffs: Tariff[];
    interventions: Intervention[];
}>();

const form = useForm({
    tarifId: '',
    amount: '',
});
const saved = ref(false);
const roleLabel = 'Profil Admin';
const navItems = ['Vue d’ensemble', 'Utilisateurs', 'Véhicules', 'Rendez-vous', 'Interventions', 'Pièces', 'Tarifs MO', 'Mon profil'];
const imageStyle = {
    '--garage-image': `url(${garageImage})`,
} as Record<string, string>;

watch(() => form.tarifId, (tarifId) => {
    const selected = props.tariffs.find((tariff) => String(tariff.id) === tarifId);
    form.amount = selected ? String(selected.amount) : '';
    saved.value = false;
});

/** Enregistre le tarif sélectionné et rafraîchit les montants renvoyés par le serveur. */
function submit(): void {
    saved.value = false;
    form.put('/tarifs-mo', {
        preserveScroll: true,
        onSuccess: () => {
            saved.value = true;
        },
    });
}

function formatCurrency(amount: number): string {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR',
        maximumFractionDigits: 2,
    }).format(amount);
}
</script>

<template>
    <Head title="Tarif MO" />
    <Toaster />

    <div class="rates-shell" :style="imageStyle">
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
                    <Link v-else-if="item === 'Véhicules'" href="/vehicules" class="nav-item">
                        {{ item }}
                    </Link>
                    <Link v-else-if="item === 'Rendez-vous'" href="/rendez-vous" class="nav-item">
                        {{ item }}
                    </Link>
                    <Link v-else-if="item === 'Tarifs MO'" href="/tarifs-mo" class="nav-item active" aria-current="page">
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

        <main class="rates-main">
            <header class="page-heading">
                <div>
                    <h1>Tableau de bord</h1>
                    <p>Tarif horaire main d’œuvre</p>
                </div>
            </header>

            <section class="settings-layout" aria-label="Configuration des tarifs horaires">
                <form class="rate-form" @submit.prevent="submit">
                    <h2>Définissez le taux horaire HT pour le calcul du coût de la main d’œuvre :</h2>
                    <div class="form-fields">
                        <label class="form-field">
                            <span>Code horaire :</span>
                            <select v-model="form.tarifId" required>
                                <option value="" disabled>Sélectionner un tarif</option>
                                <option v-for="tariff in tariffs" :key="tariff.id" :value="String(tariff.id)">
                                    {{ tariff.label }}
                                </option>
                            </select>
                            <small v-if="form.errors.tarifId">{{ form.errors.tarifId }}</small>
                        </label>
                        <label class="form-field">
                            <span>Tarif (en €) :</span>
                            <input
                                v-model="form.amount"
                                type="number"
                                min="0"
                                max="99999999.99"
                                step="0.01"
                                placeholder="0.00"
                                required
                            />
                            <small v-if="form.errors.amount">{{ form.errors.amount }}</small>
                        </label>
                    </div>
                    <div class="form-footer">
                        <span v-if="saved" class="saved-message" role="status">Tarif enregistré.</span>
                        <button class="action-button" type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Enregistrement…' : 'Enregistrer' }}
                        </button>
                    </div>
                </form>

                <aside class="current-rates" aria-label="Tarifs horaires actuels">
                    <h2>Tarifs horaires actuels</h2>
                    <p v-for="tariff in tariffs" :key="tariff.id">
                        <strong>{{ tariff.label }}</strong>
                        <span>{{ formatCurrency(tariff.amount) }} / heure</span>
                    </p>
                    <p v-if="tariffs.length === 0" class="empty-state">Aucun tarif n’est configuré.</p>
                </aside>
            </section>

            <!-- Chaque carte présente les dates et le code tarifaire avec le coût calculé. -->
            <section class="interventions-section" aria-label="Coûts estimés par intervention">
                <h2>Coûts estimés par intervention :</h2>
                <article v-for="intervention in interventions" :key="intervention.id" class="intervention-card">
                    <div class="intervention-info">
                        <h3>{{ intervention.description }}</h3>
                        <p class="client-name">{{ intervention.client || 'Client non renseigné' }}</p>
                        <p v-if="intervention.date">Date d’intervention : {{ intervention.date }}</p>
                        <p v-if="intervention.departureDate">Date de départ du véhicule : {{ intervention.departureDate }}</p>
                    </div>
                    <div class="cost-info">
                        <strong>{{ formatCurrency(intervention.estimatedCost) }}</strong>
                        <span>{{ intervention.minutes }} min × {{ formatCurrency(intervention.hourlyRate) }} / h</span>
                    </div>
                    <div class="intervention-footer">
                        <span class="hourly-code">Code horaire : {{ intervention.tarif }}</span>
                        <span class="details-prompt">Voir le détail</span>
                    </div>
                </article>
                <p v-if="interventions.length === 0" class="empty-state">
                    Aucune intervention non terminée.
                </p>
            </section>
        </main>
    </div>
</template>

<style scoped>
.rates-shell {
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

.rates-main { width: min(100%, 1180px); margin: 0 auto; padding: 30px 34px 48px; }
.page-heading { margin-bottom: 24px; }
.page-heading h1 { margin: 0; font-size: 23px; font-weight: 600; }
.page-heading p { margin: 3px 0 0; color: #68737b; font-size: 12px; }

.settings-layout { display: grid; grid-template-columns: minmax(0, 1fr) minmax(190px, 250px); gap: 20px; align-items: start; }
.rate-form { padding: 18px; background: #fff; }
.rate-form h2 { max-width: 620px; margin: 0 0 20px; font-size: 15px; font-weight: 500; }
.form-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 24px; }
.form-field { display: grid; gap: 7px; color: #252a2e; font-size: 13px; }
.form-field input,
.form-field select { width: 100%; min-width: 0; height: 38px; padding: 0 11px; border: 0; background: #dfe1e3; color: inherit; font: inherit; font-size: 12px; }
.form-field input:focus-visible,
.form-field select:focus-visible { outline: 2px solid #3e657e; outline-offset: 2px; }
.form-field small { color: #a33a35; font-size: 11px; }
.form-footer { display: flex; min-height: 48px; align-items: center; justify-content: flex-end; gap: 14px; margin-top: 22px; }
.action-button { min-height: 40px; padding: 0 18px; border: 0; border-radius: 3px; background: #3e657e; box-shadow: 0 2px 5px rgb(34 57 71 / 18%); color: #fff; cursor: pointer; font: inherit; font-size: 12px; font-weight: 600; }
.action-button:hover:not(:disabled) { background: #2f536a; }
.action-button:focus-visible { outline: 2px solid #193b50; outline-offset: 3px; }
.action-button:disabled { cursor: wait; opacity: 0.6; }
.saved-message { color: #278348; font-size: 12px; }

.current-rates { padding: 10px 0; }
.current-rates h2 { margin: 0 0 12px; font-size: 14px; font-weight: 600; }
.current-rates p { display: flex; justify-content: space-between; gap: 8px; margin: 0 0 9px; color: #4e5960; font-size: 12px; }
.current-rates p strong { color: #252a2e; }
.interventions-section { margin-top: 30px; }
.interventions-section > h2 { margin: 0 0 16px; font-size: 18px; font-weight: 500; }
.intervention-card { position: relative; display: flex; min-height: 96px; align-items: center; justify-content: space-between; gap: 20px; margin-bottom: 18px; padding: 14px 20px 31px; background: #fff; }
.intervention-info { min-width: 0; }
.intervention-info h3 { margin: 0 0 2px; font-size: 14px; font-weight: 700; }
.intervention-info p { margin: 2px 0; color: #68737b; font-size: 12px; }
.intervention-info .client-name { font-style: italic; }
.cost-info { display: flex; flex: 0 0 auto; flex-direction: column; align-items: flex-end; gap: 4px; }
.cost-info strong { font-size: 16px; font-weight: 500; }
.cost-info span { color: #68737b; font-size: 11px; font-style: italic; }
.intervention-footer { position: absolute; right: 20px; bottom: 14px; left: 20px; display: flex; justify-content: space-between; gap: 12px; color: #252a2e; font-size: 11px; }
.hourly-code { color: #68737b; font-size: 12px; }
.details-prompt { font-style: italic; }
.empty-state { margin: 0; padding: 14px; color: #68737b; font-size: 13px; }

@media (max-width: 760px) {
    .rates-shell { display: block; }
    .sidebar { position: relative; width: 100%; height: auto; min-height: 0; }
    .sidebar nav { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); padding: 8px; }
    .sidebar-footer { display: none; }
    .rates-main { padding: 22px 16px 36px; }
    .settings-layout { grid-template-columns: minmax(0, 1fr); gap: 12px; }
    .current-rates { padding: 8px 4px; }
    .form-fields { grid-template-columns: minmax(0, 1fr); gap: 14px; }
    .intervention-card { align-items: flex-start; flex-direction: column; gap: 12px; }
    .cost-info { align-items: flex-start; }
}
</style>
