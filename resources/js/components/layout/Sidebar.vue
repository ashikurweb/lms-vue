<template>
  <aside 
    :class="[
      'fixed inset-y-0 left-0 z-50 w-72 theme-bg-sidebar backdrop-blur-xl border-r theme-border transition-transform duration-300 lg:translate-x-0',
      isOpen ? 'translate-x-0' : '-translate-x-full'
    ]"
  >
    <div class="flex flex-col h-full">
      <!-- Logo -->
      <div class="p-8 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
          </svg>
        </div>
        <span class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-slate-900 via-slate-700 to-slate-500 dark:from-white dark:via-slate-200 dark:to-slate-400 tracking-tight">EduNexus</span>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 px-4 space-y-1.5 overflow-y-auto custom-scrollbar pt-2">
        <div v-for="group in navGroups" :key="group.title" class="mb-6">
          <h3 class="px-4 text-[10px] font-bold theme-text-dim uppercase tracking-[0.2em] mb-3">{{ group.title }}</h3>
          <router-link 
            v-for="item in group.items" 
            :key="item.name"
            :to="item.to"
            @click="$emit('close')"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 group relative border border-transparent theme-bg-hover"
            :class="[$route.name === item.routeName ? 'bg-indigo-500/10 border-indigo-500/20 text-indigo-600 dark:text-indigo-400 font-bold' : 'theme-text-muted hover:theme-text-main']"
          >
            <div v-html="item.icon" class="w-5 h-5 transition-transform duration-300 group-hover:scale-110"></div>
            <span class="text-sm font-semibold tracking-tight">{{ item.name }}</span>
            <div v-if="$route.name === item.routeName" class="absolute right-3 w-1.5 h-1.5 bg-indigo-500 rounded-full shadow-lg shadow-indigo-500/50"></div>
          </router-link>
        </div>
      </nav>

      <!-- User Profile (Bottom) -->
      <div class="p-4 mt-auto border-t theme-border">
        <div class="flex items-center gap-3 p-3 rounded-2xl theme-bg-card border theme-border theme-shadow transition-all theme-border-hover theme-bg-hover group cursor-pointer">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-500 p-[2px]">
            <img src="https://ui-avatars.com/api/?name=Ashikur+Rahman&background=0b1120&color=fff" class="w-full h-full rounded-[10px] shadow-sm transform group-hover:scale-105 transition-transform" alt="User">
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-bold truncate theme-text-main">Ashikur Rahman</p>
            <p class="text-[10px] theme-text-dim font-bold uppercase tracking-wider">Premium Student</p>
          </div>
        </div>
      </div>
    </div>
  </aside>
</template>

<script setup>
defineProps({
  isOpen: Boolean
});

defineEmits(['close']);

const navGroups = [
  {
    title: 'Overview',
    items: [
      { name: 'Dashboard', to: '/', routeName: 'home', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>' },
      { name: 'My Courses', to: '/courses', routeName: 'courses', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>' },
      { name: 'Assignments', to: '/assignments', routeName: 'assignments', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>' },
    ]
  },
  {
    title: 'Learning Tools',
    items: [
      { name: 'Quizzes', to: '/quizzes', routeName: 'quizzes', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' },
      { name: 'Wishlist', to: '/wishlist', routeName: 'wishlist', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>' },
      { name: 'Study Notes', to: '/notes', routeName: 'notes', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>' },
    ]
  },
  {
    title: 'Personal',
    items: [
      { name: 'Certificates', to: '/certificates', routeName: 'certificates', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2v16z"/></svg>' },
      { name: 'Affiliate', to: '/affiliate', routeName: 'affiliate', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' },
      { name: 'Settings', to: '/settings', routeName: 'settings', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>' },
    ]
  }
];
</script>
