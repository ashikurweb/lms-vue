<template>
  <div class="min-h-screen pt-24 pb-12 px-4 sm:px-6 lg:px-8 theme-bg-main">
    <div class="max-w-4xl mx-auto space-y-8">
      
      <!-- Welcome Header -->
      <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-3xl p-8 flex items-center justify-between">
         <div>
            <h1 class="text-3xl font-black theme-text-main tracking-tight">My Profile</h1>
            <p class="theme-text-dim mt-2 font-medium">Manage your account settings and preferences.</p>
         </div>
         <div class="hidden sm:block">
            <div class="w-16 h-16 rounded-2xl bg-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-600/20">
               <span class="text-2xl font-bold text-white uppercase">{{ user?.name?.charAt(0) }}</span>
            </div>
         </div>
      </div>

      <!-- Profile Edit Form -->
      <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-3xl p-8">
        <div class="flex items-center justify-between mb-8">
          <div>
            <h2 class="text-2xl font-black theme-text-main tracking-tight">Edit Profile</h2>
            <p class="theme-text-dim mt-1">Update your personal information and account settings</p>
          </div>
          <button 
            @click="handleSave"
            :disabled="saving"
            class="px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-2xl font-bold shadow-lg shadow-indigo-600/20 active:scale-95 transition-all disabled:opacity-50 flex items-center gap-2"
          >
            <svg v-if="saving" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            <span>{{ saving ? 'Saving...' : 'Save Changes' }}</span>
          </button>
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

          <!-- Avatar Upload Section -->
          <div class="border-t border-white/10 pt-6">
            <div class="flex items-center gap-4">
              <div class="relative">
                <img :src="form.avatar || '/storage/default-avatar.png'" class="w-20 h-20 rounded-2xl object-cover border-2 border-white/20" alt="Avatar">
                <button 
                  @click="triggerFileInput"
                  type="button"
                  class="absolute -bottom-2 -right-2 w-8 h-8 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl flex items-center justify-center shadow-lg transition-colors"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                  </svg>
                </button>
                <input ref="fileInput" type="file" accept="image/*" @change="handleAvatarUpload" class="hidden">
              </div>
              <div>
                <h3 class="text-lg font-bold theme-text-main">Profile Picture</h3>
                <p class="text-sm theme-text-dim">Upload a new profile picture</p>
              </div>
            </div>
          </div>
        </form>
      </div>

      <!-- Account Information -->
      <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-3xl p-8">
        <h3 class="text-xl font-black theme-text-main tracking-tight mb-6">Account Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="space-y-4">
            <div>
              <label class="text-[10px] uppercase tracking-widest theme-text-muted font-bold">Account Role</label>
              <div class="mt-2 inline-flex items-center px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 text-xs font-black uppercase tracking-wider">
                {{ user?.roles?.[0]?.name || 'Student' }}
              </div>
            </div>
            <div>
              <label class="text-[10px] uppercase tracking-widest theme-text-muted font-bold">Member Since</label>
              <p class="theme-text-main font-bold text-lg mt-1">{{ formatDate(user?.created_at) }}</p>
            </div>
          </div>
          <div class="space-y-4">
            <div>
              <label class="text-[10px] uppercase tracking-widest theme-text-muted font-bold">Last Login</label>
              <p class="theme-text-main font-bold text-lg mt-1">{{ user?.last_login_at ? formatDate(user?.last_login_at) : 'Never' }}</p>
            </div>
            <div>
              <label class="text-[10px] uppercase tracking-widest theme-text-muted font-bold">Account Status</label>
              <div class="mt-2 inline-flex items-center px-3 py-1 rounded-full bg-green-500/10 border border-green-500/20 text-green-500 text-xs font-black uppercase tracking-wider">
                Active
              </div>
            </div>
          </div>
        </div>
      </div>

      <button @click="handleLogout" class="w-full py-4 bg-rose-500/10 hover:bg-rose-500 border border-rose-500/20 text-rose-500 hover:text-white rounded-2xl font-black text-xs uppercase tracking-widest transition-all flex items-center justify-center gap-2 group">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
        Sign Out Securely
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import { useAuth } from '../../../composables/useAuth';
import { useRouter } from 'vue-router';
import { userService } from '../../../services/userService';
import { uploadService } from '../../../services/uploadService';
import { useToast } from '../../../composables/useToast';
import FormInput from '../../../components/common/FormInput.vue';

const { user, logout } = useAuth();
const router = useRouter();
const toast = useToast();
const saving = ref(false);
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

// Populate form when component mounts
onMounted(() => {
  form.name = user.value?.name || '';
  form.username = user.value?.username || '';
  form.email = user.value?.email || '';
  form.phone = user.value?.phone || '';
  form.avatar = user.value?.avatar || '';
  form.bio = user.value?.bio || '';
  form.language = user.value?.language || '';
  form.timezone = user.value?.timezone || 'UTC';
  form.meta = user.value?.meta || null;
});

const handleSave = async () => {
  saving.value = true;
  errors.value = {};
  
  try {
    await userService.update(user.value.uuid, form);
    toast.success('Profile updated successfully');
    // Update the user object
    Object.assign(user.value, form);
  } catch (error) {
    if (error.response && error.response.status === 422) {
      errors.value = error.response.data.errors;
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

const handleLogout = async () => {
  await logout();
  router.push({ name: 'login' });
};
</script>
