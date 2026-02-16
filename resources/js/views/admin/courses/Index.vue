<template>
  <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <PageHeader title="Courses" subtitle="Manage your education catalog, pricing, and curriculum." v-model="search"
      add-label="Create Course" search-placeholder="Search courses..." @search="debounceSearch">
      <template #icon>
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
        </svg>
      </template>
      <template #actions>
        <PrimaryButton label="Create Course" @click="createNewCourse" class="!px-6 !py-3 !rounded-2xl">
          <template #icon>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
          </template>
        </PrimaryButton>
      </template>
    </PageHeader>

    <DataTable :headers="tableHeaders" :items="courses" :loading="loading" :pagination="pagination"
      empty-title="No courses found"
      empty-message="Start building your learning platform by creating your first course." @page-change="fetchCourses">
      <template #row="{ item: course, index }">
        <TableSLCell :index="formatSL(index)" />

        <div class="col-span-3">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl border theme-border overflow-hidden bg-slate-100 flex-shrink-0">
              <img v-if="course.thumbnail" :src="course.thumbnail" class="w-full h-full object-cover">
              <div v-else class="w-full h-full flex items-center justify-center theme-bg-element theme-text-muted">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
            </div>
            <div class="flex flex-col min-w-0">
              <span class="text-sm font-black theme-text-main truncate tracking-tight">{{ course.title }}</span>
              <span class="text-[10px] theme-text-dim font-bold uppercase tracking-widest">{{ course.category?.name ||
                `Uncategorized` }}</span>
            </div>
          </div>
        </div>

        <div class="col-span-3">
          <div class="flex flex-col">
            <span class="text-xs font-bold theme-text-main">{{ course.price_type === 'free' ? 'Free' :
              formatPrice(course.price) }}</span>
            <span class="text-[9px] theme-text-dim font-medium">{{ course.level }}</span>
          </div>
        </div>

        <div class="col-span-1 text-center">
          <span class="text-xs font-bold theme-text-main">{{ course.total_lectures }} Lectures</span>
        </div>

        <div class="col-span-1 flex justify-center">
          <FeaturedToggle :model-value="!!course.is_featured" @toggle="toggleFeatured(course)" />
        </div>

        <div class="col-span-1 flex justify-center">
          <StatusToggle :model-value="course.status === 'published'" @toggle="toggleStatus(course)" />
        </div>

        <div class="col-span-2 text-right">
          <div class="flex items-center justify-end">
            <TableActionDock>
              <button @click="editCourse(course)"
                class="w-9 h-9 flex items-center justify-center rounded-xl text-amber-500 hover:bg-amber-500/10 transition-all">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
              </button>
              <button @click="manageCurriculum(course)"
                class="w-9 h-9 flex items-center justify-center rounded-xl text-indigo-500 hover:bg-indigo-500/10 transition-all"
                title="Manage Curriculum">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
              </button>
              <button @click="triggerDelete(course)"
                class="w-9 h-9 flex items-center justify-center rounded-xl text-rose-500 hover:bg-rose-500/10 transition-all">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </TableActionDock>
          </div>
        </div>
      </template>
    </DataTable>

    <ActionDialog :show="showDeleteModal" title="Delete Course"
      :message="`Are you sure you want to delete '${courseToDelete?.title}'? This action cannot be undone.`"
      :loading="deleting" @confirm="confirmDelete" @cancel="showDeleteModal = false" />
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { courseService } from '../../../services/courseService';
import PageHeader from '../../../components/common/PageHeader.vue';
import TableActionDock from '../../../components/common/TableActionDock.vue';
import TableSLCell from '../../../components/common/table/TableSLCell.vue';
import PrimaryButton from '../../../components/common/PrimaryButton.vue';
import FeaturedToggle from '../../../components/common/FeaturedToggle.vue';
import StatusToggle from '../../../components/common/StatusToggle.vue';
import ActionDialog from '../../../components/common/ActionDialog.vue';
import DataTable from '../../../components/common/DataTable.vue';
import { useToast } from '../../../composables/useToast';
import { useCurrency } from '../../../composables/useCurrency';

const router = useRouter();
const toast = useToast();
const { formatPrice } = useCurrency();

const tableHeaders = [
  { label: 'SL', span: 1, align: 'left' },
  { label: 'Course Info', span: 3, align: 'left' },
  { label: 'Pricing & Level', span: 3, align: 'left' },
  { label: 'Content', span: 1, align: 'center' },
  { label: 'Featured', span: 1, align: 'center' },
  { label: 'Status', span: 1, align: 'center' },
  { label: 'Actions', span: 2, align: 'right' },
];

const courses = ref([]);
const loading = ref(true);
const search = ref('');
const deleting = ref(false);
const showDeleteModal = ref(false);
const courseToDelete = ref(null);

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
});

const fetchCourses = async (page = 1) => {
  loading.value = true;
  try {
    const data = await courseService.index({
      page,
      search: search.value,
      per_page: pagination.per_page
    });
    courses.value = data.data;
    const meta = data.meta;
    pagination.current_page = meta.current_page;
    pagination.last_page = meta.last_page;
    pagination.total = meta.total;
  } catch (error) {
    toast.error('Failed to fetch courses');
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
    fetchCourses(1);
  }, 500);
};

const createNewCourse = () => {
  router.push({ name: 'admin.courses.create' });
};

const editCourse = (course) => {
  router.push({ name: 'admin.courses.edit', params: { slug: course.slug } });
};

const manageCurriculum = (course) => {
  router.push({ name: 'admin.courses.curriculum', params: { slug: course.slug } });
};

const toggleFeatured = async (course) => {
  try {
    await courseService.toggleFeatured(course.slug);
    course.is_featured = !course.is_featured;
    toast.success('Featured status updated');
  } catch (error) {
    toast.error('Failed to update featured status');
  }
};

const toggleStatus = async (course) => {
  try {
    await courseService.toggleStatus(course.slug);
    course.status = course.status === 'published' ? 'unpublished' : 'published';
    toast.success('Status updated');
  } catch (error) {
    toast.error('Failed to update status');
  }
};

const triggerDelete = (course) => {
  courseToDelete.value = course;
  showDeleteModal.value = true;
};

const confirmDelete = async () => {
  if (!courseToDelete.value) return;
  deleting.value = true;
  try {
    await courseService.destroy(courseToDelete.value.slug);
    toast.success('Course deleted successfully');
    showDeleteModal.value = false;
    fetchCourses(pagination.current_page);
  } catch (error) {
    toast.error('Failed to delete course');
  } finally {
    deleting.value = false;
  }
};

onMounted(() => {
  fetchCourses();
});
</script>
