<template>
  <div class="col-span-1">
    <div class="w-10 h-10 rounded-xl theme-bg-element border theme-border flex items-center justify-center transition-all duration-500 group-hover:border-indigo-500/30 group-hover:bg-indigo-500/5">
      <span class="text-[10px] font-black theme-text-dim group-hover:theme-text-main tabular-nums tracking-tighter">
        {{ index }}
      </span>
    </div>
  </div>
</template>

<script setup>
defineProps({
  index: [Number, String]
});
</script>
