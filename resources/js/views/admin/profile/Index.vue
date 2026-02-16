<template>
  <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <!-- Profile Header -->
    <div class="relative h-64 rounded-[3rem] overflow-hidden group shadow-2xl">
      <div class="absolute inset-0 bg-linear-to-br from-indigo-600 via-violet-600 to-fuchsia-600"></div>
      <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-20"></div>

      <div class="absolute bottom-8 left-8 text-white">
        <h1 class="text-4xl font-black tracking-tight">My Profile</h1>
        <p class="text-indigo-100 font-medium mt-1">Manage your account settings and preferences</p>
      </div>

      <!-- Action Buttons -->
      <div class="absolute top-8 right-8 flex gap-3">
        <button
          @click="handleSave"
          :disabled="saving"
          class="px-6 py-3 rounded-2xl backdrop-blur-md bg-white/20 text-white hover:bg-white/30 transition-all active:scale-90 flex items-center gap-2 shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <svg v-if="saving" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
          <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          <span class="text-sm font-bold">{{ saving ? 'Saving...' : 'Save Changes' }}</span>
        </button>
      </div>
    </div>

    <!-- Main Content -->
    <div v-if="loading" class="flex items-center justify-center py-40">
      <div class="flex flex-col items-center gap-4">
        <div class="w-12 h-12 border-4 border-indigo-500/20 border-t-indigo-500 rounded-full animate-spin"></div>
        <p class="text-xs font-black theme-text-dim uppercase tracking-widest">Loading Profile...</p>
      </div>
    </div>

    <div v-else-if="profile" class="grid grid-cols-12 gap-8 -mt-32 relative px-8">
      <!-- Left Sidebar: Profile Identity -->
      <div class="col-span-12 lg:col-span-4 space-y-6">
        <div class="theme-bg-card border theme-border rounded-[2.5rem] p-8 shadow-xl backdrop-blur-sm relative overflow-hidden group">
          <div class="absolute -top-24 -left-24 w-48 h-48 bg-indigo-500/10 blur-[80px] rounded-full"></div>

          <div class="relative flex flex-col items-center text-center space-y-4">
            <div class="relative">
              <div class="w-32 h-32 rounded-[2.5rem] p-1 bg-linear-to-tr from-indigo-500 to-fuchsia-500 shadow-2xl overflow-hidden">
                <img :src="profile.avatar || '/storage/default-avatar.png'" class="w-full h-full object-cover rounded-[2.3rem] theme-bg-element" alt="Profile">
              </div>
              <button @click="triggerFileInput" class="absolute -bottom-2 -right-2 w-10 h-10 rounded-2xl bg-indigo-500 text-white flex items-center justify-center shadow-lg hover:bg-indigo-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
              </button>
              <input ref="fileInput" type="file" accept="image/*" @change="handleAvatarUpload" class="hidden">
            </div>

            <div class="space-y-1">
              <h2 class="text-2xl font-black theme-text-main tracking-tight">{{ profile.name }}</h2>
              <p class="text-sm theme-text-dim font-bold tracking-tight opacity-70">@{{ profile.username }}</p>
            </div>

            <div class="pt-4 flex flex-wrap justify-center gap-2">
              <span class="px-4 py-2 bg-indigo-500/10 text-indigo-500 text-[10px] font-black uppercase tracking-widest rounded-xl border border-indigo-500/20">Administrator</span>
              <span class="px-4 py-2 bg-slate-500/10 theme-text-muted text-[10px] font-black uppercase tracking-widest rounded-xl border theme-border">System Access</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Side: Profile Form -->
      <div class="col-span-12 lg:col-span-8 space-y-6">
        <div class="theme-bg-card border theme-border rounded-[2.5rem] p-8 shadow-xl">
          <div class="flex items-center gap-4 mb-8">
            <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 flex items-center justify-center text-indigo-500">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <div>
              <h3 class="text-xl font-black theme-text-main tracking-tight">Personal Information</h3>
              <p class="text-xs theme-text-dim font-medium">Update your personal details and contact information</p>
            </div>
          </div>

          <form @submit.prevent="handleSave" class="space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <FormInput 
                v-model="form.name"
                label="Full Name"
                placeholder="Enter your full name"
                :error="errors.name"
                required
              />

              <FormInput 
                v-model="form.username"
                label="Username"
                placeholder="Enter your username"
                :error="errors.username"
                required
              />

              <FormInput 
                v-model="form.email"
                label="Email Address"
                type="email"
                placeholder="Enter your email"
                :error="errors.email"
                required
              />

              <FormInput 
                v-model="form.phone"
                label="Phone Number"
                placeholder="Enter your phone number"
                :error="errors.phone"
              />

              <FormInput 
                v-model="form.language"
                label="Language"
                placeholder="e.g. en, bn"
                :error="errors.language"
              />

              <FormInput 
                v-model="form.timezone"
                label="Timezone"
                placeholder="e.g. Asia/Dhaka"
                :error="errors.timezone"
              />
            </div>

            <div>
              <FormInput 
                v-model="form.bio"
                label="Bio"
                type="textarea"
                placeholder="Tell us about yourself..."
                :error="errors.bio"
              />
            </div>
          </form>
        </div>

        <!-- System Information -->
        <div class="theme-bg-card border theme-border rounded-[2.5rem] p-8 shadow-xl">
          <div class="flex items-center gap-4 mb-8">
            <div class="w-12 h-12 rounded-2xl bg-slate-500/10 flex items-center justify-center theme-text-muted">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div>
              <h3 class="text-xl font-black theme-text-main tracking-tight">System Information</h3>
              <p class="text-xs theme-text-dim font-medium">Your account metadata and system details</p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="space-y-4">
              <div class="flex items-start gap-4">
                <div class="w-2 h-2 rounded-full bg-indigo-500 mt-2"></div>
                <div>
                  <p class="text-[10px] theme-text-dim font-black uppercase tracking-widest">Account Role</p>
                  <p class="text-sm font-bold theme-text-main mt-1">Administrator</p>
                </div>
              </div>
              <div class="flex items-start gap-4">
                <div class="w-2 h-2 rounded-full bg-indigo-500 mt-2"></div>
                <div>
                  <p class="text-[10px] theme-text-dim font-black uppercase tracking-widest">Member Since</p>
                  <p class="text-sm font-bold theme-text-main mt-1">{{ formatDate(profile.created_at) }}</p>
                </div>
              </div>
            </div>

            <div class="space-y-4">
              <div class="flex items-start gap-4">
                <div class="w-2 h-2 rounded-full bg-indigo-500 mt-2"></div>
                <div>
                  <p class="text-[10px] theme-text-dim font-black uppercase tracking-widest">Last Login</p>
                  <p class="text-sm font-bold theme-text-main mt-1">{{ profile.last_login_at ? formatDate(profile.last_login_at) : 'Never' }}</p>
                </div>
              </div>
              <div class="flex items-start gap-4">
                <div class="w-2 h-2 rounded-full bg-indigo-500 mt-2"></div>
                <div>
                  <p class="text-[10px] theme-text-dim font-black uppercase tracking-widest">Account Status</p>
                  <p class="text-sm font-bold text-emerald-500 mt-1">Active</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import { adminProfileService } from '../../../services/adminProfileService';
import { uploadService } from '../../../services/uploadService';
import { useToast } from '../../../composables/useToast';

const toast = useToast();
const loading = ref(true);
const saving = ref(false);
const profile = ref(null);
const fileInput = ref(null);

const form = reactive({
  name: '',
  username: '',
  email: '',
  phone: '',
  avatar: '',
  bio: '',
  language: '',
  timezone: '',
  meta: null
});

const errors = ref({});

const fetchProfile = async () => {
  loading.value = true;
  try {
    const response = await adminProfileService.show();
    profile.value = response.data;
    // Populate form
    form.name = profile.value.name;
    form.username = profile.value.username;
    form.email = profile.value.email;
    form.phone = profile.value.phone;
    form.avatar = profile.value.avatar;
    form.bio = profile.value.bio;
    form.language = profile.value.language || '';
    form.timezone = profile.value.timezone || 'UTC';
    form.meta = profile.value.meta;
  } catch (error) {
    toast.error('Failed to load profile');
  } finally {
    loading.value = false;
  }
};

const handleSave = async () => {
  saving.value = true;
  errors.value = {};
  
  try {
    const response = await adminProfileService.update(form);
    profile.value = response.data;
    toast.success('Profile updated successfully');
  } catch (error) {
    if (error.response && error.response.status === 422) {
      errors.value = error.response.data.errors || {};
    } else {
      toast.error('Failed to update profile');
    }
  } finally {
    saving.value = false;
  }
};

const triggerFileInput = () => {
  fileInput.value.click();
};

const handleAvatarUpload = async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  try {
    const response = await uploadService.uploadImage(file);
    form.avatar = response.data.url;
    toast.success('Avatar uploaded successfully');
  } catch (error) {
    toast.error('Failed to upload avatar');
  }
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

onMounted(() => {
  fetchProfile();
});
</script>
