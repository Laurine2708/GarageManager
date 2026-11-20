<script setup lang="ts">
/**
 * Affiche un message d’erreur de validation lorsqu’il est fourni.
 * @prop message Texte d’erreur facultatif.
 */
defineProps<{
    message?: string;
}>();
</script>

<template>
    <div v-show="message">
        <p class="text-sm text-red-600 dark:text-red-500">
            {{ message }}
        </p>
    </div>
</template>
