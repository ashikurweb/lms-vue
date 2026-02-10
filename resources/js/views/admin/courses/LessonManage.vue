<template>
    <div class="max-w-5xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700 pb-20">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <button @click="$router.back()"
                    class="w-10 h-10 flex items-center justify-center rounded-xl theme-bg-element theme-text-muted hover:theme-text-main transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <div>
                    <h1 class="text-2xl font-black theme-text-main tracking-tight">{{ isEditing ? 'Edit Lesson' : 'Add New Lesson' }}</h1>
                    <p class="text-sm theme-text-dim font-medium">Configure your lesson content, settings, and
                        resources.</p>
                </div>
            </div>
            <PrimaryButton :label="isEditing ? 'Update Lesson' : 'Create Lesson'" @click="saveLesson" :loading="saving"
                class="!px-8 !py-3.5 !rounded-2xl shadow-xl shadow-indigo-600/20" />
        </div>

        <!-- Navigation Tabs -->
        <div
            class="flex items-center gap-2 p-1.5 theme-bg-card border theme-border rounded-2xl overflow-x-auto custom-scrollbar no-scrollbar sticky top-4 z-20 backdrop-blur-xl shadow-sm">
            <button v-for="tab in lessonTabs" :key="tab.id" @click="activeTab = tab.id"
                class="flex items-center gap-2 px-6 py-2.5 rounded-xl transition-all duration-300 whitespace-nowrap"
                :class="activeTab === tab.id ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30 font-bold' : 'theme-text-muted hover:theme-text-main font-semibold hover:theme-bg-hover'">
                <div v-html="tab.icon" class="w-4 h-4"></div>
                <span class="text-xs uppercase tracking-widest">{{ tab.label }}</span>
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content Area -->
            <div class="lg:col-span-2 space-y-8">

                <!-- Basic Info Tab -->
                <div v-show="activeTab === 'basic'"
                    class="theme-bg-card border theme-border rounded-3xl p-8 shadow-sm space-y-6">
                    <h3 class="text-lg font-black theme-text-main flex items-center gap-3">
                        <span
                            class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-500 flex items-center justify-center">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        Basic Information
                    </h3>
                    <div class="space-y-6">
                        <FormInput v-model="form.title" label="Lesson Title" placeholder="e.g. Introduction to Vue 3"
                            required />
                        <FormDropdown v-model="form.type" label="Lesson Type" :options="lessonTypeOptions" />
                        <FormInput v-model="form.order" label="Sort Order" type="number" placeholder="0" />
                        <div class="space-y-2">
                            <label
                                class="text-[10px] font-black theme-text-dim uppercase tracking-[0.2em] ml-2">Description</label>
                            <textarea v-model="form.description" rows="4"
                                class="w-full px-6 py-4 rounded-2xl theme-bg-element border-2 theme-border outline-none focus:border-indigo-500/50 transition-all text-sm font-bold theme-text-main"
                                placeholder="What will students learn in this lesson?"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Content Tab -->
                <div v-show="activeTab === 'content'"
                    class="theme-bg-card border theme-border rounded-3xl p-8 shadow-sm">
                    <h3 class="text-lg font-black theme-text-main mb-6 flex items-center gap-3">
                        <span
                            class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-500 flex items-center justify-center">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </span>
                        Lesson Content
                    </h3>

                    <!-- Video Type -->
                    <div v-if="form.type === 'video'" class="space-y-6">
                        <div class="p-8 rounded-3xl bg-indigo-50/50 border border-indigo-100/50 space-y-6">
                            <FormDropdown v-model="form.video_provider" label="Video Provider"
                                :options="videoProviderOptions" />

                            <div v-if="form.video_provider === 'upload'" class="space-y-4">
                                <label
                                    class="text-[10px] font-black theme-text-dim uppercase tracking-[0.2em] ml-2">Video
                                    File</label>
                                <div
                                    class="relative aspect-video rounded-3xl border-2 border-dashed theme-border bg-white flex flex-col items-center justify-center cursor-pointer hover:border-indigo-500 transition-all group overflow-hidden">
                                    <div v-if="videoUploading" class="flex flex-col items-center gap-3">
                                        <div
                                            class="w-12 h-12 border-4 border-indigo-500/20 border-t-indigo-500 rounded-full animate-spin">
                                        </div>
                                        <span
                                            class="text-xs font-black theme-text-main uppercase tracking-widest">Uploading...</span>
                                    </div>
                                    <template v-else-if="form.video_url">
                                        <video :src="form.video_url"
                                            class="absolute inset-0 w-full h-full object-cover opacity-20"></video>
                                        <div class="relative z-10 flex flex-col items-center gap-3">
                                            <div
                                                class="w-16 h-16 bg-emerald-500 text-white rounded-full flex items-center justify-center shadow-xl">
                                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <span
                                                class="text-sm font-black theme-text-main uppercase tracking-widest">Video
                                                Uploaded</span>
                                            <button @click.stop="form.video_url = ''"
                                                class="text-[10px] font-black text-rose-500 uppercase hover:underline">Change
                                                Video</button>
                                        </div>
                                    </template>
                                    <template v-else>
                                        <svg class="w-12 h-12 theme-text-muted mb-4 group-hover:scale-110 transition-transform"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                        </svg>
                                        <span class="text-xs font-black theme-text-dim uppercase tracking-widest">Select
                                            Video File (MP4, max 100MB)</span>
                                        <input type="file" @change="handleVideoUpload"
                                            class="absolute inset-0 opacity-0 cursor-pointer" accept="video/*">
                                    </template>
                                </div>
                            </div>

                            <FormInput v-else v-model="form.video_url" label="Video Link" placeholder="https://..." />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <FormInput v-model="form.video_id" label="Video ID (Optional)" placeholder="abc-123" />
                            <FormInput v-model="form.duration_seconds" type="number" label="Duration (Seconds)">
                                <template #icon><span class="text-[10px] font-black theme-text-dim">{{
                                    formatDuration(form.duration_seconds) }}</span></template>
                            </FormInput>
                        </div>
                    </div>

                    <!-- Text Type -->
                    <div v-else-if="form.type === 'text'" class="space-y-4">
                        <label
                            class="text-[10px] font-black theme-text-dim uppercase tracking-[0.2em] ml-2 font-mono">Article
                            Content
                            (Markdown Supported)</label>
                        <textarea v-model="form.content" rows="20"
                            class="w-full px-8 py-6 rounded-3xl theme-bg-element border-2 theme-border outline-none focus:border-indigo-500/50 transition-all text-sm font-medium theme-text-main font-mono leading-relaxed shadow-inner"
                            placeholder="Start writing your lesson content here..."></textarea>
                    </div>

                    <!-- Audio Type -->
                    <div v-else-if="form.type === 'audio'"
                        class="space-y-6 p-8 rounded-3xl bg-violet-50/50 border border-violet-100/50">
                        <FormInput v-model="form.audio_url" label="Audio File URL" placeholder="https://..." />
                        <FormInput v-model="form.audio_duration" type="number" label="Audio Duration (Seconds)" />
                    </div>

                    <!-- Document Type -->
                    <div v-else-if="form.type === 'document'"
                        class="space-y-6 p-8 rounded-3xl bg-amber-50/50 border border-amber-100/50">
                        <FormInput v-model="form.document_url" label="Document URL" placeholder="https://..." />
                        <FormDropdown v-model="form.document_type" label="Document Type" :options="[
                            { label: 'PDF', value: 'pdf' },
                            { label: 'PowerPoint (PPT)', value: 'ppt' },
                            { label: 'Excel', value: 'excel' },
                            { label: 'Other', value: 'other' }
                        ]" />
                    </div>

                    <!-- Embed Type -->
                    <div v-else-if="form.type === 'embed'" class="space-y-4">
                        <label class="text-[10px] font-black theme-text-dim uppercase tracking-[0.2em] ml-2">iFrame /
                            Embed Code</label>
                        <textarea v-model="form.embed_code" rows="8"
                            class="w-full px-6 py-4 rounded-2xl theme-bg-element border-2 theme-border outline-none focus:border-cyan-500/50 transition-all text-xs font-mono theme-text-main"
                            placeholder='<iframe src="..." width="100%" height="400"></iframe>'></textarea>
                    </div>
                </div>

                <!-- Resources Tab -->
                <div v-show="activeTab === 'resources'" class="space-y-8">
                    <div class="theme-bg-card border theme-border rounded-3xl p-8 shadow-sm space-y-6">
                        <h3 class="text-lg font-black theme-text-main flex items-center gap-3">
                            <span
                                class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                            </span>
                            Add Downloadable Resource
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <FormInput v-model="resourceForm.title" label="Resource Title"
                                placeholder="e.g. Source Code" />
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-black theme-text-dim uppercase tracking-[0.2em] ml-2 font-bold">File
                                    Selection</label>
                                <div
                                    class="relative h-14 rounded-2xl border-2 border-dashed theme-border bg-theme-bg-element flex items-center px-4 cursor-pointer hover:border-emerald-500 transition-all overflow-hidden">
                                    <span class="text-xs font-bold theme-text-muted truncate flex-1">{{
                                        resourceForm.file ?
                                            resourceForm.file.name : 'Choose File (Max 50MB)' }}</span>
                                    <svg class="w-5 h-5 theme-text-muted" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                    </svg>
                                    <input type="file" @change="handleResourceSelect"
                                        class="absolute inset-0 opacity-0 cursor-pointer">
                                </div>
                            </div>
                        </div>
                        <PrimaryButton label="Upload Resource" @click="uploadResource" :loading="resourceUploading"
                            :disabled="!resourceForm.file || !resourceForm.title"
                            class="w-full !bg-emerald-600 !hover:bg-emerald-700 shadow-lg shadow-emerald-500/20" />
                    </div>

                    <!-- Resource List -->
                    <div v-if="resources.length > 0"
                        class="theme-bg-card border theme-border rounded-3xl p-8 shadow-sm space-y-6">
                        <h4 class="text-sm font-black theme-text-dim uppercase tracking-[0.2em]">Uploaded Resources ({{
                            resources.length
                        }})</h4>
                        <div class="grid grid-cols-1 gap-4">
                            <div v-for="res in resources" :key="res.id"
                                class="flex items-center justify-between p-4 rounded-2xl theme-bg-element border theme-border group hover:border-emerald-500/30 transition-all">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-xl flex items-center justify-center text-[10px] font-black uppercase"
                                        :class="getFileTypeColor(res.file_type)">
                                        {{ res.file_type || '?' }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-black theme-text-main">{{ res.title }}</p>
                                        <p class="text-[10px] theme-text-dim font-bold uppercase tracking-wider">{{
                                            formatFileSize(res.file_size) }} • {{ res.download_count }} Downloads</p>
                                    </div>
                                </div>
                                <button @click="deleteResource(res.id)"
                                    class="p-2 text-rose-500 hover:bg-rose-500/10 rounded-xl transition-all opacity-0 group-hover:opacity-100">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Video Tracks Tab -->
                <div v-show="activeTab === 'tracks'" class="space-y-8">
                    <div class="theme-bg-card border theme-border rounded-3xl p-8 shadow-sm space-y-6">
                        <h3 class="text-lg font-black theme-text-main flex items-center gap-3">
                            <span
                                class="w-8 h-8 rounded-lg bg-sky-500/10 text-sky-500 flex items-center justify-center">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                </svg>
                            </span>
                            Add Video Subtitles / Tracks
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <FormInput v-model="trackForm.label" label="Label" placeholder="e.g. English" />
                            <FormInput v-model="trackForm.language" label="Lang Code" placeholder="e.g. en" />
                            <FormDropdown v-model="trackForm.kind" label="Type"
                                :options="[{ label: 'Subtitles', value: 'subtitles' }, { label: 'Captions', value: 'captions' }]" />
                        </div>
                        <div
                            class="relative h-14 rounded-2xl border-2 border-dashed theme-border bg-theme-bg-element flex items-center px-4 cursor-pointer hover:border-sky-500 transition-all overflow-hidden">
                            <span class="text-xs font-bold theme-text-muted truncate flex-1">{{ trackForm.file ?
                                trackForm.file.name :
                                'Choose Subtitle File (.vtt, .srt)' }}</span>
                            <input type="file" @change="handleTrackSelect"
                                class="absolute inset-0 opacity-0 cursor-pointer" accept=".vtt,.srt">
                        </div>
                        <PrimaryButton label="Add Track" @click="uploadTrack" :loading="trackUploading"
                            :disabled="!trackForm.file"
                            class="w-full !bg-sky-600 !hover:bg-sky-700 shadow-lg shadow-sky-500/20" />
                    </div>

                    <!-- Track List -->
                    <div v-if="tracks.length > 0"
                        class="theme-bg-card border theme-border rounded-3xl p-8 shadow-sm space-y-6">
                        <h4 class="text-sm font-black theme-text-dim uppercase tracking-[0.2em]">Active Tracks ({{
                            tracks.length }})
                        </h4>
                        <div class="grid grid-cols-1 gap-4">
                            <div v-for="trk in tracks" :key="trk.id"
                                class="flex items-center justify-between p-4 rounded-2xl theme-bg-element border theme-border group">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-sky-500/10 text-sky-600 flex items-center justify-center font-bold text-xs uppercase">
                                        {{ trk.language }}</div>
                                    <div>
                                        <p class="text-sm font-black theme-text-main">{{ trk.label }}</p>
                                        <p class="text-[10px] theme-text-dim font-bold uppercase">{{ trk.kind }}</p>
                                    </div>
                                </div>
                                <button @click="deleteTrack(trk.id)"
                                    class="p-2 text-rose-500 hover:bg-rose-500/10 rounded-xl transition-all opacity-0 group-hover:opacity-100">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Sidebar (Settings & Visibility) -->
            <div class="space-y-8">
                <!-- Settings Card -->
                <div class="theme-bg-card border theme-border rounded-3xl p-8 shadow-sm space-y-6">
                    <h4 class="text-xs font-black theme-text-dim uppercase tracking-widest opacity-70">Lesson Settings
                    </h4>
                    <div class="space-y-4">
                        <label v-for="setting in lessonSettings" :key="setting.key"
                            class="flex items-center justify-between p-4 rounded-2xl theme-bg-element border-2 theme-border cursor-pointer group hover:border-indigo-500/30 transition-all">
                            <span class="text-[10px] font-black theme-text-main uppercase tracking-widest">{{
                                setting.label
                            }}</span>
                            <div class="w-10 h-5 rounded-full p-1 transition-colors duration-300 relative"
                                :class="form[setting.key] ? setting.color : 'bg-slate-200 dark:bg-slate-700'">
                                <div class="w-3 h-3 bg-white rounded-full transition-transform duration-300 shadow-sm"
                                    :class="form[setting.key] ? 'translate-x-5' : 'translate-x-0'"></div>
                                <input type="checkbox" v-model="form[setting.key]" class="hidden">
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Drip Content Card -->
                <div class="theme-bg-card border theme-border rounded-3xl p-8 shadow-sm space-y-6">
                    <h4 class="text-xs font-black theme-text-dim uppercase tracking-widest opacity-70">Access & Drip
                    </h4>
                    <label
                        class="flex items-center justify-between p-4 rounded-2xl bg-indigo-600 text-white cursor-pointer shadow-lg shadow-indigo-500/20">
                        <span class="text-[10px] font-black uppercase tracking-widest">Enable Drip</span>
                        <div class="w-10 h-5 rounded-full p-1 bg-white/20 relative transition-all">
                            <div class="w-3 h-3 bg-white rounded-full transition-transform duration-300"
                                :class="form.drip_enabled ? 'translate-x-5' : 'translate-x-0'"></div>
                            <input type="checkbox" v-model="form.drip_enabled" class="hidden">
                        </div>
                    </label>

                    <div v-if="form.drip_enabled" class="space-y-4 animate-in fade-in slide-in-from-top-2 duration-300">
                        <FormDropdown v-model="form.drip_type" label="Access Trigger" :options="dripTypeOptions" />
                        <FormInput v-if="form.drip_type === 'days_after_enrollment'" v-model="form.drip_days"
                            type="number" label="Days Delay" />
                        <FormInput v-if="form.drip_type === 'date'" v-model="form.drip_date" type="date"
                            label="Release Date" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { courseService } from '../../../services/courseService';
import { uploadService } from '../../../services/uploadService';
import { lessonAttachmentService } from '../../../services/lessonAttachmentService';
import { useToast } from '../../../composables/useToast';
import PrimaryButton from '../../../components/common/PrimaryButton.vue';
import FormInput from '../../../components/common/FormInput.vue';
import FormDropdown from '../../../components/common/FormDropdown.vue';

const route = useRoute();
const router = useRouter();
const toast = useToast();

const isEditing = computed(() => !!route.params.lessonId);
const activeTab = ref('basic');
const saving = ref(false);
const videoUploading = ref(false);
const resourceUploading = ref(false);
const trackUploading = ref(false);

const resources = ref([]);
const tracks = ref([]);

const form = reactive({
    title: '',
    description: '',
    content: '',
    type: 'video',
    order: 0,
    video_provider: 'upload',
    video_url: '',
    video_id: '',
    duration_seconds: 0,
    audio_url: '',
    audio_duration: null,
    document_url: '',
    document_type: 'pdf',
    embed_code: '',
    is_published: true,
    is_free: false,
    is_downloadable: false,
    is_locked: false,
    is_prerequisite: false,
    drip_enabled: false,
    drip_type: 'days_after_enrollment',
    drip_days: 0,
    drip_date: '',
});

const resourceForm = reactive({ title: '', file: null });
const trackForm = reactive({ label: '', language: '', kind: 'subtitles', file: null });

const lessonTabs = computed(() => {
    const tabs = [
        { id: 'basic', label: 'Basic Info', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' },
        { id: 'content', label: 'Content', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>' }
    ];
    if (isEditing.value) {
        tabs.push(
            { id: 'resources', label: 'Resources', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>' },
            { id: 'tracks', label: 'Video Tracks', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" /></svg>' }
        );
    }
    return tabs;
});

const lessonSettings = [
    { label: 'Published', key: 'is_published', color: 'bg-indigo-600' },
    { label: 'Free Preview', key: 'is_free', color: 'bg-emerald-600' },
    { label: 'Downloadable', key: 'is_downloadable', color: 'bg-amber-600' },
    { label: 'Locked', key: 'is_locked', color: 'bg-rose-600' },
    { label: 'Prerequisite', key: 'is_prerequisite', color: 'bg-violet-600' },
];

const lessonTypeOptions = [
    { label: 'Video', value: 'video' },
    { label: 'Text / Article', value: 'text' },
    { label: 'Audio', value: 'audio' },
    { label: 'Document (PDF/PPT)', value: 'document' },
    { label: 'Embed (iFrame)', value: 'embed' },
];

const videoProviderOptions = [
    { label: 'Upload (Local)', value: 'upload' },
    { label: 'Bunny Stream', value: 'bunny' },
    { label: 'External Resource', value: 'external' },
];

const dripTypeOptions = [
    { label: 'Days After Enrollment', value: 'days_after_enrollment' },
    { label: 'Fixed Date', value: 'date' },
    { label: 'After Previous Lesson', value: 'after_lesson' },
];

const fetchLesson = async () => {
    if (!isEditing.value) return;
    try {
        const response = await courseService.getLesson(route.params.lessonId);
        Object.assign(form, response);
        fetchAttachments();
    } catch (error) {
        toast.error('Failed to load lesson details');
    }
};

const fetchAttachments = async () => {
    if (!isEditing.value) return;
    try {
        resources.value = await lessonAttachmentService.getResources(route.params.lessonId);
        tracks.value = await lessonAttachmentService.getTracks(route.params.lessonId);
    } catch (error) { }
};

const saveLesson = async () => {
    saving.value = true;
    try {
        if (isEditing.value) {
            await courseService.updateLesson(route.params.lessonId, form);
            toast.success('Lesson updated successfully');
        } else {
            const sectionId = route.query.section_id;
            if (!sectionId) throw new Error('Section ID is missing');
            await courseService.storeLesson(sectionId, form);
            toast.success('Lesson created successfully');
        }
        router.back();
    } catch (error) {
        toast.error('Failed to save lesson');
    } finally {
        saving.value = false;
    }
};

const handleVideoUpload = async (e) => {
    const file = e.target.files[0];
    if (!file) return;
    videoUploading.value = true;
    try {
        const response = await uploadService.uploadVideo(file);
        form.video_url = response.url;
        // Auto-duration logic
        const video = document.createElement('video');
        video.preload = 'metadata';
        video.src = URL.createObjectURL(file);
        video.onloadedmetadata = () => {
            form.duration_seconds = Math.round(video.duration);
            URL.revokeObjectURL(video.src);
        };
        toast.success('Video uploaded');
    } catch (error) {
        toast.error('Video upload failed');
    } finally {
        videoUploading.value = false;
    }
};

const handleResourceSelect = (e) => resourceForm.file = e.target.files[0];
const uploadResource = async () => {
    resourceUploading.value = true;
    try {
        const formData = new FormData();
        formData.append('title', resourceForm.title);
        formData.append('file', resourceForm.file);
        await lessonAttachmentService.uploadResource(route.params.lessonId, formData);
        toast.success('Resource added');
        resourceForm.title = '';
        resourceForm.file = null;
        fetchAttachments();
    } catch (error) {
        toast.error('Upload failed');
    } finally {
        resourceUploading.value = false;
    }
};

const deleteResource = async (id) => {
    if (!confirm('Delete this resource?')) return;
    try {
        await lessonAttachmentService.deleteResource(id);
        fetchAttachments();
    } catch (error) { }
};

const handleTrackSelect = (e) => trackForm.file = e.target.files[0];
const uploadTrack = async () => {
    trackUploading.value = true;
    try {
        const formData = new FormData();
        formData.append('label', trackForm.label);
        formData.append('language', trackForm.language);
        formData.append('kind', trackForm.kind);
        formData.append('file', trackForm.file);
        await lessonAttachmentService.uploadTrack(route.params.lessonId, formData);
        toast.success('Track added');
        trackForm.file = null;
        fetchAttachments();
    } catch (error) { }
    finally { trackUploading.value = false; }
};

const deleteTrack = async (id) => {
    if (!confirm('Delete track?')) return;
    try {
        await lessonAttachmentService.deleteTrack(id);
        fetchAttachments();
    } catch (error) { }
};

const formatDuration = (s) => {
    const mins = Math.floor(s / 60);
    const secs = s % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
};

const formatFileSize = (bytes) => {
    if (!bytes) return '0 B';
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return (bytes / Math.pow(1024, i)).toFixed(1) + ' ' + ['B', 'KB', 'MB', 'GB'][i];
};

const getFileTypeColor = (t) => {
    const colors = { pdf: 'bg-rose-500/10 text-rose-600', zip: 'bg-amber-500/10 text-amber-600' };
    return colors[t] || 'bg-slate-500/10 text-slate-600';
};

onMounted(fetchLesson);
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
    height: 4px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(99, 102, 241, 0.2);
    border-radius: 10px;
}

.no-scrollbar::-webkit-scrollbar {
    display: none;
}
</style>
