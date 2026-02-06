<template>
  <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-black theme-text-main tracking-tight">Current Currency</h1>
        <p class="text-sm theme-text-muted mt-1 font-medium">Manage your currently active currency settings.</p>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-32 theme-bg-card border theme-border rounded-[2.5rem] shadow-sm">
       <SkeletonLoader :cols="1" />
    </div>

    <template v-else>
      <!-- Current Currency Card -->
      <div class="theme-bg-card border theme-border rounded-[2.5rem] shadow-sm p-8">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-xl font-black theme-text-main tracking-tight">Active Currency Settings</h2>
          <button 
            @click="editMode = !editMode"
            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl font-bold text-sm transition-all active:scale-95"
          >
            {{ editMode ? 'Cancel' : 'Edit Settings' }}
          </button>
        </div>

        <div v-if="!editMode && currentCurrency" class="space-y-6">
          <div class="flex items-center gap-6">
            <div 
              class="w-16 h-16 rounded-2xl flex items-center justify-center text-2xl font-bold border-2 theme-border shadow-inner"
              :style="{ color: currentCurrency.color || '#22c55e', backgroundColor: (currentCurrency.color || '#22c55e') + '10', borderColor: (currentCurrency.color || '#22c55e') + '20' }"
            >
              {{ currentCurrency.symbol }}
            </div>
            <div class="flex-1">
              <h3 class="text-2xl font-black theme-text-main">{{ currentCurrency.name }}</h3>
              <p class="text-sm theme-text-dim font-medium">{{ currentCurrency.code }} • Exchange Rate: {{ currentCurrency.exchange_rate }}</p>
            </div>
            <div class="text-right">
              <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                Active
              </span>
            </div>
          </div>
        </div>

        <!-- Edit Form -->
        <form v-if="editMode" @submit.prevent="saveSettings" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <FormInput 
              v-model="form.name"
              label="Currency Name"
              placeholder="e.g. US Dollar"
              :error="errors.name"
              required
            />

            <FormInput 
              v-model="form.code"
              label="Currency Code"
              placeholder="e.g. USD"
              :error="errors.code"
              required
              maxlength="3"
            />

            <FormInput 
              v-model="form.symbol"
              label="Currency Symbol"
              placeholder="e.g. $"
              :error="errors.symbol"
              required
            />

            <FormInput 
              v-model="form.exchange_rate"
              label="Exchange Rate"
              placeholder="e.g. 1.00"
              :error="errors.exchange_rate"
              type="number"
              step="0.01"
              required
            />

            <FormInput 
              v-model="form.color"
              label="Color Code"
              placeholder="#22c55e"
              :error="errors.color"
              class="md:col-span-2"
            />
          </div>

          <div class="flex items-center gap-6 py-2">
            <label class="flex items-center gap-3 cursor-pointer group">
              <div 
                class="w-12 h-6 rounded-full p-1 transition-colors duration-300 relative"
                :class="form.is_active ? 'bg-indigo-600' : 'theme-bg-element'"
              >
                <div 
                  class="w-4 h-4 bg-white rounded-full transition-transform duration-300 shadow-sm"
                  :class="form.is_active ? 'translate-x-6' : 'translate-x-0'"
                ></div>
                <input type="checkbox" v-model="form.is_active" class="hidden">
              </div>
              <span class="text-xs font-black theme-text-main uppercase tracking-widest">Active</span>
            </label>

            <label class="flex items-center gap-3 cursor-pointer group">
              <div 
                class="w-12 h-6 rounded-full p-1 transition-colors duration-300 relative"
                :class="form.is_default ? 'bg-indigo-600' : 'theme-bg-element'"
              >
                <div 
                  class="w-4 h-4 bg-white rounded-full transition-transform duration-300 shadow-sm"
                  :class="form.is_default ? 'translate-x-6' : 'translate-x-0'"
                ></div>
                <input type="checkbox" v-model="form.is_default" class="hidden">
              </div>
              <span class="text-xs font-black theme-text-main uppercase tracking-widest">Default</span>
            </label>
          </div>

          <div class="flex items-center gap-4">
            <button 
              type="submit"
              :disabled="saving"
              class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white py-4 rounded-2xl font-bold shadow-lg shadow-indigo-600/20 active:scale-95 transition-all flex items-center justify-center gap-2 disabled:opacity-50"
            >
              <svg v-if="saving" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
              <span>Update Settings</span>
            </button>
            <button 
              type="button"
              @click="editMode = false"
              class="px-6 py-4 theme-bg-card border theme-border theme-text-main rounded-2xl font-bold hover:bg-slate-50 dark:hover:bg-slate-800 transition-all"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>

      <!-- Quick Stats -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="theme-bg-card border theme-border rounded-2xl shadow-sm p-6">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
              <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div>
              <p class="text-sm theme-text-dim font-medium">Exchange Rate</p>
              <p class="text-xl font-black theme-text-main">{{ currentCurrency?.exchange_rate || '1.00' }}</p>
            </div>
          </div>
        </div>

        <div class="theme-bg-card border theme-border rounded-2xl shadow-sm p-6">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
              <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div>
              <p class="text-sm theme-text-dim font-medium">Status</p>
              <p class="text-xl font-black theme-text-main">{{ currentCurrency?.is_active ? 'Active' : 'Inactive' }}</p>
            </div>
          </div>
        </div>

        <div class="theme-bg-card border theme-border rounded-2xl shadow-sm p-6">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
              <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
              </svg>
            </div>
            <div>
              <p class="text-sm theme-text-dim font-medium">Currency Code</p>
              <p class="text-xl font-black theme-text-main">{{ currentCurrency?.code || 'USD' }}</p>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import FormInput from '../../../components/common/FormInput.vue';
import SkeletonLoader from '../../../components/common/SkeletonLoader.vue';
import { useToast } from '../../../composables/useToast';

const toast = useToast();
const loading = ref(true);
const editMode = ref(false);
const saving = ref(false);
const currentCurrency = ref(null);

const form = reactive({
  name: '',
  code: '',
  symbol: '',
  exchange_rate: '',
  color: '#22c55e',
  is_active: true,
  is_default: true
});

const errors = ref({});

// Mock current currency data
const mockCurrentCurrency = {
  id: 1,
  name: 'US Dollar',
  code: 'USD',
  symbol: '$',
  exchange_rate: '1.00',
  color: '#22c55e',
  is_active: true,
  is_default: true
};

const fetchCurrentCurrency = async () => {
  loading.value = true;
  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 500));
    
    currentCurrency.value = mockCurrentCurrency;
    Object.assign(form, mockCurrentCurrency);
  } catch (error) {
    toast.error('Failed to fetch current currency settings');
  } finally {
    loading.value = false;
  }
};

const saveSettings = async () => {
  saving.value = true;
  errors.value = {};
  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000));
    
    currentCurrency.value = { ...form };
    editMode.value = false;
    toast.success('Currency settings updated successfully');
  } catch (error) {
    if (error.response && error.response.status === 422) {
      errors.value = error.response.data.errors;
    } else {
      toast.error(error.response?.data?.message || 'Something went wrong');
    }
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  fetchCurrentCurrency();
});
</script>
