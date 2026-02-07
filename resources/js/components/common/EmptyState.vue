<template>
  <div class="theme-bg-card border theme-border rounded-[2.5rem] py-40 text-center shadow-sm relative overflow-hidden group">
    <!-- Decorative Background Glows -->
    <div class="absolute -top-24 -left-24 w-48 h-48 bg-indigo-500/10 blur-[100px] rounded-full group-hover:bg-indigo-500/20 transition-all duration-700"></div>
    <div class="absolute -bottom-24 -right-24 w-48 h-48 bg-violet-500/10 blur-[100px] rounded-full group-hover:bg-violet-500/20 transition-all duration-700"></div>

    <div class="relative z-10 flex flex-col items-center justify-center space-y-6 animate-in fade-in zoom-in slide-in-from-bottom-8 duration-700">
      <!-- Icon Container -->
      <div class="relative w-40 h-40 flex items-center justify-center">
        <!-- Floating Rings -->
        <div class="absolute inset-0 border-2 border-dashed border-indigo-500/20 rounded-[3rem] animate-[spin_20s_linear_infinite]"></div>
        <div class="absolute inset-4 border border-dashed border-violet-500/20 rounded-[2.5rem] animate-[spin_15s_linear_infinite_reverse]"></div>
        
        <!-- Main Icon Card (Hover effect removed) -->
        <div class="relative w-24 h-24 theme-bg-element rounded-[2rem] border-2 theme-border shadow-2xl flex items-center justify-center overflow-hidden">
          <div class="absolute inset-0 bg-linear-to-br from-indigo-500/10 via-transparent to-violet-500/10"></div>
          
          <slot name="icon">
            <svg class="w-12 h-12 text-indigo-500 drop-shadow-[0_0_8px_rgba(99,102,241,0.4)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
          </slot>
        </div>

        <!-- Decorative Particles -->
        <div class="absolute top-0 right-4 w-3 h-3 bg-indigo-500 rounded-full animate-pulse blur-[1px]"></div>
        <div class="absolute bottom-4 left-0 w-2 h-2 bg-violet-500 rounded-full animate-pulse blur-[1px]" style="animation-delay: 1s"></div>
      </div>

      <!-- Text Content -->
      <div class="space-y-2 max-w-sm mx-auto px-6">
        <h3 class="text-2xl font-black theme-text-main tracking-tight leading-tight">
          {{ title }}
        </h3>
        <p class="text-sm theme-text-dim font-medium leading-relaxed opacity-80">
          {{ message }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    default: 'No data found'
  },
  message: {
    type: String,
    default: 'We couldn\'t find any records matching your criteria.'
  }
});
</script>

<style scoped>
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>
