<template>
    <component :is="to ? 'router-link' : (href ? 'a' : 'button')" ref="buttonRef" :type="!to && !href ? type : null"
        :to="to" :href="href" :disabled="loading || disabled"
        class="ripple-button relative overflow-hidden group px-7 py-3 rounded-2xl font-bold text-sm tracking-tight transition-all active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed disabled:active:scale-100 flex items-center justify-center gap-2.5 select-none decoration-none outline-none"
        :class="variantClasses" @mousedown="createRipple">
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
    </component>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    label: String,
    loading: Boolean,
    disabled: Boolean,
    to: [String, Object],
    href: String,
    type: {
        type: String,
        default: 'button'
    },
    variant: {
        type: String,
        default: 'primary'
    }
});

const buttonRef = ref(null);
const ripples = ref([]);

const variantClasses = computed(() => {
    const base = {
        primary: 'bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/20 bg-linear-to-br from-indigo-600 to-indigo-700',
        secondary: 'theme-bg-element theme-text-main border-2 theme-border hover:theme-border-indigo-500/50 shadow-sm',
        danger: 'bg-rose-500 hover:bg-rose-600 text-white shadow-lg shadow-rose-500/20 bg-linear-to-br from-rose-500 to-rose-600',
        success: 'bg-emerald-500 hover:bg-emerald-600 text-white shadow-lg shadow-emerald-500/20 bg-linear-to-br from-emerald-500 to-emerald-600',
    };
    return base[props.variant] || base.primary;
});

const createRipple = (event) => {
    if (props.loading || props.disabled) return;

    const el = buttonRef.value?.$el || buttonRef.value;
    if (!el) return;

    const rect = el.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height) * 1.5;
    const x = event.clientX - rect.left - size / 2;
    const y = event.clientY - rect.top - size / 2;

    const id = Date.now();
    ripples.value.push({ id, x, y, size });

    setTimeout(() => {
        ripples.value = ripples.value.filter(r => r.id !== id);
    }, 1000);
};
</script>

<style scoped>
.ripple-button {
    isolation: isolate;
    text-decoration: none !important;
}

.ripple {
    position: absolute;
    background: rgba(255, 255, 255, 0.2);
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

.ripple-button.theme-bg-element .ripple {
    background: rgba(99, 102, 241, 0.1);
}
</style>
