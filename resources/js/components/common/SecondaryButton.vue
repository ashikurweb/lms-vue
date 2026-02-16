<template>
    <button ref="buttonRef" :type="type" :disabled="loading || disabled"
        class="ripple-button relative overflow-hidden group px-6 py-3 rounded-2xl font-bold text-sm transition-all active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2.5 select-none border-2 theme-border theme-bg-card theme-text-main hover:theme-border-indigo-500/50 shadow-sm"
        @mousedown="createRipple">
        <!-- Ripple Container -->
        <span v-for="ripple in ripples" :key="ripple.id" class="ripple" :style="{
            left: ripple.x + 'px',
            top: ripple.y + 'px',
            width: ripple.size + 'px',
            height: ripple.size + 'px'
        }"></span>

        <!-- Loading Spinner -->
        <svg v-if="loading" class="animate-spin h-4 w-4 shrink-0 relative z-10" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
            </path>
        </svg>

        <!-- Standard Content -->
        <span class="flex items-center gap-2 relative z-10">
            <slot name="icon"></slot>
            <slot>{{ label }}</slot>
        </span>
    </button>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
    label: String,
    loading: Boolean,
    disabled: Boolean,
    type: {
        type: String,
        default: 'button'
    }
});

const buttonRef = ref(null);
const ripples = ref([]);

const createRipple = (event) => {
    if (props.loading || props.disabled) return;
    const button = buttonRef.value;
    const rect = button.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height) * 1.5;
    const x = event.clientX - rect.left - size / 2;
    const y = event.clientY - rect.top - size / 2;
    const id = Date.now();
    ripples.value.push({ id, x, y, size });
    setTimeout(() => ripples.value = ripples.value.filter(r => r.id !== id), 1000);
};
</script>

<style scoped>
.ripple-button {
    isolation: isolate;
}

.ripple {
    position: absolute;
    background: rgba(99, 102, 241, 0.1);
    border-radius: 50%;
    transform: scale(0);
    animation: ripple-animation 1s ease-out;
    pointer-events: none;
    z-index: 0;
}

@keyframes ripple-animation {
    from {
        transform: scale(0);
        opacity: 1;
    }

    to {
        transform: scale(1);
        opacity: 0;
    }
}
</style>
