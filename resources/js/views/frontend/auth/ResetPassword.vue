<template>
  <div class="min-h-screen flex flex-col lg:flex-row overflow-hidden theme-bg-main selection:bg-indigo-500/30">
    <!-- Left Side: Immersive Visual -->
    <div class="hidden lg:flex w-1/2 relative items-center justify-center p-20 overflow-hidden bg-slate-950">
        <!-- Abstract Background -->
        <div class="absolute inset-0 opacity-20 bg-grid-white/[0.05] mask-[radial-gradient(white,transparent_70%)]"></div>
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-cyan-600/20 blur-[120px] rounded-full"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-blue-600/20 blur-[120px] rounded-full"></div>

        <div class="relative z-10 max-w-xl space-y-12">
            <div class="reveal-up opacity-0 space-y-6">
                <div class="flex items-center gap-4">
                    <div class="h-[2px] w-12 bg-cyan-500"></div>
                    <span class="text-[10px] font-black uppercase tracking-[0.5em] text-cyan-400">Security Update</span>
                </div>
                <h1 class="text-7xl font-black text-white tracking-tighter leading-[0.9]">
                    New <br />
                    <span class="text-cyan-500 italic">Credentials</span> <br />
                    Established.
                </h1>
                <p class="text-xl text-slate-400 font-medium leading-relaxed">
                    Set a new strong password to secure your account and access the Nexus Network.
                </p>
            </div>
        </div>
    </div>

    <!-- Right Side: Auth Form -->
    <div class="flex-1 flex flex-col items-center pt-28 lg:pt-32 pb-20 px-8 md:px-20 relative overflow-y-auto">
        <!-- Background Elements for Mobile/Dark Mode -->
        <div class="absolute inset-0 pointer-events-none -z-10 opacity-5 lg:hidden">
            <div class="absolute top-0 right-0 w-full h-full bg-linear-to-bl from-cyan-500/20 to-transparent"></div>
        </div>

        <div class="w-full max-w-md space-y-12">
            <div class="reveal-up opacity-0 space-y-4 text-center lg:text-left">
                 <router-link to="/login" class="inline-flex mb-8 items-center gap-2 text-xs font-bold text-cyan-500 uppercase tracking-widest hover:text-cyan-400 transition-colors">
                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                     Back to Login
                </router-link>
                <h2 class="text-4xl md:text-5xl font-black theme-text-main tracking-tighter">Reset Password</h2>
            </div>

            <form @submit.prevent="handleSubmit" class="reveal-up opacity-0 space-y-6">
                <!-- Inline alerts replaced by Toast -->

                <div class="space-y-6">

                     <FormInput 
                        v-model="form.token"
                        label="Reset Code / Token"
                        placeholder="Enter the code from email"
                        required
                    >
                        <template #label-right>
                            <button 
                                type="button"
                                @click.stop.prevent="handleResend"
                                :disabled="countdown > 0 || loading || isResending"
                                class="text-[10px] font-black uppercase tracking-widest transition-colors"
                                :class="(countdown > 0 || isResending) ? 'text-slate-500 cursor-not-allowed' : 'text-cyan-500 hover:text-cyan-400'"
                            >
                                <span v-if="isResending" class="flex items-center gap-1 animate-pulse">
                                    Sending...
                                </span>
                                <span v-else>{{ countdown > 0 ? `Resend (${countdown}s)` : 'Resend Code' }}</span>
                            </button>
                        </template>
                         <template #icon>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11.536 9.636 13.536 7.636 17 4.101a2.25 2.25 0 00-3.182-3.182L13 1.732 11.732 3 7.5 7.232 5.232 5 3 7.232 1.5 5.732 5.732 1.5 7.5 3.268 9 4.768 11.232 7 13.5 9.268 17.743 7.743 19 13.5 19 21 16.5 21 15 19.5 15 15 15 19.5 15 21 16.5 21 19 13.5 19 12 19 17.743 13.257 21 11.232 21 9 19.232 7.5 17.732 9z"/></svg>
                        </template>
                    </FormInput>

                    <FormInput 
                        v-model="form.password"
                        type="password"
                        label="New Password"
                        placeholder="Create strong password"
                        required
                    >
                        <template #icon>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </template>
                    </FormInput>

                    <FormInput 
                        v-model="form.password_confirmation"
                        type="password"
                        label="Confirm Password"
                        placeholder="Repeat password"
                        required
                    >
                        <template #icon>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </template>
                    </FormInput>
                </div>

                <button 
                    type="submit" 
                    :disabled="loading && !isResending"
                    class="w-full py-6 bg-cyan-600 hover:bg-cyan-500 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-2xl shadow-cyan-600/30 active:scale-[0.98] transition-all flex items-center justify-center gap-4 disabled:opacity-70 disabled:cursor-not-allowed group"
                >
                    <span v-if="!loading || isResending">Reset Password</span>
                    <span v-else class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Updating...
                    </span>
                    <svg v-if="!loading || isResending" class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 8l5 5-5 5M6 8l5 5-5 5"/></svg>
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
import { ref, reactive, onMounted, onUnmounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuth } from '../../../composables/useAuth';
import { useToast } from '../../../composables/useToast';
import FormInput from '../../../components/common/FormInput.vue';
import gsap from 'gsap';

const router = useRouter();
const route = useRoute();
const { resetPassword, forgotPassword, loading, error: authError } = useAuth();
const { success, error: toastError } = useToast();

const form = reactive({
    email: '',
    token: '',
    password: '',
    password_confirmation: ''
});

const countdown = ref(30);
let timer = null;
const isResending = ref(false);

const startTimer = () => {
    countdown.value = 60;
    if (timer) clearInterval(timer);
    timer = setInterval(() => {
        if (countdown.value > 0) {
            countdown.value--;
        } else {
            clearInterval(timer);
        }
    }, 1000);
};

const handleResend = async () => {
    // Basic validation
    if (countdown.value > 0 || loading.value) return;

    // Check if we have the email to resend to
    if (!form.email) {
        toastError("Session expired or invalid link. Please try the 'Forgot Password' process again.");
        return;
    }
    
    isResending.value = true;
    
    try {
        await forgotPassword(form.email);
        success('New code sent to your email.');
        startTimer();
    } catch (err) {
        const errorMsg = authError.value || "Failed to resend code. Please try again.";
        toastError(errorMsg);
        console.error("Resend failed:", err);
    } finally {
        isResending.value = false;
    }
};

onMounted(() => {
    // Prefill from query params
    if (route.query.email) {
        form.email = route.query.email;
    }
    if (route.query.token) {
        form.token = route.query.token;
    }

    startTimer(); // Start cooldown on mount since they likely just received a code

    gsap.to(".reveal-up", {
        y: 0,
        opacity: 1,
        duration: 1.2,
        stagger: 0.15,
        ease: "power4.out",
        delay: 0.3
    });
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});

const handleSubmit = async () => {
    if (form.password !== form.password_confirmation) {
        toastError("Passwords do not match.");
        return;
    }

    try {
        await resetPassword(form);
        success('Password reset successful! Redirecting to login...');
        setTimeout(() => {
            router.push({ name: 'login' });
        }, 2000);
    } catch (err) {
        const errorMsg = authError.value || 'Failed to reset password.';
        toastError(errorMsg);
    }
};
</script>

<style scoped>
.reveal-up { transform: translateY(30px); }
.fade-enter-active, .fade-leave-active { transition: opacity 0.5s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
