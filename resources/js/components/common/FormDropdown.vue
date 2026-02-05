<template>
  <div class="space-y-2 relative" v-click-outside="close">
    <div v-if="label" class="flex items-center justify-between ml-2">
      <label class="text-[10px] font-black theme-text-dim uppercase tracking-[0.2em] pointer-events-none">
        {{ label }}
        <span v-if="required" class="text-rose-500 ml-0.5">*</span>
      </label>
    </div>

    <div class="relative">
      <button
        type="button"
        @click="toggle"
        class="w-full px-6 py-4 rounded-2xl theme-bg-element border-2 theme-border outline-none transition-all text-sm font-bold theme-text-main flex items-center justify-between group"
        :class="[
          isOpen ? 'border-indigo-500/50 ring-4 ring-indigo-500/10' : 'hover:theme-border-main',
          error ? 'border-rose-500/50' : ''
        ]"
      >
        <span :class="{ 'theme-text-muted': !selectedOption }">
          {{ selectedOption ? selectedOption.label : placeholder }}
        </span>
        <svg 
          class="w-4 h-4 transition-transform duration-300 theme-text-dim group-hover:theme-text-main" 
          :class="{ 'rotate-180': isOpen }" 
          fill="none" 
          stroke="currentColor" 
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
        </svg>
      </button>

      <!-- Dropdown Menu -->
      <transition
        enter-active-class="transition duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)]"
        enter-from-class="transform scale-95 opacity-0 -translate-y-4 blur-sm"
        enter-to-class="transform scale-100 opacity-100 translate-y-0 blur-0"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="transform scale-100 opacity-100 translate-y-0 blur-0"
        leave-to-class="transform scale-95 opacity-0 -translate-y-2 blur-sm"
      >
        <div 
          v-if="isOpen" 
          class="absolute z-[60] left-0 right-0 mt-2 p-2 theme-bg-card border theme-border rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.3)] max-h-60 overflow-y-auto custom-scrollbar"
        >
          <div v-if="options.length === 0" class="px-4 py-3 text-xs theme-text-muted italic">
            No options available
          </div>
          <button
            v-for="option in options"
            :key="option.value"
            type="button"
            @click="select(option)"
            class="w-full px-4 py-3 rounded-xl text-left text-sm font-bold transition-all flex items-center justify-between group"
            :class="[
              modelValue === option.value 
                ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' 
                : 'theme-text-main hover:theme-bg-sidebar hover:theme-text-main'
            ]"
          >
            {{ option.label }}
            <svg 
              v-if="modelValue === option.value" 
              class="w-4 h-4 text-white" 
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
            </svg>
          </button>
        </div>
      </transition>
    </div>

    <!-- Error Message -->
    <transition name="fade">
      <p v-if="error" class="text-[10px] font-black text-rose-500 uppercase tracking-widest ml-4 mt-1 italic">
        {{ Array.isArray(error) ? error[0] : error }}
      </p>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  modelValue: [String, Number],
  options: {
    type: Array,
    default: () => [] // [{ label: 'Draft', value: 'draft' }]
  },
  label: String,
  placeholder: {
    type: String,
    default: 'Select an option'
  },
  required: Boolean,
  error: [String, Array]
});

const emit = defineEmits(['update:modelValue', 'change']);

const isOpen = ref(false);

const toggle = () => isOpen.value = !isOpen.value;
const close = () => isOpen.value = false;

const selectedOption = computed(() => {
  return props.options.find(opt => opt.value === props.modelValue);
});

const select = (option) => {
  emit('update:modelValue', option.value);
  emit('change', option.value);
  close();
};

// Simple directive for clicking outside
const vClickOutside = {
  mounted(el, binding) {
    el._clickOutside = (event) => {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value(event);
      }
    };
    document.addEventListener('click', el._clickOutside);
  },
  unmounted(el) {
    document.removeEventListener('click', el._clickOutside);
  }
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(99, 102, 241, 0.2);
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(99, 102, 241, 0.4);
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
