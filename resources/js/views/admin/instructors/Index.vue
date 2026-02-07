<template>
  <div class="space-y-8 animate-in fade-in slide-in-from-bottom-6 duration-1000">
    <!-- Premium Dashboard Header -->
    <PageHeader 
      title="Instructors"
      subtitle="Orchestrating professional educators across your platform."
      v-model="search"
      :add-route="{ name: 'instructors.create' }"
      add-label="Add Creator"
      search-placeholder="Search mentors..."
      @search="debounceSearch"
    >
      <template #icon>
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
      </template>
    </PageHeader>

    <!-- Dynamic DataTable Engine -->
    <DataTable 
      :headers="tableHeaders"
      :items="instructors"
      :loading="loading"
      :pagination="pagination"
      empty-title="No mentors found"
      empty-message="Your faculty is currently empty or no results match your query."
      @page-change="fetchInstructors"
    >
      <template #row="{ item: instructor, index }">
        <!-- SL Column -->
        <TableSLCell :index="formatSL(index)" />

        <!-- Identity Column -->
        <TableIdentityCell 
          class="col-span-3"
          :title="instructor.name"
          :subtitle="instructor.email"
          :image="instructor.avatar_url"
          :to="{ name: 'instructors.show', params: { id: instructor.id } }"
        >
          <template #metadata v-if="instructor.is_featured">
             <span class="w-3 h-3 text-amber-500"><svg fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg></span>
          </template>
        </TableIdentityCell>

        <!-- Subject Column -->
        <div class="col-span-2 flex flex-col gap-1">
          <span class="text-[11px] font-black theme-text-main truncate italic">{{ instructor.headline || 'Professional Mentor' }}</span>
          <div class="flex flex-wrap gap-1">
              <span v-for="tag in (instructor.expertise?.split(',') || ['Academic'])" :key="tag" class="px-2 py-0.5 rounded-md bg-slate-800/10 dark:bg-slate-200/5 theme-text-dim text-[8px] font-black uppercase tracking-tighter">
                {{ tag.trim() }}
              </span>
          </div>
        </div>

        <!-- Stats Columns -->
        <div class="col-span-1 text-center">
            <TableStatsCell label="Courses" :value="instructor.total_courses" />
        </div>
        
        <div class="col-span-1 text-center">
            <TableStatsCell label="Revenue" :value="`$${instructor.total_earnings?.toLocaleString()}`" />
        </div>

        <!-- Toggles & Status -->
        <div class="col-span-1 flex justify-center">
          <TableToggleCell 
            :enabled="instructor.is_featured" 
            label="Featured"
            active-class="bg-amber-500 border-amber-500/50 shadow-amber-900/20"
            icon-active-class="text-amber-500"
            icon="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
            @toggle="toggleFeatured(instructor)"
          />
        </div>

        <div class="col-span-1 flex justify-center">
          <TableBadgeCell 
            :label="instructor.status" 
            sub-label="Toggle Status"
            :variant="instructor.status === 'approved' ? 'emerald' : 'slate'"
            @action="toggleStatus(instructor)"
          />
        </div>

        <!-- Actions -->
        <div class="col-span-2 text-right flex justify-end">
          <TableActionDock>
              <router-link :to="{ name: 'instructors.show', params: { id: instructor.id } }" class="w-10 h-10 flex items-center justify-center rounded-2xl text-indigo-500 hover:bg-indigo-500/10 transition-all active:scale-90"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></router-link>
              <div class="w-px h-4 bg-slate-700/30"></div>
              <router-link :to="{ name: 'instructors.edit', params: { id: instructor.id } }" class="w-10 h-10 flex items-center justify-center rounded-2xl text-amber-500 hover:bg-amber-500/10 transition-all active:scale-90" title="Update Profile">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
              </router-link>
              <div class="w-px h-4 bg-slate-700/30"></div>
              <button @click="triggerDelete(instructor)" class="w-10 h-10 flex items-center justify-center rounded-2xl text-rose-500 hover:bg-rose-500/10 transition-all active:scale-90"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
          </TableActionDock>
        </div>
      </template>
    </DataTable>

    <!-- Global Action Dialog -->
    <ActionDialog
      :show="showDeleteModal"
      title="Irreversible Account Purge"
      :message="`You are about to permanently delete '${instructorToDelete?.name}'. This profile, their credentials, and all associated analytics will be erased from the secure cluster. Continue?`"
      :loading="deleting"
      @confirm="confirmDelete"
      @cancel="showDeleteModal = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import { instructorService } from '../../../services/instructorService';
import PageHeader from '../../../components/common/PageHeader.vue';
import TableActionDock from '../../../components/common/TableActionDock.vue';
import ActionDialog from '../../../components/common/ActionDialog.vue';
import DataTable from '../../../components/common/DataTable.vue';

// Import New Table Atom Components
import TableSLCell from '../../../components/common/table/TableSLCell.vue';
import TableIdentityCell from '../../../components/common/table/TableIdentityCell.vue';
import TableStatsCell from '../../../components/common/table/TableStatsCell.vue';
import TableToggleCell from '../../../components/common/table/TableToggleCell.vue';
import TableBadgeCell from '../../../components/common/table/TableBadgeCell.vue';

import { useToast } from '../../../composables/useToast';

const toast = useToast();

const tableHeaders = [
  { label: 'SL', span: 1, align: 'left' },
  { label: 'Instructor Identity', span: 3, align: 'left' },
  { label: 'Subject Matter', span: 2, align: 'left' },
  { label: 'Impact', span: 1, align: 'center' },
  { label: 'Revenue', span: 1, align: 'center' },
  { label: 'Featured', span: 1, align: 'center' },
  { label: 'Status', span: 1, align: 'center' },
  { label: 'Actions', span: 2, align: 'right' },
];

const instructors = ref([]);
const loading = ref(true);
const search = ref('');
const deleting = ref(false);
const showDeleteModal = ref(false);
const instructorToDelete = ref(null);

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
});

const fetchInstructors = async (page = 1) => {
  loading.value = true;
  try {
    const data = await instructorService.index({ 
      page, 
      search: search.value,
      per_page: pagination.per_page 
    });
    
    await new Promise(resolve => setTimeout(resolve, 300));
    
    instructors.value = data.data;
    const meta = data.meta;
    pagination.current_page = meta.current_page;
    pagination.last_page = meta.last_page;
    pagination.total = meta.total;
  } catch (error) {
    toast.error('Sync failed: Could not fetch educators');
  } finally {
    loading.value = false;
  }
};

const formatSL = (index) => {
  const sl = (pagination.current_page - 1) * pagination.per_page + index + 1;
  return sl < 10 ? `0${sl}` : sl;
};

let debounceTimer;
const debounceSearch = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    fetchInstructors(1);
  }, 600);
};

const toggleStatus = async (instructor) => {
  try {
    const data = await instructorService.toggleStatus(instructor.id);
    instructor.status = data.instructor.status;
    toast.success(`Access updated: ${data.instructor.status}`);
  } catch (error) {
    toast.error('Governance update failed');
  }
};

const toggleFeatured = async (instructor) => {
  try {
    await instructorService.toggleFeatured(instructor.id);
    instructor.is_featured = !instructor.is_featured;
    toast.success(instructor.is_featured ? 'Highlighted on Mainframe' : 'Removed from Spotlight');
  } catch (error) {
    toast.error('Highlighting failed');
  }
};

const triggerDelete = (instructor) => {
  instructorToDelete.value = instructor;
  showDeleteModal.value = true;
};

const confirmDelete = async () => {
  if (!instructorToDelete.value) return;
  
  deleting.value = true;
  try {
    await instructorService.destroy(instructorToDelete.value.id);
    toast.success('Nucleus purged: Mentor account deleted');
    showDeleteModal.value = false;
    instructorToDelete.value = null;
    fetchInstructors(pagination.current_page);
  } catch (error) {
    toast.error(error.response?.data?.message || 'Purge interrupted');
  } finally {
    deleting.value = false;
  }
};

onMounted(() => {
  fetchInstructors();
});
</script>
