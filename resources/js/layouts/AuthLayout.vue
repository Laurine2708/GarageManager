<script setup lang="ts">
/**
 * Fournit la mise en page simple commune aux formulaires d’authentification.
 * @prop title Titre transmis à la mise en page.
 * @prop description Description transmise à la mise en page.
 */
import AuthLayout from '@/layouts/auth/AuthSimpleLayout.vue';

const { title = '', description = '' } = defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <AuthLayout :title="title" :description="description">
        <slot />
    </AuthLayout>
</template>
