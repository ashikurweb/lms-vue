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
      <!-- Search -->
      <div class="hidden md:flex relative group">
        <input type="text" placeholder="Search anything..." class="w-64 pl-10 pr-4 py-2 theme-bg-element border border-transparent theme-border rounded-xl text-sm transition-all focus:w-80 focus:theme-bg-card focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/5 theme-text-main shadow-inner">
        <svg class="absolute left-3 top-2.5 w-4 h-4 text-slate-400 group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      </div>

      <!-- Theme Toggle -->
      <button @click="toggleTheme" class="p-2.5 theme-text-muted theme-bg-hover rounded-xl transition-all relative group" title="Toggle Theme">
        <div class="relative w-5 h-5 overflow-hidden">
            <transition name="sun-moon">
                <svg v-if="theme === 'dark'" key="sun" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M16.95 16.95l.707.707M7.05 7.05l.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
                </svg>
                <svg v-else key="moon" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
            </transition>
        </div>
      </button>

      <!-- Notifications -->
      <button class="p-2.5 theme-text-muted theme-bg-hover rounded-xl transition-all relative">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
        <span class="absolute top-2.5 right-2.5 w-2 h-2 bg-rose-500 rounded-full border-2 theme-border"></span>
      </button>

      <div class="h-8 w-px theme-border mx-2"></div>

      <button class="flex items-center gap-2 p-1.5 theme-bg-hover rounded-xl transition-all group">
         <div class="w-8 h-8 rounded-lg overflow-hidden border theme-border group-hover:border-indigo-500/30 transition-all">
            <img src="https://ui-avatars.com/api/?name=Ashikur+Rahman&background=0b1120&color=fff" alt="User">
         </div>
         <span class="hidden sm:block text-xs font-bold theme-text-main group-hover:text-indigo-600 transition-colors uppercase tracking-wider">Profile</span>
      </button>
    </div>
  </header>
</template>

<script setup>
import { useTheme } from '../../composables/useTheme';

const { theme, toggleTheme } = useTheme();

defineEmits(['toggle-sidebar']);
</script>

<style scoped>
.sun-moon-enter-active, .sun-moon-leave-active { transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); }
.sun-moon-enter-from { transform: translateY(30px) rotate(90deg); opacity: 0; }
.sun-moon-leave-to { transform: translateY(-30px) rotate(-90deg); opacity: 0; }
</style>
