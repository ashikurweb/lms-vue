<template>
  <div class="space-y-2 w-full">
    <div v-if="label" class="flex items-center justify-between ml-2">
      <label :for="id" class="text-[10px] font-black theme-text-dim uppercase tracking-[0.2em] pointer-events-none">
        {{ label }}
        <span v-if="required" class="text-rose-500 ml-0.5">*</span>
      </label>
      <slot name="label-right"></slot>
    </div>
    
    <div class="relative group">
      <!-- Icon Slot/Prop -->
      <div 
        v-if="icon || $slots.icon" 
        class="absolute inset-y-0 left-6 flex items-center pointer-events-none theme-text-dim group-focus-within:text-indigo-500 transition-colors"
      >
        <slot name="icon">
          <svg v-if="icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" v-html="icon"></svg>
        </slot>
      </div>

      <!-- Input Field -->
      <input
        :id="id"
        :type="inputType"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        :placeholder="placeholder"
        :required="required"
        :disabled="disabled"
        :class="[
          'w-full py-6 rounded-2xl theme-bg-element border-2 theme-border outline-none focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10 transition-all text-sm font-bold theme-text-main',
          (icon || $slots.icon) ? 'pl-16 pr-12' : 'px-8',
          disabled ? 'opacity-60 cursor-not-allowed bg-slate-100/5' : ''
        ]"
      />

      <!-- Password Toggle -->
      <button 
        v-if="type === 'password' && !disabled" 
        type="button"
        @click="showPassword = !showPassword"
        class="absolute inset-y-0 right-6 flex items-center theme-text-dim hover:theme-text-main transition-colors"
      >
        <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        </svg>
        <svg v-else class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.059 10.059 0 014.293-5.774M6.228 6.228A10.45 10.45 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.059 10.059 0 01-2.015 3.558m-4.356-1.358A3 3 0 1112.602 9.13" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" />
        </svg>
      </button>
    </div>

    <!-- Error Message -->
    <transition name="fade">
      <p v-if="error" class="text-[10px] font-black text-red-500 uppercase tracking-widest ml-4 mt-1">
        {{ error }}
      </p>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  modelValue: [String, Number],
  type: { type: String, default: 'text' },
  label: String,
  placeholder: String,
  required: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  icon: String,
  error: String,
  id: { type: String, default: () => `input-${Math.random().toString(36).substr(2, 9)}` }
});

defineEmits(['update:modelValue']);

const showPassword = ref(false);

const inputType = computed(() => {
  if (props.type === 'password') {
    return showPassword.value ? 'text' : 'password';
  }
  return props.type;
});
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* Date Picker Premium Styles */
input[type="datetime-local"] {
  accent-color: #6366f1; /* Indigo-500 to match theme */
}

/* Move the calendar icon to the left and make it look professional */
input[type="datetime-local"]::-webkit-calendar-picker-indicator {
  position: absolute;
  left: 20px;
  cursor: pointer;
  padding: 10px;
  margin: 0;
  width: 20px;
  height: 20px;
  z-index: 10;
}

input[type="datetime-local"] {
  padding-left: 60px !important;
  position: relative;
}
</style>
