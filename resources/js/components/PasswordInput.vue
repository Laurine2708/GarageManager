<script setup lang="ts">
import { Eye, EyeOff } from '@lucide/vue';
import { ref, useTemplateRef } from 'vue';
import type { HTMLAttributes } from 'vue';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';

/**
 * Champ de mot de passe avec contrôle d’affichage et focus exposé au parent.
 * @prop class Classe facultative ajoutée au champ.
 * @event Attributs et événements du champ natif relayés à Input.
 */
defineOptions({ inheritAttrs: false });

const props = defineProps<{
    class?: HTMLAttributes['class'];
}>();

/** Indique si la valeur est momentanément affichée en clair. */
const showPassword = ref(false);
const inputRef = useTemplateRef('inputRef');

// Les formulaires parents peuvent remettre le focus sur le champ après une erreur.
defineExpose({
    $el: inputRef,
    focus: () => inputRef.value?.$el?.focus(),
});
</script>

<template>
    <div class="relative">
        <Input
            ref="inputRef"
            :type="showPassword ? 'text' : 'password'"
            :class="cn('pr-10', props.class)"
            v-bind="$attrs"
        />
        <button
            type="button"
            @click="showPassword = !showPassword"
            :class="
                cn(
                    'text-muted-foreground focus-visible:ring-ring absolute inset-y-0 right-0 flex items-center rounded-r-md px-3 focus-visible:ring-[3px] focus-visible:outline-none',
                )
            "
            :aria-label="showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'"
        >
            <EyeOff v-if="showPassword" class="size-4" />
            <Eye v-else class="size-4" />
        </button>
    </div>
</template>
