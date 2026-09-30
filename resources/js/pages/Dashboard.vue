<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import InterventionBanner, { type Intervention } from '@/components/InterventionBanner.vue';
import garageImage from '../../assets/images/accueil.jpg';
import gearLogo from '../../assets/images/rouage.png';

/** Données sérialisées par le contrôleur pour le rôle de la session courante. */
const props = defineProps<{
    role: string;
    stats: Record<string, number>;
    interventions?: Intervention[];
    latestInterventions?: Intervention[];
}>();

/** Libellés de navigation selon le rôle; les modules eux-mêmes restent hors de ce périmètre. */
const navigation: Record<string, string[]> = {
    client: ['Vue d’ensemble', 'Mes véhicules', 'Mes rendez-vous', 'Mes interventions'],
    mecanicien: ['Vue d’ensemble', 'Utilisateurs', 'Véhicules', 'Interventions', 'Mon profil'],
    administrateur: ['Vue d’ensemble', 'Utilisateurs', 'Véhicules', 'Interventions', 'Pièces', 'Tarifs MO'],
};

/** Libellé de profil affiché dans le pied de la sidebar. */
const roleLabels: Record<string, string> = {
    client: 'Profil Client',
    mecanicien: 'Profil Mécanicien',
    administrateur: 'Profil Admin',
};

const navItems = navigation[props.role] ?? ['Vue d’ensemble'];
const roleLabel = roleLabels[props.role] ?? '';
/** Les clés correspondent aux compteurs de statut calculés par Laravel. */
const mechanicStatusStats = [
    { label: 'À faire', key: 'toDo' },
    { label: 'En cours', key: 'inProgress' },
    { label: 'Terminées', key: 'completed' },
] as const;

const imageStyle = {
    '--garage-image': `url(${garageImage})`,
} as Record<string, string>;
</script>

<template>
    <Head title="Accueil" />

    <!-- Le shell transmet l'image; son usage CSS est limité à la sidebar. -->
    <div class="dashboard-shell" :style="imageStyle">
        <aside class="sidebar">
            <Link href="/dashboard" class="brand">
                <img :src="gearLogo" alt="" />
                <span>GarageManager</span>
            </Link>

            <nav aria-label="Navigation principale">
                <span
                    v-for="(item, index) in navItems"
                    :key="item"
                    class="nav-item"
                    :class="{ active: index === 0 }"
                >
                    {{ item }}
                </span>
            </nav>

            <div class="sidebar-footer">
                <span class="role-label">{{ roleLabel }}</span>
                <Link href="/logout" method="post" as="button" class="logout-button">
                    Déconnexion
                </Link>
            </div>
        </aside>

        <main class="dashboard-main">
            <header class="page-heading">
                <div>
                    <h1>Tableau de bord</h1>
                    <p>Vue d’ensemble</p>
                </div>
                <div class="heading-actions">
                    <span class="role-label mobile-role-label">{{ roleLabel }}</span>
                    <Link href="/logout" method="post" as="button" class="logout-button mobile-logout">
                        Déconnexion
                    </Link>
                </div>
            </header>

            <template v-if="props.role === 'administrateur'">
                <section class="stats-grid" aria-label="Statistiques du garage">
                    <article
                        v-for="item in [
                            { label: 'Utilisateurs', value: props.stats.users },
                            { label: 'Véhicules', value: props.stats.vehicles },
                            { label: 'Rendez-vous', value: props.stats.appointments },
                            { label: 'Interventions', value: props.stats.interventions },
                            { label: 'En cours', value: props.stats.inProgress },
                            { label: 'Terminées', value: props.stats.completed },
                        ]"
                        :key="item.label"
                        class="stat-tile"
                    >
                        <span>{{ item.label }}</span>
                        <strong>{{ item.value }}</strong>
                    </article>
                </section>

                <section class="content-section">
                    <div class="section-heading">
                        <h2>Dernières interventions</h2>
                    </div>
                    <div v-if="props.latestInterventions?.length" class="record-list">
                        <InterventionBanner
                            v-for="intervention in props.latestInterventions"
                            :key="intervention.id"
                            :intervention="intervention"
                        />
                    </div>
                    <p v-else class="empty-state">Aucune intervention enregistrée.</p>
                </section>
            </template>

            <template v-else-if="props.role === 'client'">
                <section class="client-status-grid" aria-label="Statuts de mes interventions">
                    <article class="stat-tile">
                        <span>En cours :</span>
                        <strong>{{ props.stats.inProgress }}</strong>
                    </article>
                    <article class="stat-tile">
                        <span>Terminées :</span>
                        <strong>{{ props.stats.completed }}</strong>
                    </article>
                </section>

                <section class="content-section">
                    <div class="section-heading"><h2>Mes interventions :</h2></div>
                    <div v-if="props.interventions?.length" class="record-list">
                        <InterventionBanner
                            v-for="intervention in props.interventions"
                            :key="intervention.id"
                            :intervention="intervention"
                        />
                    </div>
                    <p v-else class="empty-state">Aucune intervention enregistrée.</p>
                </section>
            </template>

            <template v-else-if="props.role === 'mecanicien'">
                <section class="mechanic-status-grid" aria-label="Statuts des interventions attribuées">
                    <article v-for="item in mechanicStatusStats" :key="item.key" class="stat-tile">
                        <span>{{ item.label }} :</span>
                        <strong>{{ props.stats[item.key] }}</strong>
                    </article>
                </section>

                <section class="content-section">
                    <div class="section-heading"><h2>Interventions attribuées :</h2></div>
                    <div v-if="props.interventions?.length" class="record-list">
                        <InterventionBanner
                            v-for="intervention in props.interventions"
                            :key="intervention.id"
                            :intervention="intervention"
                        />
                    </div>
                    <p v-else class="empty-state">Aucune intervention attribuée.</p>
                </section>
            </template>
        </main>
    </div>
</template>

<style scoped>
.dashboard-shell {
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

.mobile-role-label {
    display: none;
}

.logout-button {
    min-height: 32px;
    border: 1px solid rgb(69 92 108 / 30%);
    background: rgb(255 255 255 / 60%);
    color: #252a2e;
    cursor: pointer;
    font: inherit;
}

.logout-button:hover {
    background: #fff;
}

.dashboard-main {
    width: min(100%, 1180px);
    margin: 0 auto;
    padding: 30px 34px 48px;
}

.page-heading,
.heading-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
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

.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
    margin-bottom: 29px;
}

.mechanic-status-grid {
    display: grid;
    width: 100%;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
    margin: 0 auto 29px;
}

.mechanic-status-grid .stat-tile {
    min-height: 76px;
}

.client-status-grid {
    display: grid;
    width: 100%;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
    margin: 0 auto 29px;
}

.client-status-grid .stat-tile {
    min-height: 78px;
}

.stat-tile {
    display: flex;
    min-height: 78px;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: #dfe1e3;
    text-align: center;
}

.stat-tile span {
    font-size: 11px;
}

.stat-tile strong {
    font-size: 15px;
    font-weight: 500;
}

.content-section {
    margin-top: 25px;
}

.section-heading {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 10px;
}

.section-heading h2 {
    margin: 0;
    font-size: 15px;
    font-weight: 500;
}

.record-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.empty-state {
    margin: 0;
    padding: 18px 14px;
    background: #f1f4f6;
    color: #68737b;
    font-size: 12px;
}

.mobile-logout {
    display: none;
}

@media (max-width: 760px) {
    .dashboard-shell {
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

    .dashboard-main {
        padding: 24px 18px 36px;
    }

    .mobile-logout {
        display: inline-block;
        padding: 0 9px;
    }

    .heading-actions {
        flex-direction: column;
        align-items: flex-end;
        justify-content: flex-end;
    }

    .mobile-role-label {
        display: inline;
    }
}

@media (max-width: 540px) {
    .stats-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 9px;
    }

    .mechanic-status-grid {
        gap: 9px;
    }

    .client-status-grid {
        gap: 9px;
    }

}
</style>
