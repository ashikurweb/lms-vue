<template>
    <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
        <PageHeader title="All Lessons" subtitle="A central view of all lectures and content across your courses."
            v-model="search" search-placeholder="Search lessons..." @search="debounceSearch">
            <template #icon>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                        d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </template>
        </PageHeader>

        <DataTable :headers="tableHeaders" :items="lessons" :loading="loading" :pagination="pagination"
            empty-title="No lessons found" empty-message="Create courses and sections to start adding lessons."
            @page-change="fetchLessons">
            <template #row="{ item: lesson, index }">
                <TableSLCell :index="formatSL(index)" />

                <div class="col-span-4">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-500 flex items-center justify-center flex-shrink-0">
                            <svg v-if="lesson.type === 'video'" class="w-5 h-5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path
                                    d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="text-sm font-black theme-text-main truncate tracking-tight">{{ lesson.title
                                }}</span>
                            <span class="text-[10px] theme-text-dim font-bold uppercase tracking-widest">{{ lesson.type
                                }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-span-3">
                    <div class="flex flex-col">
                        <span class="text-xs font-bold theme-text-main">{{ lesson.course?.title }}</span>
                        <span class="text-[9px] theme-text-dim font-medium">{{ lesson.section?.title }}</span>
                    </div>
                </div>

                <div class="col-span-2 text-center">
                    <span v-if="lesson.type === 'video'"
                        class="text-[10px] font-black theme-text-accent bg-indigo-500/10 px-2 py-1 rounded-lg uppercase tracking-widest">{{
                            lesson.video_provider || 'Local' }}</span>
                    <span v-else
                        class="text-[10px] font-black theme-text-muted bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded-lg uppercase tracking-widest">Standard</span>
                </div>

                <div class="col-span-2 text-right">
                    <TableActionDock>
                        <button @click="goToCourse(lesson.course)"
                            class="w-9 h-9 flex items-center justify-center rounded-xl text-indigo-500 hover:bg-indigo-500/10 transition-all"
                            title="Edit in Course">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>
                    </TableActionDock>
                </div>
            </template>
        </DataTable>
    </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../../services/api';
import PageHeader from '../../../components/common/PageHeader.vue';
import TableActionDock from '../../../components/common/TableActionDock.vue';
import TableSLCell from '../../../components/common/table/TableSLCell.vue';
import DataTable from '../../../components/common/DataTable.vue';
import { useToast } from '../../../composables/useToast';

const router = useRouter();
const toast = useToast();

const tableHeaders = [
    { label: 'SL', span: 1, align: 'left' },
    { label: 'Lesson Title', span: 4, align: 'left' },
    { label: 'Course & Section', span: 3, align: 'left' },
    { label: 'Source', span: 2, align: 'center' },
    { label: 'Actions', span: 2, align: 'right' },
];

const lessons = ref([]);
const loading = ref(true);
const search = ref('');

const pagination = reactive({
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0,
});

const fetchLessons = async (page = 1) => {
    loading.value = true;
    try {
        const response = await api.get('/admin/lessons', {
            params: {
                page,
                search: search.value,
                per_page: pagination.per_page
            }
        });
        const data = response.data;
        lessons.value = data.data;
        pagination.current_page = data.current_page;
        pagination.last_page = data.last_page;
        pagination.total = data.total;
    } catch (error) {
        toast.error('Failed to fetch lessons');
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
        fetchLessons(1);
    }, 500);
};

const goToCourse = (course) => {
    if (course) {
        router.push({ name: 'admin.courses.curriculum', params: { slug: course.slug } });
    }
};

onMounted(() => {
    fetchLessons();
});
</script>
