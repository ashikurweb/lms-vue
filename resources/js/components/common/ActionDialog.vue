<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="show" class="fixed inset-0 z-[110] flex items-center justify-center p-4 overflow-hidden">
        <!-- Backdrop - Using explicit background and backdrop blur -->
        <div 
          class="absolute inset-0 bg-slate-950/60 backdrop-blur-md transition-all duration-300"
          @click="$emit('cancel')"
        ></div>

        <!-- Dialog Card -->
        <Transition
          enter-active-class="transition duration-300 cubic-bezier(0.34, 1.56, 0.64, 1)"
          enter-from-class="opacity-0 scale-90 translate-y-8"
          enter-to-class="opacity-100 scale-100 translate-y-0"
          leave-active-class="transition duration-200 ease-in"
          leave-from-class="opacity-100 scale-100 translate-y-0"
          leave-to-class="opacity-0 scale-90 translate-y-8"
        >
            <div v-if="show" class="relative w-full max-w-md theme-bg-card border theme-border rounded-[2.5rem] shadow-3xl shadow-slate-950/50 p-10 overflow-hidden group">
              <!-- Decorative blur element -->
              <div class="absolute -top-10 -right-10 w-32 h-32 bg-rose-500/10 blur-3xl rounded-full group-hover:bg-rose-500/20 transition-all duration-700"></div>

              <!-- Content -->
              <div class="relative z-10 space-y-6 text-center">
                <!-- Icon Area -->
                <div class="mx-auto w-20 h-20 rounded-[2rem] bg-rose-500/10 flex items-center justify-center text-rose-500 mb-2">
                  <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                  </svg>
                </div>

                <div class="space-y-2">
                  <h3 class="text-3xl font-black theme-text-main tracking-tighter">{{ title }}</h3>
                  
                  <!-- Custom Content Slot -->
                  <slot></slot>
                  
                  <!-- Default Message if no slot content -->
                  <p v-if="!$slots.default" class="text-sm theme-text-dim font-medium leading-relaxed px-4">{{ message }}</p>
                </div>

                <!-- Actions -->
                <div class="flex flex-col gap-4 pt-4">
                  <button
                    @click="$emit('confirm')"
                    :disabled="loading"
                    class="w-full bg-rose-500 hover:bg-rose-600 text-white py-5 rounded-2xl text-[11px] font-black uppercase tracking-[0.2em] shadow-xl shadow-rose-500/30 active:scale-[0.98] transition-all flex items-center justify-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed group/btn"
                  >
                    <svg v-if="loading" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="transition-all">{{ confirmText }}</span>
                  </button>
                  <button
                    @click="$emit('cancel')"
                    :disabled="loading"
                    class="w-full theme-bg-element hover:theme-bg-hover border theme-border py-5 rounded-2xl text-[11px] font-black uppercase tracking-[0.2em] theme-text-main active:scale-[0.98] transition-all disabled:opacity-50"
                  >
                    {{ cancelText }}
                  </button>
                </div>
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
  title: {
    type: String,
    default: 'Are you sure?'
  },
  message: {
    type: String,
    default: 'This action cannot be undone.'
  },
  confirmText: {
    type: String,
    default: 'Yes, Delete'
  },
  cancelText: {
    type: String,
    default: 'Cancel'
  },
  loading: {
    type: Boolean,
    default: false
  }
});

defineEmits(['confirm', 'cancel']);
</script>

<style scoped>
/* Removed glass-card as it was causing blur issues and replaced with solid theme card */
.shadow-3xl {
  box-shadow: 0 40px 100px -20px rgba(0, 0, 0, 0.5);
}
</style>
