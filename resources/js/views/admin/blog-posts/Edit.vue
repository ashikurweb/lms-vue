<template>
  <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700 pb-20">
    <!-- Header -->
    <div v-if="loading" class="flex items-center justify-center py-32">
       <SkeletonLoader :cols="3" />
    </div>

    <template v-else>
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
          <router-link :to="{ name: 'blog-posts' }" class="p-3 theme-bg-card border theme-border rounded-xl theme-text-dim hover:theme-text-main transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
          </router-link>
          <div>
            <h1 class="text-3xl font-black theme-text-main tracking-tight">Edit Post</h1>
            <p class="text-sm theme-text-muted mt-1 font-medium">Updating: {{ originalTitle }}</p>
          </div>
        </div>
        <div class="flex items-center gap-3">
          <button 
            @click="savePost(form.status)" 
            :disabled="saving"
            class="bg-indigo-600 hover:bg-indigo-500 text-white px-8 py-3 rounded-2xl font-bold shadow-lg shadow-indigo-600/20 active:scale-95 transition-all flex items-center gap-2"
          >
            <svg v-if="saving" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            Update Post
          </button>
        </div>
      </div>

      <div class="grid grid-cols-12 gap-8">
        <!-- Main Content Area -->
        <div class="col-span-12 lg:col-span-8 space-y-6">
          <div class="theme-bg-card border theme-border rounded-[2rem] p-8 shadow-sm space-y-6">
            <div class="space-y-4">
              <label class="text-[10px] font-black theme-text-dim uppercase tracking-[0.2em] ml-2">Article Title <span class="text-rose-500">*</span></label>
              <input 
                v-model="form.title"
                type="text" 
                required
                class="w-full px-6 py-5 rounded-2xl theme-bg-element border-2 theme-border outline-none focus:border-indigo-500/50 transition-all text-xl font-black theme-text-main"
              >
              <p v-if="errors.title" class="text-xs text-rose-500 font-bold ml-2">{{ errors.title[0] }}</p>
            </div>

            <div class="space-y-4">
              <label class="text-[10px] font-black theme-text-dim uppercase tracking-[0.2em] ml-2">Content Editor <span class="text-rose-500">*</span></label>
              <Editor v-model="form.content" />
              <p v-if="errors.content" class="text-xs text-rose-500 font-bold ml-2">{{ errors.content[0] }}</p>
            </div>
            
            <div class="space-y-4">
              <label class="text-[10px] font-black theme-text-dim uppercase tracking-[0.2em] ml-2">Short Excerpt</label>
              <textarea 
                v-model="form.excerpt"
                rows="3"
                class="w-full px-6 py-4 rounded-2xl theme-bg-element border-2 theme-border outline-none focus:border-indigo-500/50 transition-all text-sm font-bold theme-text-main"
              ></textarea>
            </div>
          </div>

          <!-- SEO Settings -->
          <div class="theme-bg-card border theme-border rounded-[2rem] p-8 shadow-sm space-y-6">
            <h3 class="text-lg font-black theme-text-main tracking-tight">SEO Settings</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <FormInput v-model="form.meta_title" label="Meta Title" />
              <FormInput v-model="form.meta_keywords" label="Meta Keywords" />
              <div class="md:col-span-2">
                 <FormInput v-model="form.canonical_url" label="Canonical URL" />
              </div>
              <div class="md:col-span-2">
                 <label class="text-[10px] font-black theme-text-dim uppercase tracking-[0.2em] ml-2">Meta Description</label>
                 <textarea v-model="form.meta_description" rows="3" class="w-full mt-2 px-6 py-4 rounded-2xl theme-bg-element border-2 theme-border outline-none focus:border-indigo-500/50 transition-all text-sm font-bold theme-text-main"></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar Area -->
        <div class="col-span-12 lg:col-span-4 space-y-6">
          <div class="theme-bg-card border theme-border rounded-[2rem] p-8 shadow-sm space-y-6">
            <h3 class="text-lg font-black theme-text-main tracking-tight border-b theme-border pb-4">Settings</h3>
            
            <FormDropdown 
              v-model="form.status"
              :options="statusOptions"
              label="Status"
              required
              :error="errors.status"
            />

            <FormDropdown 
              v-model="form.category_id"
              :options="categoryOptions"
              label="Category"
              placeholder="Select a category"
              required
              :error="errors.category_id"
            />

            <div class="space-y-4">
              <label class="text-[10px] font-black theme-text-dim uppercase tracking-[0.2em] ml-2">Tags <span class="text-rose-500">*</span></label>
              <div class="flex flex-wrap gap-2">
                 <label v-for="tag in tags" :key="tag.id" class="relative cursor-pointer">
                  <input type="checkbox" v-model="form.tags" :value="tag.id" class="sr-only peer">
                  <div class="px-4 py-2 rounded-xl border-2 theme-border theme-text-dim text-xs font-black peer-checked:bg-indigo-500 peer-checked:border-indigo-500 peer-checked:text-white! transition-all">
                    #{{ tag.name }}
                  </div>
                </label>
              </div>
            </div>

            <div class="space-y-4 theme-bg-element p-4 rounded-2xl border theme-border">
              <div class="flex items-center justify-between" v-for="toggle in toggles" :key="toggle.key">
                <span class="text-xs font-black theme-text-main">{{ toggle.label }}</span>
                <button @click="form[toggle.key] = !form[toggle.key]" class="w-12 h-6 rounded-full relative transition-colors" :class="form[toggle.key] ? 'bg-indigo-600' : 'bg-slate-700'">
                  <div class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full transition-transform" :class="form[toggle.key] ? 'translate-x-6' : ''"></div>
                </button>
              </div>
              <div class="flex items-center justify-between border-t theme-border pt-4 mt-2">
                <span class="text-xs font-black theme-text-dim uppercase tracking-wider">Reading Time</span>
                <span class="text-xs font-black theme-text-main tabular-nums">{{ form.reading_time }} min read</span>
              </div>
            </div>
            
            <div class="pt-4">
               <FormInput v-model="form.published_at" type="datetime-local" label="Publish Date" required />
            </div>
          </div>

          <div class="theme-bg-card border theme-border rounded-[2rem] p-8 shadow-sm space-y-4">
            <h3 class="text-lg font-black theme-text-main tracking-tight border-b theme-border pb-4">Media & Links</h3>
            <div>
              <label class="text-[10px] font-black theme-text-dim uppercase tracking-[0.2em] ml-2 mb-2 block">Cover Image <span class="text-rose-500">*</span></label>
              <ImageUploader v-model="form.featured_image" folder="blogs" />
            </div>
            <FormInput v-model="form.video_url" label="Video URL" />
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { blogPostService } from '../../../services/blogPostService';
import { blogCategoryService } from '../../../services/blogCategoryService';
import { blogTagService } from '../../../services/blogTagService';
import Editor from '../../../components/common/Editor.vue';
import FormInput from '../../../components/common/FormInput.vue';
import FormDropdown from '../../../components/common/FormDropdown.vue';
import ImageUploader from '../../../components/common/ImageUploader.vue';
import SkeletonLoader from '../../../components/common/SkeletonLoader.vue';
import { useToast } from '../../../composables/useToast';

const router = useRouter();
const route = useRoute();
const toast = useToast();
const categories = ref([]);
const tags = ref([]);
const loading = ref(true);
const saving = ref(false);
const originalTitle = ref('');

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
  status: 'draft',
  featured_image: '',
  reading_time: 5,
  is_featured: false,
  is_pinned: false,
  allow_comments: true,
  meta_title: '',
  meta_description: '',
  meta_keywords: '',
  author_id: 1,
});

const toggles = [
  { label: 'Featured Post', key: 'is_featured' },
  { label: 'Pinned Post', key: 'is_pinned' },
  { label: 'Allow Comments', key: 'allow_comments' },
];

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
    loading.value = true;
    try {
        const [post, cats, tagList] = await Promise.all([
            blogPostService.show(route.params.slug),
            blogCategoryService.getAll(),
            blogTagService.getAll()
        ]);
        
        Object.assign(form, {
          title: post.data.title,
          excerpt: post.data.excerpt || '',
          content: post.data.content || '',
          category_id: post.data.category?.id || '',
          tags: post.data.tags?.map(t => t.id) || [],
          status: post.data.status,
          featured_image: post.data.featured_image || '',
          video_url: post.data.video_url || '',
          reading_time: post.data.reading_time,
          is_featured: post.data.is_featured,
          is_pinned: post.data.is_pinned,
          allow_comments: post.data.allow_comments,
          meta_title: post.data.meta_title || '',
          meta_description: post.data.meta_description || '',
          meta_keywords: post.data.meta_keywords || '',
          canonical_url: post.data.canonical_url || '',
          published_at: post.data.published_at ? post.data.published_at.substring(0, 16) : '',
          author_id: post.data.author?.id || 1,
        });

        originalTitle.value = post.data.title;
        categories.value = cats;
        tags.value = tagList;
    } catch (error) {
        toast.error('Failed to load post data');
        router.push({ name: 'blog-posts' });
    } finally {
        loading.value = false;
    }
};

const savePost = async (status) => {
    saving.value = true;
    errors.value = {};
    form.status = status;

    try {
        await blogPostService.update(route.params.slug, form);
        toast.success('Post updated successfully!');
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

onMounted(fetchData);
</script>
