<template>
  <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <!-- Premium Dashboard Header Component -->
    <PageHeader title="Assignments" subtitle="Create, manage, and grade student assignments across all courses."
      v-model="search" search-placeholder="Search assignments..." @search="debounceSearch">
      <template #icon>
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
      </template>
      <template #actions>
        <button @click="openAddModal"
          class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-2xl font-bold flex items-center justify-center gap-2 shadow-lg shadow-indigo-600/20 active:scale-95 transition-all">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          <span class="hidden sm:inline">Add Assignment</span>
        </button>
      </template>
    </PageHeader>

    <!-- Filter Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 custom-scrollbar">
      <button v-for="filter in filters" :key="filter.value" @click="selectedFilter = filter.value; fetchAssignments(1)"
        class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all whitespace-nowrap"
        :class="selectedFilter === filter.value
          ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20'
          : 'theme-bg-element theme-text-dim hover:theme-text-main'">
        {{ filter.label }}
      </button>
    </div>

    <!-- Dynamic DataTable Engine -->
    <DataTable :headers="tableHeaders" :items="assignments" :loading="loading" :pagination="pagination"
      empty-title="No assignments found"
      empty-message="Start creating assignments to track student progress and submissions."
      @page-change="fetchAssignments">
      <template #row="{ item: assignment, index }">
        <!-- SL Column -->
        <TableSLCell :index="formatSL(index)" />

        <!-- Assignment Details Column -->
        <div class="col-span-3">
          <div class="flex items-start gap-4">
            <div
              class="w-12 h-12 rounded-xl flex items-center justify-center text-lg font-bold border-2 theme-border shadow-inner transition-transform duration-500 group-hover:scale-110 bg-indigo-500/10 border-indigo-500/20">
              <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <div class="flex flex-col flex-1">
              <span class="text-sm font-black theme-text-main tracking-tight transition-colors duration-300">{{
                assignment.title }}</span>
              <span class="text-[9px] theme-text-dim font-bold uppercase tracking-widest opacity-70">{{
                assignment.course?.title || 'No Course' }}</span>
              <div class="flex items-center gap-2 mt-2">
                <span
                  class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest bg-indigo-500/10 text-indigo-600 border border-indigo-500/20">
                  {{ assignment.total_points }} Points
                </span>
                <span v-if="assignment.is_overdue"
                  class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest bg-rose-500/10 text-rose-600 border border-rose-500/20">
                  Overdue
                </span>
                <span v-else-if="assignment.time_remaining"
                  class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest bg-amber-500/10 text-amber-600 border border-amber-500/20">
                  {{ assignment.time_remaining }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Submissions Column -->
        <div class="col-span-2 text-center">
          <div class="inline-flex flex-col items-center">
            <span class="text-base font-black theme-text-main leading-none transition-transform duration-300">{{
              assignment.submissions_count || 0 }}</span>
            <span class="text-[8px] font-bold theme-text-dim uppercase tracking-tighter">Submissions</span>
          </div>
        </div>

        <!-- Due Date Column -->
        <div class="col-span-2">
          <p class="text-[11px] font-medium theme-text-dim leading-relaxed">
            {{ assignment.due_date ? formatDate(assignment.due_date) : 'No deadline' }}
          </p>
        </div>

        <!-- Required Column -->
        <div class="col-span-1 flex justify-center">
          <StatusToggle v-model="assignment.is_required" @toggle="toggleRequired(assignment)" />
        </div>

        <!-- Status Column -->
        <div class="col-span-1 flex justify-center">
          <StatusToggle v-model="assignment.is_published" @toggle="togglePublished(assignment)" />
        </div>

        <!-- Actions Column -->
        <div class="col-span-2 text-right">
          <div class="flex items-center justify-end">
            <TableActionDock>
              <button @click="viewStatistics(assignment)"
                class="w-9 h-9 flex items-center justify-center rounded-xl text-indigo-500 hover:bg-indigo-500/10 transition-all active:scale-90"
                title="View Statistics">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
              </button>
              <div class="w-px h-4 bg-slate-700/50"></div>
              <button @click="editAssignment(assignment)"
                class="w-9 h-9 flex items-center justify-center rounded-xl text-amber-500 hover:bg-amber-500/10 transition-all active:scale-90"
                title="Edit Assignment">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
              </button>
              <div class="w-px h-4 bg-slate-700/50"></div>
              <button @click="triggerDelete(assignment)"
                class="w-9 h-9 flex items-center justify-center rounded-xl text-rose-500 hover:bg-rose-500/10 transition-all active:scale-90"
                title="Delete Assignment">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </TableActionDock>
          </div>
        </div>
      </template>
    </DataTable>

    <!-- Side Over Panel / Modal for Add/Edit -->
    <transition name="panel">
      <div v-if="showModal" class="fixed inset-0 z-100 overflow-hidden">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="closeModal"></div>

        <div
          class="absolute inset-y-0 right-0 w-full max-w-2xl theme-bg-card border-l theme-border shadow-2xl flex flex-col">
          <div class="p-8 border-b theme-border flex items-center justify-between">
            <div>
              <h2 class="text-xl font-black theme-text-main tracking-tight">{{ isEditing ? 'Edit' : 'Add' }} Assignment
              </h2>
              <p class="text-xs theme-text-dim font-medium">{{ isEditing ? 'Modify existing assignment details' :
                'Create a new assignment for your course' }}</p>
            </div>
            <button @click="closeModal" class="p-2 theme-text-muted hover:theme-text-main transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="flex-1 overflow-y-auto p-8 custom-scrollbar">
            <form @submit.prevent="saveAssignment" class="space-y-6">
              <FormDropdown v-model="form.course_id" label="Course" placeholder="Select Course" :options="courseOptions"
                :error="errors.course_id" required />

              <FormInput v-model="form.title" label="Assignment Title" placeholder="e.g. Week 1 Project"
                :error="errors.title" required />

              <div class="space-y-2">
                <label class="text-[10px] font-black theme-text-dim uppercase tracking-[0.2em] ml-2">Description</label>
                <textarea v-model="form.description" rows="3"
                  class="w-full px-6 py-4 rounded-2xl theme-bg-element border-2 theme-border outline-none focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10 transition-all text-sm font-bold theme-text-main"
                  placeholder="Brief description of the assignment"></textarea>
              </div>

              <div class="space-y-2">
                <label
                  class="text-[10px] font-black theme-text-dim uppercase tracking-[0.2em] ml-2">Instructions</label>
                <textarea v-model="form.instructions" rows="4"
                  class="w-full px-6 py-4 rounded-2xl theme-bg-element border-2 theme-border outline-none focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10 transition-all text-sm font-bold theme-text-main"
                  placeholder="Detailed instructions for students"></textarea>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <FormInput v-model.number="form.total_points" label="Total Points" type="number" placeholder="100"
                  :error="errors.total_points" required />

                <FormInput v-model.number="form.passing_points" label="Passing Points" type="number" placeholder="60"
                  :error="errors.passing_points" required />
              </div>

              <FormInput v-model="form.due_date" label="Due Date" type="datetime-local" :error="errors.due_date" />

              <div class="grid grid-cols-2 gap-4">
                <FormInput v-model.number="form.max_submissions" label="Max Submissions" type="number" placeholder="3"
                  :error="errors.max_submissions" />

                <FormInput v-model.number="form.max_file_size" label="Max File Size (MB)" type="number" placeholder="10"
                  :error="errors.max_file_size" />
              </div>

              <div class="flex items-center gap-6 py-2">
                <div class="flex items-center gap-3">
                  <StatusToggle v-model="form.allow_late_submission" />
                  <span class="text-xs font-black theme-text-main uppercase tracking-widest">Allow Late</span>
                </div>

                <FormInput v-if="form.allow_late_submission" v-model.number="form.late_submission_penalty"
                  label="Penalty %" type="number" placeholder="10" class="flex-1" />
              </div>

              <div class="flex items-center gap-6 py-2">
                <div class="flex items-center gap-3">
                  <StatusToggle v-model="form.is_required" />
                  <span class="text-xs font-black theme-text-main uppercase tracking-widest">Required</span>
                </div>

                <div class="flex items-center gap-3">
                  <StatusToggle v-model="form.is_published" />
                  <span class="text-xs font-black theme-text-main uppercase tracking-widest">Published</span>
                </div>
              </div>

              <div class="pt-4 flex items-center gap-4">
                <button type="submit" :disabled="saving"
                  class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white py-4 rounded-2xl font-bold shadow-lg shadow-indigo-600/20 active:scale-95 transition-all flex items-center justify-center gap-2 disabled:opacity-50">
                  <svg v-if="saving" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                  </svg>
                  <span>{{ isEditing ? 'Update Assignment' : 'Create Assignment' }}</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </transition>

    <!-- Statistics Modal -->
    <transition name="fade">
      <div v-if="showStatsModal" class="fixed inset-0 z-100 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showStatsModal = false"></div>

        <div class="relative w-full max-w-4xl theme-bg-card border theme-border rounded-3xl shadow-2xl p-8">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h2 class="text-2xl font-black theme-text-main tracking-tight">Assignment Statistics</h2>
              <p class="text-sm theme-text-dim font-medium">{{ selectedAssignment?.title }}</p>
            </div>
            <button @click="showStatsModal = false"
              class="p-2 theme-text-muted hover:theme-text-main transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div v-if="loadingStats" class="flex items-center justify-center py-12">
            <svg class="animate-spin h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
              </path>
            </svg>
          </div>

          <div v-else-if="statistics" class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="p-6 theme-bg-element rounded-2xl">
              <p class="text-xs font-bold theme-text-dim uppercase tracking-widest mb-2">Total Submissions</p>
              <p class="text-3xl font-black theme-text-main">{{ statistics.total_submissions }}</p>
            </div>
            <div class="p-6 theme-bg-element rounded-2xl">
              <p class="text-xs font-bold theme-text-dim uppercase tracking-widest mb-2">Graded</p>
              <p class="text-3xl font-black text-emerald-500">{{ statistics.graded_count }}</p>
            </div>
            <div class="p-6 theme-bg-element rounded-2xl">
              <p class="text-xs font-bold theme-text-dim uppercase tracking-widest mb-2">Average Score</p>
              <p class="text-3xl font-black text-indigo-500">{{ statistics.average_score }}</p>
            </div>
            <div class="p-6 theme-bg-element rounded-2xl">
              <p class="text-xs font-bold theme-text-dim uppercase tracking-widest mb-2">Pass Rate</p>
              <p class="text-3xl font-black text-amber-500">{{ statistics.pass_rate }}%</p>
            </div>
            <div class="p-6 theme-bg-element rounded-2xl">
              <p class="text-xs font-bold theme-text-dim uppercase tracking-widest mb-2">Needs Grading</p>
              <p class="text-3xl font-black text-rose-500">{{ statistics.needs_grading_count }}</p>
            </div>
            <div class="p-6 theme-bg-element rounded-2xl">
              <p class="text-xs font-bold theme-text-dim uppercase tracking-widest mb-2">Passed</p>
              <p class="text-3xl font-black text-emerald-500">{{ statistics.passed_count }}</p>
            </div>
            <div class="p-6 theme-bg-element rounded-2xl">
              <p class="text-xs font-bold theme-text-dim uppercase tracking-widest mb-2">Failed</p>
              <p class="text-3xl font-black text-rose-500">{{ statistics.failed_count }}</p>
            </div>
            <div class="p-6 theme-bg-element rounded-2xl">
              <p class="text-xs font-bold theme-text-dim uppercase tracking-widest mb-2">Late Submissions</p>
              <p class="text-3xl font-black text-amber-500">{{ statistics.late_submissions }}</p>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <!-- Global Action Dialog -->
    <ActionDialog :show="showDeleteModal" title="Delete Assignment"
      :message="`Are you sure you want to delete '${assignmentToDelete?.title}'? This action cannot be undone.`"
      :loading="deleting" @confirm="confirmDelete" @cancel="showDeleteModal = false" />
  </div>
</template>

<script setup>
import { ref, onMounted, reactive, computed } from 'vue';
import { assignmentService } from '../../../services/assignmentService';
import { courseService } from '../../../services/courseService';
import PageHeader from '../../../components/common/PageHeader.vue';
import TableActionDock from '../../../components/common/TableActionDock.vue';
import TableSLCell from '../../../components/common/table/TableSLCell.vue';
import FormInput from '../../../components/common/FormInput.vue';
import FormDropdown from '../../../components/common/FormDropdown.vue';
import ActionDialog from '../../../components/common/ActionDialog.vue';
import DataTable from '../../../components/common/DataTable.vue';
import StatusToggle from '../../../components/common/StatusToggle.vue';
import { useToast } from '../../../composables/useToast';

const toast = useToast();

const filters = [
  { label: 'All', value: 'all' },
  { label: 'Published', value: 'published' },
  { label: 'Draft', value: 'draft' },
  { label: 'Required', value: 'required' },
  { label: 'Overdue', value: 'overdue' },
];

const tableHeaders = [
  { label: 'SL', span: 1, align: 'left' },
  { label: 'Assignment Details', span: 3, align: 'left' },
  { label: 'Submissions', span: 2, align: 'center' },
  { label: 'Due Date', span: 2, align: 'left' },
  { label: 'Required', span: 1, align: 'center' },
  { label: 'Status', span: 1, align: 'center' },
  { label: 'Actions', span: 2, align: 'right' },
];

const assignments = ref([]);
const courses = ref([]);
const loading = ref(true);
const search = ref('');
const selectedFilter = ref('all');
const showModal = ref(false);
const isEditing = ref(false);
const saving = ref(false);
const deleting = ref(false);
const selectedId = ref(null);
const showDeleteModal = ref(false);
const assignmentToDelete = ref(null);
const showStatsModal = ref(false);
const selectedAssignment = ref(null);
const statistics = ref(null);
const loadingStats = ref(false);

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
});

const form = reactive({
  course_id: null,
  lesson_id: null,
  title: '',
  description: '',
  instructions: '',
  total_points: 100,
  passing_points: 60,
  due_date: '',
  allow_late_submission: true,
  late_submission_penalty: 0,
  max_file_size: 10,
  max_submissions: 1,
  is_required: false,
  is_published: true,
});

const errors = ref({});

const courseOptions = computed(() => {
  return courses.value.map(course => ({
    label: course.title,
    value: course.id
  }));
});

const fetchAssignments = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      page,
      search: search.value,
      per_page: pagination.per_page
    };

    if (selectedFilter.value === 'published') params.is_published = true;
    if (selectedFilter.value === 'draft') params.is_published = false;
    if (selectedFilter.value === 'required') params.is_required = true;
    if (selectedFilter.value === 'overdue') params.status = 'overdue';

    const data = await assignmentService.index(params);
    await new Promise(resolve => setTimeout(resolve, 500));

    assignments.value = data.data;
    const meta = data.meta;
    pagination.current_page = meta.current_page;
    pagination.last_page = meta.last_page;
    pagination.total = meta.total;
  } catch (error) {
    toast.error('Failed to fetch assignments');
  } finally {
    loading.value = false;
  }
};

const fetchCourses = async () => {
  try {
    const data = await courseService.index({ per_page: 100 });
    courses.value = data.data;
  } catch (error) {
    console.error('Error fetching courses');
  }
};

const formatSL = (index) => {
  const sl = (pagination.current_page - 1) * pagination.per_page + index + 1;
  return sl < 10 ? `0${sl}` : sl;
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

let debounceSearchTimer;
const debounceSearch = () => {
  clearTimeout(debounceSearchTimer);
  debounceSearchTimer = setTimeout(() => {
    fetchAssignments(1);
  }, 500);
};

const openAddModal = () => {
  isEditing.value = false;
  selectedId.value = null;
  resetForm();
  showModal.value = true;
  fetchCourses();
};

const editAssignment = (assignment) => {
  isEditing.value = true;
  selectedId.value = assignment.id;
  Object.assign(form, {
    course_id: assignment.course_id,
    lesson_id: assignment.lesson_id,
    title: assignment.title,
    description: assignment.description || '',
    instructions: assignment.instructions || '',
    total_points: assignment.total_points,
    passing_points: assignment.passing_points,
    due_date: assignment.due_date ? assignment.due_date.slice(0, 16) : '',
    allow_late_submission: !!assignment.allow_late_submission,
    late_submission_penalty: assignment.late_submission_penalty || 0,
    max_file_size: assignment.max_file_size || 10,
    max_submissions: assignment.max_submissions || 1,
    is_required: !!assignment.is_required,
    is_published: !!assignment.is_published,
  });
  showModal.value = true;
  fetchCourses();
};

const resetForm = () => {
  Object.assign(form, {
    course_id: null,
    lesson_id: null,
    title: '',
    description: '',
    instructions: '',
    total_points: 100,
    passing_points: 60,
    due_date: '',
    allow_late_submission: true,
    late_submission_penalty: 0,
    max_file_size: 10,
    max_submissions: 1,
    is_required: false,
    is_published: true,
  });
  errors.value = {};
};

const closeModal = () => {
  showModal.value = false;
};

const saveAssignment = async () => {
  saving.value = true;
  errors.value = {};
  try {
    if (isEditing.value) {
      await assignmentService.update(selectedId.value, form);
      toast.success('Assignment updated successfully');
    } else {
      await assignmentService.store(form);
      toast.success('Assignment created successfully');
    }
    closeModal();
    fetchAssignments(pagination.current_page);
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

const triggerDelete = (assignment) => {
  assignmentToDelete.value = assignment;
  showDeleteModal.value = true;
};

const confirmDelete = async () => {
  if (!assignmentToDelete.value) return;

  deleting.value = true;
  try {
    await assignmentService.destroy(assignmentToDelete.value.id);
    toast.success('Assignment deleted successfully');
    showDeleteModal.value = false;
    assignmentToDelete.value = null;
    fetchAssignments(pagination.current_page);
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to delete assignment');
  } finally {
    deleting.value = false;
  }
};

const togglePublished = async (assignment) => {
  try {
    await assignmentService.togglePublished(assignment.id);
    assignment.is_published = !assignment.is_published;
    toast.success(`Assignment ${assignment.is_published ? 'published' : 'unpublished'} successfully`);
  } catch (error) {
    toast.error('Failed to update status');
  }
};

const toggleRequired = async (assignment) => {
  try {
    await assignmentService.toggleRequired(assignment.id);
    assignment.is_required = !assignment.is_required;
    toast.success(`Assignment ${assignment.is_required ? 'marked as required' : 'marked as optional'}`);
  } catch (error) {
    toast.error('Failed to update required status');
  }
};

const viewStatistics = async (assignment) => {
  selectedAssignment.value = assignment;
  showStatsModal.value = true;
  loadingStats.value = true;

  try {
    statistics.value = await assignmentService.getStatistics(assignment.id);
  } catch (error) {
    toast.error('Failed to load statistics');
  } finally {
    loadingStats.value = false;
  }
};

onMounted(() => {
  fetchAssignments();
});
</script>

<style scoped>
.panel-enter-active,
.panel-leave-active {
  transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}

.panel-enter-from,
.panel-leave-to {
  transform: translateX(100%);
  opacity: 0;
}

.fade-enter-active,
.fade-leave-active {
  transition: all 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: scale(0.95);
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
