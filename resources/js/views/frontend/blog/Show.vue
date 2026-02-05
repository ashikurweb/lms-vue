<template>
  <div class="min-h-screen bg-[#fafafa] dark:bg-[#0a0a0b] transition-colors duration-500">
    
    <!-- Loading State -->
    <div v-if="loading" class="min-h-screen flex items-center justify-center">
      <div class="text-center">
        <div class="w-12 h-12 border-2 border-violet-500 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
        <p class="text-sm font-medium text-slate-400">Loading article...</p>
      </div>
    </div>

    <template v-else-if="post">
      <!-- Scroll Progress -->
      <div class="fixed top-0 left-0 w-full h-1 z-50 bg-slate-200/50 dark:bg-slate-800/50">
        <div class="h-full bg-gradient-to-r from-violet-500 to-fuchsia-500 transition-all duration-150 ease-out" :style="{ width: `${scrollProgress}%` }"></div>
      </div>

      <!-- Navigation -->
      <nav class="sticky top-0 z-40 bg-white/95 dark:bg-[#0a0a0b]/95 backdrop-blur-xl border-b border-slate-200 dark:border-slate-800">
        <div class="max-w-5xl mx-auto px-6 py-4 flex items-center justify-between">
          <router-link to="/blog" class="inline-flex items-center gap-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-violet-600 dark:hover:text-violet-400 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Blog
          </router-link>
          
          <div class="flex items-center gap-3">
            <button class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 dark:text-slate-400 transition-colors" title="Share">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
            </button>
            <button class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 dark:text-slate-400 transition-colors" title="Bookmark">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
            </button>
          </div>
        </div>
      </nav>

      <!-- Article Header -->
      <header class="pt-24 pb-12 px-6">
        <div class="max-w-3xl mx-auto text-center">
          <!-- Category & Meta -->
          <div class="flex flex-wrap items-center justify-center gap-3 mb-8">
            <span class="inline-flex items-center px-3 py-1.5 bg-violet-50 dark:bg-violet-500/10 text-violet-600 dark:text-violet-400 text-xs font-semibold rounded-full">
              {{ post.category?.name || 'Article' }}
            </span>
            <span v-if="post.is_featured" class="inline-flex items-center gap-1 px-3 py-1.5 bg-gradient-to-r from-amber-500 to-orange-500 text-white text-xs font-semibold rounded-full">
              <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
              Featured
            </span>
            <span v-if="post.is_pinned" class="inline-flex items-center gap-1 px-3 py-1.5 bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 text-xs font-semibold rounded-full">
              <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 3.636a1 1 0 010 1.414 7 7 0 000 9.9 1 1 0 11-1.414 1.414 9 9 0 010-12.728 1 1 0 011.414 0zm9.9 0a1 1 0 011.414 0 9 9 0 010 12.728 1 1 0 11-1.414-1.414 7 7 0 000-9.9 1 1 0 010-1.414zM7.879 6.464a1 1 0 010 1.414 3 3 0 000 4.243 1 1 0 11-1.415 1.414 5 5 0 010-7.07 1 1 0 011.415 0zm4.242 0a1 1 0 011.415 0 5 5 0 010 7.072 1 1 0 01-1.415-1.415 3 3 0 000-4.242 1 1 0 010-1.415zM10 9a1 1 0 011 1v.01a1 1 0 11-2 0V10a1 1 0 011-1z" clip-rule="evenodd"/></svg>
              Pinned
            </span>
          </div>
          
          <!-- Title -->
          <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-slate-900 dark:text-white leading-tight tracking-tight mb-8">
            {{ post.title }}
          </h1>

          <!-- Author & Meta -->
          <div class="flex flex-wrap items-center justify-center gap-6 text-sm text-slate-500 dark:text-slate-400">
            <div class="flex items-center gap-3">
              <img :src="post.author?.avatar || `https://ui-avatars.com/api/?name=${post.author?.name}&background=6366f1&color=fff`" class="w-10 h-10 rounded-full ring-2 ring-white dark:ring-slate-800 shadow-md">
              <div class="text-left">
                <p class="font-semibold text-slate-900 dark:text-white">{{ post.author?.name }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">Author</p>
              </div>
            </div>
            <div class="flex items-center gap-4 text-slate-400 dark:text-slate-500">
              <span class="flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                {{ formatDate(post.published_at) }}
              </span>
              <span class="flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ post.reading_time }} min read
              </span>
            </div>
          </div>
        </div>
      </header>
      <!-- Video Section (with YouTube Thumbnail) -->
      <div v-if="post.video_url" class="max-w-5xl mx-auto px-6 mb-12">
        <div class="relative rounded-3xl overflow-hidden shadow-2xl shadow-slate-500/10 dark:shadow-none ring-1 ring-slate-200 dark:ring-slate-800">
          <div class="aspect-video relative">
            <!-- YouTube Thumbnail -->
            <img 
              :src="getYouTubeThumbnail(post.video_url)"
              class="absolute inset-0 w-full h-full object-cover"
            >
            <!-- Dark Overlay -->
            <div class="absolute inset-0 bg-black/30"></div>
            <!-- Play Button -->
            <a 
              :href="post.video_url" 
              target="_blank"
              class="absolute inset-0 flex flex-col items-center justify-center group"
            >
              <div class="w-20 h-20 md:w-24 md:h-24 rounded-full bg-red-600 flex items-center justify-center shadow-2xl group-hover:scale-110 group-hover:bg-red-500 transition-all">
                <svg class="w-10 h-10 md:w-12 md:h-12 text-white ml-1" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
              </div>
              <span class="mt-4 text-white font-bold text-lg tracking-wide drop-shadow-lg">Watch on YouTube</span>
            </a>
          </div>
        </div>
      </div>

      <!-- Stats Bar -->
      <div class="max-w-3xl mx-auto px-6 mb-12">
        <div class="flex flex-wrap items-center justify-center gap-6 py-4 px-6 bg-white dark:bg-[#111113] rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-sm">
          <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            <span class="font-semibold">{{ formatNumber(post.views_count) }}</span> views
          </div>
          <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
            <svg class="w-5 h-5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            <span class="font-semibold">{{ formatNumber(post.likes_count) }}</span> likes
          </div>
          <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            <span class="font-semibold">{{ formatNumber(post.comments_count) }}</span> comments
          </div>
          <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
            <span class="font-semibold">{{ formatNumber(post.shares_count) }}</span> shares
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <main class="max-w-3xl mx-auto px-6">
        <!-- Excerpt -->
        <div class="relative p-6 bg-gradient-to-r from-violet-50 to-fuchsia-50 dark:from-violet-500/5 dark:to-fuchsia-500/5 rounded-2xl border border-violet-100 dark:border-violet-500/20 mb-12">
          <div class="absolute -left-3 top-6 w-1.5 h-12 bg-gradient-to-b from-violet-500 to-fuchsia-500 rounded-full"></div>
          <p class="text-lg md:text-xl text-slate-700 dark:text-slate-300 leading-relaxed font-medium italic">
            "{{ post.excerpt }}"
          </p>
        </div>

        <!-- Article Content -->
        <article class="prose prose-lg prose-slate dark:prose-invert max-w-none prose-headings:font-bold prose-headings:tracking-tight prose-p:leading-relaxed prose-a:text-violet-600 dark:prose-a:text-violet-400 prose-img:rounded-2xl prose-img:shadow-lg">
          <div v-html="post.content"></div>
        </article>

        <!-- Featured Image (above Topics) -->
        <div v-if="post.featured_image" class="mt-16 pt-8 border-t border-slate-200 dark:border-slate-800">
          <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4">Featured Image</h3>
          <div class="rounded-2xl overflow-hidden shadow-lg ring-1 ring-slate-200 dark:ring-slate-800">
            <img 
              :src="post.featured_image"
              class="w-full object-cover"
            >
          </div>
        </div>

        <!-- Tags -->
        <div v-if="post.tags && post.tags.length" class="mt-12 pt-8 border-t border-slate-200 dark:border-slate-800">
          <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4">Topics</h3>
          <div class="flex flex-wrap gap-2">
            <span 
              v-for="tag in post.tags" 
              :key="tag.id" 
              class="inline-flex items-center px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-sm font-medium rounded-full hover:bg-violet-50 dark:hover:bg-violet-500/10 hover:text-violet-600 dark:hover:text-violet-400 cursor-pointer transition-colors"
            >
              #{{ tag.name }}
            </span>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center justify-center gap-4 mt-12 py-8 border-t border-b border-slate-200 dark:border-slate-800">
          <button 
            @click="handleLike"
            class="inline-flex items-center gap-2 px-6 py-3 text-sm font-semibold rounded-full transition-colors"
            :class="post.is_liked 
              ? 'bg-rose-500 text-white hover:bg-rose-600 shadow-[0_0_20px_rgba(244,63,94,0.4)]' 
              : 'bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-500/20'"
          >
            <svg class="w-5 h-5" :class="{ 'fill-current': post.is_liked }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            {{ post.is_liked ? 'Liked' : 'Like this article' }}
          </button>
          <button 
            @click="handleShare"
            class="inline-flex items-center gap-2 px-6 py-3 bg-violet-50 dark:bg-violet-500/10 text-violet-600 dark:text-violet-400 text-sm font-semibold rounded-full hover:bg-violet-100 dark:hover:bg-violet-500/20 transition-colors"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
            Share article
          </button>
        </div>

        <!-- Comments Section -->
        <div class="mt-16 mb-20">
          <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-8 flex items-center gap-3">
            <svg class="w-6 h-6 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            Discussion ({{ post.comments_count || 0 }})
          </h2>
          
          <div v-if="post.allow_comments" class="bg-slate-50 dark:bg-[#111113] rounded-2xl border border-slate-200/80 dark:border-slate-800/80 p-6">
            <div v-if="isAuthenticated" class="flex gap-4">
              <img :src="user.avatar || `https://ui-avatars.com/api/?name=${user.name}&background=6366f1&color=fff`" class="w-10 h-10 rounded-full shadow-sm shrink-0">
              <div class="flex-1">
                <textarea 
                  v-model="newComment"
                  placeholder="Share your thoughts..." 
                  class="w-full p-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500/50 focus:border-violet-500 transition-all min-h-[100px] resize-none"
                ></textarea>
                <div class="mt-3 flex justify-end">
                  <button 
                    @click="submitComment"
                    :disabled="submittingComment || !newComment.trim()"
                    class="px-6 py-2.5 bg-gradient-to-r from-violet-600 to-fuchsia-600 text-white text-sm font-semibold rounded-full hover:shadow-lg hover:shadow-violet-500/30 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                  >
                    <span v-if="submittingComment" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    Post Comment
                  </button>
                </div>
              </div>
            </div>
            
            <div v-else class="text-center py-6 bg-slate-100 dark:bg-slate-900/50 rounded-xl">
              <p class="text-slate-500 dark:text-slate-400 mb-3">Please login to join the discussion.</p>
              <router-link to="/login" class="inline-flex items-center px-6 py-2 bg-violet-600 text-white rounded-full text-sm font-semibold hover:bg-violet-700 transition-colors">
                Login
              </router-link>
            </div>

            <!-- Comments List -->
            <div class="mt-10 space-y-8">
                <div v-if="!post.comments?.length" class="text-center text-slate-400 text-sm py-8">
                   Be the first to share your thoughts on this article.
                </div>
                
                <!-- Comments with recursive rendering -->
                <template v-for="comment in post.comments" :key="comment.id">
                    <CommentItem :comment="comment" :depth="0" />
                </template>
            </div>
          </div>
          <div v-else class="bg-slate-50 dark:bg-[#111113] rounded-2xl border border-slate-200/80 dark:border-slate-800/80 p-8 text-center">
            <svg class="w-12 h-12 mx-auto text-slate-300 dark:text-slate-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
            <p class="text-slate-500 dark:text-slate-400 font-medium">Comments are disabled for this article.</p>
          </div>
        </div>

      </main>
    </template>

    <!-- 404 State -->
    <div v-else class="min-h-screen flex flex-col items-center justify-center px-6 text-center">
      <div class="w-20 h-20 mb-8 rounded-3xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
        <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
      <h2 class="text-3xl font-bold text-slate-900 dark:text-white mb-4">Article not found</h2>
      <p class="text-slate-500 dark:text-slate-400 mb-8 max-w-md">The article you're looking for might have been removed or is temporarily unavailable.</p>
      <router-link to="/blog" class="inline-flex items-center gap-2 px-8 py-4 bg-gradient-to-r from-violet-600 to-fuchsia-600 text-white font-semibold rounded-full hover:shadow-xl hover:shadow-violet-500/30 transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back to Blog
      </router-link>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, computed, provide } from 'vue';
import { useRoute } from 'vue-router';
import { publicBlogService } from '../../../services/publicBlogService';
import { useAuth } from '../../../composables/useAuth';
import { useToast } from '../../../composables/useToast';
import CommentItem from '../../../components/ui/CommentItem.vue';

const route = useRoute();
const auth = useAuth();
const toast = useToast();

const post = ref(null);
const loading = ref(true);
const scrollProgress = ref(0);
const newComment = ref('');
const submittingComment = ref(false);
const replyingTo = ref(null);
const replyContent = ref('');

const user = computed(() => auth.user.value);
const isAuthenticated = computed(() => auth.isAuthenticated.value);

const handleCommentReply = (replyData) => {
  if (replyData === null) {
    // Close reply form
    replyingTo.value = null;
    replyContent.value = '';
  } else if (typeof replyData === 'number' || typeof replyData === 'string') {
    // Open reply form for specific comment
    replyingTo.value = replyData;
  } else {
    // Submit reply
    submitReplyToComment(replyData.parentId, replyData.content);
  }
};

// Provide methods to child components
provide('handleCommentReply', handleCommentReply);
provide('replyingTo', replyingTo);

const fetchPost = async () => {
  loading.value = true;
  try {
    const data = await publicBlogService.getPost(route.params.slug);
    post.value = data.data;
  } catch (error) {
    console.error('Failed to fetch post', error);
  } finally {
    loading.value = false;
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

// Extract YouTube thumbnail from URL
const getYouTubeThumbnail = (url) => {
  if (!url) return 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2000&auto=format&fit=crop';
  
  // Match YouTube URL patterns
  const patterns = [
    /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/,
    /youtube\.com\/v\/([a-zA-Z0-9_-]{11})/
  ];
  
  for (const pattern of patterns) {
    const match = url.match(pattern);
    if (match && match[1]) {
      // Return high quality YouTube thumbnail
      return `https://img.youtube.com/vi/${match[1]}/maxresdefault.jpg`;
    }
  }
  
  // Fallback image if not a YouTube URL
  return 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2000&auto=format&fit=crop';
};

const updateScrollProgress = () => {
  const scrollTop = window.scrollY;
  const docHeight = document.documentElement.scrollHeight;
  const winHeight = window.innerHeight;
  const scrollPercent = (scrollTop / (docHeight - winHeight)) * 100;
  scrollProgress.value = Math.min(100, Math.max(0, scrollPercent));
};

const handleLike = async () => {
  if (!isAuthenticated.value) {
    toast.error('Please login to like this post');
    return;
  }
  
  try {
    const response = await publicBlogService.likePost(post.value.slug);
    post.value.is_liked = response.liked;
    post.value.likes_count = response.likes_count;
    if (response.liked) {
      toast.success('You liked this post!');
    }
  } catch (error) {
    toast.error('Failed to update like');
  }
};

const handleShare = async () => {
  try {
    await publicBlogService.sharePost(post.value.slug);
    post.value.shares_count++;
    
    // Copy link to clipboard
    await navigator.clipboard.writeText(window.location.href);
    toast.success('Link copied to clipboard!');
  } catch (error) {
    toast.error('Failed to share post');
  }
};

const submitComment = async () => {
  if (!isAuthenticated.value) {
    toast.error('Please login to comment');
    return;
  }
  if (!newComment.value.trim()) return;

  submittingComment.value = true;
  try {
    const response = await publicBlogService.commentOnPost(post.value.slug, {
      content: newComment.value
    });
    
    // Add new comment to top of list
    const newCommentObj = {
      ...response.data,
      author: {
        name: user.value.name,
        avatar: user.value.avatar
      },
      created_at: new Date().toISOString()
    };
    
    if (!post.value.comments) post.value.comments = [];
    post.value.comments.unshift(newCommentObj);
    post.value.comments_count++;
    
    newComment.value = '';
    toast.success('Comment posted successfully');
  } catch (error) {
    toast.error('Failed to post comment');
  } finally {
    submittingComment.value = false;
  }
};

const submitReply = async (commentId) => {
  if (!isAuthenticated.value) {
    toast.error('Please login to reply');
    return;
  }
  if (!replyContent.value.trim()) return;

  try {
    const response = await publicBlogService.commentOnPost(post.value.slug, {
      content: replyContent.value,
      parent_id: commentId
    });

    // Function to find and add reply to nested comment structure
    const addReplyToComment = (comments, targetId) => {
      for (let comment of comments) {
        if (comment.id === targetId) {
          if (!comment.replies) comment.replies = [];
          const replyObj = {
            ...response.data,
            author: {
              name: user.value.name,
              avatar: user.value.avatar
            },
            created_at: new Date().toISOString()
          };
          comment.replies.push(replyObj);
          post.value.comments_count++;
          return true;
        }
        if (comment.replies && comment.replies.length > 0) {
          if (addReplyToComment(comment.replies, targetId)) {
            return true;
          }
        }
      }
      return false;
    };

    // Add reply to the correct comment (main comment or nested reply)
    addReplyToComment(post.value.comments, commentId);

    // Clear the reply field and close reply form
    replyingTo.value = null;
    replyContent.value = '';
    toast.success('Reply posted successfully');
  } catch (error) {
    toast.error('Failed to post reply');
  }
};

const submitReplyToComment = async (commentId, content) => {
  if (!isAuthenticated.value) {
    toast.error('Please login to reply');
    return;
  }

  try {
    const response = await publicBlogService.commentOnPost(post.value.slug, {
      content: content,
      parent_id: commentId
    });

    // Function to find and add reply to nested comment structure
    const addReplyToComment = (comments, targetId) => {
      for (let comment of comments) {
        if (comment.id === targetId) {
          if (!comment.replies) comment.replies = [];
          const replyObj = {
            ...response.data,
            author: {
              name: user.value.name,
              avatar: user.value.avatar
            },
            created_at: new Date().toISOString()
          };
          comment.replies.push(replyObj);
          post.value.comments_count++;
          return true;
        }
        if (comment.replies && comment.replies.length > 0) {
          if (addReplyToComment(comment.replies, targetId)) {
            return true;
          }
        }
      }
      return false;
    };

    // Add reply to the correct comment
    addReplyToComment(post.value.comments, commentId);

    // Close reply form
    replyingTo.value = null;
    toast.success('Reply posted successfully');
  } catch (error) {
    toast.error('Failed to post reply');
  }
};

onMounted(() => {
  fetchPost();
  window.addEventListener('scroll', updateScrollProgress);
  window.scrollTo(0, 0);
});

onUnmounted(() => {
  window.removeEventListener('scroll', updateScrollProgress);
});

watch(() => route.params.slug, (newSlug) => {
  if (newSlug) {
    fetchPost();
    window.scrollTo(0, 0);
  }
});
</script>
