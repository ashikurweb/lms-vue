<template>
  <div class="relative group">
    <input 
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value); $emit('search')"
      type="text" 
      :placeholder="placeholder" 
      class="pl-12 pr-6 py-4 theme-bg-card border-2 theme-border rounded-3xl text-sm font-bold theme-text-main outline-none focus:border-indigo-500 focus:ring-8 focus:ring-indigo-500/5 transition-all w-72 shadow-xl"
    >
    <svg 
      class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 theme-text-dim group-focus-within:text-indigo-500 transition-colors" 
      fill="none" 
      stroke="currentColor" 
      viewBox="0 0 24 24"
    >
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
    </svg>
  </div>
</template>

<script setup>
defineProps({
  modelValue: String,
  placeholder: {
    type: String,
    default: 'Search...'
  }
});

defineEmits(['update:modelValue', 'search']);
</script>
