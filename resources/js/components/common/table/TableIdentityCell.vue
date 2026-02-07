<template>
  <div class="flex items-center gap-5">
    <!-- Avatar / Initial -->
    <div class="relative group/avatar shrink-0">
      <div class="absolute inset-0 bg-indigo-500/20 rounded-2xl blur-lg opacity-0 group-hover/avatar:opacity-100 transition-opacity"></div>
      
      <img 
        v-if="image" 
        :src="image" 
        class="relative w-14 h-14 rounded-2xl object-cover border-2 theme-border shadow-inner transition-transform duration-700 group-hover/avatar:scale-110"
      >
      <div 
        v-else-if="title"
        class="relative w-14 h-14 rounded-2xl flex items-center justify-center text-xl font-black bg-indigo-600/10 text-indigo-600 border-2 theme-border transition-transform duration-700 group-hover/avatar:scale-110 uppercase"
      >
        {{ title.charAt(0) }}
      </div>
      
      <!-- Mini Icon Overlay (Optional) -->
      <slot name="overlay"></slot>
    </div>

    <!-- Info -->
    <div class="flex flex-col space-y-1 min-w-0">
      <component 
        :is="to ? 'router-link' : 'span'"
        v-bind="to ? { to } : {}"
        class="text-base font-black theme-text-main tracking-tight transition-colors truncate"
        :class="{ 'hover:text-indigo-500 cursor-pointer': to }"
      >
        {{ title }}
      </component>
      
      <div class="flex items-center gap-2">
        <span class="text-[9px] theme-text-dim font-black uppercase tracking-widest truncate">{{ subtitle }}</span>
        <slot name="metadata"></slot>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: String,
  subtitle: String,
  image: String,
  to: [Object, String],
});
</script>
