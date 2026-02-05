<template>
  <div class="min-h-screen bg-[#fafafa] dark:bg-[#0a0a0b] transition-colors duration-500">
    
    <!-- Hero Section -->
    <section class="relative pt-32 pb-24 overflow-hidden">
      <!-- Gradient Orbs -->
      <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-[600px] h-[600px] bg-gradient-to-br from-violet-500/20 via-fuchsia-500/20 to-transparent rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-[600px] h-[600px] bg-gradient-to-tr from-cyan-500/20 via-blue-500/20 to-transparent rounded-full blur-3xl"></div>
      </div>
      
      <div class="relative z-10 max-w-6xl mx-auto px-6 text-center">
        <div class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-violet-500/10 to-fuchsia-500/10 dark:from-violet-500/20 dark:to-fuchsia-500/20 rounded-full border border-violet-500/20 mb-8">
          <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
          <span class="text-sm font-semibold text-slate-600 dark:text-slate-300">Insights & Stories</span>
        </div>
        
        <h1 class="text-5xl md:text-7xl font-bold bg-gradient-to-br from-slate-900 via-slate-700 to-slate-900 dark:from-white dark:via-slate-200 dark:to-slate-400 bg-clip-text text-transparent mb-6 tracking-tight">
          Learn, Build, Grow
        </h1>
        
        <p class="text-lg md:text-xl text-slate-500 dark:text-slate-400 max-w-2xl mx-auto leading-relaxed">
          Discover the latest in technology, design patterns, and development best practices from industry experts.
        </p>
      </div>
    </section>

    <!-- Main Content -->
    <section class="max-w-7xl mx-auto px-6 pb-32">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
        
        <!-- Posts Column -->
        <main class="lg:col-span-8 space-y-8">
          
          <!-- Loading State -->
          <div v-if="loading" class="space-y-6">
            <div v-for="n in 3" :key="n" class="group relative bg-white dark:bg-[#111113] rounded-2xl border border-slate-200/80 dark:border-slate-800/80 overflow-hidden">
              <div class="p-6 animate-pulse">
                <div class="flex gap-6">
                  <div class="w-48 h-32 bg-slate-200 dark:bg-slate-800 rounded-xl shrink-0"></div>
                  <div class="flex-1 space-y-4">
                    <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded w-1/4"></div>
                    <div class="h-6 bg-slate-200 dark:bg-slate-800 rounded w-3/4"></div>
                    <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded w-full"></div>
                    <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded w-2/3"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else-if="posts.length === 0" class="text-center py-24 bg-white dark:bg-[#111113] rounded-2xl border border-slate-200/80 dark:border-slate-800/80">
            <div class="w-16 h-16 mx-auto mb-6 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
              <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-slate-900 dark:text-white mb-2">No articles yet</h3>
            <p class="text-slate-500 dark:text-slate-400">Check back soon for new content.</p>
          </div>

          <!-- Posts List -->
          <template v-else>
            <!-- Featured Post -->
            <article v-if="posts[0]" class="group relative bg-white dark:bg-[#111113] rounded-2xl border border-slate-200/80 dark:border-slate-800/80 overflow-hidden hover:border-slate-300 dark:hover:border-slate-700 transition-all duration-300 hover:shadow-xl hover:shadow-slate-200/50 dark:hover:shadow-none">
              <router-link :to="{ name: 'frontend.blog.show', params: { slug: posts[0].slug } }" class="block">
                <!-- Image -->
                <div class="relative aspect-[2/1] overflow-hidden">
                  <img 
                    :src="posts[0].featured_image || 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2000&auto=format&fit=crop'"
                    class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                  >
                  <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/0 to-black/0"></div>
                  
                  <!-- Badge -->
                  <div class="absolute top-4 left-4 flex gap-2">
                    <span class="px-3 py-1.5 bg-white/95 dark:bg-black/80 backdrop-blur-md text-xs font-semibold text-violet-600 dark:text-violet-400 rounded-full shadow-lg">
                      {{ posts[0].category?.name || 'Article' }}
                    </span>
                    <span v-if="posts[0].is_featured" class="px-3 py-1.5 bg-gradient-to-r from-amber-500 to-orange-500 text-xs font-semibold text-white rounded-full shadow-lg flex items-center gap-1">
                      <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                      Featured
                    </span>
                  </div>
                </div>
                
                <!-- Content -->
                <div class="p-6 md:p-8">
                  <div class="flex items-center gap-3 mb-4 text-sm text-slate-500 dark:text-slate-400">
                    <img :src="posts[0].author?.avatar || `https://ui-avatars.com/api/?name=${posts[0].author?.name}&background=6366f1&color=fff`" class="w-6 h-6 rounded-full ring-2 ring-white dark:ring-slate-900">
                    <span class="font-medium text-slate-700 dark:text-slate-300">{{ posts[0].author?.name }}</span>
                    <span class="text-slate-300 dark:text-slate-600">•</span>
                    <span>{{ formatDate(posts[0].published_at) }}</span>
                    <span class="text-slate-300 dark:text-slate-600">•</span>
                    <span>{{ posts[0].reading_time }} min read</span>
                  </div>
                  
                  <h2 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-white mb-3 group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors">
                    {{ posts[0].title }}
                  </h2>
                  
                  <p class="text-slate-600 dark:text-slate-400 leading-relaxed line-clamp-2 mb-6">
                    {{ posts[0].excerpt }}
                  </p>
                  
                  <div class="flex items-center justify-between pt-6 border-t border-slate-100 dark:border-slate-800/80">
                    <div class="flex items-center gap-4 text-sm text-slate-500 dark:text-slate-400">
                      <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        {{ formatNumber(posts[0].views_count) }}
                      </span>
                      <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        {{ formatNumber(posts[0].likes_count) }}
                      </span>
                    </div>
                    <span class="text-sm font-semibold text-violet-600 dark:text-violet-400 flex items-center gap-2 group-hover:gap-3 transition-all">
                      Read more
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </span>
                  </div>
                </div>
              </router-link>
            </article>

            <!-- Regular Posts List -->
            <div class="space-y-6">
              <article 
                v-for="post in posts.slice(1)" 
                :key="post.id" 
                class="group"
              >
                <router-link :to="{ name: 'frontend.blog.show', params: { slug: post.slug } }" class="block">
                  <div class="flex flex-col sm:flex-row gap-6 p-5 bg-white dark:bg-slate-900/50 rounded-2xl border border-transparent hover:border-slate-200 dark:hover:border-slate-700/50 hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-all duration-300">
                    
                    <!-- Thumbnail -->
                    <div class="relative w-full sm:w-56 h-44 sm:h-40 shrink-0 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800">
                      <img 
                        :src="post.featured_image || 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=600&auto=format&fit=crop'"
                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                      >
                      <!-- Category Pill -->
                      <div class="absolute bottom-3 left-3">
                        <span class="px-2.5 py-1 bg-black/70 backdrop-blur-sm text-[10px] font-bold uppercase tracking-wider text-white rounded-md">
                          {{ post.category?.name }}
                        </span>
                      </div>
                    </div>
                    
                    <!-- Content -->
                    <div class="flex-1 flex flex-col justify-between py-1">
                      <!-- Top: Tags & Meta -->
                      <div>
                        <!-- Tags Row -->
                        <div v-if="post.tags && post.tags.length" class="flex flex-wrap items-center gap-2 mb-3">
                          <span 
                            v-for="tag in post.tags.slice(0, 3)" 
                            :key="tag.id"
                            class="text-xs font-semibold text-violet-600 dark:text-violet-400 hover:underline"
                          >
                            #{{ tag.name }}
                          </span>
                          <span v-if="post.tags.length > 3" class="text-xs text-slate-400">+{{ post.tags.length - 3 }} more</span>
                        </div>
                        
                        <!-- Title -->
                        <h3 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white mb-2 line-clamp-2 group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors leading-snug">
                          {{ post.title }}
                        </h3>
                        
                        <!-- Excerpt -->
                        <p class="text-sm text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed hidden sm:block">
                          {{ post.excerpt }}
                        </p>
                      </div>
                      
                      <!-- Bottom: Author & Stats -->
                      <div class="flex items-center justify-between mt-4 pt-4 border-t border-slate-100 dark:border-slate-800/50">
                        <div class="flex items-center gap-3">
                          <img :src="post.author?.avatar || `https://ui-avatars.com/api/?name=${post.author?.name}&background=6366f1&color=fff`" class="w-7 h-7 rounded-full">
                          <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                            <span class="font-semibold text-slate-700 dark:text-slate-300">{{ post.author?.name }}</span>
                            <span>•</span>
                            <span>{{ formatDate(post.published_at) }}</span>
                            <span>•</span>
                            <span>{{ post.reading_time }} min</span>
                          </div>
                        </div>
                        
                        <div class="flex items-center gap-3 text-xs text-slate-400">
                          <span class="flex items-center gap-1" title="Views">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            {{ formatNumber(post.views_count) }}
                          </span>
                          <span class="flex items-center gap-1 text-rose-400" title="Likes">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            {{ formatNumber(post.likes_count) }}
                          </span>
                        </div>
                      </div>
                    </div>
                    
                  </div>
                </router-link>
              </article>
            </div>

            <!-- Load More -->
            <div v-if="meta.last_page > 1 && meta.current_page < meta.last_page" class="flex justify-center pt-8">
              <button 
                @click="loadMore"
                :disabled="loadingMore"
                class="group inline-flex items-center gap-2 px-8 py-4 bg-white dark:bg-[#111113] border border-slate-200 dark:border-slate-800 rounded-full text-sm font-semibold text-slate-700 dark:text-slate-300 hover:border-violet-500 hover:text-violet-600 dark:hover:text-violet-400 transition-all duration-300 shadow-sm hover:shadow-lg"
              >
                <span v-if="loadingMore" class="w-4 h-4 border-2 border-violet-500 border-t-transparent rounded-full animate-spin"></span>
                <span v-else>Load more articles</span>
                <svg v-if="!loadingMore" class="w-4 h-4 transition-transform group-hover:translate-y-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
              </button>
            </div>
          </template>
        </main>

        <!-- Sidebar -->
        <aside class="lg:col-span-4 space-y-8">
          <div class="sticky top-8 space-y-8">
            
            <!-- Search -->
            <div class="bg-white dark:bg-[#111113] rounded-2xl border border-slate-200/80 dark:border-slate-800/80 p-6">
              <div class="relative">
                <input 
                  v-model="searchQuery"
                  @input="handleSearch"
                  type="text" 
                  placeholder="Search articles..." 
                  class="w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-xl text-sm text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500/50 focus:border-violet-500 transition-all"
                >
                <svg class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
              </div>
            </div>

            <!-- Categories -->
            <div class="bg-white dark:bg-[#111113] rounded-2xl border border-slate-200/80 dark:border-slate-800/80 p-6">
              <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4">Topics</h3>
              <div class="space-y-1">
                <button 
                  @click="selectCategory(null)"
                  class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-all"
                  :class="!selectedCategory 
                    ? 'bg-violet-50 dark:bg-violet-500/10 text-violet-600 dark:text-violet-400' 
                    : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                >
                  <span>All Posts</span>
                  <span class="text-xs bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 px-2 py-0.5 rounded-full">{{ totalPosts }}</span>
                </button>
                <button 
                  v-for="cat in categories" 
                  :key="cat.id" 
                  @click="selectCategory(cat.slug)"
                  class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-all"
                  :class="selectedCategory === cat.slug 
                    ? 'bg-violet-50 dark:bg-violet-500/10 text-violet-600 dark:text-violet-400' 
                    : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                >
                  <span>{{ cat.name }}</span>
                  <span class="text-xs bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 px-2 py-0.5 rounded-full">{{ cat.posts_count }}</span>
                </button>
              </div>
            </div>

            <!-- Newsletter -->
            <div class="relative bg-gradient-to-br from-violet-600 to-fuchsia-600 rounded-2xl p-6 overflow-hidden">
              <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg viewBox=%220 0 200 200%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noiseFilter%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.65%22 numOctaves=%223%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noiseFilter)%22/%3E%3C/svg%3E')] opacity-20"></div>
              <div class="relative z-10">
                <h3 class="text-lg font-bold text-white mb-2">Stay in the loop</h3>
                <p class="text-violet-100 text-sm mb-4">Get weekly insights delivered to your inbox.</p>
                <div class="space-y-3">
                  <input type="email" placeholder="your@email.com" class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-sm text-white placeholder:text-white/60 focus:outline-none focus:bg-white/20 transition-all">
                  <button class="w-full py-3 bg-white text-violet-600 rounded-xl text-sm font-semibold hover:bg-violet-50 transition-colors shadow-lg">
                    Subscribe
                  </button>
                </div>
              </div>
            </div>

          </div>
        </aside>

      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { publicBlogService } from '../../../services/publicBlogService';

const router = useRouter();
const route = useRoute();

const posts = ref([]);
const categories = ref([]);
const loading = ref(true);
const loadingMore = ref(false);
const searchQuery = ref('');
const selectedCategory = ref(null);
const meta = ref({});
const totalPosts = ref(0);

const initData = async () => {
  loading.value = true;
  try {
    const [postsData, catsData] = await Promise.all([
      publicBlogService.getPosts({ 
        category: selectedCategory.value,
        search: searchQuery.value
      }),
      publicBlogService.getCategories()
    ]);
    
    posts.value = postsData.data;
    meta.value = postsData.meta;
    categories.value = catsData.data;
    totalPosts.value = postsData.meta.total;
  } catch (error) {
    console.error('Initial load failed', error);
  } finally {
    loading.value = false;
  }
};

let debounceTimer;
const handleSearch = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    selectedCategory.value = null;
    fetchPosts(1);
  }, 500);
};

const selectCategory = (slug) => {
  selectedCategory.value = slug;
  if (slug) {
    router.replace({ query: { ...route.query, category: slug } });
  } else {
    const query = { ...route.query };
    delete query.category;
    router.replace({ query });
  }
  fetchPosts(1);
};

const fetchPosts = async (page = 1) => {
  if (page === 1) loading.value = true;
  else loadingMore.value = true;

  try {
    const data = await publicBlogService.getPosts({
      page,
      category: selectedCategory.value,
      search: searchQuery.value
    });

    if (page === 1) {
      posts.value = data.data;
    } else {
      posts.value = [...posts.value, ...data.data];
    }
    meta.value = data.meta;
  } catch (error) {
    console.error('Fetch posts error', error);
  } finally {
    loading.value = false;
    loadingMore.value = false;
  }
};

const loadMore = () => {
  if (meta.value.current_page < meta.value.last_page) {
    fetchPosts(meta.value.current_page + 1);
  }
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const formatNumber = (num) => {
  if (!num) return 0;
  return new Intl.NumberFormat('en-US', { notation: 'compact', compactDisplay: 'short' }).format(num);
};

onMounted(() => {
  if (route.query.category) {
    selectedCategory.value = route.query.category;
  }
  if (route.query.search) {
    searchQuery.value = route.query.search;
  }
  initData();
});
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
