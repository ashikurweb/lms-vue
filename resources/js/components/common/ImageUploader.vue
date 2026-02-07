<template>
  <div class="space-y-4">
    <div 
      class="relative rounded-4xl theme-bg-element border-2 border-dashed theme-border flex flex-col items-center justify-center group overflow-hidden transition-all duration-500 hover:border-indigo-500/50"
      :class="[
        aspect === 'square' ? 'aspect-square min-w-[280px]' : 'aspect-video w-full'
      ]"
      @dragover.prevent="isDragging = true"
      @dragleave.prevent="isDragging = false"
      @drop.prevent="handleDrop"
      @paste="handlePaste"
      tabindex="0"
    >
      <!-- Background Glow Effect -->
      <div class="absolute inset-0 bg-indigo-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>

      <!-- Preview Image -->
      <img 
        v-if="modelValue" 
        :src="modelValue" 
        class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" 
      />

      <!-- Empty State -->
      <div v-if="!modelValue" class="text-center p-8 z-10">
        <div class="w-16 h-16 rounded-2xl bg-indigo-500/10 flex items-center justify-center text-indigo-500 mx-auto mb-4 group-hover:scale-110 transition-transform duration-500">
          <svg v-if="!uploading" class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          <svg v-else class="w-8 h-8 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>
        <p class="text-[10px] font-black theme-text-main uppercase tracking-[0.2em]">
          {{ uploading ? 'Uploading Store...' : 'Click or Drop Image' }}
        </p>
        <p class="text-[9px] theme-text-dim mt-1 font-bold">Supports: PNG, JPG, WEBP • Paste URL or Image</p>
      </div>

      <!-- Upload Overlay (Hover) -->
      <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md opacity-0 group-hover:opacity-100 transition-all duration-500 flex flex-col items-center justify-center gap-4 z-20">
        <div class="flex gap-2">
          <button 
            @click="triggerFileInput"
            class="bg-indigo-600 hover:bg-indigo-500 text-white px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-indigo-600/20 active:scale-95 transition-all"
          >
            Upload File
          </button>
          <button 
            v-if="modelValue"
            @click="removeImage"
            class="bg-rose-500 hover:bg-rose-400 text-white px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-rose-600/20 active:scale-95 transition-all"
          >
            Remove
          </button>
        </div>
        <p class="text-[9px] text-white/60 font-black uppercase tracking-widest">Or Paste Link / Image</p>
      </div>

      <!-- Dragging Overlay -->
      <div v-if="isDragging" class="absolute inset-0 bg-indigo-600/20 backdrop-blur-sm border-4 border-indigo-500 border-dashed rounded-4xl z-30 flex items-center justify-center">
        <div class="bg-white text-indigo-600 px-6 py-3 rounded-2xl font-black uppercase tracking-widest animate-bounce">
          Drop it here!
        </div>
      </div>

      <input 
        ref="fileInput"
        type="file" 
        class="hidden" 
        accept="image/*"
        @change="handleFileSelect"
      >
    </div>

    <!-- URL Input (Optional persistent view) -->
    <div v-if="showUrlInput" class="relative group mt-4">
      <input 
        v-model="pastedUrl"
        type="text" 
        placeholder="Paste image URL here and press Enter..." 
        class="w-full pl-6 pr-12 py-4 rounded-2xl theme-bg-element border-2 theme-border outline-none focus:border-indigo-500/50 transition-all text-sm font-bold theme-text-main"
        @keyup.enter="handleUrlSubmit"
      >
      <button 
        @click="handleUrlSubmit"
        class="absolute right-4 top-1/2 -translate-y-1/2 theme-text-dim hover:text-indigo-500 transition-colors"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import api from '../../services/api';
import { useToast } from '../../composables/useToast';

const props = defineProps({
  modelValue: String,
  folder: { type: String, default: 'uploads/others' },
  aspect: { type: String, default: 'video' }
});

const emit = defineEmits(['update:modelValue']);
const toast = useToast();

const fileInput = ref(null);
const isDragging = ref(false);
const uploading = ref(false);
const pastedUrl = ref('');
const showUrlInput = ref(false);

const triggerFileInput = () => {
  fileInput.value.click();
};

const handleFileSelect = (event) => {
  const file = event.target.files[0];
  if (file) {
    uploadFile(file);
  }
};

const handleDrop = (event) => {
  isDragging.value = false;
  const file = event.dataTransfer.files[0];
  if (file && file.type.startsWith('image/')) {
    uploadFile(file);
  }
};

const handlePaste = (event) => {
  const items = (event.clipboardData || event.originalEvent.clipboardData).items;
  
  for (const item of items) {
    if (item.type.indexOf('image') !== -1) {
      const file = item.getAsFile();
      uploadFile(file);
      return;
    }
  }

  // Handle URL paste
  const text = (event.clipboardData || event.originalEvent.clipboardData).getData('text');
  if (text && (text.startsWith('http://') || text.startsWith('https://'))) {
    pastedUrl.value = text;
    handleUrlSubmit();
  }
};

const uploadFile = async (file) => {
  if (file.size > 2 * 1024 * 1024) {
    toast.error('Image is too large. Max 2MB allowed.');
    return;
  }

  uploading.value = true;
  const formData = new FormData();
  formData.append('image', file);
  formData.append('folder', props.folder);

  try {
    const response = await api.post('/admin/upload-image', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    emit('update:modelValue', response.data.url);
    toast.success('Image uploaded successfully');
  } catch (error) {
    toast.error(error.response?.data?.message || 'Upload failed');
  } finally {
    uploading.value = false;
  }
};

const handleUrlSubmit = async () => {
  if (!pastedUrl.value) return;
  
  uploading.value = true;
  try {
    const response = await api.post('/admin/upload-image', {
      url: pastedUrl.value,
      folder: props.folder
    });
    emit('update:modelValue', response.data.url);
    pastedUrl.value = '';
    toast.success('Image loaded from URL');
  } catch (error) {
    toast.error('Could not load image from URL');
  } finally {
    uploading.value = false;
  }
};

const removeImage = () => {
  emit('update:modelValue', '');
  toast.info('Image removed');
};
</script>

<style scoped>
.animate-bounce {
  animation: bounce 1s infinite;
}

@keyframes bounce {
  0%, 100% { transform: translateY(-5%); animation-timing-function: cubic-bezier(0.8,0,1,1); }
  50% { transform: translateY(0); animation-timing-function: cubic-bezier(0,0,0.2,1); }
}
</style>
