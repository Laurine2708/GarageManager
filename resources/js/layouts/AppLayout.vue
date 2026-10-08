<script setup lang="ts">
/**
 * Compose la structure générale des pages authentifiées.
 * @prop breadcrumbs Fil d’Ariane transmis à l’en-tête.
 */
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItem } from '@/types';

const { breadcrumbs = [] } = defineProps<{
    breadcrumbs?: BreadcrumbItem[];
}>();
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>
</template>
