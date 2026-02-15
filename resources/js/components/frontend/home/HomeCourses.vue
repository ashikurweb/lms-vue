<template>
  <section class="py-40 px-6">
    <div class="max-w-[1600px] mx-auto">
        <div class="revealer-up-course opacity-0 flex flex-col md:flex-row md:items-end justify-between gap-10 mb-24">
            <div class="space-y-4">
                <h4 class="text-[11px] font-black text-indigo-600 uppercase tracking-[0.5em]">Curriculum Database</h4>
                <h2 class="text-6xl md:text-8xl font-black theme-text-main tracking-tighter leading-none">Engineering Tracks</h2>
            </div>
            <router-link to="/courses" class="text-[10px] font-black theme-text-main uppercase tracking-[0.4em] flex items-center gap-5 group py-4 px-10 border-2 theme-border rounded-full hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                Access All Modules
                <div class="w-10 h-10 rounded-full bg-indigo-600 group-hover:bg-white text-white group-hover:text-indigo-600 flex items-center justify-center transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </div>
            </router-link>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
            <div v-for="i in 3" :key="i" class="theme-bg-card border-2 theme-border rounded-[4rem] overflow-hidden animate-pulse">
                <div class="h-80 bg-slate-200 dark:bg-slate-700"></div>
                <div class="p-12 space-y-6">
                    <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded-full w-1/3"></div>
                    <div class="h-8 bg-slate-200 dark:bg-slate-700 rounded-full w-4/5"></div>
                    <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded-full w-full"></div>
                    <div class="pt-10 border-t theme-border flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-slate-200 dark:bg-slate-700"></div>
                        <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded-full w-24"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="courses.length === 0" class="text-center py-32 revealer-up-course opacity-0">
            <div class="w-24 h-24 mx-auto mb-8 rounded-3xl bg-indigo-500/10 flex items-center justify-center">
                <svg class="w-12 h-12 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
            <h3 class="text-2xl font-black theme-text-main mb-3">No Courses Available Yet</h3>
            <p class="theme-text-dim text-lg">Check back soon for amazing new courses!</p>
        </div>

        <!-- Courses Grid -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
            <router-link v-for="course in courses" :key="course.id" :to="`/courses/${course.slug}`" class="revealer-up-course opacity-0 group theme-bg-card border-2 theme-border rounded-[4rem] overflow-hidden hover:shadow-4xl transition-all duration-700 hover:-translate-y-4 block cursor-pointer">
                <!-- Course Media Layer -->
                <div class="h-80 relative overflow-hidden">
                    <img :src="course.thumbnail || defaultThumbnail" class="w-full h-full object-cover grayscale-[0.5] group-hover:grayscale-0 group-hover:scale-110 transition-all duration-[1.5s] ease-out" :alt="course.title">
                    <div class="absolute inset-0 bg-linear-to-t from-black/90 via-black/20 to-transparent"></div>
                    
                    <!-- Metadata Badges -->
                    <div class="absolute top-8 left-8 flex flex-col gap-2">
                        <span v-if="course.is_bestseller" class="px-4 py-1.5 bg-amber-500 text-black text-[9px] font-black uppercase tracking-widest rounded-lg shadow-xl">Bestseller</span>
                        <span v-if="course.is_new" class="px-4 py-1.5 bg-indigo-600 text-white text-[9px] font-black uppercase tracking-widest rounded-lg shadow-xl">New</span>
                        <span v-if="course.discount_percentage" class="px-4 py-1.5 bg-rose-500 text-white text-[9px] font-black uppercase tracking-widest rounded-lg shadow-xl">{{ course.discount_percentage }}% OFF</span>
                    </div>

                    <div class="absolute bottom-8 left-8 right-8 flex items-center justify-between text-white">
                        <div class="flex items-center gap-3">
                            <span class="px-4 py-1.5 bg-white/10 backdrop-blur-xl rounded-xl text-[9px] font-black uppercase tracking-widest border border-white/20">{{ course.level || 'All Levels' }}</span>
                            <span class="text-white/60 text-[10px] font-bold">{{ course.duration }}</span>
                        </div>
                        <div class="flex flex-col items-end">
                            <span v-if="course.compare_price" class="text-xs text-white/40 line-through font-bold">৳{{ course.compare_price }}</span>
                            <span class="text-3xl font-black tracking-tighter">
                                <template v-if="course.price_type === 'free'">Free</template>
                                <template v-else>৳{{ course.price }}</template>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Course Content Layer -->
                <div class="p-12 space-y-8">
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <div class="flex items-center text-amber-500">
                                <svg v-for="i in 5" :key="i" class="w-3 h-3" :class="i <= Math.floor(course.rating || 0) ? 'fill-current' : 'opacity-30'" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            </div>
                            <span class="text-[10px] font-black theme-text-dim">({{ course.total_reviews || 0 }} reviews)</span>
                        </div>
                        <h3 class="text-3xl font-black theme-text-main group-hover:text-indigo-600 transition-colors leading-[1.1] tracking-tighter">{{ course.title }}</h3>
                        <p v-if="course.short_description" class="theme-text-dim text-sm font-medium leading-relaxed line-clamp-2 italic">"{{ course.short_description }}"</p>
                    </div>
                    
                    <!-- Instructor & Meta Footer -->
                    <div class="pt-10 border-t theme-border flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-linear-to-tr from-indigo-500 to-violet-500 border-2 border-white/10 p-0.5">
                                <img :src="course.instructor?.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(course.instructor?.name || 'Unknown')}&background=0b1120&color=fff`" class="w-full h-full rounded-[0.9rem] object-cover" :alt="course.instructor?.name">
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xs font-black theme-text-main uppercase tracking-widest leading-none">{{ course.instructor?.name || 'Unknown' }}</span>
                                <span class="text-[9px] font-bold theme-text-dim uppercase tracking-[0.3em] mt-2">Instructor</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-1.5 px-4 py-2 rounded-xl theme-bg-element text-[9px] font-black theme-text-dim uppercase tracking-wider">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                {{ course.total_lectures || 0 }}
                            </div>
                            <div class="flex items-center gap-1.5 px-4 py-2 rounded-xl theme-bg-element text-[9px] font-black theme-text-dim uppercase tracking-wider">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                {{ course.total_enrollments || 0 }}
                            </div>
                        </div>
                    </div>
                </div>
            </router-link>
        </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { publicCourseService } from '../../../services/publicCourseService';

gsap.registerPlugin(ScrollTrigger);

const courses = ref([]);
const loading = ref(true);
const defaultThumbnail = 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&q=80&w=800';

const fetchFeaturedCourses = async () => {
    loading.value = true;
    try {
        const data = await publicCourseService.featured(6);
        courses.value = data;
    } catch (error) {
        console.error('Failed to fetch featured courses:', error);
        courses.value = [];
    } finally {
        loading.value = false;
        // Animate after data loads
        setTimeout(() => {
            gsap.to(".revealer-up-course", {
                scrollTrigger: {
                    trigger: ".revealer-up-course",
                    start: "top 85%",
                },
                y: 0,
                opacity: 1,
                duration: 1.2,
                stagger: 0.2,
                ease: "power3.out"
            });
        }, 100);
    }
};

onMounted(fetchFeaturedCourses);
</script>

<style scoped>
.revealer-up-course { transform: translateY(70px); }
.shadow-4xl {
    box-shadow: 0 60px 100px -20px rgba(0, 0, 0, 0.4), 0 30px 60px -30px rgba(79, 70, 229, 0.2);
}
</style>
