<template>
    <div class="space-y-4">
        <div class="relative rounded-4xl theme-bg-element border-2 border-dashed theme-border flex flex-col items-center justify-center group overflow-hidden transition-all duration-500 hover:border-indigo-500/50"
            :class="[
                aspect === 'square' ? 'aspect-square' : 'aspect-video w-full',
                isDragging ? 'border-indigo-500 bg-indigo-50/50' : ''
            ]" @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false" @drop.prevent="handleDrop">
            <!-- Background Glow Effect -->
            <div
                class="absolute inset-0 bg-indigo-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-700">
            </div>

            <!-- Preview Image/Video -->
            <template v-if="modelValue">
                <img v-if="isImage" :src="modelValue"
                    class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" />
                <video v-else-if="isVideo" :src="modelValue" class="absolute inset-0 w-full h-full object-cover"
                    controls></video>
                <div v-else class="absolute inset-0 flex items-center justify-center bg-indigo-600 text-white p-4">
                    <div class="text-center">
                        <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="text-xs font-black uppercase tracking-widest">{{ fileName || 'File Uploaded' }}</p>
                    </div>
                </div>
            </template>

            <!-- Empty State -->
            <div v-if="!modelValue" class="text-center p-8 z-10">
                <div
                    class="w-16 h-16 rounded-2xl bg-indigo-500/10 flex items-center justify-center text-indigo-500 mx-auto mb-4 group-hover:scale-110 transition-transform duration-500">
                    <svg v-if="!uploading" class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <svg v-else class="w-8 h-8 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </div>
                <p class="text-[10px] font-black theme-text-main uppercase tracking-[0.2em]">
                    {{ uploading ? 'Uploading...' : placeholder }}
                </p>
                <p class="text-[9px] theme-text-dim mt-1 font-bold">Max Size: {{ maxMB }}MB • {{ acceptLabel }}</p>
            </div>

            <!-- Upload Overlay (Hover) -->
            <div v-if="!uploading"
                class="absolute inset-0 bg-slate-900/60 backdrop-blur-md opacity-0 group-hover:opacity-100 transition-all duration-500 flex flex-col items-center justify-center gap-4 z-20">
                <div class="flex gap-2">
                    <button type="button" @click="triggerFileInput"
                        class="bg-white text-slate-900 px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl active:scale-95 transition-all">
                        {{ modelValue ? 'Replace' : 'Upload File' }}
                    </button>
                    <button v-if="modelValue" type="button" @click="removeFile"
                        class="bg-rose-500 text-white px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl active:scale-95 transition-all">
                        Remove
                    </button>
                </div>
            </div>

            <input ref="fileInput" type="file" class="hidden" :accept="accept" @change="handleFileSelect">
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { uploadService } from '../../services/uploadService';
import { useToast } from '../../composables/useToast';

const props = defineProps({
    modelValue: String,
    type: { type: String, default: 'image' }, // image, video, document
    folder: { type: String, default: 'uploads' },
    aspect: { type: String, default: 'video' },
    placeholder: { type: String, default: 'Click or Drop File' },
    maxMB: { type: Number, default: 2 },
});

const emit = defineEmits(['update:modelValue', 'change']);
const toast = useToast();

const fileInput = ref(null);
const isDragging = ref(false);
const uploading = ref(false);
const fileName = ref('');

const isImage = computed(() => props.type === 'image');
const isVideo = computed(() => props.type === 'video');

const accept = computed(() => {
    if (props.type === 'image') return 'image/*';
    if (props.type === 'video') return 'video/*';
    return '*/*';
});

const acceptLabel = computed(() => {
    if (props.type === 'image') return 'PNG, JPG, WEBP';
    if (props.type === 'video') return 'MP4, MOV, AVI';
    return 'Any File';
});

const triggerFileInput = () => {
    fileInput.value.click();
};

const handleFileSelect = (event) => {
    const file = event.target.files[0];
    if (file) uploadFile(file);
};

const handleDrop = (event) => {
    isDragging.value = false;
    const file = event.dataTransfer.files[0];
    if (file) uploadFile(file);
};

const uploadFile = async (file) => {
    if (file.size > props.maxMB * 1024 * 1024) {
        toast.error(`File is too large. Max ${props.maxMB}MB allowed.`);
        return;
    }

    uploading.value = true;
    try {
        let response;
        if (isVideo.value) {
            response = await uploadService.uploadVideo(file, props.folder);
        } else {
            response = await uploadService.uploadImage(file, props.folder);
        }

        fileName.value = file.name;
        emit('update:modelValue', response.url);
        emit('change', response);
        toast.success('File uploaded successfully');
    } catch (error) {
        toast.error(error.response?.data?.message || 'Upload failed');
    } finally {
        uploading.value = false;
    }
};

const removeFile = () => {
    emit('update:modelValue', '');
    emit('change', null);
    fileName.value = '';
    toast.info('File removed');
};
</script>
