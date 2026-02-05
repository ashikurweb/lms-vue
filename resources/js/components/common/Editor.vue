<template>
  <div class="editor-container theme-bg-element border-2 theme-border rounded-2xl overflow-hidden focus-within:border-indigo-500/50 focus-within:ring-4 focus-within:ring-indigo-500/10 transition-all">
    <!-- Toolbar -->
    <div v-if="editor" class="editor-toolbar flex flex-wrap items-center gap-1 p-2 border-b theme-border bg-slate-50/50 dark:bg-slate-800/50 backdrop-blur-sm sticky top-0 z-10">
      
      <!-- Headings Dropdown -->
      <div class="relative group heading-dropdown">
        <button 
          type="button"
          class="h-9 px-3 flex items-center gap-2 rounded-lg theme-text-muted hover:theme-bg-sidebar hover:theme-text-main transition-all active:scale-95 border theme-border"
          @click="showHeadings = !showHeadings"
        >
          <span class="text-[10px] font-black uppercase tracking-widest">{{ currentHeadingLabel }}</span>
          <svg class="w-3 h-3 transition-transform duration-300" :class="{ 'rotate-180': showHeadings }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/></svg>
        </button>
        
        <div v-if="showHeadings" class="absolute top-full left-0 mt-1 w-40 theme-bg-card border theme-border rounded-xl shadow-2xl overflow-hidden z-50 animate-in fade-in zoom-in-95 duration-200">
           <button 
            v-for="h in [1, 2, 3, 4, 5, 6]" 
            :key="h"
            @click="setHeading(h)"
            class="w-full px-4 py-2 text-left text-xs font-bold hover:theme-bg-sidebar transition-colors"
            :class="editor.isActive('heading', { level: h }) ? 'text-indigo-500' : 'theme-text-main'"
           >
            Heading {{ h }}
           </button>
           <button 
            @click="editor.chain().focus().setParagraph().run(); showHeadings = false"
            class="w-full px-4 py-2 text-left text-xs font-bold hover:theme-bg-sidebar transition-colors border-t theme-border"
            :class="editor.isActive('paragraph') ? 'text-indigo-500' : 'theme-text-main'"
           >
            Paragraph
           </button>
        </div>
      </div>

      <div class="w-px h-6 bg-slate-700/20 mx-1"></div>

      <!-- Basic Formatting -->
      <button 
        v-for="btn in mainToolbarItems" 
        :key="btn.action"
        @click="executeAction(btn)"
        type="button"
        class="w-9 h-9 flex items-center justify-center rounded-lg transition-all active:scale-90"
        :class="[editor.isActive(btn.action, btn.params) ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'theme-text-muted hover:theme-bg-sidebar hover:theme-text-main']"
        :title="btn.label"
      >
        <div v-html="btn.icon" class="w-4.5 h-4.5"></div>
      </button>

      <div class="w-px h-6 bg-slate-700/20 mx-1"></div>

      <!-- Text Color Palette -->
      <div class="relative group color-dropdown">
        <button 
          @click="showColorPicker = !showColorPicker"
          type="button"
          class="w-9 h-9 flex items-center justify-center rounded-lg transition-all active:scale-95"
          :class="[showColorPicker ? 'bg-indigo-600/10 text-indigo-500' : 'theme-text-dim hover:theme-bg-sidebar hover:theme-text-main']"
          title="Text Color"
        >
          <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-3"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 7a2 2 0 114 0 2 2 0 01-4 0z"/>
          </svg>
        </button>
        
        <div v-if="showColorPicker" class="absolute top-full left-0 mt-2 p-3 w-48 theme-bg-card border theme-border rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.3)] z-50 animate-in fade-in zoom-in-95 duration-200">
          <div class="text-[8px] font-black theme-text-dim uppercase tracking-[0.2em] mb-3 px-1">Curated Palette</div>
          
          <div class="grid grid-cols-5 gap-2">
            <button 
              v-for="color in colors" 
              :key="color"
              @click="setColor(color)"
              class="w-7 h-7 rounded-lg border-2 border-white/5 hover:scale-110 hover:border-white/20 transition-all shadow-sm relative group/swatch"
              :style="{ backgroundColor: color }"
            >
              <div v-if="editor.isActive('textStyle', { color })" class="absolute inset-0 flex items-center justify-center">
                <div class="w-1.5 h-1.5 bg-white rounded-full shadow-sm"></div>
              </div>
            </button>
          </div>

          <button 
            @click="editor.chain().focus().unsetColor().run(); showColorPicker = false" 
            class="w-full mt-4 py-2 flex items-center justify-center gap-2 rounded-xl theme-bg-element hover:bg-red-500/10 hover:text-red-500 transition-all text-[10px] font-black uppercase tracking-widest border theme-border"
          >
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            Reset Color
          </button>
        </div>
      </div>

      <div class="w-px h-6 bg-slate-700/20 mx-1"></div>
      
      <!-- Lists & Align -->
      <button 
        v-for="btn in listToolbarItems" 
        :key="btn.action"
        @click="executeAction(btn)"
        type="button"
        class="w-9 h-9 flex items-center justify-center rounded-lg transition-all active:scale-90"
        :class="[editor.isActive(btn.action, btn.params) ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'theme-text-muted hover:theme-bg-sidebar hover:theme-text-main']"
        :title="btn.label"
      >
        <div v-html="btn.icon" class="w-4.5 h-4.5"></div>
      </button>

      <div class="w-px h-6 bg-slate-700/20 mx-1"></div>

      <!-- Image Upload -->
      <div class="relative">
        <button 
          @click="triggerImageUpload"
          type="button"
          class="w-9 h-9 flex items-center justify-center rounded-lg theme-text-dim hover:theme-bg-sidebar hover:theme-text-main transition-all active:scale-90"
          :class="{ 'animate-pulse': uploading }"
          title="Upload Image"
        >
          <svg v-if="!uploading" class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          <svg v-else class="w-4 h-4 animate-spin" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
        </button>
        <input type="file" ref="imageInput" class="hidden" accept="image/*" @change="handleFileUpload">
      </div>
    </div>

    <!-- Editor Content -->
    <editor-content :editor="editor" class="prose dark:prose-invert max-w-none min-h-[400px] p-8 focus:outline-none" />
  </div>
</template>

<script setup>
import { ref, computed, onBeforeUnmount, watch } from 'vue';
import { useEditor, EditorContent } from '@tiptap/vue-3';
import { StarterKit } from '@tiptap/starter-kit';
import { Image } from '@tiptap/extension-image';
import { Link } from '@tiptap/extension-link';
import { Placeholder } from '@tiptap/extension-placeholder';
import { Underline } from '@tiptap/extension-underline';
import { TextAlign } from '@tiptap/extension-text-align';
import { TextStyle } from '@tiptap/extension-text-style';
import { Color } from '@tiptap/extension-color';
import api from '../../services/api';
import { useToast } from '../../composables/useToast';

const props = defineProps({
  modelValue: { type: String, default: '' },
  placeholder: { type: String, default: 'Write something amazing...' }
});

const emit = defineEmits(['update:modelValue']);
const toast = useToast();

const imageInput = ref(null);
const uploading = ref(false);
const showHeadings = ref(false);
const showColorPicker = ref(false);

const editor = useEditor({
  content: props.modelValue,
  extensions: [
    StarterKit.configure({
        heading: {
            levels: [1, 2, 3, 4, 5, 6],
        },
    }),
    Underline,
    TextStyle,
    Color,
    Link.configure({ openOnClick: false }),
    Image.configure({ allowBase64: true }),
    TextAlign.configure({ types: ['heading', 'paragraph'] }),
    Placeholder.configure({ placeholder: props.placeholder }),
  ],
  onUpdate: ({ editor }) => {
    emit('update:modelValue', editor.getHTML());
  },
});

// Sync modelValue if changed externally
watch(() => props.modelValue, (newValue) => {
    const isSame = editor.value.getHTML() === newValue;
    if (!isSame) {
        editor.value.commands.setContent(newValue, false);
    }
});

onBeforeUnmount(() => {
    editor.value?.destroy();
});

const currentHeadingLabel = computed(() => {
    if (!editor.value) return 'Paragraph';
    for (let i = 1; i <= 6; i++) {
        if (editor.value.isActive('heading', { level: i })) return `Heading ${i}`;
    }
    return 'Paragraph';
});

const setHeading = (level) => {
    editor.value.chain().focus().toggleHeading({ level }).run();
    showHeadings.value = false;
};

const setColor = (color) => {
    editor.value.chain().focus().setColor(color).run();
    showColorPicker.value = false;
};

const triggerImageUpload = () => imageInput.value?.click();

const handleFileUpload = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    uploading.value = true;
    const formData = new FormData();
    formData.append('image', file);
    formData.append('folder', 'editor/blogs');

    try {
        const response = await api.post('/admin/upload-image', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        
        if (response.data.url) {
            editor.value.chain().focus().setImage({ src: response.data.url }).run();
            toast.success('Image uploaded successfully');
        }
    } catch (error) {
        toast.error('Failed to upload image');
        console.error(error);
    } finally {
        uploading.value = false;
        event.target.value = ''; // Reset input
    }
};

const executeAction = (btn) => {
  const chain = editor.value.chain().focus();
  if (btn.params) {
    chain[btn.action](btn.params).run();
  } else {
    chain[btn.action]().run();
  }
};

const colors = [
    '#000000', '#334155', '#475569', '#64748b', '#94a3b8', 
    '#ef4444', '#f97316', '#f59e0b', '#10b981', '#06b6d4', 
    '#3b82f6', '#6366f1', '#8b5cf6', '#d946ef', '#ec4899'
];

const mainToolbarItems = [
  { action: 'toggleBold', label: 'Bold', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 4h8a4 4 0 014 4 4 4 0 01-4 4H6V4zm0 8h9a4 4 0 014 4 4 4 0 01-4 4H6v-8z"/></svg>' },
  { action: 'toggleItalic', label: 'Italic', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 4h4M8 20h4m-2-16l-4 16"/></svg>' },
  { action: 'toggleUnderline', label: 'Underline', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 4v12a4 4 0 01-8 0V4M4 20h16"/></svg>' },
  { action: 'toggleStrike', label: 'Strike', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-1 9l-4-4m0 0l-4 4m4-4V3M12 12l4 4m0 0l4-4m-4 4V3"/></svg>' },
];

const listToolbarItems = [
  { action: 'toggleBulletList', label: 'Bullet List', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>' },
  { action: 'toggleOrderedList', label: 'Ordered List', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 6h13M7 12h13M7 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>' },
  { action: 'toggleBlockquote', label: 'Blockquote', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 11H8a2 2 0 01-2-2V7a2 2 0 012-2h4v11a3 3 0 01-3 3H8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 11h4a2 2 0 002-2V7a2 2 0 00-2-2h-4v11a3 3 0 013 3h4"/></svg>' },
  { action: 'setTextAlign', params: 'left', label: 'Align Left', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h16"/></svg>' },
  { action: 'setTextAlign', params: 'center', label: 'Align Center', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M7 12h10M4 18h16"/></svg>' },
];
</script>

<style>
/* Tiptap Custom Styles */
.editor-container .ProseMirror {
    outline: none !important;
}

.editor-container .ProseMirror p.is-editor-empty:first-child::before {
    content: attr(data-placeholder);
    float: left;
    color: #64748b;
    pointer-events: none;
    height: 0;
}

/* Fix Headings Styles in Editor */
.editor-container .ProseMirror h1 { font-size: 2.25rem; font-weight: 900; margin-bottom: 1.5rem; }
.editor-container .ProseMirror h2 { font-size: 1.875rem; font-weight: 900; margin-bottom: 1.25rem; }
.editor-container .ProseMirror h3 { font-size: 1.5rem; font-weight: 800; margin-bottom: 1rem; }
.editor-container .ProseMirror h4 { font-size: 1.25rem; font-weight: 800; margin-bottom: 0.75rem; }

/* Lists Basic Reset */
.editor-container .ProseMirror ul { list-style-type: disc !important; padding-left: 2rem !important; margin-bottom: 1rem !important; display: block !important; }
.editor-container .ProseMirror ol { list-style-type: decimal !important; padding-left: 2rem !important; margin-bottom: 1rem !important; display: block !important; }
.editor-container .ProseMirror li { margin-bottom: 0.5rem !important; display: list-item !important; }

.editor-container .ProseMirror img {
    max-width: 100%;
    height: auto;
    border-radius: 1.5rem;
    margin: 2rem 0;
    display: block;
    box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
}

.editor-container .ProseMirror blockquote {
    border-left: 6px solid #6366f1;
    padding: 1rem 1.5rem;
    background: rgba(99, 102, 241, 0.05);
    border-radius: 0 1rem 1rem 0;
    font-style: italic;
    color: #818cf8;
    margin: 2rem 0;
}
</style>
