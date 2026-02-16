<template>
    <Teleport to="body">
        <Transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0"
            enter-to-class="opacity-100" leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-md shadow-inner" @click="$emit('close')">
                </div>

                <!-- Modal Container -->
                <Transition enter-active-class="transition duration-500 cubic-bezier(0.34, 1.56, 0.64, 1)"
                    enter-from-class="opacity-0 scale-90 translate-y-12"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="transition duration-300 ease-in"
                    leave-from-class="opacity-100 scale-100 translate-y-0"
                    leave-to-class="opacity-0 scale-90 translate-y-12">
                    <div
                        class="relative w-full overflow-hidden theme-bg-card border theme-border rounded-[2.5rem] shadow-2xl shadow-slate-950/50 flex flex-col sm:max-w-xl animate-in zoom-in duration-300">
                        <!-- Header -->
                        <div class="px-10 pt-10 pb-6 flex items-center justify-between relative">
                            <div class="flex flex-col gap-1">
                                <h3 class="text-2xl font-black theme-text-main tracking-tight leading-none">{{ title }}
                                </h3>
                                <p v-if="subtitle" class="text-xs theme-text-dim font-bold uppercase tracking-widest">{{
                                    subtitle }}</p>
                            </div>
                            <button @click="$emit('close')"
                                class="w-12 h-12 flex items-center justify-center rounded-2xl theme-bg-element theme-text-muted hover:theme-text-main hover:rotate-90 transition-all duration-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                            <!-- Decorative blur -->
                            <div class="absolute -top-10 -right-10 w-32 h-32 bg-indigo-500/10 blur-3xl rounded-full">
                            </div>
                        </div>

                        <!-- Body -->
                        <div class="px-10 pb-10 overflow-y-auto max-h-[70vh] custom-scrollbar">
                            <slot></slot>
                        </div>

                        <!-- Footer -->
                        <div v-if="$slots.footer"
                            class="px-10 py-8 theme-bg-element/50 border-t theme-border flex items-center">
                            <slot name="footer"></slot>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
defineProps({
    show: Boolean,
    title: String,
    subtitle: String
});

defineEmits(['close']);
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(var(--text-dim-rgb), 0.1);
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(var(--text-dim-rgb), 0.2);
}
</style>
