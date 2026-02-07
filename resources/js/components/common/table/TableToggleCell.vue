<template>
  <div class="flex flex-col items-center gap-1 group/toggle">
    <button 
      @click="$emit('toggle')"
      class="relative w-11 h-6 rounded-full transition-all duration-500 shadow-inner overflow-hidden border theme-border flex items-center px-1"
      :class="enabled ? activeClass : 'theme-bg-element'"
    >
      <div 
        class="w-4 h-4 bg-white rounded-full transition-all duration-500 shadow-lg flex items-center justify-center"
        :class="enabled ? 'translate-x-5 rotate-180' : 'translate-x-0'"
      >
        <slot name="icon">
           <svg v-if="icon" class="w-2.5 h-2.5" :class="enabled ? iconActiveClass : 'text-slate-300'" fill="currentColor" viewBox="0 0 20 20" v-html="icon"></svg>
        </slot>
      </div>
    </button>
    <span v-if="label" class="text-[7px] font-bold theme-text-dim opacity-40 uppercase group-hover/toggle:opacity-100 transition-opacity">
      {{ label }}
    </span>
  </div>
</template>

<script setup>
defineProps({
  enabled: Boolean,
  label: String,
  icon: String,
  activeClass: { type: String, default: 'bg-indigo-600 border-indigo-500/50 shadow-indigo-900/20' },
  iconActiveClass: { type: String, default: 'text-indigo-600' }
});

defineEmits(['toggle']);
</script>
