<template>
    <div class="max-w-5xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700 pb-20">
        <!-- Header -->
        <PageHeader title="Manage Curriculum" subtitle="Build your course structure with sections and lessons."
            :show-search="false">
            <template #icon>
                <button @click="$router.push({ name: 'admin.courses.index' })"
                    class="w-full h-full flex items-center justify-center theme-text-white hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
            </template>
            <template #actions>
                <PrimaryButton label="Add Section" @click="toggleInlineSection"
                    class="!px-6 !py-3 !rounded-2xl shadow-lg shadow-indigo-600/10" />
            </template>
        </PageHeader>

        <!-- Side Panel Modal for Section Form -->
        <transition name="panel">
            <div v-if="showInlineSection" class="fixed inset-0 z-[100] overflow-hidden">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showInlineSection = false"></div>

                <div
                    class="absolute inset-y-0 right-0 w-full max-w-md theme-bg-card border-l theme-border shadow-2xl flex flex-col">
                    <div class="p-8 border-b theme-border flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-black theme-text-main tracking-tight">
                                {{ isEditingSection ? 'Edit Section' : 'Add New Section' }}</h2>
                            <p class="text-xs theme-text-dim font-medium">Configure your curriculum structure</p>
                        </div>
                        <button @click="showInlineSection = false"
                            class="p-2 theme-text-muted hover:theme-text-main transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto p-8 custom-scrollbar">
                        <div class="space-y-6">
                            <FormInput v-model="sectionForm.title" label="Section Title"
                                placeholder="e.g. Getting Started" required />

                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-black theme-text-dim uppercase tracking-[0.2em] ml-2">Description</label>
                                <textarea v-model="sectionForm.description" rows="4"
                                    class="w-full px-6 py-4 rounded-2xl theme-bg-element border-2 theme-border outline-none focus:border-indigo-500/50 transition-all text-sm font-bold theme-text-main"
                                    placeholder="Describe what students will learn in this section..."></textarea>
                            </div>

                            <div class="grid grid-cols-1 gap-4">
                                <div
                                    class="p-4 rounded-2xl theme-bg-element border theme-border flex items-center justify-between group">
                                    <div class="flex flex-col gap-0.5">
                                        <span
                                            class="text-[10px] font-black theme-text-main uppercase tracking-widest">Published</span>
                                        <span class="text-[9px] theme-text-dim font-bold">Make visible to
                                            students</span>
                                    </div>
                                    <StatusToggle :model-value="sectionForm.is_published"
                                        @toggle="sectionForm.is_published = !sectionForm.is_published"
                                        class="border border-slate-700/50" />
                                </div>

                                <div
                                    class="p-4 rounded-2xl theme-bg-element border theme-border flex items-center justify-between group">
                                    <div class="flex flex-col gap-0.5">
                                        <span
                                            class="text-[10px] font-black theme-text-main uppercase tracking-widest text-amber-500">Drip
                                            Content</span>
                                        <span class="text-[9px] theme-text-dim font-bold">Release over time</span>
                                    </div>
                                    <FeaturedToggle :model-value="sectionForm.drip_enabled"
                                        @toggle="sectionForm.drip_enabled = !sectionForm.drip_enabled"
                                        class="border border-slate-700/50" />
                                </div>
                            </div>
                        </div>

                        <div class="pt-8 flex items-center gap-4">
                            <button @click="saveSection" :disabled="saving"
                                class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white py-4 rounded-2xl font-bold shadow-lg shadow-indigo-600/20 active:scale-95 transition-all flex items-center justify-center gap-2 disabled:opacity-50">
                                <svg v-if="saving" class="animate-spin h-5 w-5 text-white" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4">
                                    </circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <span>{{ isEditingSection ? 'Update Section' : 'Save Section' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Curriculum Builder -->
        <div class="space-y-6">
            <DataTable :headers="tableHeaders" :items="sections" :loading="loading" empty-title="No Curriculum Yet"
                empty-message="Start by adding your first section, then populate it with lectures, quizzes, and assignments.">
                <template #empty-action>
                    <PrimaryButton label="Create First Section" @click="toggleInlineSection" />
                </template>
                <template #row="{ item: section, index }">
                    <TableSLCell :index="formatSL(index)" class="col-span-1" />

                    <div class="col-span-3">
                        <span class="text-sm font-black theme-text-main truncate tracking-tight">{{ section.title
                        }}</span>
                    </div>

                    <div class="col-span-3">
                        <span class="text-xs theme-text-dim truncate block">{{ truncateText(section.description, 80)
                        }}</span>
                    </div>

                    <div class="col-span-1 text-center">
                        <span class="text-xs font-bold theme-text-main">{{ section.lessons?.length || 0 }}</span>
                    </div>

                    <div class="col-span-2 flex justify-center">
                        <StatusToggle :model-value="!!section.is_published" @toggle="toggleSectionStatus(section)" />
                    </div>

                    <div class="col-span-2 flex justify-end">
                        <TableActionDock>
                            <button @click="editSection(section)"
                                class="w-9 h-9 flex items-center justify-center rounded-xl text-amber-500 hover:bg-amber-500/10 transition-all">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <button @click="navigateToCreateLesson(section.id)"
                                class="w-9 h-9 flex items-center justify-center rounded-xl text-indigo-500 hover:bg-indigo-500/10 transition-all"
                                title="Add Lesson">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </button>
                            <button @click="triggerSectionDelete(section)"
                                class="w-9 h-9 flex items-center justify-center rounded-xl text-rose-500 hover:bg-rose-500/10 transition-all">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </TableActionDock>
                    </div>
                </template>
            </DataTable>
        </div>

        <!-- Delete Modal -->
        <ActionDialog :show="showDeleteModal" title="Delete Confirmation"
            :message="`Are you sure you want to delete this ${deleteType}? This cannot be undone.`" :loading="deleting"
            @confirm="confirmDelete" @cancel="showDeleteModal = false" />
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { courseService } from '../../../services/courseService';
import { useToast } from '../../../composables/useToast';
import PageHeader from '../../../components/common/PageHeader.vue';
import PrimaryButton from '../../../components/common/PrimaryButton.vue';
import FormInput from '../../../components/common/FormInput.vue';
import ActionDialog from '../../../components/common/ActionDialog.vue';
import DataTable from '../../../components/common/DataTable.vue';
import TableActionDock from '../../../components/common/TableActionDock.vue';
import TableSLCell from '../../../components/common/table/TableSLCell.vue';
import StatusToggle from '../../../components/common/StatusToggle.vue';
import FeaturedToggle from '../../../components/common/FeaturedToggle.vue';

const route = useRoute();
const router = useRouter();
const toast = useToast();

const sections = ref([]);
const loading = ref(true);
const saving = ref(false);
const deleting = ref(false);
const showInlineSection = ref(false);
const isEditingSection = ref(false);
const activeSectionId = ref(null);

const sectionForm = reactive({
    title: '',
    description: '',
    is_published: true,
    drip_enabled: false,
});

const showDeleteModal = ref(false);
const deleteType = ref('lesson');
const itemToDelete = ref(null);

const tableHeaders = [
    { label: 'SL', span: 1, align: 'left' },
    { label: 'Section Title', span: 3, align: 'left' },
    { label: 'Description', span: 3, align: 'left' },
    { label: 'Lessons', span: 1, align: 'center' },
    { label: 'Status', span: 2, align: 'center' },
    { label: 'Actions', span: 2, align: 'right' },
];

const fetchCurriculum = async () => {
    loading.value = true;
    try {
        sections.value = await courseService.getCurriculum(route.params.slug);
    } catch (error) {
        toast.error('Failed to load curriculum');
    } finally {
        loading.value = false;
    }
};

const navigateToCreateLesson = (sectionId) => {
    router.push({
        name: 'admin.courses.lesson.create',
        params: { slug: route.params.slug },
        query: { section_id: sectionId }
    });
};

const navigateToEditLesson = (lessonId) => {
    router.push({
        name: 'admin.courses.lesson.edit',
        params: { slug: route.params.slug, lessonId }
    });
};

const toggleInlineSection = () => {
    isEditingSection.value = false;
    Object.assign(sectionForm, { title: '', description: '', is_published: true, drip_enabled: false });
    showInlineSection.value = !showInlineSection.value;
};

const editSection = (section) => {
    isEditingSection.value = true;
    activeSectionId.value = section.id;
    Object.assign(sectionForm, {
        title: section.title,
        description: section.description,
        is_published: !!section.is_published,
        drip_enabled: !!section.drip_enabled
    });
    showInlineSection.value = true;
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const saveSection = async () => {
    saving.value = true;
    try {
        if (isEditingSection.value) {
            await courseService.updateSection(activeSectionId.value, sectionForm);
            toast.success('Section updated');
        } else {
            await courseService.storeSection(route.params.slug, sectionForm);
            toast.success('Section added');
        }
        showInlineSection.value = false;
        fetchCurriculum();
    } catch (error) {
        toast.error('Failed to save section');
    } finally {
        saving.value = false;
    }
};

const triggerSectionDelete = (section) => {
    itemToDelete.value = section;
    deleteType.value = 'section';
    showDeleteModal.value = true;
};

const triggerLessonDelete = (lesson) => {
    itemToDelete.value = lesson;
    deleteType.value = 'lesson';
    showDeleteModal.value = true;
};

const confirmDelete = async () => {
    deleting.value = true;
    try {
        if (deleteType.value === 'section') {
            await courseService.destroySection(itemToDelete.value.id);
        } else {
            await courseService.destroyLesson(itemToDelete.value.id);
        }
        toast.success(`${deleteType.value.charAt(0).toUpperCase() + deleteType.value.slice(1)} deleted`);
        showDeleteModal.value = false;
        fetchCurriculum();
    } catch (error) {
        toast.error('Delete failed');
    } finally {
        deleting.value = false;
    }
};

const getLessonIcon = (type) => {
    const icons = {
        video: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>',
        text: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>',
        quiz: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
        audio: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" /></svg>',
        document: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>',
        assignment: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>',
        live_class: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>',
    };
    return icons[type] || icons.text;
};

const formatLessonMeta = (lesson) => {
    if (lesson.type === 'video') return `${Math.floor(lesson.duration_seconds / 60)}m`;
    return 'Text';
};

const truncateText = (text, length) => {
    if (!text) return '';
    return text.length > length ? text.substring(0, length) + '...' : text;
};

const toggleSectionStatus = async (section) => {
    try {
        await courseService.updateSection(section.id, {
            title: section.title,
            description: section.description,
            is_published: !section.is_published,
            drip_enabled: section.drip_enabled
        });
        section.is_published = !section.is_published;
        toast.success(`Section ${section.is_published ? 'published' : 'unpublished'}`);
    } catch (error) {
        toast.error('Failed to update status');
    }
};

const formatSL = (index) => {
    return (index + 1).toString().padStart(2, '0');
};

onMounted(fetchCurriculum);
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
