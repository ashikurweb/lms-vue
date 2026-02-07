<template>
  <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 relative px-2">
    <!-- Fluid Decoration Background -->
    <div class="absolute -top-12 -left-12 w-64 h-64 bg-indigo-500/5 blur-[100px] rounded-full"></div>
    
    <div class="relative space-y-2">
      <h1 class="text-4xl font-black theme-text-main tracking-tighter leading-none flex items-center gap-3">
        <div v-if="$slots.icon || icon" class="w-12 h-12 rounded-2xl bg-linear-to-br from-indigo-600 to-violet-600 flex items-center justify-center text-white shadow-lg shadow-indigo-600/20">
          <slot name="icon">
            <svg v-if="icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" v-html="icon"></svg>
          </slot>
        </div>
        {{ title }}
      </h1>
      <p class="text-sm theme-text-muted font-bold tracking-tight ml-1">
        <slot name="subtitle">
          {{ subtitle }} <span v-if="count !== undefined" class="theme-text-main">{{ count }}</span> {{ countLabel }}
        </slot>
      </p>
    </div>

    <div class="flex items-center gap-4 relative z-10">
      <!-- Search Component -->
      <TableSearch 
        v-if="showSearch"
        :model-value="modelValue"
        :placeholder="searchPlaceholder"
        @update:model-value="$emit('update:modelValue', $event)"
        @search="$emit('search')"
      />

      <!-- Add Button -->
      <slot name="actions">
        <router-link 
          v-if="addRoute"
          :to="addRoute"
          class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-2xl font-bold flex items-center justify-center gap-2 shadow-lg shadow-indigo-600/20 active:scale-95 transition-all whitespace-nowrap"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          <span>{{ addLabel }}</span>
        </router-link>
      </slot>
    </div>
  </div>
</template>

<script setup>
import TableSearch from './table/TableSearch.vue';

defineProps({
  title: String,
  subtitle: String,
  icon: String,
  count: [Number, String],
  countLabel: String,
  modelValue: String,
  showSearch: { type: Boolean, default: true },
  searchPlaceholder: { type: String, default: 'Search...' },
  addRoute: [Object, String],
  addLabel: { type: String, default: 'Add New' }
});

defineEmits(['update:modelValue', 'search']);
</script>
