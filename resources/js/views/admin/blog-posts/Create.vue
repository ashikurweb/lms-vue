<template>
  <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700 pb-20">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-4">
        <router-link :to="{ name: 'blog-posts' }"
          class="p-3 theme-bg-card border theme-border rounded-xl theme-text-dim hover:theme-text-main transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
        </router-link>
        <div>
          <h1 class="text-3xl font-black theme-text-main tracking-tight">Create Post</h1>
          <p class="text-sm theme-text-muted mt-1 font-medium">Compose a new article for your blog.</p>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <SecondaryButton label="Save Draft" :loading="saving && form.status === 'draft'" @click="savePost('draft')" />
        <PrimaryButton label="Publish Now" :loading="saving && form.status === 'published'"
          @click="savePost('published')" />
      </div>
    </div>

    <div class="grid grid-cols-12 gap-8">
      <!-- Main Content Area -->
      <div class="col-span-12 lg:col-span-8 space-y-6">
        <!-- Title & Content -->
        <div class="theme-bg-card border theme-border rounded-[2rem] p-8 shadow-sm space-y-6">
          <div class="space-y-4">
            <label class="text-[10px] font-black theme-text-dim uppercase tracking-[0.2em] ml-2">Article Title <span
                class="text-rose-500">*</span></label>
            <input v-model="form.title" type="text" placeholder="Enter a catchy title..." required
              class="w-full px-6 py-5 rounded-2xl theme-bg-element border-2 theme-border outline-none focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10 transition-all text-xl font-black theme-text-main">
            <p v-if="errors.title" class="text-xs text-rose-500 font-bold ml-2">{{ errors.title[0] }}</p>
          </div>

          <div class="space-y-4">
            <label class="text-[10px] font-black theme-text-dim uppercase tracking-[0.2em] ml-2">Content Editor <span
                class="text-rose-500">*</span></label>
            <Editor v-model="form.content" placeholder="Start writing your masterpiece..." />
            <p v-if="errors.content" class="text-xs text-rose-500 font-bold ml-2">{{ errors.content[0] }}</p>
          </div>

          <div class="space-y-4">
            <label class="text-[10px] font-black theme-text-dim uppercase tracking-[0.2em] ml-2">Short Excerpt</label>
            <textarea v-model="form.excerpt" rows="3"
              class="w-full px-6 py-4 rounded-2xl theme-bg-element border-2 theme-border outline-none focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10 transition-all text-sm font-bold theme-text-main"
              placeholder="Wait, what's this article about? (Keep it short)"></textarea>
            <p v-if="errors.excerpt" class="text-xs text-rose-500 font-bold ml-2">{{ errors.excerpt[0] }}</p>
          </div>
        </div>

        <!-- SEO Settings -->
        <div class="theme-bg-card border theme-border rounded-[2rem] p-8 shadow-sm space-y-6">
          <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 flex items-center justify-center text-indigo-500">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <h3 class="text-lg font-black theme-text-main tracking-tight">Search Engine Optimization (SEO)</h3>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <FormInput v-model="form.meta_title" label="Meta Title" placeholder="Title for Google search"
              :error="errors.meta_title" />
            <FormInput v-model="form.meta_keywords" label="Meta Keywords" placeholder="Keyword1, Keyword2"
              :error="errors.meta_keywords" />
            <div class="md:col-span-2">
              <FormInput v-model="form.canonical_url" label="Canonical URL"
                placeholder="https://example.com/original-post" :error="errors.canonical_url" />
            </div>
            <div class="md:col-span-2">
              <label class="text-[10px] font-black theme-text-dim uppercase tracking-[0.2em] ml-2">Meta
                Description</label>
              <textarea v-model="form.meta_description" rows="3"
                class="w-full mt-2 px-6 py-4 rounded-2xl theme-bg-element border-2 theme-border outline-none focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10 transition-all text-sm font-bold theme-text-main"
                placeholder="Summary for search engines..."></textarea>
            </div>
          </div>
        </div>
      </div>

      <!-- Sidebar Area -->
      <div class="col-span-12 lg:col-span-4 space-y-6">
        <!-- Publishing Info -->
        <div class="theme-bg-card border theme-border rounded-[2rem] p-8 shadow-sm space-y-6">
          <h3 class="text-lg font-black theme-text-main tracking-tight border-b theme-border pb-4">Publish & Organize
          </h3>
          <FormDropdown v-model="form.status" :options="statusOptions" label="Post Status" required
            :error="errors.status" />

          <FormDropdown v-model="form.category_id" :options="categoryOptions" label="Category"
            placeholder="Select a category" required :error="errors.category_id" />

          <div class="space-y-4">
            <label class="text-[10px] font-black theme-text-dim uppercase tracking-[0.2em] ml-2">Tags <span
                class="text-rose-500">*</span></label>
            <div class="flex flex-wrap gap-2">
              <label v-for="tag in tags" :key="tag.id" class="relative cursor-pointer group">
                <input type="checkbox" v-model="form.tags" :value="tag.id" class="sr-only peer">
                <div
                  class="px-4 py-2 rounded-xl border-2 theme-border theme-text-dim text-xs font-black peer-checked:bg-indigo-500 peer-checked:border-indigo-500 peer-checked:text-white! hover:theme-border-main transition-all group-active:scale-95">
                  #{{ tag.name }}
                </div>
              </label>
            </div>
          </div>

          <div class="space-y-4 theme-bg-element p-4 rounded-2xl border theme-border">
            <div class="flex items-center justify-between">
              <span class="text-xs font-black theme-text-main">Featured Post</span>
              <button @click="form.is_featured = !form.is_featured"
                class="w-12 h-6 rounded-full relative transition-colors duration-300"
                :class="form.is_featured ? 'bg-indigo-600' : 'bg-slate-700'">
                <div class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full transition-transform duration-300"
                  :class="form.is_featured ? 'translate-x-6' : ''"></div>
              </button>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-xs font-black theme-text-main">Pinned on Top</span>
              <button @click="form.is_pinned = !form.is_pinned"
                class="w-12 h-6 rounded-full relative transition-colors duration-300"
                :class="form.is_pinned ? 'bg-indigo-600' : 'bg-slate-700'">
                <div class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full transition-transform duration-300"
                  :class="form.is_pinned ? 'translate-x-6' : ''"></div>
              </button>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-xs font-black theme-text-main">Allow Comments</span>
              <button @click="form.allow_comments = !form.allow_comments"
                class="w-12 h-6 rounded-full relative transition-colors duration-300"
                :class="form.allow_comments ? 'bg-indigo-600' : 'bg-slate-700'">
                <div class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full transition-transform duration-300"
                  :class="form.allow_comments ? 'translate-x-6' : ''"></div>
              </button>
            </div>
            <div class="flex items-center justify-between border-t theme-border pt-4 mt-2">
              <span class="text-xs font-black theme-text-dim uppercase tracking-wider">Reading Time</span>
              <span class="text-xs font-black theme-text-main tabular-nums">{{ form.reading_time }} min read</span>
            </div>
          </div>

          <div class="pt-4">
            <FormInput v-model="form.published_at" type="datetime-local" label="Publish Date" required
              :error="errors.published_at" />
          </div>
        </div>

        <!-- Featured & Thumbnail Image -->
        <div class="theme-bg-card border theme-border rounded-[2rem] p-8 shadow-sm space-y-6">
          <h3 class="text-lg font-black theme-text-main tracking-tight border-b theme-border pb-4">Media Assets</h3>

          <div class="space-y-6">
            <div>
              <label class="text-[10px] font-black theme-text-dim uppercase tracking-[0.2em] ml-2 mb-2 block">Cover
                Image <span class="text-rose-500">*</span></label>
              <ImageUploader v-model="form.featured_image" folder="blogs" />
            </div>

            <FormInput v-model="form.video_url" label="Video URL (YouTube/Vimeo)" placeholder="https://..."
              :error="errors.video_url" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch, computed } from 'vue';
import { useRouter } from 'vue-router';
import { blogPostService } from '../../../services/blogPostService';
import { blogCategoryService } from '../../../services/blogCategoryService';
import { blogTagService } from '../../../services/blogTagService';
import Editor from '../../../components/common/Editor.vue';
import FormInput from '../../../components/common/FormInput.vue';
import FormDropdown from '../../../components/common/FormDropdown.vue';
import ImageUploader from '../../../components/common/ImageUploader.vue';
import PrimaryButton from '../../../components/common/PrimaryButton.vue';
import SecondaryButton from '../../../components/common/SecondaryButton.vue';
import { useToast } from '../../../composables/useToast';

const router = useRouter();
const toast = useToast();
const categories = ref([]);
const tags = ref([]);
const saving = ref(false);

const statusOptions = [
  { label: 'Published', value: 'published' },
  { label: 'Draft', value: 'draft' },
  { label: 'Pending Review', value: 'pending' },
  { label: 'Archived', value: 'archived' }
];

const categoryOptions = computed(() => {
  return categories.value.map(cat => ({
    label: cat.name,
    value: cat.id
  }));
});

const form = reactive({
  title: '',
  excerpt: '',
  content: '',
  category_id: '',
  tags: [],
  status: 'published',
  featured_image: '',
  video_url: '',
  reading_time: 5,
  is_featured: false,
  is_pinned: false,
  allow_comments: true,
  meta_title: '',
  meta_description: '',
  meta_keywords: '',
  canonical_url: '',
  published_at: '',
  author_id: 1,
});

const errors = ref({});

// Auto-calculate reading time
watch(() => form.content, (newContent) => {
  if (!newContent) {
    form.reading_time = 0;
    return;
  }
  const words = newContent.replace(/<[^>]*>/g, ' ').trim().split(/\s+/).length;
  form.reading_time = Math.max(1, Math.ceil(words / 200));
});

const fetchData = async () => {
  try {
    const [cats, tagList] = await Promise.all([
      blogCategoryService.getAll(),
      blogTagService.getAll()
    ]);
    categories.value = cats;
    tags.value = tagList;
  } catch (error) {
    toast.error('Failed to load form data');
  }
};

const savePost = async (status) => {
  saving.value = true;
  errors.value = {};
  form.status = status;

  try {
    // In a real app, author_id should come from auth state
    const user = JSON.parse(localStorage.getItem('auth_user'));
    form.author_id = user?.id || 1;

    await blogPostService.store(form);
    toast.success(`Post ${status === 'published' ? 'published' : 'saved'} successfully!`);
    router.push({ name: 'blog-posts' });
  } catch (error) {
    if (error.response && error.response.status === 422) {
      errors.value = error.response.data.errors;
      toast.error('Please fix the errors in the form');
    } else {
      toast.error('Something went wrong');
    }
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  fetchData();
});
</script>
