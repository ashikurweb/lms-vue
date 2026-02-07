<template>
  <div class="inline-flex flex-col items-center">
    <span class="text-xl font-black theme-text-main tabular-nums leading-none">
      <slot>{{ value }}</slot>
    </span>
    <span class="text-[8px] font-black theme-text-dim uppercase tracking-widest mt-1">
      {{ label }}
    </span>
  </div>
</template>

<script setup>
defineProps({
  value: [Number, String],
  label: String
});
</script>
