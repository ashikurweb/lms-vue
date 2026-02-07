<template>
  <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <!-- Premium Dashboard Header Component -->
    <PageHeader 
      title="Course Categories"
      subtitle="Organize your courses into meaningful categories."
      v-model="search"
      add-label="Add Category"
      search-placeholder="Search categories..."
      @search="debounceSearch"
    >
      <template #icon>
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
      </template>
      <template #actions>
        <button 
          @click="openAddModal"
          class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-2xl font-bold flex items-center justify-center gap-2 shadow-lg shadow-indigo-600/20 active:scale-95 transition-all"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          <span class="hidden sm:inline">Add Category</span>
        </button>
      </template>
    </PageHeader>

    <!-- Dynamic DataTable Engine -->
    <DataTable 
      :headers="tableHeaders"
      :items="categories"
      :loading="loading"
      :pagination="pagination"
      empty-title="No categories found"
      empty-message="Start organizing your content by creating a new category and grouping your courses."
      @page-change="fetchCategories"
    >
      <template #row="{ item: category, index }">
        <!-- SL Column -->
        <TableSLCell :index="formatSL(index)" />

        <!-- Details Column -->
        <div class="col-span-3">
          <div class="flex items-center gap-4">
            <div 
              class="w-11 h-11 rounded-xl flex items-center justify-center text-lg font-bold border-2 theme-border shadow-inner transition-transform duration-500 group-hover:scale-110"
              :style="{ color: category.color || '#6366f1', backgroundColor: (category.color || '#6366f1') + '10', borderColor: (category.color || '#6366f1') + '20' }"
            >
              {{ category.name.charAt(0) }}
            </div>
            <div class="flex flex-col">
              <span class="text-sm font-black theme-text-main tracking-tight transition-colors duration-300">{{ category.name }}</span>
              <span class="text-[9px] theme-text-dim font-bold uppercase tracking-widest opacity-70">{{ category.parent ? 'Parent: ' + category.parent.name : 'Primary Category' }}</span>
            </div>
          </div>
        </div>

        <!-- Description Column -->
        <div class="col-span-3">
          <p class="text-[11px] font-medium theme-text-dim leading-relaxed italic opacity-80 group-hover:opacity-100 transition-opacity" :title="category.description">
            "{{ truncateText(category.description, 55) || 'No description provided' }}"
          </p>
        </div>

        <!-- Courses Column -->
        <div class="col-span-1 text-center">
          <div class="inline-flex flex-col items-center">
            <span class="text-base font-black theme-text-main leading-none transition-transform duration-300">{{ category.courses_count }}</span>
            <span class="text-[8px] font-bold theme-text-dim uppercase tracking-tighter">Courses</span>
          </div>
        </div>

        <!-- Featured Column -->
        <div class="col-span-1 flex justify-center">
          <button 
            @click="toggleFeatured(category)"
            class="relative w-11 h-6 rounded-full transition-all duration-500 shadow-inner overflow-hidden flex items-center px-1"
            :class="category.is_featured ? 'bg-amber-500 shadow-amber-900/20' : 'theme-bg-element'"
          >
            <div 
              class="w-4 h-4 bg-white rounded-full transition-all duration-500 shadow-lg flex items-center justify-center"
              :class="category.is_featured ? 'translate-x-5 rotate-180' : 'translate-x-0'"
            >
              <svg class="w-2.5 h-2.5" :class="category.is_featured ? 'text-amber-500' : 'text-slate-300'" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            </div>
          </button>
        </div>

        <!-- Status Column -->
        <div class="col-span-1 flex justify-center">
          <button 
            @click="toggleStatus(category)"
            class="flex flex-col items-center gap-1 group/status"
          >
            <div 
              class="w-10 h-5 rounded-full transition-all duration-500 relative shadow-inner overflow-hidden"
              :class="category.is_active ? 'bg-emerald-500 shadow-emerald-900/20' : 'bg-slate-400/20'"
            >
              <div 
                class="absolute top-1 left-1 w-3 h-3 bg-white rounded-full transition-all duration-500"
                :class="category.is_active ? 'translate-x-5' : 'translate-x-0'"
              ></div>
            </div>
          </button>
        </div>

        <!-- Actions Column -->
        <div class="col-span-2 text-right">
          <div class="flex items-center justify-end">
            <TableActionDock>
                <button 
                  @click="editCategory(category)"
                  class="w-9 h-9 flex items-center justify-center rounded-xl text-amber-500 hover:bg-amber-500/10 transition-all active:scale-90"
                  title="Edit Category"
                >
                  <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                  </svg>
                </button>
                <div class="w-px h-4 bg-slate-700/50"></div>
                <button 
                  @click="triggerDelete(category)"
                  class="w-9 h-9 flex items-center justify-center rounded-xl text-rose-500 hover:bg-rose-500/10 transition-all active:scale-90"
                  title="Delete Category"
                >
                  <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </TableActionDock>
          </div>
        </div>
      </template>
    </DataTable>

    <!-- Side Over Panel / Modal -->
    <transition name="panel">
      <div v-if="showModal" class="fixed inset-0 z-100 overflow-hidden">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="closeModal"></div>
        
        <div class="absolute inset-y-0 right-0 w-full max-w-md theme-bg-card border-l theme-border shadow-2xl flex flex-col">
          <div class="p-8 border-b theme-border flex items-center justify-between">
            <div>
              <h2 class="text-xl font-black theme-text-main tracking-tight">{{ isEditing ? 'Edit' : 'Add' }} Category</h2>
              <p class="text-xs theme-text-dim font-medium">{{ isEditing ? 'Modify existing category details' : 'Create a new course category' }}</p>
            </div>
            <button @click="closeModal" class="p-2 theme-text-muted hover:theme-text-main transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>

          <div class="flex-1 overflow-y-auto p-8 custom-scrollbar">
            <form @submit.prevent="saveCategory" class="space-y-6">
              <FormInput 
                v-model="form.name"
                label="Category Name"
                placeholder="e.g. Web Development"
                :error="errors.name"
                required
              />

              <FormDropdown
                v-model="form.parent_id"
                label="Parent Category"
                placeholder="None (Primary)"
                :options="parentCategoryOptions"
              />

              <FormInput 
                v-model="form.color"
                label="Color Code"
                placeholder="#6366f1"
                :error="errors.color"
              />

              <div class="space-y-2">
                <label class="text-[10px] font-black theme-text-dim uppercase tracking-[0.2em] ml-2">Description</label>
                <textarea 
                  v-model="form.description"
                  rows="3"
                  class="w-full px-6 py-4 rounded-2xl theme-bg-element border-2 theme-border outline-none focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10 transition-all text-sm font-bold theme-text-main"
                  placeholder="What is this category about?"
                ></textarea>
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
                    :class="form.is_featured ? 'bg-indigo-600' : 'theme-bg-element'"
                  >
                    <div 
                      class="w-4 h-4 bg-white rounded-full transition-transform duration-300 shadow-sm"
                      :class="form.is_featured ? 'translate-x-6' : 'translate-x-0'"
                    ></div>
                    <input type="checkbox" v-model="form.is_featured" class="hidden">
                  </div>
                  <span class="text-xs font-black theme-text-main uppercase tracking-widest">Featured</span>
                </label>
              </div>

              <div class="pt-4 flex items-center gap-4">
                <button 
                  type="submit"
                  :disabled="saving"
                  class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white py-4 rounded-2xl font-bold shadow-lg shadow-indigo-600/20 active:scale-95 transition-all flex items-center justify-center gap-2 disabled:opacity-50"
                >
                  <svg v-if="saving" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                  <span>{{ isEditing ? 'Update Category' : 'Create Category' }}</span>
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
      title="Delete Category"
      :message="`Are you sure you want to delete '${categoryToDelete?.name}'? This action cannot be undone.`"
      :loading="deleting"
      @confirm="confirmDelete"
      @cancel="showDeleteModal = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, reactive, computed } from 'vue';
import { categoryService } from '../../../services/categoryService';
import PageHeader from '../../../components/common/PageHeader.vue';
import TableActionDock from '../../../components/common/TableActionDock.vue';
import TableSLCell from '../../../components/common/table/TableSLCell.vue';
import FormInput from '../../../components/common/FormInput.vue';
import FormDropdown from '../../../components/common/FormDropdown.vue';
import ActionDialog from '../../../components/common/ActionDialog.vue';
import DataTable from '../../../components/common/DataTable.vue';
import { useToast } from '../../../composables/useToast';
import { truncateText } from '../../../utils/helpers';

const toast = useToast();

const tableHeaders = [
  { label: 'SL', span: 1, align: 'left' },
  { label: 'Category Details', span: 3, align: 'left' },
  { label: 'Description', span: 3, align: 'left' },
  { label: 'Courses', span: 1, align: 'center' },
  { label: 'Featured', span: 1, align: 'center' },
  { label: 'Status', span: 1, align: 'center' },
  { label: 'Actions', span: 2, align: 'right' },
];
const categories = ref([]);
const allCategories = ref([]);
const loading = ref(true);
const search = ref('');
const showModal = ref(false);
const isEditing = ref(false);
const saving = ref(false);
const deleting = ref(false);
const selectedSlug = ref(null);
const showDeleteModal = ref(false);
const categoryToDelete = ref(null);

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
});

const form = reactive({
  name: '',
  description: '',
  parent_id: null,
  color: '#6366f1',
  is_active: true,
  is_featured: false
});

const errors = ref({});

const parentCategoryOptions = computed(() => {
  return allCategories.value.map(cat => ({
    label: cat.name,
    value: cat.id
  }));
});

const fetchCategories = async (page = 1) => {
  loading.value = true;
  try {
    const data = await categoryService.index({ 
      page, 
      search: search.value,
      per_page: pagination.per_page 
    });
    await new Promise(resolve => setTimeout(resolve, 500));
    
    categories.value = data.data;
    const meta = data.meta;
    pagination.current_page = meta.current_page;
    pagination.last_page = meta.last_page;
    pagination.total = meta.total;
  } catch (error) {
    toast.error('Failed to fetch categories');
  } finally {
    loading.value = false;
  }
};

const formatSL = (index) => {
  const sl = (pagination.current_page - 1) * pagination.per_page + index + 1;
  return sl < 10 ? `0${sl}` : sl;
};

const fetchAllForSelect = async () => {
  try {
    const data = await categoryService.getAll();
    allCategories.value = data;
  } catch (error) {
    console.error('Error fetching parent categories');
  }
};

let debounceSearchTimer;
const debounceSearch = () => {
  clearTimeout(debounceSearchTimer);
  debounceSearchTimer = setTimeout(() => {
    fetchCategories(1);
  }, 500);
};

const openAddModal = () => {
  isEditing.value = false;
  selectedSlug.value = null;
  resetForm();
  showModal.value = true;
  fetchAllForSelect();
};

const editCategory = (category) => {
  isEditing.value = true;
  selectedSlug.value = category.slug;
  Object.assign(form, {
    name: category.name,
    description: category.description || '',
    parent_id: category.parent_id,
    color: category.color || '#6366f1',
    is_active: !!category.is_active,
    is_featured: !!category.is_featured
  });
  showModal.value = true;
  fetchAllForSelect();
};

const resetForm = () => {
  Object.assign(form, {
    name: '',
    description: '',
    parent_id: null,
    color: '#6366f1',
    is_active: true,
    is_featured: false
  });
  errors.value = {};
};

const closeModal = () => {
  showModal.value = false;
};

const saveCategory = async () => {
  saving.value = true;
  errors.value = {};
  try {
    if (isEditing.value) {
      await categoryService.update(selectedSlug.value, form);
      toast.success('Category updated successfully');
    } else {
      await categoryService.store(form);
      toast.success('Category created successfully');
    }
    closeModal();
    fetchCategories(pagination.current_page);
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

const triggerDelete = (category) => {
  categoryToDelete.value = category;
  showDeleteModal.value = true;
};

const confirmDelete = async () => {
  if (!categoryToDelete.value) return;
  
  deleting.value = true;
  try {
    await categoryService.destroy(categoryToDelete.value.slug);
    toast.success('Category deleted successfully');
    showDeleteModal.value = false;
    categoryToDelete.value = null;
    fetchCategories(pagination.current_page);
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to delete category');
  } finally {
    deleting.value = false;
  }
};

const toggleStatus = async (category) => {
  try {
    await categoryService.toggleStatus(category.slug);
    category.is_active = !category.is_active;
    toast.success(`Category ${category.is_active ? 'activated' : 'hidden'} successfully`);
  } catch (error) {
    toast.error('Failed to update status');
  }
};

const toggleFeatured = async (category) => {
  try {
    await categoryService.toggleFeatured(category.slug);
    category.is_featured = !category.is_featured;
    toast.success(`Category ${category.is_featured ? 'marked as featured' : 'removed from featured'}`);
  } catch (error) {
    toast.error('Failed to update featured status');
  }
};

onMounted(() => {
  fetchCategories();
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
