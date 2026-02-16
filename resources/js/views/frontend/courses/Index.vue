<template>
    <div class="min-h-screen">
        <!-- Hero Section -->
        <section class="relative pt-40 pb-20 px-6 overflow-hidden">
            <div class="absolute inset-0 bg-linear-to-b from-indigo-600/5 via-transparent to-transparent"></div>
            <div class="max-w-[1600px] mx-auto relative z-10">
                <div class="max-w-3xl animate-in fade-in slide-in-from-bottom-6 duration-700">
                    <h4 class="text-[11px] font-black text-indigo-600 uppercase tracking-[0.5em] mb-6">Course Catalog</h4>
                    <h1 class="text-6xl md:text-8xl font-black theme-text-main tracking-tighter leading-[0.9] mb-6">
                        Explore Our<br>
                        <span class="bg-linear-to-r from-indigo-600 to-violet-500 bg-clip-text text-transparent">Courses</span>
                    </h1>
                    <p class="theme-text-dim text-xl font-medium leading-relaxed max-w-xl">
                        Discover world-class courses designed to accelerate your career and expand your skills.
                    </p>
                </div>
            </div>
        </section>

        <!-- Filters & Content -->
        <section class="px-6 pb-32">
            <div class="max-w-[1600px] mx-auto">
                <!-- Filters Bar -->
                <div class="theme-bg-card border-2 theme-border rounded-[2rem] p-6 md:p-8 mb-16 shadow-sm animate-in fade-in slide-in-from-bottom-4 duration-700 delay-150">
                    <div class="flex flex-col lg:flex-row items-start lg:items-center gap-6">
                        <!-- Search -->
                        <div class="relative flex-1 w-full">
                            <svg class="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 theme-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input v-model="filters.search" @input="debouncedSearch" type="text" placeholder="Search courses..."
                                class="w-full pl-14 pr-6 py-4 rounded-2xl theme-bg-element border-2 theme-border outline-none focus:border-indigo-500/50 transition-all text-sm font-bold theme-text-main">
                        </div>

                        <!-- Category Filter -->
                        <select v-model="filters.category" @change="fetchCourses"
                            class="px-6 py-4 rounded-2xl theme-bg-element border-2 theme-border outline-none focus:border-indigo-500/50 transition-all text-xs font-black theme-text-main uppercase tracking-wider cursor-pointer min-w-[180px]">
                            <option value="">All Categories</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }} ({{ cat.courses_count }})</option>
                        </select>

                        <!-- Level Filter -->
                        <select v-model="filters.level" @change="fetchCourses"
                            class="px-6 py-4 rounded-2xl theme-bg-element border-2 theme-border outline-none focus:border-indigo-500/50 transition-all text-xs font-black theme-text-main uppercase tracking-wider cursor-pointer min-w-[160px]">
                            <option value="">All Levels</option>
                            <option value="beginner">Beginner</option>
                            <option value="intermediate">Intermediate</option>
                            <option value="advanced">Advanced</option>
                            <option value="expert">Expert</option>
                        </select>

                        <!-- Sort -->
                        <select v-model="filters.sort" @change="fetchCourses"
                            class="px-6 py-4 rounded-2xl theme-bg-element border-2 theme-border outline-none focus:border-indigo-500/50 transition-all text-xs font-black theme-text-main uppercase tracking-wider cursor-pointer min-w-[160px]">
                            <option value="newest">Newest</option>
                            <option value="popular">Most Popular</option>
                            <option value="rating">Top Rated</option>
                            <option value="price_low">Price: Low → High</option>
                            <option value="price_high">Price: High → Low</option>
                        </select>
                    </div>

                    <!-- Active Filters -->
                    <div v-if="hasActiveFilters" class="flex items-center gap-3 mt-6 pt-6 border-t theme-border">
                        <span class="text-[9px] font-black theme-text-dim uppercase tracking-[0.3em]">Active Filters:</span>
                        <button v-if="filters.search" @click="filters.search = ''; fetchCourses()"
                            class="px-4 py-1.5 rounded-xl bg-indigo-500/10 text-indigo-600 text-[10px] font-black uppercase tracking-wider flex items-center gap-2 hover:bg-indigo-500/20 transition-all">
                            "{{ filters.search }}" <span class="text-base leading-none">&times;</span>
                        </button>
                        <button v-if="filters.category" @click="filters.category = ''; fetchCourses()"
                            class="px-4 py-1.5 rounded-xl bg-emerald-500/10 text-emerald-600 text-[10px] font-black uppercase tracking-wider flex items-center gap-2 hover:bg-emerald-500/20 transition-all">
                            {{ categories.find(c => c.id == filters.category)?.name }} <span class="text-base leading-none">&times;</span>
                        </button>
                        <button v-if="filters.level" @click="filters.level = ''; fetchCourses()"
                            class="px-4 py-1.5 rounded-xl bg-violet-500/10 text-violet-600 text-[10px] font-black uppercase tracking-wider flex items-center gap-2 hover:bg-violet-500/20 transition-all">
                            {{ filters.level }} <span class="text-base leading-none">&times;</span>
                        </button>
                        <button @click="clearFilters" class="text-[10px] font-black text-rose-500 uppercase tracking-wider hover:underline ml-2">
                            Clear All
                        </button>
                    </div>
                </div>

                <!-- Results Info -->
                <div class="flex items-center justify-between mb-10">
                    <p class="text-sm font-bold theme-text-dim">
                        <span v-if="!loading">Showing <span class="font-black theme-text-main">{{ courses.length }}</span> of <span class="font-black theme-text-main">{{ totalCourses }}</span> courses</span>
                    </p>
                </div>

                <!-- Loading State -->
                <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
                    <div v-for="i in 6" :key="i" class="theme-bg-card border-2 theme-border rounded-[3rem] overflow-hidden animate-pulse">
                        <div class="h-72 bg-slate-200 dark:bg-slate-700"></div>
                        <div class="p-10 space-y-5">
                            <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded-full w-1/3"></div>
                            <div class="h-7 bg-slate-200 dark:bg-slate-700 rounded-full w-4/5"></div>
                            <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded-full w-full"></div>
                            <div class="pt-8 border-t theme-border flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-slate-200 dark:bg-slate-700"></div>
                                <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded-full w-24"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else-if="courses.length === 0" class="text-center py-40">
                    <div class="w-28 h-28 mx-auto mb-10 rounded-[2rem] bg-indigo-500/10 flex items-center justify-center">
                        <svg class="w-14 h-14 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-3xl font-black theme-text-main mb-3 tracking-tight">No Courses Found</h3>
                    <p class="theme-text-dim text-lg font-medium mb-8">Try adjusting your filters or search term.</p>
                    <button @click="clearFilters" class="px-10 py-4 rounded-2xl bg-indigo-600 text-white text-xs font-black uppercase tracking-widest hover:bg-indigo-500 transition-all shadow-xl shadow-indigo-600/20">
                        Clear All Filters
                    </button>
                </div>

                <!-- Courses Grid -->
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
                    <router-link v-for="course in courses" :key="course.id" :to="`/courses/${course.slug}`"
                        class="group theme-bg-card border-2 theme-border rounded-[3rem] overflow-hidden hover:shadow-4xl transition-all duration-700 hover:-translate-y-3 block animate-in fade-in slide-in-from-bottom-4 duration-500">
                        <!-- Thumbnail -->
                        <div class="h-72 relative overflow-hidden">
                            <img :src="course.thumbnail || defaultThumbnail" class="w-full h-full object-cover grayscale-[0.3] group-hover:grayscale-0 group-hover:scale-110 transition-all duration-[1.5s] ease-out" :alt="course.title">
                            <div class="absolute inset-0 bg-linear-to-t from-black/80 via-black/10 to-transparent"></div>
                            
                            <!-- Badges -->
                            <div class="absolute top-6 left-6 flex flex-col gap-2">
                                <span v-if="course.is_bestseller" class="px-3 py-1 bg-amber-500 text-black text-[8px] font-black uppercase tracking-widest rounded-lg shadow-lg">Bestseller</span>
                                <span v-if="course.is_new" class="px-3 py-1 bg-indigo-600 text-white text-[8px] font-black uppercase tracking-widest rounded-lg shadow-lg">New</span>
                                <span v-if="course.discount_percentage" class="px-3 py-1 bg-rose-500 text-white text-[8px] font-black uppercase tracking-widest rounded-lg shadow-lg">{{ course.discount_percentage }}% OFF</span>
                            </div>

                            <!-- Bottom Overlay Info -->
                            <div class="absolute bottom-6 left-6 right-6 flex items-end justify-between text-white">
                                <div class="flex items-center gap-2">
                                    <span class="px-3 py-1 bg-white/10 backdrop-blur-xl rounded-lg text-[8px] font-black uppercase tracking-widest border border-white/20">{{ course.level || 'All' }}</span>
                                    <span v-if="course.duration" class="text-white/50 text-[9px] font-bold">{{ course.duration }}</span>
                                </div>
                                <div class="flex flex-col items-end">
                                    <span v-if="course.compare_price" class="text-[10px] text-white/40 line-through font-bold">৳{{ course.compare_price }}</span>
                                    <span class="text-2xl font-black tracking-tighter">
                                        <template v-if="course.price_type === 'free'">Free</template>
                                        <template v-else>৳{{ course.price }}</template>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-10 space-y-6">
                            <div class="space-y-3">
                                <!-- Category Badge -->
                                <span v-if="course.category" class="text-[9px] font-black text-indigo-600 uppercase tracking-[0.3em]">{{ course.category.name }}</span>
                                
                                <!-- Rating -->
                                <div class="flex items-center gap-2">
                                    <div class="flex items-center text-amber-500">
                                        <svg v-for="i in 5" :key="i" class="w-3 h-3" :class="i <= Math.floor(course.rating || 0) ? 'fill-current' : 'opacity-30'" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    </div>
                                    <span class="text-[10px] font-bold theme-text-dim">{{ course.rating || '0.0' }} ({{ course.total_reviews || 0 }})</span>
                                </div>

                                <!-- Title -->
                                <h3 class="text-2xl font-black theme-text-main group-hover:text-indigo-600 transition-colors leading-tight tracking-tight line-clamp-2">{{ course.title }}</h3>
                                
                                <!-- Description -->
                                <p v-if="course.short_description" class="theme-text-dim text-sm font-medium leading-relaxed line-clamp-2">{{ course.short_description }}</p>
                            </div>
                            
                            <!-- Footer -->
                            <div class="pt-8 border-t theme-border flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-linear-to-tr from-indigo-500 to-violet-500 p-0.5">
                                        <img :src="course.instructor?.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(course.instructor?.name || 'U')}&background=0b1120&color=fff`" class="w-full h-full rounded-[0.6rem] object-cover" :alt="course.instructor?.name">
                                    </div>
                                    <span class="text-[10px] font-black theme-text-main uppercase tracking-wider">{{ course.instructor?.name }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[9px] font-bold theme-text-dim">{{ course.total_lectures || 0 }} lessons</span>
                                    <span class="text-[9px] theme-text-dim">•</span>
                                    <span class="text-[9px] font-bold theme-text-dim">{{ course.total_enrollments || 0 }} students</span>
                                </div>
                            </div>
                        </div>
                    </router-link>
                </div>

                <!-- Pagination -->
                <div v-if="lastPage > 1" class="flex items-center justify-center gap-3 mt-20">
                    <button @click="changePage(currentPage - 1)" :disabled="currentPage <= 1"
                        class="w-14 h-14 rounded-2xl theme-bg-card border-2 theme-border flex items-center justify-center hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition-all disabled:opacity-30 disabled:cursor-not-allowed">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <template v-for="page in visiblePages" :key="page">
                        <button v-if="page === '...'" disabled class="w-14 h-14 rounded-2xl theme-text-dim text-sm font-bold flex items-center justify-center">...</button>
                        <button v-else @click="changePage(page)"
                            class="w-14 h-14 rounded-2xl border-2 text-sm font-black flex items-center justify-center transition-all"
                            :class="page === currentPage ? 'bg-indigo-600 text-white border-indigo-600 shadow-xl shadow-indigo-500/30' : 'theme-bg-card theme-border theme-text-main hover:border-indigo-500'">
                            {{ page }}
                        </button>
                    </template>
                    <button @click="changePage(currentPage + 1)" :disabled="currentPage >= lastPage"
                        class="w-14 h-14 rounded-2xl theme-bg-card border-2 theme-border flex items-center justify-center hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition-all disabled:opacity-30 disabled:cursor-not-allowed">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
        </section>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { publicCourseService } from '../../../services/publicCourseService';

const route = useRoute();
const router = useRouter();

const courses = ref([]);
const categories = ref([]);
const loading = ref(true);
const totalCourses = ref(0);
const currentPage = ref(1);
const lastPage = ref(1);
const defaultThumbnail = 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&q=80&w=800';

const filters = reactive({
    search: '',
    category: '',
    level: '',
    sort: 'newest',
});

let searchTimeout = null;

const hasActiveFilters = computed(() => {
    return filters.search || filters.category || filters.level;
});

const visiblePages = computed(() => {
    const pages = [];
    const total = lastPage.value;
    const current = currentPage.value;

    if (total <= 7) {
        for (let i = 1; i <= total; i++) pages.push(i);
    } else {
        pages.push(1);
        if (current > 3) pages.push('...');
        
        const start = Math.max(2, current - 1);
        const end = Math.min(total - 1, current + 1);
        for (let i = start; i <= end; i++) pages.push(i);
        
        if (current < total - 2) pages.push('...');
        pages.push(total);
    }
    return pages;
});

const fetchCourses = async () => {
    loading.value = true;
    try {
        const params = {
            page: currentPage.value,
            per_page: 12,
            sort: filters.sort,
        };
        if (filters.search) params.search = filters.search;
        if (filters.category) params.category = filters.category;
        if (filters.level) params.level = filters.level;

        const response = await publicCourseService.index(params);
        courses.value = response.data;
        totalCourses.value = response.total;
        currentPage.value = response.current_page;
        lastPage.value = response.last_page;
    } catch (error) {
        console.error('Failed to fetch courses:', error);
        courses.value = [];
    } finally {
        loading.value = false;
    }
};

const fetchCategories = async () => {
    try {
        const data = await publicCourseService.categories();
        categories.value = data;
    } catch (error) {
        console.error('Failed to fetch categories:', error);
    }
};

const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        currentPage.value = 1;
        fetchCourses();
    }, 400);
};

const changePage = (page) => {
    if (page < 1 || page > lastPage.value) return;
    currentPage.value = page;
    fetchCourses();
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const clearFilters = () => {
    filters.search = '';
    filters.category = '';
    filters.level = '';
    filters.sort = 'newest';
    currentPage.value = 1;
    fetchCourses();
};

onMounted(() => {
    fetchCategories();
    fetchCourses();
});
</script>

<style scoped>
.shadow-4xl {
    box-shadow: 0 60px 100px -20px rgba(0, 0, 0, 0.3), 0 30px 60px -30px rgba(79, 70, 229, 0.15);
}
</style>
