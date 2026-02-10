<template>
  <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700 pb-20">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-4">
        <router-link to="/admin/discussions"
          class="p-3 theme-bg-card border theme-border rounded-xl theme-text-dim hover:theme-text-main transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
        </router-link>
        <div>
          <h1 class="text-3xl font-black theme-text-main tracking-tight">Create Discussion</h1>
          <p class="text-sm theme-text-muted mt-1 font-medium">Add a new discussion to the forum.</p>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <PrimaryButton label="Create Discussion" :loading="saving" @click="saveDiscussion" />
      </div>
    </div>

    <div class="theme-bg-card border theme-border rounded-[2rem] p-8 shadow-sm space-y-6">
      <form @submit.prevent="saveDiscussion" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="space-y-4">
            <FormInput v-model="form.title" label="Title" placeholder="Enter discussion title" :error="errors.title"
              required />
          </div>

          <FormDropdown v-model="form.type" label="Type" :options="typeOptions" :error="errors.type" required />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <FormDropdown v-model="form.status" label="Status" :options="statusOptions" :error="errors.status" required />

          <FormDropdown v-model="form.course_id" label="Course" :options="courseOptions" :error="errors.course_id"
            required />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <FormDropdown v-model="form.lesson_id" label="Lesson (Optional)" :options="lessonOptions"
            :error="errors.lesson_id" />

          <FormDropdown v-model="form.user_id" label="User" :options="userOptions" :error="errors.user_id" required />
        </div>

        <div class="flex gap-8 py-2">
          <label class="flex items-center gap-3 cursor-pointer group">
            <FeaturedToggle :model-value="form.is_featured" @toggle="form.is_featured = !form.is_featured" />
            <span class="text-xs font-black theme-text-main uppercase tracking-widest">Featured</span>
          </label>

          <label class="flex items-center gap-3 cursor-pointer group">
            <div
              class="w-11 h-6 rounded-full transition-all duration-500 shadow-inner overflow-hidden flex items-center px-1"
              :class="form.is_pinned ? 'bg-indigo-600 shadow-indigo-900/20' : 'theme-bg-element'"
              @click="form.is_pinned = !form.is_pinned">
              <div class="w-4 h-4 bg-white rounded-full transition-all duration-500 shadow-lg"
                :class="form.is_pinned ? 'translate-x-5' : 'translate-x-0'"></div>
            </div>
            <span class="text-xs font-black theme-text-main uppercase tracking-widest">Pinned</span>
          </label>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Content</label>
          <Editor v-model="form.content" placeholder="Enter discussion content" />
          <div v-if="errors.content" class="mt-1 text-sm text-red-600">{{ errors.content[0] }}</div>
        </div>

      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { discussionService } from '../../../services/discussionService';
import FormInput from '../../../components/common/FormInput.vue';
import FormDropdown from '../../../components/common/FormDropdown.vue';
import FeaturedToggle from '../../../components/common/FeaturedToggle.vue';
import PrimaryButton from '../../../components/common/PrimaryButton.vue';
import Editor from '../../../components/common/Editor.vue';
import { useToast } from '../../../composables/useToast';

const router = useRouter();
const toast = useToast();
const saving = ref(false);
const allCourses = ref([]);
const allUsers = ref([]);
const allLessons = ref([]);

const form = reactive({
  title: '',
  content: '',
  type: 'question',
  status: 'open',
  course_id: '',
  lesson_id: null,
  user_id: '',
  is_featured: false,
  is_pinned: false
});

const errors = ref({});

const typeOptions = [
  { label: 'Question', value: 'question' },
  { label: 'Discussion', value: 'discussion' },
  { label: 'Announcement', value: 'announcement' }
];

const statusOptions = [
  { label: 'Open', value: 'open' },
  { label: 'Answered', value: 'answered' },
  { label: 'Closed', value: 'closed' },
  { label: 'Flagged', value: 'flagged' }
];

const courseOptions = computed(() => {
  return allCourses.value.map(course => ({
    label: course.title,
    value: course.id
  }));
});

const userOptions = computed(() => {
  return allUsers.value.map(user => ({
    label: user.name,
    value: user.id
  }));
});

const lessonOptions = computed(() => {
  return allLessons.value.map(lesson => ({
    label: lesson.title,
    value: lesson.id
  }));
});

const fetchSelectData = async () => {
  // TODO: Implement fetching courses, users, lessons
  // For now, placeholder
  allCourses.value = [];
  allUsers.value = [];
  allLessons.value = [];
};

const saveDiscussion = async () => {
  saving.value = true;
  errors.value = {};
  try {
    await discussionService.store(form);
    toast.success('Discussion created successfully');
    router.push('/admin/discussions');
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
  fetchSelectData();
});
</script>
