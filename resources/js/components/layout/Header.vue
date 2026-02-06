<template>
  <header class="sticky top-0 z-30 flex items-center justify-between h-20 px-6 md:px-10 theme-bg-navbar backdrop-blur-md border-b theme-border">
    <div class="flex items-center gap-4">
        <button @click="$emit('toggle-sidebar')" class="lg:hidden p-2.5 theme-text-muted theme-bg-hover rounded-xl transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
            </svg>
        </button>
        <div class="hidden md:flex items-center gap-2 text-sm font-bold theme-text-dim uppercase tracking-widest">
            <span>Dashboard</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
            <span class="text-indigo-600 dark:text-indigo-400">Overview</span>
        </div>
    </div>

    <div class="flex items-center gap-2 md:gap-4">
      <!-- Search Component -->
      <div class="hidden lg:block flex-1 max-w-md mx-8">
        <GlobalSearch />
      </div>

      <!-- Theme Toggle -->
      <button 
        @click="toggleTheme" 
        class="w-10 h-10 flex items-center justify-center rounded-xl hover:theme-bg-hover transition-all relative group active:scale-90"
        :class="theme === 'dark' 
          ? 'bg-slate-800/50 border border-slate-700/50 shadow-inner shadow-black/20' 
          : 'bg-white border theme-border shadow-sm'"
        title="Toggle Theme"
      >
        <div class="relative w-5 h-5 overflow-hidden">
            <transition name="sun-moon">
                <svg v-if="theme === 'dark'" key="sun" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M16.95 16.95l.707.707M7.05 7.05l.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
                </svg>
                <svg v-else key="moon" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
            </transition>
        </div>
      </button>

      <!-- Notifications -->
      <button 
        class="w-10 h-10 flex items-center justify-center rounded-xl hover:theme-bg-hover transition-all relative group active:scale-90"
        :class="theme === 'dark' 
          ? 'bg-slate-800/50 border border-slate-700/50 shadow-inner shadow-black/20' 
          : 'bg-white border theme-border shadow-sm'"
      >
        <svg class="w-5 h-5 theme-text-dim group-hover:text-indigo-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        <span class="absolute top-1 right-2 flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
        </span>
      </button>

      <div class="h-8 w-px theme-border mx-2"></div>

      <!-- Profile Dropdown -->
      <div class="relative">
        <button 
          @click="isProfileOpen = !isProfileOpen"
          class="flex items-center gap-2 p-1.5 theme-bg-hover rounded-xl transition-all group"
          :class="{ 'theme-bg-element': isProfileOpen }"
        >
           <div class="w-8 h-8 rounded-lg overflow-hidden border theme-border group-hover:border-indigo-500/30 transition-all">
              <img :src="`https://ui-avatars.com/api/?name=${user?.name || 'User'}&background=6366f1&color=fff`" alt="User">
           </div>
           <span class="hidden sm:block text-xs font-bold theme-text-main group-hover:text-indigo-600 transition-colors uppercase tracking-wider">{{ user?.name }}</span>
           <svg 
             class="w-4 h-4 theme-text-dim transition-transform duration-300"
             :class="{ 'rotate-180': isProfileOpen }"
             fill="none" viewBox="0 0 24 24" stroke="currentColor"
           >
             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
           </svg>
        </button>

        <!-- Dropdown Menu -->
        <transition name="dropdown">
          <div 
            v-if="isProfileOpen"
            class="absolute right-0 mt-3 w-64 theme-bg-card border theme-border rounded-[1.5rem] shadow-2xl py-3 z-50 overflow-hidden"
          >
            <div class="px-5 py-4 border-b theme-border mb-2">
                <p class="text-sm font-black theme-text-main">{{ user?.name || 'Anonymous User' }}</p>
                <p class="text-[10px] theme-text-dim font-bold uppercase tracking-widest mt-1">{{ user?.email || 'N/A' }}</p>
            </div>
            
            <div class="px-2 space-y-1">
                <button class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl theme-text-muted theme-bg-hover hover:theme-text-main group">
                    <div class="w-8 h-8 rounded-lg bg-indigo-500/10 flex items-center justify-center group-hover:bg-indigo-500 group-hover:text-white transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <span class="text-sm font-bold">My Account</span>
                </button>
                <button class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl theme-text-muted theme-bg-hover hover:theme-text-main group">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <span class="text-sm font-bold">Privacy Settings</span>
                </button>
                <button class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl theme-text-muted theme-bg-hover hover:theme-text-main group">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 flex items-center justify-center group-hover:bg-amber-500 group-hover:text-white transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="text-sm font-bold">Billing & Plans</span>
                </button>
            </div>

            <div class="px-2 py-2 mt-2 border-t theme-border pt-3">
                <button 
                  @click="logout"
                  class="w-full flex items-center justify-start gap-3 px-4 py-2.5 bg-rose-500/10 hover:bg-rose-500 text-rose-600 hover:text-white rounded-xl transition-all group"
                >
                    <div class="w-8 h-8 rounded-lg bg-rose-500/20 flex items-center justify-center group-hover:bg-white group-hover:text-rose-500 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </div>
                    <span class="text-xs font-black uppercase tracking-widest">Logout</span>
                </button>
            </div>
          </div>
        </transition>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useTheme } from '../../composables/useTheme';
import { useAuth } from '../../composables/useAuth';
import GlobalSearch from '../common/GlobalSearch.vue';

const { theme, toggleTheme } = useTheme();
const { user, logout } = useAuth();
const isProfileOpen = ref(false);

const closeProfile = (e) => {
    if (!e.target.closest('.relative')) {
        isProfileOpen.value = false;
    }
};

onMounted(() => {
    window.addEventListener('click', closeProfile);
});

onUnmounted(() => {
    window.removeEventListener('click', closeProfile);
});

defineEmits(['toggle-sidebar']);
</script>

<style scoped>
.sun-moon-enter-active, .sun-moon-leave-active { transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); }
.sun-moon-enter-from { transform: translateY(30px) rotate(90deg); opacity: 0; }
.sun-moon-leave-to { transform: translateY(-30px) rotate(-90deg); opacity: 0; }

.dropdown-enter-active, .dropdown-leave-active { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.dropdown-enter-from { opacity: 0; transform: translateY(10px) scale(0.95); }
.dropdown-leave-to { opacity: 0; transform: translateY(10px) scale(0.95); }
</style>
