<template>
  <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <!-- Page Header -->
    <PageHeader title="Quiz Management" subtitle="Create and manage quizzes, questions, and student attempts."
      v-model="search" add-label="Add Quiz" search-placeholder="Search quizzes..." @search="fetchQuizzes"
      @add="openAddModal">
      <template #icon>
        <div
          class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-600/20">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
              d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.989-2.386l-.548-.547z" />
          </svg>
        </div>
      </template>
    </PageHeader>

    <!-- Filters -->
    <div class="flex items-center gap-2 p-1.5 theme-bg-card border theme-border rounded-2xl w-fit">
      <button v-for="filter in filters" :key="filter.value" @click="setFilter(filter.value)"
        class="px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all duration-300"
        :class="selectedFilter === filter.value ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'theme-text-dim hover:theme-bg-element'">
        {{ filter.label }}
      </button>
    </div>

    <!-- Data Table -->
    <DataTable :headers="tableHeaders" :items="quizzes" :loading="loading" :pagination="pagination"
      empty-title="No quizzes found"
      empty-message="You haven't created any quizzes for this course yet. Click 'Add Quiz' to get started."
      @page-change="fetchQuizzes">
      <template #row="{ item: quiz, index }">
        <TableSLCell :index="formatSL(index)" />

        <div class="col-span-3">
          <div class="flex flex-col gap-1">
            <span class="text-sm font-black theme-text-main tracking-tight leading-tight">{{ quiz.title }}</span>
            <div class="flex items-center gap-2">
              <span class="text-[10px] font-black theme-text-dim uppercase tracking-widest">{{ quiz.course?.title
              }}</span>
              <span v-if="quiz.lesson" class="w-1 h-1 rounded-full bg-slate-600"></span>
              <span v-if="quiz.lesson" class="text-[10px] font-medium theme-text-muted">{{ quiz.lesson?.title }}</span>
            </div>
          </div>
        </div>

        <div class="col-span-1 flex flex-col items-center">
          <span class="text-xs font-black theme-text-main">{{ quiz.total_questions }}</span>
          <span class="text-[9px] font-bold theme-text-dim uppercase tracking-tighter">Questions</span>
        </div>

        <div class="col-span-1 flex flex-col items-center">
          <span class="text-xs font-black theme-text-main">{{ quiz.time_limit ? quiz.time_limit + 'm' : '∞' }}</span>
          <span class="text-[9px] font-bold theme-text-dim uppercase tracking-tighter">Time</span>
        </div>

        <div class="col-span-1 flex flex-col items-center">
          <span class="text-xs font-black theme-text-main">{{ quiz.passing_score }}%</span>
          <span class="text-[9px] font-bold theme-text-dim uppercase tracking-tighter">Passing</span>
        </div>

        <div class="col-span-1 flex justify-center">
          <StatusToggle v-model="quiz.is_required" @toggle="toggleRequired(quiz)" />
        </div>

        <div class="col-span-1 flex justify-center">
          <StatusToggle v-model="quiz.is_published" @toggle="togglePublished(quiz)" />
        </div>

        <!-- Actions -->
        <div class="col-span-2 flex justify-end">
          <TableActionDock>
            <button @click="viewStats(quiz)"
              class="w-9 h-9 flex items-center justify-center rounded-xl text-indigo-500 hover:bg-indigo-500/10 transition-all active:scale-90"
              title="View Statistics">
              <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </button>

            <button @click="editQuiz(quiz)"
              class="w-9 h-9 flex items-center justify-center rounded-xl text-amber-500 hover:bg-amber-500/10 transition-all active:scale-90"
              title="Edit Quiz">
              <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
            </button>

            <button @click="triggerDelete(quiz)"
              class="w-9 h-9 flex items-center justify-center rounded-xl text-rose-500 hover:bg-rose-500/10 transition-all active:scale-90"
              title="Delete Quiz">
              <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </TableActionDock>
        </div>
      </template>
    </DataTable>

    <!-- Side Panel Modal (Add/Edit) -->
    <transition name="panel">
      <div v-if="showModal" class="fixed inset-0 z-[100] overflow-hidden">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="closeModal"></div>

        <div
          class="absolute inset-y-0 right-0 w-full max-w-2xl theme-bg-card border-l theme-border shadow-2xl flex flex-col">
          <!-- Modal Header -->
          <div class="p-8 border-b theme-border flex items-center justify-between">
            <div>
              <h2 class="text-xl font-black theme-text-main tracking-tight">{{ isEditing ? 'Edit' : 'Add' }} Quiz</h2>
              <p class="text-xs theme-text-dim font-medium">{{ isEditing ? 'Modify existing quiz configuration' :
                'Create a new quiz for your course' }}</p>
            </div>
            <button @click="closeModal" class="p-2 theme-text-muted hover:theme-text-main transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Modal Body -->
          <div class="flex-1 overflow-y-auto p-8 custom-scrollbar">
            <form @submit.prevent="saveQuiz" class="space-y-6">
              <!-- Basic Info -->
              <div class="space-y-6">
                <div class="flex items-center gap-3 mb-2">
                  <div class="w-8 h-8 rounded-lg bg-indigo-600/10 flex items-center justify-center">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                  <h3 class="text-sm font-black theme-text-main uppercase tracking-widest">Basic Information</h3>
                </div>

                <FormDropdown v-model="form.course_id" label="Course" placeholder="Select Course"
                  :options="courseOptions" :error="errors.course_id" required />

                <FormInput v-model="form.title" label="Quiz Title" placeholder="e.g. Final Examination"
                  :error="errors.title" required />

                <div class="space-y-2">
                  <label
                    class="text-[10px] font-black theme-text-dim uppercase tracking-[0.2em] ml-2">Description</label>
                  <textarea v-model="form.description" rows="3"
                    class="w-full px-6 py-4 rounded-2xl theme-bg-element border-2 theme-border outline-none focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10 transition-all text-sm font-bold theme-text-main"
                    placeholder="Brief description of the quiz"></textarea>
                </div>

                <div class="space-y-2">
                  <label
                    class="text-[10px] font-black theme-text-dim uppercase tracking-[0.2em] ml-2">Instructions</label>
                  <textarea v-model="form.instructions" rows="4"
                    class="w-full px-6 py-4 rounded-2xl theme-bg-element border-2 theme-border outline-none focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10 transition-all text-sm font-bold theme-text-main"
                    placeholder="Detailed instructions for students"></textarea>
                </div>
              </div>

              <!-- Configuration -->
              <div class="pt-6 border-t theme-border space-y-6">
                <div class="flex items-center gap-3 mb-2">
                  <div class="w-8 h-8 rounded-lg bg-amber-600/10 flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    </svg>
                  </div>
                  <h3 class="text-sm font-black theme-text-main uppercase tracking-widest">Configuration</h3>
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <FormInput v-model.number="form.time_limit" label="Time Limit (Min)" type="number"
                    placeholder="0 = Unlimited" :error="errors.time_limit" />
                  <FormInput v-model.number="form.passing_score" label="Passing Score (%)" type="number"
                    placeholder="50" :error="errors.passing_score" required />
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <FormInput v-model.number="form.max_attempts" label="Max Attempts" type="number"
                    placeholder="0 = Unlimited" :error="errors.max_attempts" required />
                  <FormInput v-model.number="form.questions_per_page" label="Questions Per Page" type="number"
                    placeholder="1" :error="errors.questions_per_page" required />
                </div>

                <!-- Toggles -->
                <div class="space-y-4">
                  <div class="grid grid-cols-2 gap-4">
                    <div class="flex items-center gap-3 py-2 px-4 theme-bg-element rounded-2xl border theme-border">
                      <StatusToggle v-model="form.randomize_questions" />
                      <span class="text-[10px] font-black theme-text-main uppercase tracking-widest">Randomize
                        Questions</span>
                    </div>
                    <div class="flex items-center gap-3 py-2 px-4 theme-bg-element rounded-2xl border theme-border">
                      <StatusToggle v-model="form.randomize_options" />
                      <span class="text-[10px] font-black theme-text-main uppercase tracking-widest">Randomize
                        Options</span>
                    </div>
                  </div>

                  <div class="grid grid-cols-2 gap-4">
                    <div class="flex items-center gap-3 py-2 px-4 theme-bg-element rounded-2xl border theme-border">
                      <StatusToggle v-model="form.show_answers_after_submission" />
                      <span class="text-[10px] font-black theme-text-main uppercase tracking-widest">Show Results
                        After</span>
                    </div>
                    <div class="flex items-center gap-3 py-2 px-4 theme-bg-element rounded-2xl border theme-border">
                      <StatusToggle v-model="form.show_correct_answers" />
                      <span class="text-[10px] font-black theme-text-main uppercase tracking-widest">Show Correct
                        Answers</span>
                    </div>
                  </div>

                  <div class="grid grid-cols-2 gap-4">
                    <div class="flex items-center gap-3 py-2 px-4 theme-bg-element rounded-2xl border theme-border">
                      <StatusToggle v-model="form.allow_review" />
                      <span class="text-[10px] font-black theme-text-main uppercase tracking-widest">Allow Review</span>
                    </div>
                    <div class="flex items-center gap-3 py-2 px-4 theme-bg-element rounded-2xl border theme-border">
                      <StatusToggle v-model="form.is_required" />
                      <span
                        class="text-[10px] font-black theme-text-main uppercase tracking-widest tracking-tight">Required
                        To Pass</span>
                    </div>
                  </div>

                  <div
                    class="flex items-center gap-3 py-3 px-6 bg-emerald-500/5 rounded-2xl border border-emerald-500/20">
                    <StatusToggle v-model="form.is_published" />
                    <span class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Published
                      Status</span>
                  </div>
                </div>
              </div>

              <!-- Action Footer -->
              <div class="pt-8 flex items-center gap-4">
                <button type="submit" :disabled="saving"
                  class="flex-2 bg-indigo-600 hover:bg-indigo-500 text-white py-4 px-8 rounded-2xl font-black shadow-lg shadow-indigo-600/20 active:scale-95 transition-all flex items-center justify-center gap-3 disabled:opacity-50 min-w-[200px]">
                  <svg v-if="saving" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                  </svg>
                  <span class="uppercase tracking-widest text-sm">{{ isEditing ? 'Update Quiz' : 'Create Quiz' }}</span>
                </button>
                <button type="button" @click="closeModal"
                  class="flex-1 theme-bg-element theme-text-main py-4 px-6 rounded-2xl font-black active:scale-95 transition-all text-sm uppercase tracking-widest border theme-border hover:theme-bg-card">
                  Cancel
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </transition>

    <!-- Statistics Modal -->
    <transition name="fade">
      <div v-if="showStatsModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showStatsModal = false"></div>

        <div
          class="relative w-full max-w-4xl theme-bg-card border theme-border rounded-3xl shadow-2xl p-8 overflow-hidden">
          <div class="flex items-center justify-between mb-8">
            <div>
              <h2 class="text-2xl font-black theme-text-main tracking-tight">Quiz Analytics</h2>
              <p class="text-sm theme-text-dim font-medium">{{ selectedQuiz?.title }}</p>
            </div>
            <button @click="showStatsModal = false"
              class="p-2 theme-text-muted hover:theme-text-main transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div v-if="stats" class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div v-for="(val, label) in mappedStats" :key="label"
              class="p-6 rounded-3xl theme-bg-element border theme-border group hover:border-indigo-500/30 transition-all duration-500">
              <span class="text-[10px] font-black theme-text-dim uppercase tracking-[0.2em] block mb-2">{{ label
                }}</span>
              <div
                class="text-3xl font-black theme-text-main tabular-nums group-hover:text-indigo-500 transition-colors">
                {{ val }}{{ label.includes('Rate') || label.includes('Score') ? '%' : '' }}
              </div>
            </div>
          </div>
          <div v-else class="flex items-center justify-center py-20">
            <div class="animate-spin rounded-full h-12 w-12 border-4 border-indigo-600 border-t-transparent"></div>
          </div>
        </div>
      </div>
    </transition>

    <!-- Delete Confirmation -->
    <ActionDialog :show="showDeleteModal" title="Delete Quiz"
      :message="`Are you sure you want to delete '${quizToDelete?.title}'? This will also delete all associated questions, answers, and student attempts. This action cannot be undone.`"
      :loading="deleting" @confirm="confirmDelete" @cancel="showDeleteModal = false" />
  </div>
</template>

<script setup>
import { ref, onMounted, reactive, computed } from 'vue';
import { quizService } from '../../../services/quizService';
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
  { label: 'All Quizzes', value: 'all' },
  { label: 'Published', value: 'published' },
  { label: 'Drafts', value: 'draft' },
  { label: 'Required', value: 'required' },
];

const tableHeaders = [
  { label: 'SL', span: 1, align: 'left' },
  { label: 'Quiz Details', span: 3, align: 'left' },
  { label: 'Questions', span: 1, align: 'center' },
  { label: 'Time Limit', span: 1, align: 'center' },
  { label: 'Passing', span: 1, align: 'center' },
  { label: 'Required', span: 1, align: 'center' },
  { label: 'Status', span: 1, align: 'center' },
  { label: 'Actions', span: 2, align: 'right' },
];

const quizzes = ref([]);
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
const quizToDelete = ref(null);
const showStatsModal = ref(false);
const selectedQuiz = ref(null);
const stats = ref(null);

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
});

const form = reactive({
  course_id: '',
  lesson_id: null,
  title: '',
  description: '',
  instructions: '',
  time_limit: null,
  passing_score: 50,
  max_attempts: 0,
  questions_per_page: 1,
  show_answers_after_submission: true,
  show_correct_answers: true,
  randomize_questions: false,
  randomize_options: false,
  allow_review: true,
  is_required: false,
  is_published: true,
  order: 0,
});

const errors = ref({});

const courseOptions = computed(() => {
  return courses.value.map(c => ({
    value: c.id,
    label: c.title
  }));
});

const mappedStats = computed(() => {
  if (!stats.value) return {};
  return {
    'Attempts': stats.value.total_attempts,
    'Passed': stats.value.passed_count,
    'Avg Score': stats.value.average_score,
    'Pass Rate': stats.value.completion_rate,
    'Failed': stats.value.failed_count,
    'Students': stats.value.unique_students,
    'Needs Grading': stats.value.needs_grading,
    'Pending': stats.value.total_attempts - stats.value.passed_count - stats.value.failed_count
  };
});

const fetchQuizzes = async (page = 1) => {
  loading.value = true;
  try {
    const response = await quizService.index({
      page,
      search: search.value,
      filter: selectedFilter.value,
      per_page: pagination.per_page
    });

    // Laravel paginated response structure has 'data' key for the items array
    quizzes.value = response.data.data || [];

    if (response.data.meta) {
      const meta = response.data.meta;
      pagination.current_page = meta.current_page;
      pagination.last_page = meta.last_page;
      pagination.total = meta.total;
    }
  } catch (error) {
    console.error('Quiz fetch error:', error);
    toast.error('Failed to fetch quizzes');
  } finally {
    loading.value = false;
  }
};

const fetchCourses = async () => {
  try {
    const response = await courseService.getAll();
    courses.value = response.data || response;
  } catch (error) {
    console.error('Failed to fetch courses', error);
  }
};

const setFilter = (val) => {
  selectedFilter.value = val;
  fetchQuizzes();
};

const openAddModal = () => {
  isEditing.value = false;
  selectedId.value = null;
  Object.assign(form, {
    course_id: '',
    lesson_id: null,
    title: '',
    description: '',
    instructions: '',
    time_limit: null,
    passing_score: 50,
    max_attempts: 0,
    questions_per_page: 1,
    show_answers_after_submission: true,
    show_correct_answers: true,
    randomize_questions: false,
    randomize_options: false,
    allow_review: true,
    is_required: false,
    is_published: true,
    order: 0,
  });
  errors.value = {};
  showModal.value = true;
};

const editQuiz = (quiz) => {
  isEditing.value = true;
  selectedId.value = quiz.id;
  Object.assign(form, {
    course_id: quiz.course_id,
    lesson_id: quiz.lesson_id,
    title: quiz.title,
    description: quiz.description,
    instructions: quiz.instructions,
    time_limit: quiz.time_limit,
    passing_score: quiz.passing_score,
    max_attempts: quiz.max_attempts,
    questions_per_page: quiz.questions_per_page,
    show_answers_after_submission: quiz.show_answers_after_submission,
    show_correct_answers: quiz.show_correct_answers,
    randomize_questions: quiz.randomize_questions,
    randomize_options: quiz.randomize_options,
    allow_review: quiz.allow_review,
    is_required: quiz.is_required,
    is_published: quiz.is_published,
    order: quiz.order,
  });
  errors.value = {};
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const saveQuiz = async () => {
  saving.value = true;
  errors.value = {};
  try {
    if (isEditing.value) {
      await quizService.update(selectedId.value, form);
      toast.success('Quiz updated successfully');
    } else {
      await quizService.store(form);
      toast.success('Quiz created successfully');
    }
    fetchQuizzes();
    closeModal();
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors;
    } else {
      toast.error('Failed to save quiz');
    }
  } finally {
    saving.value = false;
  }
};

const triggerDelete = (quiz) => {
  quizToDelete.value = quiz;
  showDeleteModal.value = true;
};

const confirmDelete = async () => {
  deleting.value = true;
  try {
    await quizService.destroy(quizToDelete.value.id);
    toast.success('Quiz deleted successfully');
    fetchQuizzes();
    showDeleteModal.value = false;
  } catch (error) {
    toast.error('Failed to delete quiz');
  } finally {
    deleting.value = false;
  }
};

const togglePublished = async (quiz) => {
  try {
    await quizService.togglePublished(quiz.id);
    toast.success('Published status toggled');
  } catch (error) {
    quiz.is_published = !quiz.is_published;
    toast.error('Failed to toggle status');
  }
};

const toggleRequired = async (quiz) => {
  try {
    await quizService.toggleRequired(quiz.id);
    toast.success('Required status toggled');
  } catch (error) {
    quiz.is_required = !quiz.is_required;
    toast.error('Failed to toggle required status');
  }
};

const viewStats = async (quiz) => {
  selectedQuiz.value = quiz;
  showStatsModal.value = true;
  stats.value = null;
  try {
    const response = await quizService.statistics(quiz.id);
    stats.value = response.data || response;
  } catch (error) {
    toast.error('Failed to fetch stats');
    showStatsModal.value = false;
  }
};

const formatSL = (index) => {
  const sl = (pagination.current_page - 1) * pagination.per_page + index + 1;
  return sl < 10 ? `0${sl}` : sl;
};

onMounted(() => {
  fetchQuizzes();
  fetchCourses();
});
</script>

<style scoped>
.panel-enter-active,
.panel-leave-active {
  transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.panel-enter-from,
.panel-leave-to {
  transform: translateX(100%);
  opacity: 0;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 5px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(99, 102, 241, 0.1);
  border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(99, 102, 241, 0.3);
}
</style>
