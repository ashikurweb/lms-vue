<template>
  <div class="min-h-screen flex flex-col lg:flex-row overflow-hidden theme-bg-main selection:bg-indigo-500/30">
    <!-- Left Side: Immersive Visual -->
    <div class="hidden lg:flex w-1/2 relative items-center justify-center p-20 overflow-hidden bg-slate-950">
        <!-- Abstract Background -->
        <div class="absolute inset-0 opacity-20 bg-grid-white/[0.05] mask-[radial-gradient(white,transparent_70%)]"></div>
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-indigo-600/20 blur-[120px] rounded-full"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-violet-600/20 blur-[120px] rounded-full"></div>

        <div class="relative z-10 max-w-xl space-y-12">
            <div class="reveal-up opacity-0 space-y-6">
                <div class="flex items-center gap-4">
                    <div class="h-[2px] w-12 bg-indigo-500"></div>
                    <span class="text-[10px] font-black uppercase tracking-[0.5em] text-indigo-400">Secure Protocol v4.0</span>
                </div>
                <h1 class="text-7xl font-black text-white tracking-tighter leading-[0.9]">
                    Access The <br />
                    <span class="text-indigo-500 italic">Nexus</span> <br />
                    Network.
                </h1>
                <p class="text-xl text-slate-400 font-medium leading-relaxed">
                    Authenticate your identity to access restricted modules, distributed infrastructure, and elite technical insights.
                </p>
            </div>

            <!-- Dashboard Teaser Card -->
            <div class="reveal-up opacity-0 pt-10">
                <div class="bg-white/3 backdrop-blur-3xl border border-white/10 rounded-[3rem] p-8 shadow-2xl transform hover:rotate-2 transition-transform duration-700 group">
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex gap-2">
                             <div v-for="i in 3" :key="i" class="w-3 h-3 rounded-full" :class="['bg-red-500/50', 'bg-amber-500/50', 'bg-emerald-500/50'][i-1]"></div>
                        </div>
                        <div class="px-4 py-1.5 rounded-full bg-white/5 border border-white/10 text-[8px] font-black text-white/40 uppercase tracking-widest leading-none">Status: Encrypted</div>
                    </div>
                    <div class="space-y-4">
                        <div class="h-4 w-[80%] bg-white/5 rounded-full overflow-hidden">
                            <div class="h-full bg-indigo-500 w-[60%] group-hover:w-[90%] transition-all duration-[2s]"></div>
                        </div>
                        <div class="h-4 w-[50%] bg-white/5 rounded-full"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Side: Auth Form -->
    <div class="flex-1 flex flex-col items-center pt-28 lg:pt-32 pb-20 px-8 md:px-20 relative overflow-y-auto">
        <!-- Background Elements for Mobile/Dark Mode -->
        <div class="absolute inset-0 pointer-events-none -z-10 opacity-5 lg:hidden">
            <div class="absolute top-0 right-0 w-full h-full bg-linear-to-bl from-indigo-500/20 to-transparent"></div>
        </div>

        <div class="w-full max-w-md space-y-12">
            <div class="reveal-up opacity-0 space-y-4 text-center lg:text-left">
                <router-link to="/" class="inline-flex lg:hidden mb-8">
                     <div class="w-12 h-12 rounded-2xl bg-indigo-600 flex items-center justify-center shadow-xl shadow-indigo-600/30">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path     stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253"/></svg>
                     </div>
                </router-link>
                <h2 class="text-4xl md:text-5xl font-black theme-text-main tracking-tighter">Welcome Back</h2>
                <p class="theme-text-dim font-medium">Verify your credentials to continue your session.</p>
            </div>

            <form @submit.prevent="handleLogin" class="reveal-up opacity-0 space-y-6">
                <div class="space-y-6">
                    <FormInput 
                        v-model="form.identity"
                        label="Account Identity (Email, Username, or Phone)"
                        placeholder="Enter your credentials"
                        required
                    >
                        <template #icon>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"/></svg>
                        </template>
                    </FormInput>

                    <FormInput 
                        v-model="form.password"
                        type="password"
                        label="Private Password"
                        placeholder="Enter your secret password"
                        required
                    >
                        <template #label-right>
                            <router-link to="/forgot-password" class="text-[10px] font-black text-indigo-500 hover:text-indigo-400 uppercase tracking-widest transition-colors">Forgot Code?</router-link>
                        </template>
                        <template #icon>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </template>
                    </FormInput>
                </div>

                <div class="flex items-center gap-3 ml-2">
                    <input type="checkbox" id="remember" class="w-5 h-5 rounded-lg theme-bg-element border-2 theme-border text-indigo-600 focus:ring-indigo-500">
                    <label for="remember" class="text-xs font-bold theme-text-dim cursor-pointer">Persist Session</label>
                </div>

                <button 
                    type="submit" 
                    :disabled="loading"
                    class="w-full py-6 bg-indigo-600 hover:bg-indigo-500 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-2xl shadow-indigo-600/30 active:scale-[0.98] transition-all flex items-center justify-center gap-4 disabled:opacity-70 disabled:cursor-not-allowed group"
                >
                    <span v-if="!loading">Initialize Session</span>
                    <span v-else class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Authenticating...
                    </span>
                    <svg v-if="!loading" class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 8l5 5-5 5M6 8l5 5-5 5"/></svg>
                </button>

                <div class="pt-8 text-center">
                    <p class="text-sm theme-text-dim font-medium">
                        Not part of the network? 
                        <router-link to="/register" class="text-indigo-500 hover:text-indigo-400 font-black uppercase tracking-widest text-[10px] ml-2">Request Identity Access</router-link>
                    </p>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="absolute bottom-8 text-[10px] font-bold theme-text-muted uppercase tracking-[0.3em]">
            &copy; 2026 EduNexus Cryptography Division
        </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '../../../composables/useAuth';
import FormInput from '../../../components/common/FormInput.vue';
import gsap from 'gsap';

import { useToast } from '../../../composables/useToast';

const router = useRouter();
const { login, loading, error: authError } = useAuth(); // rename destructured error to avoid conflict
const { success, error: toastError } = useToast();

const form = reactive({
    identity: '',
    password: ''
});

const handleLogin = async () => {
    try {
        const response = await login(form);
        const userRoles = response.data?.user?.roles || [];
        
        success('Welcome back! Session initialized.');

        // Check if user has admin/super-admin role
        const isAdmin = userRoles.some((role) => {
            // If roles are simple strings: ["user", "super-admin"]
            if (typeof role === 'string') {
                return ['admin', 'super-admin'].includes(role);
            }
            // Fallback if roles are objects: [{ name: "super-admin" }]
            return ['admin', 'super-admin'].includes(role.name);
        });

        if (isAdmin) {
             router.push({ name: 'dashboard' });
        } else {
             router.push({ name: 'profile' });
        }
    } catch (err) {
        // If unverified, redirect to verify-email
        if (err.response?.status === 403 && err.response?.data?.message === 'Email not verified.') {
            const userEmail = err.response.data.data?.user?.email;
            router.push({ 
                name: 'verify-email', 
                query: { email: userEmail, reason: 'unverified' }
            });
            toastError('Email not verified. Redirecting...');
        } else {
            // Use toast for other errors
            const message = authError.value || 'Login failed';
            toastError(message);
        }
        console.error('Login failed', err);
    }
};

onMounted(() => {
    gsap.to(".reveal-up", {
        y: 0,
        opacity: 1,
        duration: 1.2,
        stagger: 0.15,
        ease: "power4.out",
        delay: 0.3
    });
});
</script>

<style scoped>
.reveal-up { transform: translateY(30px); }
.fade-enter-active, .fade-leave-active { transition: opacity 0.5s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
