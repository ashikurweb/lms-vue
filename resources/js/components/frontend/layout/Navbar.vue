<template>
  <header 
    class="fixed top-6 left-1/2 -translate-x-1/2 z-[150] w-[92%] max-w-[1600px] transition-all duration-700"
  >
    <div 
      class="flex justify-between items-center px-4 md:px-10 py-4 md:py-5 rounded-[2.5rem] theme-border border backdrop-blur-2xl transition-all duration-500"
      :class="[
        isScrolled 
          ? 'theme-bg-navbar shadow-2xl shadow-indigo-500/10 scale-95 md:scale-100' 
          : 'bg-white/10 border-white/10 scale-100'
      ]"
    >
      <!-- Brand -->
      <router-link to="/" class="flex items-center gap-2 md:gap-3 group shrink-0">
        <div class="w-8 h-8 md:w-10 md:h-10 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-600/20 group-hover:rotate-12 transition-all">
           <span class="text-white font-black text-lg md:text-xl">N</span>
        </div>
        <div class="flex flex-col -space-y-1">
          <span class="text-lg md:text-xl font-black theme-text-main tracking-tighter uppercase italic leading-none">Nexus<span class="text-indigo-600 not-italic">LMS</span></span>
          <span class="text-[8px] font-black theme-text-dim uppercase tracking-[0.3em] pl-1 hidden sm:block">Engineering</span>
        </div>
      </router-link>

      <!-- Minimal Center Nav -->
      <nav class="hidden lg:flex items-center gap-10 px-10 border-x theme-border mx-6">
        <router-link 
          v-for="item in navItems" 
          :key="item.name" 
          :to="item.path"
          class="text-[10px] font-black theme-text-dim hover:theme-text-main uppercase tracking-widest transition-all relative group"
        >
          {{ item.name }}
          <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-indigo-600 transition-all duration-300 group-hover:w-full"></span>
        </router-link>
      </nav>

      <!-- Actions -->
      <div class="flex items-center gap-2 md:gap-5">
        <button @click="toggleTheme" class="w-9 h-9 md:w-10 md:h-10 flex items-center justify-center rounded-xl hover:theme-bg-hover transition-all border theme-border theme-bg-element shadow-sm shrink-0">
          <svg v-if="theme === 'dark'" class="w-4 h-4 md:w-5 md:h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M16.95 16.95l.707.707M7.05 7.05l.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
          </svg>
          <svg v-else class="w-4 h-4 md:w-5 md:h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
          </svg>
        </button>
        
        <router-link v-if="!isAuthenticated" to="/login" class="text-[10px] font-black theme-text-main uppercase tracking-widest hover:text-indigo-600 transition-colors hidden md:block text-nowrap">Login</router-link>
        
        <!-- Profile Dropdown (Authenticated) -->
        <div v-else class="relative hidden md:block">
          <button 
            @click="isProfileOpen = !isProfileOpen"
            class="flex items-center gap-2 p-1.5 theme-bg-hover rounded-2xl transition-all group border theme-border"
            :class="{ 'theme-bg-element shadow-lg': isProfileOpen }"
          >
             <div class="w-7 h-7 rounded-xl overflow-hidden border theme-border group-hover:border-indigo-500/30 transition-all">
                <img :src="`https://ui-avatars.com/api/?name=${user?.name || 'User'}&background=6366f1&color=fff`" alt="User" class="w-full h-full object-cover">
             </div>
             <span class="text-[10px] font-black theme-text-main group-hover:text-indigo-600 transition-colors uppercase tracking-widest">{{ user?.name?.split(' ')[0] || 'User' }}</span>
             <svg 
               class="w-3.5 h-3.5 theme-text-dim transition-transform duration-300"
               :class="{ 'rotate-180': isProfileOpen }"
               fill="none" viewBox="0 0 24 24" stroke="currentColor"
             >
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
             </svg>
          </button>

          <!-- Dropdown Menu -->
          <transition name="dropdown">
            <div 
              v-if="isProfileOpen"
              class="absolute right-0 mt-4 w-64 theme-bg-card border theme-border rounded-[2rem] shadow-2xl py-3 z-[200] overflow-hidden"
            >
              <div class="px-5 py-4 border-b theme-border mb-2">
                  <p class="text-sm font-black theme-text-main">{{ user?.name || 'Nexus User' }}</p>
                  <p class="text-[9px] theme-text-dim font-bold uppercase tracking-widest mt-1 opacity-70">{{ user?.email }}</p>
              </div>
              
              <div class="px-2 space-y-1">
                  <!-- Conditional Dashboard Link based on role -->
                  <router-link 
                    v-if="isAdmin"
                    :to="{ name: 'dashboard' }"
                    class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl theme-text-muted theme-bg-hover hover:theme-text-main group"
                    @click="isProfileOpen = false"
                  >
                      <div class="w-8 h-8 rounded-lg bg-indigo-500/10 flex items-center justify-center group-hover:bg-indigo-500 group-hover:text-white transition-colors">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                      </div>
                      <span class="text-xs font-black uppercase tracking-widest">Dashboard</span>
                  </router-link>

                  <router-link 
                    :to="isAdmin ? { name: 'admin.profile' } : { name: 'user.profile', params: { username: user.username } }"
                    class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl theme-text-muted theme-bg-hover hover:theme-text-main group"
                    @click="isProfileOpen = false"
                  >
                      <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                      </div>
                      <span class="text-xs font-black uppercase tracking-widest">My Account</span>
                  </router-link>

                  <button class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl theme-text-muted theme-bg-hover hover:theme-text-main group">
                      <div class="w-8 h-8 rounded-lg bg-amber-500/10 flex items-center justify-center group-hover:bg-amber-500 group-hover:text-white transition-colors">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                      </div>
                      <span class="text-xs font-black uppercase tracking-widest">Billing</span>
                  </button>
              </div>

              <div class="px-2 py-2 mt-2 border-t theme-border pt-3">
                  <button 
                    @click="handleLogout"
                    class="w-full flex items-center justify-start gap-3 px-4 py-2.5 bg-rose-500/10 hover:bg-rose-500 text-rose-600 hover:text-white rounded-xl transition-all group"
                  >
                      <div class="w-8 h-8 rounded-lg bg-rose-500/20 flex items-center justify-center group-hover:bg-white group-hover:text-rose-500 transition-colors">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                      </div>
                      <span class="text-[10px] font-black uppercase tracking-widest">Logout System</span>
                  </button>
              </div>
            </div>
          </transition>
        </div>
        
        <router-link 
          to="/pricing" 
          class="hidden sm:flex px-5 md:px-7 py-2.5 md:py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-[1.2rem] font-black text-[10px] uppercase tracking-widest shadow-lg shadow-indigo-600/20 active:scale-95 transition-all items-center gap-2 group/sub shrink-0"
        >
          <svg class="w-4 h-4 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
          </svg>
          {{ isAuthenticated ? 'Pro Protocol' : 'Subscribe' }}
        </router-link>
        
        <!-- Mobile Menu Trigger -->
        <button 
          @click="$emit('toggle-mobile-menu')" 
          class="lg:hidden w-9 h-9 md:w-10 md:h-10 flex items-center justify-center rounded-xl theme-bg-element border theme-border shrink-0 transition-all active:scale-95"
          aria-label="Toggle Menu"
        >
          <div class="relative w-5 h-5 md:w-6 md:h-6">
            <transition name="icon-pop">
              <svg v-if="!isMobileMenuOpen" class="absolute inset-0 w-full h-full text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16m-7 6h7" />
              </svg>
              <svg v-else class="absolute inset-0 w-full h-full text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </transition>
          </div>
        </button>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useTheme } from '../../../composables/useTheme';
import { useAuth } from '../../../composables/useAuth';

defineProps({
  isScrolled: Boolean,
  isMobileMenuOpen: Boolean,
  navItems: Array
});

defineEmits(['toggle-mobile-menu']);
const { theme, toggleTheme } = useTheme();
const { isAuthenticated, user, logout } = useAuth();

const isProfileOpen = ref(false);
const isAdmin = computed(() => user.value?.roles?.some(role => ['admin', 'super-admin'].includes(role.name)));

const closeProfile = (e) => {
    if (!e.target.closest('.relative')) {
        isProfileOpen.value = false;
    }
};

const handleLogout = async () => {
    isProfileOpen.value = false;
    await logout();
};

onMounted(() => {
    window.addEventListener('click', closeProfile);
});

onUnmounted(() => {
    window.removeEventListener('click', closeProfile);
});
</script>

<style scoped>
.icon-pop-enter-active, .icon-pop-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.icon-pop-enter-from { opacity: 0; transform: rotate(-90deg) scale(0.5); }
.icon-pop-leave-to { opacity: 0; transform: rotate(90deg) scale(0.5); }

.dropdown-enter-active, .dropdown-leave-active { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
.dropdown-enter-from { opacity: 0; transform: translateY(15px) scale(0.9) rotateX(-15deg); }
.dropdown-leave-to { opacity: 0; transform: translateY(15px) scale(0.9); }
</style>
