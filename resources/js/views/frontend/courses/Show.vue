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
                                            class="group flex items-center justify-between p-5 rounded-2xl theme-bg-element border-2 theme-border hover:border-indigo-500/30 transition-all cursor-pointer">
                                            <div class="flex items-center gap-5">
                                                <div class="w-8 h-8 rounded-lg bg-theme-bg-main flex items-center justify-center font-black text-[9px] theme-text-dim group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-sm">
                                                    {{ (li+1).toString().padStart(2, '0') }}
                                                </div>
                                                <div class="space-y-1">
                                                    <h4 class="text-[13px] font-bold theme-text-main group-hover:text-indigo-600 transition-colors line-clamp-1">{{ lesson.title }}</h4>
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-[8px] font-black theme-text-dim uppercase tracking-widest">{{ lesson.type }}</span>
                                                        <span v-if="lesson.is_free" class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                        <span v-if="lesson.is_free" class="text-[8px] font-black text-emerald-500 uppercase tracking-widest">Free</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="w-8 h-8 rounded-full border-2 theme-border flex items-center justify-center theme-text-dim group-hover:border-indigo-600 group-hover:text-indigo-600 transition-all">
                                                <svg v-if="lesson.is_free" class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
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
                        
                        <!-- Premium Video Player -->
                        <div class="relative bg-black rounded-[3rem] overflow-hidden shadow-6xl border-4 border-white/5 active:scale-[0.99] transition-transform duration-500 group">
                            <div v-if="course.promo_video" class="aspect-video w-full relative">
                                <video ref="videoPlayer" class="plyr-player" playsinline controls :poster="course.thumbnail">
                                    <source :src="course.promo_video" type="video/mp4" />
                                </video>
                                <div v-if="isPlayerReady" class="absolute inset-0 pointer-events-none flex items-center justify-center z-20">
                                    <div ref="playPauseIcon" class="w-24 h-24 rounded-full bg-indigo-600/90 text-white flex items-center justify-center shadow-2xl scale-0 opacity-0 backdrop-blur-md border border-white/20">
                                        <svg v-if="isPlaying" class="w-10 h-10 ml-1" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
                                        <svg v-else class="w-10 h-10" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="aspect-video w-full flex flex-col items-center justify-center space-y-6 bg-slate-900/50">
                                <div class="w-20 h-20 rounded-full bg-indigo-600/5 flex items-center justify-center text-indigo-500/30">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </div>
                                <p class="text-[10px] font-black theme-text-dim uppercase tracking-[0.5em]">Session Preview Not Found</p>
                            </div>
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
import { ref, onMounted, nextTick, onBeforeUnmount } from 'vue';
import { useRoute } from 'vue-router';
import { publicCourseService } from '../../../services/publicCourseService';
import gsap from 'gsap';

const route = useRoute();
const course = ref(null);
const loading = ref(true);
const videoPlayer = ref(null);
const playPauseIcon = ref(null);
const isPlaying = ref(false);
const isPlayerReady = ref(false);
let playerInstance = null;

const fetchCourse = async () => {
    loading.value = true;
    try {
        const data = await publicCourseService.show(route.params.slug);
        course.value = data;
        if (course.value?.promo_video) {
            initVideoPlayer();
        }
    } catch (error) {
        console.error('Core Link Failure:', error);
    } finally {
        loading.value = false;
    }
};

const initVideoPlayer = async () => {
    await nextTick();
    if (!window.Plyr) {
        const script = document.createElement('script');
        script.src = 'https://cdn.plyr.io/3.7.8/plyr.js';
        script.onload = () => createPlayerInstance();
        document.head.appendChild(script);

        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://cdn.plyr.io/3.7.8/plyr.css';
        document.head.appendChild(link);
    } else {
        createPlayerInstance();
    }
};

const createPlayerInstance = () => {
    if (videoPlayer.value && window.Plyr) {
        if (playerInstance) playerInstance.destroy();
        
        playerInstance = new window.Plyr(videoPlayer.value, {
            controls: ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'captions', 'settings', 'pip', 'airplay', 'fullscreen'],
            tooltips: { controls: true, seek: true },
            keyboard: { focused: true, global: true },
            quality: { default: 1080, options: [1080, 720, 480] }
        });

        playerInstance.on('play', () => {
            isPlaying.value = true;
            animateIcon();
        });

        playerInstance.on('pause', () => {
            isPlaying.value = false;
            animateIcon();
        });

        isPlayerReady.value = true;
        videoPlayer.value.load();
    }
};

const animateIcon = () => {
    if (!playPauseIcon.value) return;
    
    gsap.fromTo(playPauseIcon.value, 
        { scale: 0, opacity: 0 },
        { 
            scale: 1, 
            opacity: 1, 
            duration: 0.4, 
            ease: "back.out(1.7)",
            onComplete: () => {
                gsap.to(playPauseIcon.value, {
                    scale: 1.5,
                    opacity: 0,
                    duration: 0.4,
                    delay: 0.2,
                    ease: "power2.in"
                });
            }
        }
    );
};

onMounted(fetchCourse);

onBeforeUnmount(() => {
    if (playerInstance) playerInstance.destroy();
});
</script>

<style>
:root {
    --plyr-color-main: #6366f1;
    --plyr-video-control-background-hover: rgba(99, 102, 241, 0.2);
    --plyr-video-controls-background: linear-gradient(rgba(0,0,0,0), rgba(0,0,0,0.85));
    --plyr-range-thumb-height: 16px;
    --plyr-range-track-height: 6px;
    --plyr-control-radius: 16px;
}

.plyr {
    height: 100% !important;
    width: 100% !important;
}

.plyr--full-ui.plyr--video .plyr__control--overlaid {
    background: rgba(99, 102, 241, 0.95) !important;
    padding: 30px !important;
    box-shadow: 0 20px 40px rgba(99, 102, 241, 0.4);
}

.plyr__control:hover {
    background: var(--plyr-color-main) !important;
    color: #fff !important;
}

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
