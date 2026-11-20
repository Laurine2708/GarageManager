<script setup lang="ts">
/** Contrat sérialisé par DashboardController pour une intervention affichable. */
export type Intervention = {
    id: number;
    description: string;
    date: string;
    appointmentDate: string | null;
    status: string | null;
    vehicle: { name: string; registration: string } | null;
    client: string | null;
};

const props = defineProps<{
    /** Données de l’intervention à afficher dans le bandeau. */
    intervention: Intervention;
}>();

/**
 * Présente le véhicule, le client, les dates et le statut d’une intervention.
 * @prop intervention Données métier affichées dans le bandeau.
 */
/** Associe les libellés de statut en base aux classes de couleur du bandeau. */
const statusClass = (status: string | null) => {
    const normalized = status?.trim().toLocaleLowerCase('fr');

    if (normalized === 'à faire') {
        return 'status-to-do';
    }

    if (normalized === 'en cours') {
        return 'status-in-progress';
    }

    if (normalized === 'terminé' || normalized === 'terminée') {
        return 'status-complete';
    }

    return 'status-unknown';
};

/** Formate les dates sans heure pour correspondre au format métier jj/mm/aaaa. */
const formatDate = (value: string) => {
    const localValue = value.includes('T') ? value : value.replace(' ', 'T');

    return new Intl.DateTimeFormat('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    }).format(new Date(localValue));
};
</script>

<template>
    <!-- Composant partagé par les vues client, mécanicien et administrateur. -->
    <article class="intervention-banner">
        <div class="record-title">
            <h3>{{ props.intervention.description }}</h3>
            <p>
                {{ props.intervention.vehicle?.name ?? 'Véhicule non renseigné' }}
                <br />
                {{ props.intervention.vehicle?.registration ?? 'Immatriculation inconnue' }}
                <br />
                {{ props.intervention.client ?? 'Client non renseigné' }}
            </p>
        </div>
        <div class="record-dates">
            <div class="record-detail">
                <span>Date d’intervention :</span>
                <strong>
                    {{ props.intervention.appointmentDate ? formatDate(props.intervention.appointmentDate) : 'Non renseignée' }}
                </strong>
            </div>
            <div class="record-detail">
                <span>Date de départ :</span>
                <strong>{{ formatDate(props.intervention.date) }}</strong>
            </div>
        </div>
        <span class="status-tag status-tag-compact" :class="statusClass(props.intervention.status)">
            {{ props.intervention.status ?? 'Sans statut' }}
        </span>
        <span class="details-prompt">Voir le détail</span>
    </article>
</template>

<style scoped>
/* Les zones CSS maintiennent titre, dates, statut et lien de détail sur deux rangées. */
.intervention-banner {
    display: grid;
    grid-template-columns: minmax(150px, 1.1fr) minmax(210px, 1.2fr) minmax(76px, auto);
    grid-template-rows: minmax(24px, auto) minmax(20px, auto);
    grid-template-areas:
        'title dates status'
        'title dates details';
    align-items: center;
    gap: 14px;
    min-height: 76px;
    padding: 11px 14px;
    background: #dfe1e3;
}

.record-title {
    grid-area: title;
    align-self: start;
}

.record-title h3 {
    margin: 0;
    font-size: 12px;
    font-weight: 600;
}

.record-title p {
    margin: 4px 0 0;
    color: #53626b;
    font-size: 10px;
}

.record-dates {
    display: flex;
    grid-area: dates;
    flex-direction: column;
    align-self: center;
    gap: 7px;
}

.record-detail {
    display: flex;
    flex-direction: row;
    gap: 5px;
    font-size: 10px;
    white-space: nowrap;
}

.record-detail span {
    color: #53626b;
}

.record-detail strong {
    font-weight: 500;
}

.status-tag {
    display: inline-flex;
    min-height: 21px;
    align-items: center;
    justify-content: center;
    padding: 0 6px;
    border: 1px solid #b9c0c5;
    border-radius: 3px;
    background: #edf1f3;
    color: #3e4c55;
    font-size: 10px;
    font-weight: 700;
    white-space: nowrap;
}

.status-tag-compact {
    min-height: 20px;
}

.status-to-do {
    border-color: #d58d8d;
    background: #f8dddd;
    color: #8e2929;
}

.status-in-progress {
    border-color: #d5bd70;
    background: #fff0c2;
    color: #795900;
}

.status-complete {
    border-color: #77aa86;
    background: #dff0e3;
    color: #28643a;
}

.status-unknown {
    border-color: #b9c0c5;
    background: #e4e6e8;
    color: #4c555c;
}

.intervention-banner .status-tag {
    grid-area: status;
    grid-row: 1 / span 2;
    align-self: center;
    justify-self: end;
}

.details-prompt {
    grid-area: details;
    align-self: end;
    justify-self: end;
    font-size: 9px;
    font-style: italic;
    white-space: nowrap;
}

@media (max-width: 540px) {
    .intervention-banner {
        grid-template-columns: minmax(0, 1fr) auto;
        grid-template-rows: auto auto auto;
        gap: 9px;
        grid-template-areas:
            'title status'
            'dates status'
            'details details';
    }

    .record-detail {
        flex-wrap: wrap;
        white-space: normal;
    }

    .intervention-banner .status-tag {
        grid-column: 2;
        grid-row: 1 / span 2;
    }
}
</style>