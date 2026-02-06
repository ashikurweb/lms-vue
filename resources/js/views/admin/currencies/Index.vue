<template>
  <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-black theme-text-main tracking-tight">Currency Settings</h1>
        <p class="text-sm theme-text-muted mt-1 font-medium">Manage currency settings and exchange rates.</p>
      </div>
      <div class="flex items-center gap-3">
        <div class="relative group">
          <input 
            v-model="search"
            type="text" 
            placeholder="Search currencies..." 
            class="pl-10 pr-4 py-3 theme-bg-card border theme-border rounded-2xl text-sm font-bold theme-text-main outline-none focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10 transition-all w-64"
            @input="debounceSearch"
          >
          <svg class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 theme-text-dim group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
        </div>
        <button 
          @click="openAddModal"
          class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-2xl font-bold flex items-center justify-center gap-2 shadow-lg shadow-indigo-600/20 active:scale-95 transition-all"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          <span class="hidden sm:inline">Add Currency</span>
        </button>
      </div>
    </div>

    <!-- Modern Floating Table -->
    <div class="relative min-h-[400px]">
      <!-- Column Headers -->
      <div class="grid grid-cols-12 gap-4 px-8 py-4 mb-4 theme-table-header rounded-2xl shadow-sm overflow-hidden relative group/header">
        <div class="absolute inset-x-0 bottom-0 h-px bg-linear-to-r from-transparent via-indigo-500/30 to-transparent"></div>
        <div class="col-span-1 text-[10px] font-black theme-table-header-text uppercase tracking-[0.2em]">SL</div>
        <div class="col-span-3 text-[10px] font-black theme-table-header-text uppercase tracking-[0.2em]">Currency Details</div>
        <div class="col-span-2 text-[10px] font-black theme-table-header-text uppercase tracking-[0.2em]">Code</div>
        <div class="col-span-2 text-[10px] font-black theme-table-header-text uppercase tracking-[0.2em]">Exchange Rate</div>
        <div class="col-span-1 text-[10px] font-black theme-table-header-text uppercase tracking-[0.2em] text-center">Default</div>
        <div class="col-span-1 text-[10px] font-black theme-table-header-text uppercase tracking-[0.2em] text-center">Status</div>
        <div class="col-span-2 text-[10px] font-black theme-table-header-text uppercase tracking-[0.2em] text-right">Actions</div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex items-center justify-center py-32 theme-bg-card border theme-border rounded-[2.5rem] shadow-sm">
         <SkeletonLoader :cols="6" />
      </div>

      <template v-else>
        <!-- Empty State Component -->
        <EmptyState 
          v-if="currencies.length === 0"
          title="No currencies found"
          message="Add your first currency to start managing exchange rates and payment options."
        />

        <!-- Floating Rows -->
        <div class="space-y-3">
          <div 
            v-for="(currency, index) in currencies" 
            :key="currency.id" 
            class="grid grid-cols-12 gap-4 items-center px-8 py-4 theme-bg-card border theme-border rounded-[1.5rem] shadow-sm transition-colors duration-300 group"
          >
            <!-- SL Column -->
            <div class="col-span-1">
              <div class="w-9 h-9 rounded-xl theme-bg-element border theme-border flex items-center justify-center text-[11px] font-black theme-text-dim transition-all">
                {{ (pagination.current_page - 1) * pagination.per_page + index + 1 }}
              </div>
            </div>

            <!-- Details Column -->
            <div class="col-span-3">
              <div class="flex items-center gap-4">
                <div 
                  class="w-14 h-14 rounded-2xl flex items-center justify-center text-xl font-bold border-2 theme-border shadow-lg transition-all duration-500 group-hover:scale-110 group-hover:shadow-xl bg-gradient-to-br from-gray-700 to-gray-800 text-white border-gray-600 relative overflow-hidden"
                >
                  <div class="absolute inset-0 bg-gradient-to-br from-transparent to-gray-900/5"></div>
                  <span class="relative z-10">{{ currency.symbol }}</span>
                </div>
                <div class="flex flex-col">
                  <span class="text-sm font-black theme-text-main tracking-tight transition-colors duration-300">{{ currency.name }}</span>
                  <span class="text-[9px] theme-text-dim font-bold uppercase tracking-widest opacity-70">{{ currency.is_default ? 'Default Currency' : 'Secondary Currency' }}</span>
                </div>
              </div>
            </div>

            <!-- Code Column -->
            <div class="col-span-2">
              <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                  {{ currency.code }}
                </span>
              </div>
            </div>

            <!-- Exchange Rate Column -->
            <div class="col-span-2">
              <div class="flex items-center">
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium theme-text-main truncate" :title="currency.exchange_rate">
                    {{ currency.exchange_rate }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Default Column -->
            <div class="col-span-1 flex justify-center">
              <button 
                @click="setDefaultCurrency(currency)"
                class="relative w-11 h-6 rounded-full transition-all duration-500 shadow-inner overflow-hidden"
                :class="currency.is_default ? 'bg-amber-500 shadow-amber-900/20' : 'theme-bg-element'"
              >
                <div 
                  class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full transition-all duration-500 shadow-lg flex items-center justify-center"
                  :class="currency.is_default ? 'translate-x-5 rotate-180' : 'translate-x-0'"
                >
                  <svg class="w-2.5 h-2.5" :class="currency.is_default ? 'text-amber-500' : 'text-slate-300'" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                </div>
              </button>
            </div>

            <!-- Status Column -->
            <div class="col-span-1 flex justify-center">
              <button 
                @click="toggleStatus(currency)"
                class="relative w-11 h-6 rounded-full transition-all duration-500 shadow-inner overflow-hidden"
                :class="currency.is_active ? 'bg-emerald-500 shadow-emerald-900/20' : 'theme-bg-element'"
              >
                <div 
                  class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full transition-all duration-500 shadow-lg flex items-center justify-center"
                  :class="currency.is_active ? 'translate-x-5' : 'translate-x-0'"
                >
                  <svg class="w-2.5 h-2.5" :class="currency.is_active ? 'text-emerald-500' : 'text-slate-300'" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                </div>
              </button>
            </div>

            <!-- Actions Column -->
            <div class="col-span-2 text-right">
              <div class="flex items-center justify-end">
                <div class="relative group/actions flex items-center">
                  <!-- Expanded Action Dock -->
                  <div class="absolute right-0 flex items-center gap-1.5 px-2 py-1.5 theme-bg-card border theme-border rounded-2xl shadow-2xl opacity-0 invisible translate-x-4 scale-90 group-hover/actions:opacity-100 group-hover/actions:visible group-hover/actions:translate-x-[-45px] group-hover/actions:scale-100 transition-all duration-500 cubic-bezier(0.34, 1.56, 0.64, 1) z-20 backdrop-blur-xl">
                    <button 
                      @click="editCurrency(currency)"
                      class="w-9 h-9 flex items-center justify-center rounded-xl text-indigo-500 hover:bg-indigo-500/10 transition-all active:scale-90"
                      title="Edit Currency"
                    >
                      <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <div class="w-px h-4 bg-slate-700/50"></div>
                    <button 
                      v-if="!currency.is_default"
                      @click="triggerDelete(currency)"
                      class="w-9 h-9 flex items-center justify-center rounded-xl text-rose-500 hover:bg-rose-500/10 transition-all active:scale-90"
                      title="Delete Currency"
                    >
                      <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                  </div>

                  <!-- Main Trigger Button -->
                  <button class="w-10 h-10 flex items-center justify-center rounded-xl theme-bg-element border theme-border theme-text-dim hover:theme-text-main hover:border-indigo-500/50 transition-all group-hover/actions:rotate-90 group-hover/actions:bg-indigo-600 group-hover/actions:text-white group-hover/actions:shadow-[0_0_20px_rgba(79,70,229,0.4)] z-30">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination Component -->
        <div class="mt-8">
          <Pagination :pagination="pagination" @change="fetchCurrencies" />
        </div>
      </template>
    </div>

    <!-- Side Over Panel / Modal -->
    <transition name="panel">
      <div v-if="showModal" class="fixed inset-0 z-100 overflow-hidden">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="closeModal"></div>
        
        <div class="absolute inset-y-0 right-0 w-full max-w-md theme-bg-card border-l theme-border shadow-2xl flex flex-col">
          <div class="p-8 border-b theme-border flex items-center justify-between">
            <div>
              <h2 class="text-xl font-black theme-text-main tracking-tight">{{ isEditing ? 'Edit' : 'Add' }} Currency</h2>
              <p class="text-xs theme-text-dim font-medium">{{ isEditing ? 'Modify existing currency details' : 'Create a new currency' }}</p>
            </div>
            <button @click="closeModal" class="p-2 theme-text-muted hover:theme-text-main transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>

          <div class="flex-1 overflow-y-auto p-8 custom-scrollbar">
            <form @submit.prevent="saveCurrency" class="space-y-6">
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
              />

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

              <div class="pt-4 flex items-center gap-4">
                <button 
                  type="submit"
                  :disabled="saving"
                  class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white py-4 rounded-2xl font-bold shadow-lg shadow-indigo-600/20 active:scale-95 transition-all flex items-center justify-center gap-2 disabled:opacity-50"
                >
                  <svg v-if="saving" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                  <span>{{ isEditing ? 'Update Currency' : 'Create Currency' }}</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </transition>

    <!-- Global Action Dialog -->
    <ActionDialog
      :show="showDeleteModal"
      title="Delete Currency"
      :message="`Are you sure you want to delete '${currencyToDelete?.name}'? This action cannot be undone.`"
      :loading="deleting"
      @confirm="confirmDelete"
      @cancel="showDeleteModal = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import FormInput from '../../../components/common/FormInput.vue';
import ActionDialog from '../../../components/common/ActionDialog.vue';
import Pagination from '../../../components/common/Pagination.vue';
import SkeletonLoader from '../../../components/common/SkeletonLoader.vue';
import EmptyState from '../../../components/common/EmptyState.vue';
import { useToast } from '../../../composables/useToast';

const toast = useToast();
const currencies = ref([]);
const loading = ref(true);
const search = ref('');
const showModal = ref(false);
const isEditing = ref(false);
const saving = ref(false);
const deleting = ref(false);
const selectedId = ref(null);
const showDeleteModal = ref(false);
const currencyToDelete = ref(null);

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
  from: 0,
  to: 0
});

const form = reactive({
  name: '',
  code: '',
  symbol: '',
  exchange_rate: '',
  is_active: true,
  is_default: false
});

const errors = ref({});

const fetchCurrencies = async (page = 1) => {
  loading.value = true;
  try {
    const token = localStorage.getItem('auth_token');
    if (!token) {
      toast.error('Authentication required. Please login again.');
      return;
    }

    const response = await fetch(`/api/admin/currencies?page=${page}&per_page=${pagination.per_page}&search=${search.value}`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    });
    
    const data = await response.json();
    
    if (response.ok) {
      currencies.value = data.data;
      Object.assign(pagination, data.pagination);
    } else {
      if (response.status === 401) {
        toast.error('Session expired. Please login again.');
        // You might want to redirect to login page here
        // window.location.href = '/login';
      } else {
        throw new Error(data.message || 'Failed to fetch currencies');
      }
    }
  } catch (error) {
    toast.error(error.message || 'Failed to fetch currencies');
  } finally {
    loading.value = false;
  }
};

let debounceTimer;
const debounceSearch = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    fetchCurrencies(1);
  }, 500);
};

const openAddModal = () => {
  isEditing.value = false;
  selectedId.value = null;
  resetForm();
  showModal.value = true;
};

const editCurrency = (currency) => {
  isEditing.value = true;
  selectedId.value = currency.id;
  Object.assign(form, {
    name: currency.name,
    code: currency.code,
    symbol: currency.symbol,
    exchange_rate: currency.exchange_rate,
    is_active: !!currency.is_active,
    is_default: !!currency.is_default
  });
  showModal.value = true;
};

const resetForm = () => {
  Object.assign(form, {
    name: '',
    code: '',
    symbol: '',
    exchange_rate: '',
    is_active: true,
    is_default: false
  });
  errors.value = {};
};

const closeModal = () => {
  showModal.value = false;
};

const saveCurrency = async () => {
  saving.value = true;
  errors.value = {};
  try {
    const url = isEditing.value ? `/api/admin/currencies/${selectedId.value}` : '/api/admin/currencies';
    const method = isEditing.value ? 'PUT' : 'POST';
    
    const response = await fetch(url, {
      method,
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(form)
    });
    
    const data = await response.json();
    
    if (response.ok) {
      toast.success(data.message);
      closeModal();
      fetchCurrencies(pagination.current_page);
    } else {
      if (response.status === 422) {
        errors.value = data.errors;
      } else {
        throw new Error(data.message || 'Something went wrong');
      }
    }
  } catch (error) {
    toast.error(error.message || 'Something went wrong');
  } finally {
    saving.value = false;
  }
};

const triggerDelete = (currency) => {
  currencyToDelete.value = currency;
  showDeleteModal.value = true;
};

const confirmDelete = async () => {
  if (!currencyToDelete.value) return;
  
  deleting.value = true;
  try {
    const response = await fetch(`/api/admin/currencies/${currencyToDelete.value.id}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Content-Type': 'application/json'
      }
    });
    
    const data = await response.json();
    
    if (response.ok) {
      toast.success(data.message);
      showDeleteModal.value = false;
      currencyToDelete.value = null;
      fetchCurrencies(pagination.current_page);
    } else {
      throw new Error(data.message || 'Failed to delete currency');
    }
  } catch (error) {
    toast.error(error.message || 'Failed to delete currency');
  } finally {
    deleting.value = false;
  }
};

const toggleStatus = async (currency) => {
  try {
    const response = await fetch(`/api/admin/currencies/${currency.id}/toggle-status`, {
      method: 'PATCH',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Content-Type': 'application/json'
      }
    });
    
    const data = await response.json();
    
    if (response.ok) {
      currency.is_active = !currency.is_active;
      toast.success(data.message);
    } else {
      throw new Error(data.message || 'Failed to update status');
    }
  } catch (error) {
    toast.error(error.message || 'Failed to update status');
  }
};

const setDefaultCurrency = async (currency) => {
  if (currency.is_default) return; // Already default
  
  try {
    const response = await fetch(`/api/admin/currencies/${currency.id}/set-default`, {
      method: 'PATCH',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Content-Type': 'application/json'
      }
    });
    
    const data = await response.json();
    
    if (response.ok) {
      // Update local state - remove default from all currencies and set new default
      currencies.value.forEach(c => c.is_default = false);
      currency.is_default = true;
      toast.success(data.message);
    } else {
      throw new Error(data.message || 'Failed to set default currency');
    }
  } catch (error) {
    toast.error(error.message || 'Failed to set default currency');
  }
};

onMounted(() => {
  fetchCurrencies();
});
</script>

<style scoped>
.panel-enter-active, .panel-leave-active {
  transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}
.panel-enter-from, .panel-leave-to {
  transform: translateX(100%);
  opacity: 0;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
  height: 4px;
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
</style>