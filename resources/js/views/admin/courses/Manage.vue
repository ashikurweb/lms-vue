<template>
    <div class="max-w-5xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700 pb-20">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <button @click="$router.back()"
                    class="w-10 h-10 flex items-center justify-center rounded-xl theme-bg-element theme-text-muted hover:theme-text-main transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <div>
                    <h1 class="text-2xl font-black theme-text-main tracking-tight">{{ isEditing ? 'Edit Course' :
                        'Create New Course' }}</h1>
                    <p class="text-sm theme-text-dim font-medium">Fill in the details below to {{ isEditing ? 'update' :
                        'publish' }} your course.</p>
                </div>
            </div>
            <PrimaryButton :label="isEditing ? 'Update Course' : 'Create Course'" @click="saveCourse" :loading="saving"
                class="!px-8 !py-3.5 !rounded-2xl shadow-xl shadow-indigo-600/20" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Basic Info Card -->
                <div class="theme-bg-card border theme-border rounded-3xl p-8 shadow-sm">
                    <h3 class="text-lg font-black theme-text-main mb-6 flex items-center gap-3">
                        <span
                            class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-500 flex items-center justify-center">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        Basic Information
                    </h3>
                    <div class="space-y-6">
                        <FormInput v-model="form.title" label="Course Title"
                            placeholder="e.g. Master Vue 3 Advanced Patterns" :error="errors.title" required />

                        <div class="grid grid-cols-1 gap-6">
                            <FormInput v-model="form.subtitle" label="Subtitle / Short Hook"
                                placeholder="e.g. Build enterprise-scale applications" :error="errors.subtitle" />

                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-black theme-text-dim uppercase tracking-[0.2em] ml-2">Short
                                    Description (SEO)</label>
                                <textarea v-model="form.short_description" rows="3"
                                    class="w-full px-6 py-4 rounded-2xl theme-bg-element border-2 theme-border outline-none focus:border-indigo-500/50 transition-all text-sm font-bold theme-text-main"
                                    placeholder="A brief summary for search results..."></textarea>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black theme-text-dim uppercase tracking-[0.2em] ml-2">Full
                                Description</label>
                            <textarea v-model="form.description" rows="8"
                                class="w-full px-6 py-4 rounded-2xl theme-bg-element border-2 theme-border outline-none focus:border-indigo-500/50 transition-all text-sm font-bold theme-text-main"
                                placeholder="Provide a detailed description of the course content and goals..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Media & Promo Card -->
                <div class="theme-bg-card border theme-border rounded-3xl p-8 shadow-sm">
                    <h3 class="text-lg font-black theme-text-main mb-6 flex items-center gap-3">
                        <span
                            class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-500 flex items-center justify-center">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </span>
                        Course Media & Promo
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Course Thumbnail -->
                        <div class="space-y-4">
                            <label class="text-[10px] font-black theme-text-dim uppercase tracking-[0.2em] ml-2">Course
                                Thumbnail</label>
                            <UploadFile v-model="form.thumbnail" type="image" folder="uploads/thumbnails"
                                placeholder="Click or Drop Course Thumbnail" />
                        </div>

                        <!-- Promo Video -->
                        <div class="space-y-4">
                            <label class="text-[10px] font-black theme-text-dim uppercase tracking-[0.2em] ml-2">Promo
                                Video</label>
                            <div class="p-8 rounded-3xl bg-indigo-50/50 border border-indigo-100/50 space-y-6">
                                <FormDropdown v-model="form.promo_video_type" label="Video Provider"
                                    :options="promoVideoOptions" />

                                <div v-if="form.promo_video_type === 'upload'"
                                    class="animate-in fade-in slide-in-from-top-2">
                                    <UploadFile v-model="form.promo_video" type="video" folder="uploads/promo-videos"
                                        placeholder="Upload Promo Video" :maxMB="100" />
                                </div>
                                <FormInput v-else v-model="form.promo_video" label="Video URL / ID"
                                    placeholder="YouTube or Vimeo Link / ID..." />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-8">
                <!-- Pricing & Details Card -->
                <div class="theme-bg-card border theme-border rounded-3xl p-8 shadow-sm">
                    <h3
                        class="text-sm font-black theme-text-main mb-6 tracking-widest uppercase opacity-70 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Pricing & Settings
                    </h3>
                    <div class="space-y-6">
                        <FormDropdown v-model="form.price_type" label="Price Type" :options="priceTypeOptions"
                            required />

                        <FormInput v-if="form.price_type !== 'free'" v-model="form.price" label="Regular Price"
                            type="number" placeholder="0.00" :error="errors.price">
                            <template #icon><span class="text-sm font-black">{{ currentCurrency?.symbol || '$'
                            }}</span></template>
                        </FormInput>

                        <FormDropdown v-model="form.level" label="Course Level" :options="levelOptions" required />
                        <FormDropdown v-model="form.language" label="Primary Language" :options="languageOptions"
                            required />

                        <hr class="theme-border border-dashed">

                        <FormDropdown v-model="form.status" label="Publishing Status" :options="statusOptions"
                            required />

                        <div class="flex flex-col gap-4 pt-2">
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <div class="w-12 h-6 rounded-full p-1 transition-colors duration-300 relative"
                                    :class="form.is_featured ? 'bg-indigo-600' : 'theme-bg-element'">
                                    <div class="w-4 h-4 bg-white rounded-full transition-transform duration-300 shadow-sm"
                                        :class="form.is_featured ? 'translate-x-6' : 'translate-x-0'"></div>
                                    <input type="checkbox" v-model="form.is_featured" class="hidden">
                                </div>
                                <span class="text-[10px] font-black theme-text-main uppercase tracking-widest">Featured
                                    Course</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { courseService } from '../../../services/courseService';
import FormInput from '../../../components/common/FormInput.vue';
import FormDropdown from '../../../components/common/FormDropdown.vue';
import PrimaryButton from '../../../components/common/PrimaryButton.vue';
import UploadFile from '../../../components/common/UploadFile.vue';
import { useToast } from '../../../composables/useToast';
import { useCurrency } from '../../../composables/useCurrency';

const route = useRoute();
const router = useRouter();
const toast = useToast();
const { currentCurrency } = useCurrency();

const isEditing = computed(() => !!route.params.slug);
const loading = ref(false);
const saving = ref(false);

const form = reactive({
    title: '',
    subtitle: '',
    short_description: '',
    description: '',
    thumbnail: '',
    promo_video_type: 'upload',
    promo_video: '',
    price_type: 'free',
    price: 0,
    currency: 'USD',
    level: 'all_levels',
    language: 'en',
    status: 'draft',
    is_featured: false,
    instructor_id: 1, // Default for now, should be dynamic
});

watch(currentCurrency, (val) => {
    if (!isEditing.value && val) {
        form.currency = val.code;
    }
});

const errors = ref({});

const priceTypeOptions = [
    { label: 'Free', value: 'free' },
    { label: 'One-time Payment', value: 'paid' },
    { label: 'Subscription', value: 'subscription' },
];

const levelOptions = [
    { label: 'Beginner', value: 'beginner' },
    { label: 'Intermediate', value: 'intermediate' },
    { label: 'Advanced', value: 'advanced' },
    { label: 'All Levels', value: 'all_levels' },
];

const languageOptions = [
    { label: 'English', value: 'en' },
    { label: 'Spanish', value: 'es' },
    { label: 'Bangla', value: 'bn' },
    { label: 'Hindi', value: 'hi' },
];

const statusOptions = [
    { label: 'Draft', value: 'draft' },
    { label: 'Pending Review', value: 'pending_review' },
    { label: 'Published (Live)', value: 'published' },
    { label: 'Unpublished', value: 'unpublished' },
];

const promoVideoOptions = [
    { label: 'Upload (Local)', value: 'upload' },
    { label: 'Bunny Stream', value: 'bunny' },
    { label: 'External Link', value: 'external' },
];

const fetchCourse = async () => {
    if (!isEditing.value) return;
    loading.value = true;
    try {
        const data = await courseService.show(route.params.slug);
        Object.assign(form, data.data);
    } catch (error) {
        toast.error('Failed to load course details');
    } finally {
        loading.value = false;
    }
};

const saveCourse = async () => {
    saving.value = true;
    errors.value = {};
    try {
        if (isEditing.value) {
            await courseService.update(route.params.slug, form);
            toast.success('Course updated successfully');
        } else {
            const response = await courseService.store(form);
            toast.success('Course created successfully');
            router.push({ name: 'admin.courses.edit', params: { slug: response.course.slug } });
        }
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors;
            toast.error('Please check the highlighted fields.');
        } else {
            toast.error('Failed to save course. Please try again.');
        }
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    fetchCourse();
});
</script>
