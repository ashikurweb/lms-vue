<template>
  <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-black theme-text-main tracking-tight">Blog Posts</h1>
        <p class="text-sm theme-text-muted mt-1 font-medium">Compose and manage your blog stories effortlessly.</p>
      </div>
      <div class="flex items-center gap-3">
        <div class="relative group">
          <input 
            v-model="search"
            type="text" 
            placeholder="Search posts..." 
            class="pl-10 pr-4 py-3 theme-bg-card border theme-border rounded-2xl text-sm font-bold theme-text-main outline-none focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10 transition-all w-64 shadow-sm"
            @input="debounceSearch"
          >
          <svg class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 theme-text-dim group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
        </div>
        <router-link 
          :to="{ name: 'blog-posts.create' }"
          class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-2xl font-bold flex items-center justify-center gap-2 shadow-lg shadow-indigo-600/20 active:scale-95 transition-all whitespace-nowrap"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          <span class="hidden sm:inline">New Post</span>
        </router-link>
      </div>
    </div>

    <!-- Main Table Area -->
    <div class="relative min-h-[400px]">
      <!-- Column Headers -->
      <!-- Table Head -->
        <div class="grid grid-cols-12 gap-4 px-8 py-4 theme-text-dim text-[10px] font-black uppercase tracking-widest pl-12">
          <div class="col-span-1">SL</div>
          <div class="col-span-3">Details</div>
          <div class="col-span-2">Stats</div>
          <div class="col-span-2">Author</div>
          <div class="col-span-1 text-center">Status</div>
          <div class="col-span-1 text-center">Feat</div>
          <div class="col-span-2 text-right pr-4">Actions</div>
        </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex items-center justify-center py-32 theme-bg-card border theme-border rounded-[2.5rem] shadow-sm">
         <SkeletonLoader :cols="6" />
      </div>

      <template v-else>
        <!-- Empty State Component -->
        <EmptyState 
          v-if="posts.length === 0"
          title="No posts found"
          message="Time to write your first story! Share your knowledge with the world."
        />

        <!-- Floating Rows -->
        <div class="space-y-4">
          <div 
            v-for="(post, index) in posts" 
            :key="post.id" 
            class="grid grid-cols-12 gap-4 items-center px-8 py-5 theme-bg-card border theme-border rounded-[1.5rem] shadow-sm transition-all duration-300 group"
          >
            <!-- SL -->
            <div class="col-span-1">
              <span class="text-xs font-black theme-text-dim tabular-nums">#{{ (pagination.current_page - 1) * pagination.per_page + index + 1 }}</span>
            </div>

            <!-- Details -->
            <div class="col-span-3">
              <div class="flex items-center gap-4">
                <div class="relative w-16 h-16 rounded-xl overflow-hidden flex-shrink-0 border theme-border bg-slate-100 dark:bg-slate-800">
                   <img 
                    v-if="post.thumbnail || post.featured_image" 
                    :src="post.thumbnail || post.featured_image" 
                    class="w-full h-full object-cover transition-transform duration-500"
                    alt="Post Thumbnail"
                  >
                  <div v-else class="w-full h-full flex items-center justify-center theme-text-dim opacity-20">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                  </div>
                </div>
                <div class="flex flex-col min-w-0">
                  <h4 class="text-sm font-black theme-text-main truncate pr-4 transition-colors">{{ post.title }}</h4>
                  <div class="flex items-center gap-2 mt-1">
                    <span v-if="post.category" class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-indigo-500/10 text-indigo-500 uppercase tracking-tighter">
                      {{ post.category.name }}
                    </span>
                    <span class="text-[10px] theme-text-dim font-medium">• {{ post.reading_time || 0 }} min read</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Stats -->
            <div class="col-span-2">
              <div class="flex items-center gap-2">
                <!-- Views -->
                <div 
                  class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/50 group/stats hover:border-indigo-500/30 transition-colors"
                  title="Total Views"
                >
                  <svg class="w-3.5 h-3.5 text-slate-400 group-hover/stats:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                  <span class="text-xs font-bold theme-text-main">{{ formatNumber(post.views_count) }}</span>
                </div>

                <!-- Engagement -->
                <div class="flex items-center gap-3 pl-3 border-l border-slate-200 dark:border-slate-700/50">
                  <!-- Likes -->
                  <div class="flex items-center gap-1" title="Likes">
                    <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    <span class="text-xs font-semibold theme-text-muted">{{ formatNumber(post.likes_count) }}</span>
                  </div>
                  <!-- Comments -->
                  <div class="flex items-center gap-1" title="Comments">
                    <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    <span class="text-xs font-semibold theme-text-muted">{{ formatNumber(post.comments_count) }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Author & Date -->
            <div class="col-span-2">
              <div class="flex flex-col">
                <span class="text-xs font-black theme-text-main">{{ post.author?.name || 'Admin' }}</span>
                <span class="text-[10px] theme-text-dim font-medium">{{ formatDate(post.created_at) }}</span>
              </div>
            </div>

            <!-- Status -->
            <div class="col-span-1 text-center">
              <span 
                class="inline-flex px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest"
                :class="{
                  'bg-emerald-500/10 text-emerald-500': post.status === 'published',
                  'bg-amber-500/10 text-amber-500': post.status === 'draft' || post.status === 'pending',
                  'bg-slate-500/10 text-slate-500': post.status === 'archived'
                }"
              >
                {{ post.status }}
              </span>
            </div>

            <!-- Featured -->
            <div class="col-span-1 text-center">
              <div class="flex justify-center">
                <div 
                  class="w-6 h-6 rounded-lg flex items-center justify-center transition-all duration-300"
                  :class="post.is_featured ? 'bg-amber-500 shadow-[0_0_15px_rgba(245,158,11,0.4)] text-white' : 'bg-slate-700/30 text-slate-500'"
                >
                  <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                  </svg>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="col-span-2 text-right">
              <div class="flex items-center justify-end">
                <div class="relative group/actions flex items-center">
                  <div class="absolute right-0 flex items-center gap-1.5 px-2 py-1.5 theme-bg-card border theme-border rounded-2xl shadow-2xl opacity-0 invisible translate-x-4 scale-90 group-hover/actions:opacity-100 group-hover/actions:visible group-hover/actions:translate-x-[-45px] group-hover/actions:scale-100 transition-all duration-500 cubic-bezier(0.34, 1.56, 0.64, 1) z-20 backdrop-blur-xl">
                    <router-link 
                      :to="{ name: 'blog-posts.edit', params: { slug: post.slug } }"
                      class="w-9 h-9 flex items-center justify-center rounded-xl text-indigo-500 hover:bg-indigo-500/10 transition-all active:scale-90"
                      title="Edit Post"
                    >
                      <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </router-link>
                    <div class="w-px h-4 bg-slate-700/50"></div>
                    <button 
                      @click="triggerDelete(post)"
                      class="w-9 h-9 flex items-center justify-center rounded-xl text-rose-500 hover:bg-rose-500/10 transition-all active:scale-90"
                      title="Delete Post"
                    >
                      <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                  </div>

                  <button class="w-10 h-10 flex items-center justify-center rounded-xl theme-bg-element border theme-border theme-text-dim hover:theme-text-main hover:border-indigo-500/50 transition-all group-hover/actions:rotate-90 group-hover/actions:bg-indigo-600 group-hover/actions:text-white group-hover/actions:shadow-[0_0_20px_rgba(79,70,229,0.4)] z-30">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div class="mt-8">
          <Pagination :pagination="pagination" @change="fetchPosts" />
        </div>
      </template>
    </div>

    <!-- Delete Dialog -->
    <ActionDialog
      :show="showDeleteModal"
      title="Delete Blog Post"
      :message="`Are you sure you want to delete the post '${postToDelete?.title}'? This action cannot be undone.`"
      :loading="deleting"
      @confirm="confirmDelete"
      @cancel="showDeleteModal = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import { blogPostService } from '../../../services/blogPostService';
import { blogCategoryService } from '../../../services/blogCategoryService';
import ActionDialog from '../../../components/common/ActionDialog.vue';
import Pagination from '../../../components/common/Pagination.vue';
import SkeletonLoader from '../../../components/common/SkeletonLoader.vue';
import EmptyState from '../../../components/common/EmptyState.vue';
import { useToast } from '../../../composables/useToast';
import { formatDate, formatNumber } from '../../../utils/helpers';

const toast = useToast();
const posts = ref([]);
const categories = ref([]);
const loading = ref(true);
const search = ref('');
const showDeleteModal = ref(false);
const postToDelete = ref(null);
const deleting = ref(false);

const filters = reactive({
  status: '',
  category_id: ''
});

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
});

const fetchPosts = async (page = 1) => {
  loading.value = true;
  try {
    const data = await blogPostService.index({ 
      page, 
      search: search.value,
      status: filters.status,
      category_id: filters.category_id,
      per_page: pagination.per_page 
    });
    
    posts.value = data.data;
    const meta = data.meta;
    pagination.current_page = meta.current_page;
    pagination.last_page = meta.last_page;
    pagination.total = meta.total;
  } catch (error) {
    toast.error('Failed to fetch posts');
  } finally {
    loading.value = false;
  }
};

const fetchCategories = async () => {
    try {
        const data = await blogCategoryService.getAll();
        categories.value = data;
    } catch (error) {
        toast.error('Failed to load categories');
    }
};

let debounceTimer;
const debounceSearch = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    fetchPosts(1);
  }, 500);
};

const triggerDelete = (post) => {
  postToDelete.value = post;
  showDeleteModal.value = true;
};

const confirmDelete = async () => {
  if (!postToDelete.value) return;
  deleting.value = true;
  try {
    await blogPostService.destroy(postToDelete.value.slug);
    toast.success('Post deleted successfully');
    showDeleteModal.value = false;
    postToDelete.value = null;
    fetchPosts(pagination.current_page);
  } catch (error) {
    toast.error('Failed to delete post');
  } finally {
    deleting.value = false;
  }
};

onMounted(() => {
  fetchPosts();
  fetchCategories();
});
</script>
