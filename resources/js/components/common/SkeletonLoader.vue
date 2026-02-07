<template>
  <div class="space-y-4">
    <div 
      v-for="n in count" 
      :key="n"
      class="grid grid-cols-12 gap-4 items-center px-8 py-6 theme-bg-card border theme-border rounded-3xl shadow-sm animate-pulse"
      :style="{ animationDelay: `${n * 0.1}s` }"
    >
      <div 
        v-for="(header, i) in headers" 
        :key="i"
        :class="[
          header.span ? `col-span-${header.span}` : 'col-span-1',
          header.align === 'center' ? 'flex justify-center' : header.align === 'right' ? 'flex justify-end' : ''
        ]"
      >
        <div 
          class="h-4 bg-slate-200 dark:bg-slate-700/50 rounded-full"
          :class="[
            header.align === 'center' ? 'w-1/2' : 'w-3/4'
          ]"
        ></div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  headers: {
    type: Array,
    required: true
  },
  count: {
    type: Number,
    default: 5
  }
});
</script>

<style scoped>
.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}
</style>
