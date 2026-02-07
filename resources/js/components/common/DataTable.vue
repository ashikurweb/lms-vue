<template>
  <div class="space-y-6">
    <!-- Table Header Design -->
    <div 
      class="grid grid-cols-12 gap-4 px-8 py-4 mb-4 theme-table-header rounded-2xl overflow-hidden relative group/header shadow-sm backdrop-blur-xl"
    >
      <div class="absolute inset-x-0 bottom-0 h-px bg-linear-to-r from-transparent via-indigo-500/30 to-transparent"></div>
      <div 
        v-for="(header, index) in headers" 
        :key="index"
        :class="[
          header.span ? `col-span-${header.span}` : 'col-span-1',
          header.align === 'center' ? 'text-center' : header.align === 'right' ? 'text-right' : 'text-left'
        ]"
        class="text-[10px] font-black theme-text-dim uppercase tracking-[0.3em]"
      >
        {{ header.label }}
      </div>
    </div>

    <!-- Loading State Matching Table Grid -->
    <div v-if="loading">
      <SkeletonLoader :headers="headers" :count="5" />
    </div>

    <template v-else>
      <!-- Empty State -->
      <EmptyState 
        v-if="items.length === 0"
        :title="emptyTitle"
        :message="emptyMessage"
      />

        <!-- Rows Container -->
        <div class="space-y-3">
          <div 
            v-for="(item, index) in items" 
            :key="item.id || index" 
            class="grid grid-cols-12 gap-4 items-center px-8 py-4 theme-bg-card border theme-border rounded-3xl shadow-sm transition-all duration-500 group relative hover:border-indigo-500/30"
          >
            <!-- Dynamic Columns via Scoped Slot -->
            <slot name="row" :item="item" :index="index"></slot>
          </div>
        </div>

      <!-- Pagination Support -->
      <div v-if="pagination && items.length > 0" class="mt-8 flex justify-center">
        <Pagination :pagination="pagination" @change="$emit('page-change', $event)" />
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import SkeletonLoader from './SkeletonLoader.vue';
import EmptyState from './EmptyState.vue';
import Pagination from './Pagination.vue';

const props = defineProps({
  headers: {
    type: Array,
    required: true
    // Structure: { label: String, span: Number, align: 'left'|'center'|'right' }
  },
  items: {
    type: Array,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  },
  pagination: {
    type: Object,
    default: null
  },
  emptyTitle: {
    type: String,
    default: 'No data found'
  },
  emptyMessage: {
    type: String,
    default: 'Try adjusting your search or filters.'
  }
});

defineEmits(['page-change']);

const totalSpans = computed(() => {
  return props.headers.reduce((acc, h) => acc + (h.span || 1), 0);
});

const getSL = (index) => {
  if (props.pagination) {
    const sl = (props.pagination.current_page - 1) * props.pagination.per_page + index + 1;
    return sl < 10 ? `0${sl}` : sl;
  }
  return index + 1 < 10 ? `0${index + 1}` : index + 1;
};
</script>
