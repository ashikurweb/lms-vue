<template>
    <button @click.stop="$emit('toggle')" type="button"
        class="relative w-11 h-6 rounded-full transition-all duration-500 shadow-inner overflow-hidden flex items-center px-1 shrink-0"
        :class="modelValue ? 'bg-emerald-500 shadow-emerald-900/20' : 'theme-bg-element'">
        <div class="w-4 h-4 bg-white rounded-full transition-all duration-500 shadow-lg flex items-center justify-center font-black"
            :class="modelValue ? 'translate-x-5' : 'translate-x-0'">
            <svg class="w-2.5 h-2.5" :class="modelValue ? 'text-emerald-500' : 'text-slate-300'" fill="currentColor"
                viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                    clip-rule="evenodd" />
            </svg>
        </div>
    </button>
</template>

<script setup>
defineProps({
    modelValue: {
        type: Boolean,
        default: false
    }
});
defineEmits(['toggle', 'update:modelValue']);
</script>
