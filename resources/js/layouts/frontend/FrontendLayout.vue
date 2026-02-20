<template>
    <div class="min-h-screen theme-bg-main transition-colors duration-500 selection:bg-indigo-500/30">
        <!-- Modular Navbar -->
        <Navbar v-if="showNavbar" :is-scrolled="isScrolled" :is-mobile-menu-open="isMobileMenuOpen"
            :nav-items="navItems" @toggle-mobile-menu="isMobileMenuOpen = !isMobileMenuOpen" />

        <!-- Premium Mobile Menu Overlay -->
        <transition @before-enter="beforeEnter" @enter="enter" @leave="leave" :css="false">
            <div v-if="isMobileMenuOpen"
                class="fixed inset-0 z-[140] bg-slate-950/98 backdrop-blur-3xl lg:hidden pt-40 pb-12 px-8 flex flex-col justify-between h-screen overflow-hidden">
                <!-- Background Mesh Decor -->
                <div class="absolute inset-0 overflow-hidden pointer-events-none opacity-40">
                    <div
                        class="absolute top-[-10%] left-[-10%] w-[60%] h-[60%] bg-indigo-600/30 blur-[120px] rounded-full">
                    </div>
                    <div
                        class="absolute bottom-[-10%] right-[-10%] w-[60%] h-[60%] bg-violet-600/20 blur-[120px] rounded-full">
                    </div>
                </div>

                <div class="flex flex-col gap-5 relative z-10">
                    <router-link v-for="(item, i) in mobileNavItems" :key="item.name" :to="item.path"
                        @click="isMobileMenuOpen = false"
                        class="text-3xl font-black text-white tracking-tighter hover:text-indigo-400 transition-colors nav-link-mobile opacity-0">
                        {{ item.name }}
                    </router-link>
                </div>

                <div
                    class="flex flex-col gap-3 pt-8 border-t border-white/10 relative z-10 footer-mobile overflow-hidden">
                    <router-link to="/login" @click="isMobileMenuOpen = false"
                        class="text-[10px] font-black text-white uppercase tracking-[0.2em] py-4 border border-white/20 rounded-2xl text-center bg-white/5 backdrop-blur-xl footer-mobile-item opacity-0">Login
                        Access</router-link>
                    <router-link to="/pricing" @click="isMobileMenuOpen = false"
                        class="text-[10px] font-black text-white bg-indigo-600 uppercase tracking-[0.2em] py-4 rounded-2xl text-center shadow-xl shadow-indigo-600/20 footer-mobile-item opacity-0">Subscribe
                        Pro</router-link>
                </div>
            </div>
        </transition>

        <!-- Content Area -->
        <main>
            <router-view v-slot="{ Component }">
                <transition name="page-fade" mode="out-in">
                    <component :is="Component" />
                </transition>
            </router-view>
        </main>

        <!-- Modular Footer -->
        <Footer v-if="showFooter" />
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import Navbar from '../../components/frontend/layout/Navbar.vue';
import Footer from '../../components/frontend/layout/Footer.vue';
import gsap from 'gsap';

const route = useRoute();
const isScrolled = ref(false);
const isMobileMenuOpen = ref(false);

const showNavbar = computed(() => !route.meta.hideNavbar);
const showFooter = computed(() => !route.meta.hideFooter);

const navItems = [
    { name: 'Academy', path: '/courses' },
    { name: 'Blog', path: '/blog' },
    { name: 'Mentors', path: '/instructors' },
];

const mobileNavItems = navItems;

// GSAP Animations
const beforeEnter = (el) => {
    gsap.set(el, {
        y: '-100%',
        opacity: 0,
        backgroundColor: '#020617'
    });
};

const enter = (el, done) => {
    const tl = gsap.timeline({
        onComplete: done,
        defaults: { ease: "power4.out", duration: 0.8 }
    });

    tl.to(el, { y: '0%', opacity: 1 })
        .to(".nav-link-mobile", {
            opacity: 1,
            y: 0,
            stagger: 0.08,
            ease: "back.out(1.7)"
        }, "-=0.4")
        .to(".footer-mobile-item", {
            opacity: 1,
            y: 0,
            stagger: 0.1,
            duration: 0.6,
            ease: "power2.out"
        }, "-=0.6");
};

const leave = (el, done) => {
    const tl = gsap.timeline({ onComplete: done });

    tl.to(".footer-mobile-item", { opacity: 0, y: 10, stagger: 0.05, duration: 0.2 })
        .to(".nav-link-mobile", { opacity: 0, y: 10, stagger: 0.05, duration: 0.2 }, "-=0.1")
        .to(el, {
            y: '-100%',
            opacity: 0,
            duration: 0.5,
            ease: "power4.in"
        });
};

const handleScroll = () => {
    isScrolled.value = window.scrollY > 20;
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<style scoped>
.page-fade-enter-active,
.page-fade-leave-active {
    transition: opacity 0.6s cubic-bezier(0.4, 0, 0.2, 1), transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.page-fade-enter-from {
    opacity: 0;
    transform: scale(0.99);
}

.page-fade-leave-to {
    opacity: 0;
    transform: scale(1.01);
}

.v-enter-active .reveal-item {
    opacity: 1;
    transform: translateY(0);
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>
