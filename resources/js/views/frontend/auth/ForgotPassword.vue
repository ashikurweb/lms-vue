<template>
  <div class="min-h-screen flex flex-col lg:flex-row overflow-hidden theme-bg-main selection:bg-indigo-500/30">
    <!-- Left Side: Immersive Visual -->
    <div class="hidden lg:flex w-1/2 relative items-center justify-center p-20 overflow-hidden bg-slate-950">
        <!-- Abstract Background -->
        <div class="absolute inset-0 opacity-20 bg-grid-white/[0.05] mask-[radial-gradient(white,transparent_70%)]"></div>
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-pink-600/20 blur-[120px] rounded-full"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-blue-600/20 blur-[120px] rounded-full"></div>

        <div class="relative z-10 max-w-xl space-y-12">
            <div class="reveal-up opacity-0 space-y-6">
                <div class="flex items-center gap-4">
                    <div class="h-[2px] w-12 bg-pink-500"></div>
                    <span class="text-[10px] font-black uppercase tracking-[0.5em] text-pink-400">System Recovery</span>
                </div>
                <h1 class="text-7xl font-black text-white tracking-tighter leading-[0.9]">
                    Restore <br />
                    <span class="text-pink-500 italic">Access</span> <br />
                    Control.
                </h1>
                <p class="text-xl text-slate-400 font-medium leading-relaxed">
                    Initiate the secure recovery protocol to regain entry to the Nexus Network.
                </p>
            </div>
        </div>
    </div>

    <!-- Right Side: Auth Form -->
    <div class="flex-1 flex flex-col items-center pt-28 lg:pt-32 pb-20 px-8 md:px-20 relative overflow-y-auto">
        <!-- Background Elements for Mobile/Dark Mode -->
        <div class="absolute inset-0 pointer-events-none -z-10 opacity-5 lg:hidden">
            <div class="absolute top-0 right-0 w-full h-full bg-linear-to-bl from-pink-500/20 to-transparent"></div>
        </div>

        <div class="w-full max-w-md space-y-12">
            <div class="reveal-up opacity-0 space-y-4 text-center lg:text-left">
                <router-link to="/login" class="inline-flex mb-8 items-center gap-2 text-xs font-bold text-pink-500 uppercase tracking-widest hover:text-pink-400 transition-colors">
                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                     Back to Login
                </router-link>
                <h2 class="text-4xl md:text-5xl font-black theme-text-main tracking-tighter">Forgot Password?</h2>
                <p class="theme-text-dim font-medium">Enter your email to receive a recovery code.</p>
            </div>

            <form @submit.prevent="handleSubmit" class="reveal-up opacity-0 space-y-6">
                <!-- Alert Message -->
                <transition name="fade">
                    <div v-if="error" class="p-6 rounded-2xl bg-red-500/10 border border-red-500/20 text-red-500 text-xs font-bold flex gap-4 items-start">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        {{ error }}
                    </div>
                </transition>
                
                 <transition name="fade">
                    <div v-if="successMessage" class="p-6 rounded-2xl bg-green-500/10 border border-green-500/20 text-green-500 text-xs font-bold flex gap-4 items-start">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        {{ successMessage }}
                    </div>
                </transition>

                <div class="space-y-6">
                    <FormInput 
                        v-model="email"
                        type="email"
                        label="Registered Email"
                        placeholder="name@example.com"
                        required
                    >
                        <template #icon>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"/></svg>
                        </template>
                    </FormInput>
                </div>

                <button 
                    type="submit" 
                    :disabled="loading"
                    class="w-full py-6 bg-pink-600 hover:bg-pink-500 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-2xl shadow-pink-600/30 active:scale-[0.98] transition-all flex items-center justify-center gap-4 disabled:opacity-70 disabled:cursor-not-allowed group"
                >
                    <span v-if="!loading">Send Reset Code</span>
                    <span v-else class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Processing...
                    </span>
                    <svg v-if="!loading" class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 8l5 5-5 5M6 8l5 5-5 5"/></svg>
                </button>
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
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '../../../composables/useAuth';
import FormInput from '../../../components/common/FormInput.vue';
import gsap from 'gsap';

const router = useRouter();
const { forgotPassword, loading, error: authError } = useAuth();

const email = ref('');
const error = ref(null);
const successMessage = ref(null);

const handleSubmit = async () => {
    error.value = null;
    successMessage.value = null;
    try {
        await forgotPassword(email.value);
        successMessage.value = 'Reset code sent! Redirecting...';
        setTimeout(() => {
            router.push({ name: 'reset-password', query: { email: email.value } });
        }, 2000);
    } catch (err) {
        error.value = authError.value || 'Failed to send reset link.';
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
