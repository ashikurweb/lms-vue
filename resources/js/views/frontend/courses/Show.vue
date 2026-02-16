<template>
    <div class="min-h-screen theme-bg-main pb-24">
        <!-- Loading State -->
        <div v-if="loading" class="pt-40 pb-20 px-6">
            <div class="max-w-7xl mx-auto animate-pulse">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                    <div class="lg:col-span-4 h-96 bg-slate-200 dark:bg-slate-700 rounded-[2.5rem]"></div>
                    <div class="lg:col-span-8 space-y-8">
                        <div class="h-96 bg-slate-200 dark:bg-slate-700 rounded-[2.5rem]"></div>
                        <div class="h-20 bg-slate-200 dark:bg-slate-700 rounded-full w-3/4"></div>
                    </div>
                </div>
            </div>
        </div>

        <template v-else-if="course">
            <!-- Breadcrumb Navigation -->
            <div class="pt-32 pb-8 px-6 max-w-[1600px] mx-auto">
                <nav class="flex items-center gap-4 text-[10px] font-black theme-text-dim uppercase tracking-[0.3em] animate-in fade-in slide-in-from-left-4 duration-500">
                    <router-link to="/courses" class="hover:text-indigo-600 transition-colors">Course Archive</router-link>
                    <span class="opacity-30">/</span>
                    <span class="theme-text-main truncate max-w-[200px]">{{ course.title }}</span>
                </nav>
            </div>

            <!-- Modern Classroom Layout -->
            <section class="max-w-[1600px] mx-auto px-6">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
                    
                    <!-- LEFT COLUMN: Module Management / Curriculum -->
                    <div class="lg:col-span-4 order-2 lg:order-1 space-y-10">
                        <div class="theme-bg-card border-2 theme-border rounded-[3rem] p-10 shadow-6xl sticky top-28 overflow-hidden">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-600/5 blur-3xl rounded-full translate-x-10 -translate-y-10"></div>
                            
                            <!-- Course Progress Bar -->
                            <div v-if="course.is_enrolled || true" class="mb-10 p-6 rounded-3xl theme-bg-element border-2 theme-border relative overflow-hidden group">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex flex-col">
                                        <span class="text-[9px] font-black theme-text-dim uppercase tracking-widest">Your Progress</span>
                                        <span class="text-xl font-black theme-text-main">{{ completionPercentage }}% Complete</span>
                                    </div>
                                    <div class="w-10 h-10 rounded-xl bg-indigo-600/10 flex items-center justify-center text-indigo-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                </div>
                                <div class="h-2.5 w-full bg-theme-bg-main rounded-full overflow-hidden border theme-border">
                                    <div class="h-full bg-indigo-600 transition-all duration-1000 ease-out shadow-[0_0_15px_rgba(79,70,229,0.4)]" :style="{ width: `${completionPercentage}%` }"></div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between mb-10 relative z-10">
                                <h2 class="text-2xl font-black theme-text-main tracking-tighter">Course Modules</h2>
                                <span class="bg-indigo-600/10 text-indigo-600 px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest border border-indigo-600/20">
                                    {{ course.total_lectures }} Units
                                </span>
                            </div>

                            <div class="space-y-6 max-h-[60vh] overflow-y-auto pr-4 custom-scrollbar relative z-10">
                                <div v-for="(section, si) in course.sections" :key="section.id" class="space-y-4">
                                    <div class="flex items-center gap-4 opacity-50">
                                        <span class="text-[9px] font-black uppercase tracking-[0.4em] theme-text-dim whitespace-nowrap">{{ section.title }}</span>
                                        <div class="h-px w-full bg-theme-border"></div>
                                    </div>

                                    <div class="grid grid-cols-1 gap-3">
                                        <div v-for="(lesson, li) in section.lessons" :key="lesson.id" 
                                            @click="handleLessonSelect(lesson)"
                                            class="group flex items-center justify-between p-5 rounded-2xl theme-bg-element border-2 theme-border transition-all cursor-pointer"
                                            :class="{ 'border-indigo-500 bg-indigo-500/5': selectedLesson?.id === lesson.id, 'hover:border-indigo-500/30': selectedLesson?.id !== lesson.id }">
                                            <div class="flex items-center gap-5">
                                                <div class="w-8 h-8 rounded-lg flex items-center justify-center font-black text-[9px] transition-all shadow-sm"
                                                    :class="[
                                                        selectedLesson?.id === lesson.id ? 'bg-indigo-600 text-white' : 'bg-theme-bg-main theme-text-dim group-hover:bg-indigo-600 group-hover:text-white',
                                                        lesson.progress?.is_completed ? '!bg-emerald-500 !text-white' : ''
                                                    ]">
                                                    <svg v-if="lesson.progress?.is_completed" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                    <span v-else>{{ (li+1).toString().padStart(2, '0') }}</span>
                                                </div>
                                                <div class="space-y-1">
                                                    <h4 class="text-[13px] font-bold transition-colors line-clamp-1"
                                                        :class="[
                                                            selectedLesson?.id === lesson.id ? 'text-indigo-600' : 'theme-text-main group-hover:text-indigo-600',
                                                            lesson.progress?.is_completed ? 'text-emerald-500 line-through opacity-60' : ''
                                                        ]">
                                                        {{ lesson.title }}
                                                    </h4>
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-[8px] font-black theme-text-dim uppercase tracking-widest">{{ lesson.type }}</span>
                                                        <span v-if="lesson.is_free" class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                        <span v-if="lesson.is_free" class="text-[8px] font-black text-emerald-500 uppercase tracking-widest">Free</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="w-8 h-8 rounded-full border-2 flex items-center justify-center transition-all"
                                                :class="[
                                                    selectedLesson?.id === lesson.id ? 'border-indigo-600 text-indigo-600' : 'theme-border theme-text-dim group-hover:border-indigo-600 group-hover:text-indigo-600',
                                                    lesson.progress?.is_completed ? 'border-emerald-500 text-emerald-500' : ''
                                                ]">
                                                <svg v-if="selectedLesson?.id === lesson.id" class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/></svg>
                                                <svg v-else-if="lesson.progress?.is_completed" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                                <svg v-else-if="lesson.is_free" class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
                                                <svg v-else class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: Video Player + Primary Details -->
                    <div class="lg:col-span-8 order-1 lg:order-2 space-y-12">
                        
                        <!-- Using New VideoPlayer Component -->
                        <div v-if="selectedLesson?.video_url || course.promo_video">
                            <VideoPlayer 
                                :src="selectedLesson?.video_qualities?.length ? selectedLesson.video_qualities : (selectedLesson?.video_url || (course.video_resolutions?.length ? course.video_resolutions : course.promo_video))" 
                                :poster="selectedLesson?.video_thumbnail || course.thumbnail"
                                @ended="handleVideoEnd"
                                @timeupdate="handleTimeUpdate"
                                @ready="onPlayerReady"
                            />
                            <div v-if="selectedLesson" class="mt-6 flex items-center justify-between p-6 theme-bg-card border-2 theme-border rounded-3xl shadow-sm">
                                <div class="space-y-1">
                                    <span class="text-[10px] font-black theme-text-dim uppercase tracking-widest">Now Playing</span>
                                    <h3 class="text-lg font-black theme-text-main leading-tight">{{ selectedLesson.title }}</h3>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-[10px] font-black theme-text-dim uppercase tracking-widest">{{ selectedLesson.type }}</span>
                                    <div class="w-1 h-1 rounded-full bg-theme-border"></div>
                                    <span class="text-[10px] font-black theme-text-dim uppercase tracking-widest">{{ selectedLesson.duration_seconds ? Math.floor(selectedLesson.duration_seconds / 60) + ' min' : '' }}</span>
                                </div>
                            </div>
                        </div>
                        <div v-else class="aspect-video w-full flex flex-col items-center justify-center space-y-6 bg-slate-900/50 rounded-[3rem] border-4 border-white/5 shadow-6xl">
                            <div class="w-20 h-20 rounded-full bg-indigo-600/5 flex items-center justify-center text-indigo-500/30">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <p class="text-[10px] font-black theme-text-dim uppercase tracking-[0.5em]">Session Preview Not Found</p>
                        </div>

                        <!-- Course Info Card -->
                        <div class="theme-bg-card border-2 theme-border rounded-[3rem] p-12 shadow-6xl relative overflow-hidden">
                            <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-indigo-500/5 blur-3xl rounded-full"></div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-12 items-end">
                                <div class="md:col-span-8 space-y-8">
                                    <div class="space-y-4">
                                        <div class="flex items-center gap-3">
                                            <span class="px-4 py-1.5 rounded-xl bg-indigo-600/10 text-indigo-600 text-[9px] font-black uppercase tracking-widest border border-indigo-600/20">{{ course.category?.name || 'Protocol' }}</span>
                                            <span class="px-4 py-1.5 rounded-xl theme-bg-element border theme-border theme-text-dim text-[9px] font-black uppercase tracking-widest capitalize">{{ course.level }} Mode</span>
                                        </div>
                                        <h1 class="text-4xl md:text-6xl font-black theme-text-main tracking-tighter leading-[0.9]">{{ course.title }}</h1>
                                        <p v-if="course.subtitle" class="text-lg theme-text-dim font-medium max-w-2xl">{{ course.subtitle }}</p>
                                    </div>

                                    <!-- Author & Meta -->
                                    <div class="flex flex-wrap items-center gap-8 pt-4 border-t theme-border">
                                        <div class="flex items-center gap-4">
                                            <img :src="course.instructor?.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(course.instructor?.name || 'U')}&background=0b1120&color=fff`" 
                                                class="w-12 h-12 rounded-[1.2rem] object-cover border-2 theme-border shadow-md">
                                            <div class="flex flex-col">
                                                <span class="text-[9px] font-black theme-text-dim uppercase tracking-widest">Lead Instruction</span>
                                                <span class="text-sm font-black theme-text-main hover:text-indigo-600 cursor-pointer transition-colors">{{ course.instructor?.name }}</span>
                                            </div>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-[9px] font-black theme-text-dim uppercase tracking-widest">Protocol Rating</span>
                                            <div class="flex items-center gap-2">
                                                <div class="flex items-center text-amber-500">
                                                    <svg v-for="i in 5" :key="i" class="w-3 h-3" :class="i <= Math.floor(course.rating || 0) ? 'fill-current' : 'opacity-20'" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                                </div>
                                                <span class="text-xs font-black theme-text-main">{{ course.rating || '0.0' }}</span>
                                            </div>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-[9px] font-black theme-text-dim uppercase tracking-widest">Deployments</span>
                                            <span class="text-xs font-black theme-text-main">{{ (course.total_enrollments || 0).toLocaleString() }} Students</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="md:col-span-4 space-y-6">
                                    <div class="theme-bg-element border-2 theme-border rounded-3xl p-6 text-center space-y-2">
                                        <span class="text-[9px] font-black theme-text-dim uppercase tracking-widest">Access Pass</span>
                                        <div class="flex items-center justify-center gap-3">
                                            <span class="text-4xl font-black theme-text-main tracking-tighter">
                                                <template v-if="course.price_type === 'free'">Free</template>
                                                <template v-else>৳{{ course.price }}</template>
                                            </span>
                                        </div>
                                    </div>
                                    <button class="w-full py-5 rounded-2xl bg-indigo-600 text-white text-[10px] font-black uppercase tracking-[0.2em] hover:bg-indigo-500 hover:shadow-2xl hover:shadow-indigo-600/30 transition-all active:scale-[0.98] flex items-center justify-center gap-3 group">
                                        Join Protocol
                                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Description -->
                            <div v-if="course.description" class="mt-16 pt-16 border-t theme-border">
                                <h3 class="text-xl font-black theme-text-main tracking-tight mb-8">Course Specification</h3>
                                <div class="prose prose-indigo max-w-none theme-text-dim text-[15px] leading-relaxed opacity-80" v-html="course.description"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </template>
    </div>
</template>

<script setup>
import { ref, onMounted, nextTick, computed } from 'vue';
import { useRoute } from 'vue-router';
import { publicCourseService } from '../../../services/publicCourseService';
import { courseProgressService } from '../../../services/courseProgressService';
import VideoPlayer from '../../../components/common/VideoPlayer.vue';

const route = useRoute();
const course = ref(null);
const loading = ref(true);
const selectedLesson = ref(null);
const lastSavedTime = ref(0);
const playerRef = ref(null);

const completionPercentage = computed(() => {
    if (!course.value || !course.value.sections) return 0;
    
    let totalLessons = 0;
    let completedLessons = 0;

    course.value.sections.forEach(section => {
        section.lessons.forEach(lesson => {
            totalLessons++;
            if (lesson.progress?.is_completed) {
                completedLessons++;
            }
        });
    });

    return totalLessons > 0 ? Math.round((completedLessons / totalLessons) * 100) : 0;
});

const fetchCourse = async () => {
    loading.value = true;
    try {
        const data = await publicCourseService.show(route.params.slug);
        course.value = data;
        
        // Always select the first lesson of the first section as default
        if (data.sections && data.sections.length > 0) {
            const firstSection = data.sections[0];
            if (firstSection.lessons && firstSection.lessons.length > 0) {
                handleLessonSelect(firstSection.lessons[0]);
            }
        }
    } catch (error) {
        console.error('Core Link Failure:', error);
    } finally {
        loading.value = false;
    }
};

const handleLessonSelect = async (lesson) => {
    if (!lesson.is_free && !course.value?.is_enrolled) {
        console.log('Enrollment required for this lesson');
        return;
    }
    
    selectedLesson.value = lesson;
    lastSavedTime.value = 0;

    // Check if we already have progress from fetchCourse or fetch it specifically
    if (lesson.progress?.last_position) {
        lesson.last_position = lesson.progress.last_position;
    } else {
        try {
            const progress = await courseProgressService.getLessonProgress(lesson.id);
            if (progress && progress.last_position) {
                lesson.last_position = progress.last_position;
                lesson.progress = { is_completed: progress.is_completed, last_position: progress.last_position };
            }
        } catch (e) {
            console.log('No progress found or user not logged in');
        }
    }
};

const onPlayerReady = (player) => {
    playerRef.value = player;
    if (selectedLesson.value?.last_position) {
        player.currentTime = selectedLesson.value.last_position;
    }
};

const handleVideoEnd = () => {
    saveProgress(100, true);
    
    if (!selectedLesson.value || !course.value) return;

    let currentLessonIndex = -1;
    let currentSectionIndex = -1;

    course.value.sections.forEach((section, si) => {
        const li = section.lessons.findIndex(l => l.id === selectedLesson.value.id);
        if (li !== -1) {
            currentLessonIndex = li;
            currentSectionIndex = si;
        }
    });

    if (currentLessonIndex !== -1) {
        if (currentLessonIndex < course.value.sections[currentSectionIndex].lessons.length - 1) {
            handleLessonSelect(course.value.sections[currentSectionIndex].lessons[currentLessonIndex + 1]);
        } 
        else if (currentSectionIndex < course.value.sections.length - 1) {
            const nextSection = course.value.sections[currentSectionIndex + 1];
            if (nextSection.lessons && nextSection.lessons.length > 0) {
                handleLessonSelect(nextSection.lessons[0]);
            }
        }
    }
};

const handleTimeUpdate = (data) => {
    const now = Date.now();
    if (now - lastSavedTime.value > 30000) {
        saveProgress(data.percentage);
        lastSavedTime.value = now;
    }
};

const saveProgress = async (percentage, isCompleted = false) => {
    if (!selectedLesson.value || !playerRef.value) return;

    try {
        await courseProgressService.save({
            lesson_id: selectedLesson.value.id,
            watch_time: playerRef.value.currentTime,
            last_position: playerRef.value.currentTime,
            progress_percentage: percentage
        });
        
        // Update local state for immediate feedback
        if (isCompleted) {
            if (!selectedLesson.value.progress) selectedLesson.value.progress = {};
            selectedLesson.value.progress.is_completed = true;
        }
    } catch (error) {
        console.error('Failed to save progress:', error);
    }
};

onMounted(fetchCourse);



</script>

<style scoped>
.shadow-6xl {
    box-shadow: 0 40px 100px -20px rgba(0, 0, 0, 0.3), 0 20px 40px -20px rgba(0, 0, 0, 0.15);
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(99, 102, 241, 0.1);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(99, 102, 241, 0.2);
}

.prose p {
    margin-bottom: 2em !important;
    line-height: 1.9 !important;
}
</style>
