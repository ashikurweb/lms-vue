<template>
  <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <!-- Page Header -->
    <PageHeader title="Discussions" subtitle="Manage forum discussions and Q&A with style." v-model="search"
      :add-route="{ name: 'discussions.create' }" add-label="Add Discussion" search-placeholder="Search discussions..."
      @search="debounceSearch">
      <template #icon>
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
            d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
        </svg>
      </template>
    </PageHeader>

    <!-- Table -->
    <DataTable :headers="tableHeaders" :loading="loading" :items="discussions" :pagination="pagination"
      empty-title="No discussions found" empty-message="Start a new trend! Initiate your first forum topic."
      @page-change="fetchDiscussions">
      <template #row="{ item, index }">
        <!-- SL Column -->
        <TableSLCell :index="formatSL(index)" />

        <!-- Discussion Details -->
        <div class="col-span-3">
          <div class="flex items-center gap-4">
            <div
              class="relative w-12 h-12 rounded-xl flex items-center justify-center shrink-0 border theme-border bg-indigo-500/5 text-indigo-500">
              <span class="text-lg font-black">{{ item.title.charAt(0).toUpperCase() }}</span>
            </div>
            <div class="flex flex-col min-w-0">
              <h4 class="text-sm font-black theme-text-main truncate pr-4">{{ item.title }}</h4>
              <div class="flex items-center gap-2 mt-1">
                <span
                  class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-indigo-500/10 text-indigo-500 uppercase tracking-tighter">
                  {{ item.type }}
                </span>
                <span class="text-[10px] theme-text-dim font-medium">• {{ formatDate(item.created_at) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Stats -->
        <div class="col-span-2">
          <div class="flex items-center gap-2">
            <div
              class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/50"
              title="Replies">
              <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
              </svg>
              <span class="text-xs font-bold theme-text-main">{{ formatNumber(item.replies_count) }}</span>
            </div>
            <div class="flex items-center gap-3 pl-3 border-l border-slate-200 dark:border-slate-700/50">
              <div class="flex items-center gap-1" title="Upvotes">
                <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                </svg>
                <span class="text-xs font-semibold theme-text-muted">{{ formatNumber(item.upvotes_count) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- User Info -->
        <div class="col-span-2">
          <div class="flex flex-col">
            <span class="text-xs font-black theme-text-main">{{ item.user?.name || 'Anonymous' }}</span>
            <span class="text-[10px] theme-text-dim font-medium truncate max-w-[150px]">{{ item.course?.title ||
              'General' }}</span>
          </div>
        </div>

        <!-- Status -->
        <div class="col-span-1 text-center">
          <span class="inline-flex px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest" :class="{
            'bg-emerald-500/10 text-emerald-500': item.status === 'open',
            'bg-blue-500/10 text-blue-500': item.status === 'answered',
            'bg-rose-500/10 text-rose-500': item.status === 'flagged',
            'bg-slate-500/10 text-slate-500': item.status === 'closed'
          }">
            {{ item.status }}
          </span>
        </div>

        <!-- Featured & Pinned Icons -->
        <div class="col-span-1 text-center">
          <div class="flex items-center justify-center gap-2">
            <FeaturedToggle :model-value="!!item.is_featured" @toggle="toggleFeatured(item)" />
            <div v-if="item.is_pinned"
              class="w-6 h-6 rounded-lg bg-indigo-500 shadow-[0_0_15px_rgba(79,70,229,0.4)] text-white flex items-center justify-center"
              title="Pinned">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                  d="M5 5l.183.183m0 0L19 19m-13.817 0L19 5.232" />
              </svg>
              <!-- Better Pin Icon -->
              <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                  d="M10 2a1 1 0 011 1v1.323l3.954 1.582a1 1 0 01.586.928v4.741c0 .445-.298.835-.718.948l-3.822 1.02a1 1 0 01-.1-.018l-.9-.126V15l1.707 1.707A1 1 0 0110 18a1 1 0 011-1 1 1 0 01-1-1v-.293l-1.707-1.707A1 1 0 017 14V13.34l-.9.126a1 1 0 01-1.1-1.02V7.75c0-.422.259-.8.647-.948l3.353-1.323V3a1 1 0 011-1z"
                  clip-rule="evenodd" />
              </svg>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="col-span-2 text-right">
          <div class="flex items-center justify-end">
            <TableActionDock>
              <router-link :to="{ name: 'discussions.edit', params: { uuid: item.uuid } }"
                class="w-9 h-9 flex items-center justify-center rounded-xl text-amber-500 hover:bg-amber-500/10 transition-all active:scale-90"
                title="Edit Discussion">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
              </router-link>
              <div class="w-px h-4 bg-slate-700/50"></div>
              <button @click="triggerDelete(item)"
                class="w-9 h-9 flex items-center justify-center rounded-xl text-rose-500 hover:bg-rose-500/10 transition-all active:scale-90"
                title="Delete Discussion">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
              <div class="w-px h-4 bg-slate-700/50"></div>
              <button @click="toggleStatus(item)"
                class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-400 hover:theme-text-main transition-all active:scale-90"
                :title="item.status === 'open' ? 'Close Discussion' : 'Open Discussion'">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                </svg>
              </button>
            </TableActionDock>
          </div>
        </div>
      </template>
    </DataTable>

    <!-- Delete Modal -->
    <ActionDialog :show="showDeleteModal" title="Delete Discussion"
      :message="`Are you sure you want to delete the discussion '${discussionToDelete?.title}'? This action cannot be undone.`"
      :loading="deleting" @confirm="confirmDelete" @cancel="showDeleteModal = false" />
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import { discussionService } from '../../../services/discussionService';
import PageHeader from '../../../components/common/PageHeader.vue';
import TableActionDock from '../../../components/common/TableActionDock.vue';
import TableSLCell from '../../../components/common/table/TableSLCell.vue';
import FeaturedToggle from '../../../components/common/FeaturedToggle.vue';
import ActionDialog from '../../../components/common/ActionDialog.vue';
import DataTable from '../../../components/common/DataTable.vue';
import { useToast } from '../../../composables/useToast';
import { formatDate, formatNumber, truncateText } from '../../../utils/helpers';

const toast = useToast();

const tableHeaders = [
  { label: 'SL', span: 1, align: 'left' },
  { label: 'Details', span: 3, align: 'left' },
  { label: 'Stats', span: 2, align: 'left' },
  { label: 'User', span: 2, align: 'left' },
  { label: 'Status', span: 1, align: 'center' },
  { label: 'Feat', span: 1, align: 'center' },
  { label: 'Actions', span: 2, align: 'right' },
];

const discussions = ref([]);
const loading = ref(true);
const search = ref('');
const showDeleteModal = ref(false);
const discussionToDelete = ref(null);
const deleting = ref(false);

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
});

const fetchDiscussions = async (page = 1) => {
  loading.value = true;
  try {
    const data = await discussionService.index({
      page,
      search: search.value,
      per_page: pagination.per_page
    });
    discussions.value = data.data;
    const meta = data.meta;
    pagination.current_page = meta.current_page;
    pagination.last_page = meta.last_page;
    pagination.total = meta.total;
  } catch (error) {
    toast.error('Failed to fetch discussions');
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
    fetchDiscussions(1);
  }, 500);
};

const triggerDelete = (discussion) => {
  discussionToDelete.value = discussion;
  showDeleteModal.value = true;
};

const confirmDelete = async () => {
  if (!discussionToDelete.value) return;

  deleting.value = true;
  try {
    await discussionService.destroy(discussionToDelete.value.uuid);
    toast.success('Discussion deleted successfully');
    showDeleteModal.value = false;
    discussionToDelete.value = null;
    fetchDiscussions(pagination.current_page);
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to delete discussion');
  } finally {
    deleting.value = false;
  }
};

const toggleStatus = async (discussion) => {
  try {
    await discussionService.toggleStatus(discussion.uuid);
    discussion.status = discussion.status === 'open' ? 'closed' : 'open';
    toast.success(`Discussion ${discussion.status === 'open' ? 'opened' : 'closed'} successfully`);
  } catch (error) {
    toast.error('Failed to update status');
  }
};

const toggleFeatured = async (discussion) => {
  try {
    await discussionService.toggleFeatured(discussion.uuid);
    discussion.is_featured = !discussion.is_featured;
    toast.success(`Discussion ${discussion.is_featured ? 'marked as featured' : 'removed from featured'}`);
  } catch (error) {
    toast.error('Failed to update featured status');
  }
};

const togglePinned = async (discussion) => {
  try {
    await discussionService.togglePinned(discussion.uuid);
    discussion.is_pinned = !discussion.is_pinned;
    toast.success(`Discussion ${discussion.is_pinned ? 'pinned' : 'unpinned'} successfully`);
  } catch (error) {
    toast.error('Failed to update pinned status');
  }
};

onMounted(() => {
  fetchDiscussions();
});
</script>
