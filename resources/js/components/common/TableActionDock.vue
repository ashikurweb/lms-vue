<template>
  <div class="relative group/actions flex items-center">
    <!-- Expanded Action Dock -->
    <div 
      class="absolute right-0 flex items-center gap-1.5 px-2.5 py-2 theme-bg-card border theme-border rounded-[2rem] shadow-2xl opacity-0 invisible translate-x-4 scale-90 group-hover/actions:opacity-100 group-hover/actions:visible group-hover/actions:translate-x-[-48px] group-hover/actions:scale-100 transition-all duration-500 cubic-bezier(0.34, 1.56, 0.64, 1) z-20 backdrop-blur-xl"
    >
      <slot></slot>
    </div>

    <!-- Main Trigger Button -->
    <button class="w-11 h-11 flex items-center justify-center rounded-2xl theme-bg-element border-2 theme-border theme-text-dim hover:theme-text-main hover:border-indigo-500/50 transition-all group-hover/actions:rotate-90 group-hover/actions:bg-indigo-600 group-hover/actions:text-white group-hover/actions:shadow-[0_0_25px_rgba(79,70,229,0.4)] z-30">
      <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
        <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
      </svg>
    </button>
  </div>
</template>

<script setup>
// This is a layout-only component to wrap action buttons
</script>

<style scoped>
.shadow-2xl {
  box-shadow: 0 25px 50px -12px rgba(67, 56, 202, 0.15);
}
</style>
