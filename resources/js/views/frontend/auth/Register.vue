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
                    <span class="text-[10px] font-black uppercase tracking-[0.5em] text-indigo-400">Creation Protocol v4.0</span>
                </div>
                <h1 class="text-7xl font-black text-white tracking-tighter leading-[0.9]">
                    Initialize Your <br />
                    <span class="text-indigo-500 italic">Identity</span>.
                </h1>
                <p class="text-xl text-slate-400 font-medium leading-relaxed">
                    Join the most rigorous technical ecosystem. Create your unique signature to begin your engineering journey.
                </p>
            </div>

            <!-- Perks Card -->
            <div class="reveal-up opacity-0 pt-10 space-y-4">
                <div v-for="(perk, i) in perks" :key="i" class="flex items-center gap-6 p-6 rounded-3xl bg-white/3 border border-white/10 backdrop-blur-3xl hover:bg-white/5 transition-all">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-600/20 flex items-center justify-center text-indigo-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" v-html="perk.icon"></svg>
                    </div>
                    <div>
                        <h4 class="text-white font-bold tracking-tight">{{ perk.title }}</h4>
                        <p class="text-slate-500 text-xs">{{ perk.desc }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Side: Auth Form -->
    <div class="flex-1 flex flex-col items-center pt-28 lg:pt-32 pb-20 px-8 md:px-20 relative overflow-y-auto">
        <div class="w-full max-w-md space-y-10">
            <div class="reveal-up opacity-0 space-y-4 text-center lg:text-left">
                <h2 class="text-4xl md:text-5xl font-black theme-text-main tracking-tighter">Create Identity</h2>
                <p class="theme-text-dim font-medium">Register your profile in the EduNexus protocol.</p>
            </div>

            <form @submit.prevent="handleRegister" class="reveal-up opacity-0 space-y-5">
                <!-- Alert Message -->
                <!-- Alert Message Replaced by Toast -->

                <div class="space-y-5">
                    <FormInput 
                        v-model="form.name"
                        label="Full Legal Name"
                        placeholder="Your full name"
                        required
                    >
                        <template #icon>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </template>
                    </FormInput>

                    <FormInput 
                        v-model="form.username"
                        label="Unique Username"
                        placeholder="Choose a username"
                        required
                    >
                        <template #icon>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"/></svg>
                        </template>
                    </FormInput>

                    <FormInput 
                        v-model="form.email"
                        type="email"
                        label="Secure Email"
                        placeholder="name@protocol.io"
                        required
                    >
                        <template #icon>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </template>
                    </FormInput>

                    <FormInput 
                        v-model="form.phone"
                        label="Mobile Number"
                        placeholder="+88017..."
                        required
                    >
                        <template #icon>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </template>
                    </FormInput>

                    <FormInput 
                        v-model="form.password"
                        type="password"
                        label="Secure Password"
                        placeholder="Set password"
                        required
                    >
                        <template #icon>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </template>
                    </FormInput>

                    <FormInput 
                        v-model="form.password_confirmation"
                        type="password"
                        label="Verify Password"
                        placeholder="Confirm password"
                        required
                    >
                        <template #icon>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </template>
                    </FormInput>
                </div>

                <div class="flex items-start gap-3 ml-2">
                    <input type="checkbox" id="terms" required class="mt-1 w-5 h-5 rounded-lg theme-bg-element border-2 theme-border text-indigo-600 focus:ring-indigo-500">
                    <label for="terms" class="text-[11px] font-medium theme-text-dim leading-relaxed">
                        I agree to the <span class="text-indigo-500">Governance Protocol</span> and <span class="text-indigo-500">Data Cryptography</span> terms.
                    </label>
                </div>

                <button 
                    type="submit" 
                    :disabled="loading"
                    class="w-full py-6 bg-indigo-600 hover:bg-indigo-500 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-2xl shadow-indigo-600/30 active:scale-[0.98] transition-all flex items-center justify-center gap-4 disabled:opacity-70 group mt-4"
                >
                    <span v-if="!loading">Initialize account</span>
                    <span v-else class="animate-pulse">Processing Protocol...</span>
                    <svg v-if="!loading" class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 8l5 5-5 5M6 8l5 5-5 5"/></svg>
                </button>

                <div class="pt-8 text-center border-t theme-border">
                    <p class="text-sm theme-text-dim font-medium">
                        Already have an identity? 
                        <router-link to="/login" class="text-indigo-500 hover:text-indigo-400 font-black uppercase tracking-widest text-[10px] ml-2">Access Session</router-link>
                    </p>
                </div>
            </form>
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
const { register, loading, error: authError } = useAuth();
const { success, error: toastError } = useToast();

const form = reactive({
    name: '',
    username: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: ''
});

const handleRegister = async () => {
    try {
        const response = await register(form);
        if (response.status === 'success') {
             success('Identity created! Please verify your email.');
             // Save token if it exists (automatically handled by composable now)
             router.push({ 
                name: 'verify-email', 
                query: { email: form.email }
             });
        }
    } catch (err) {
        const message = authError.value || 'Registration failed';
        toastError(message);
        console.error('Registration failed', err);
    }
};

const perks = [
    { title: 'Global Infrastructure', desc: 'Access to distributed cloud nodes.', icon: '<path d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>' },
    { title: 'Zero-Trust Security', desc: 'End-to-end encrypted learning environment.', icon: '<path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>' },
    { title: 'Technical Mastery', desc: 'Curriculum designed by industry veterans.', icon: '<path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>' },
];

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
