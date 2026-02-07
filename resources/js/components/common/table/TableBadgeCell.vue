<template>
  <div class="flex flex-col items-center gap-1 group/status">
    <div 
      class="px-3 py-1.5 rounded-xl border-2 transition-all duration-500 text-[9px] font-black uppercase tracking-widest shadow-sm"
      :class="variantClasses"
    >
      {{ label }}
    </div>
    <span v-if="subLabel" @click="$emit('action')" class="text-[7px] font-bold theme-text-dim opacity-40 uppercase group-hover/status:opacity-100 transition-opacity cursor-pointer">
      {{ subLabel }}
    </span>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  label: String,
  subLabel: String,
  variant: {
    type: String,
    default: 'default' // emerald, amber, rose, indigo, slate
  }
});

defineEmits(['action']);

const variantClasses = computed(() => {
  const base = {
    emerald: 'bg-emerald-500/10 border-emerald-500/50 text-emerald-600',
    amber: 'bg-amber-500/10 border-amber-500/50 text-amber-600',
    rose: 'bg-rose-500/10 border-rose-500/50 text-rose-600',
    indigo: 'bg-indigo-500/10 border-indigo-500/50 text-indigo-500',
    slate: 'bg-slate-500/10 border-slate-500/50 theme-text-dim',
    default: 'bg-slate-500/10 border-slate-500/50 theme-text-dim'
  };
  return base[props.variant] || base.default;
});
</script>
