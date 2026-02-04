<template>
  <div class="min-h-screen theme-bg-main theme-text-main font-sans selection:bg-indigo-500/30 transition-colors duration-500">
    <!-- Sidebar Component -->
    <Sidebar :isOpen="isSidebarOpen" @close="isSidebarOpen = false" />

    <!-- Overlay for Mobile -->
    <transition name="fade">
        <div v-if="isSidebarOpen" @click="isSidebarOpen = false" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 lg:hidden"></div>
    </transition>

    <!-- Main Content -->
    <div class="lg:pl-72 flex flex-col min-h-screen">
      <!-- Header Component -->
      <Header @toggle-sidebar="isSidebarOpen = true" />

      <!-- Main Content Area -->
      <main class="flex-1 p-6 md:p-10 max-w-[1600px] mx-auto w-full">
        <router-view v-slot="{ Component }">
          <transition name="page" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import Sidebar from '../components/layout/Sidebar.vue';
import Header from '../components/layout/Header.vue';

const isSidebarOpen = ref(false);
</script>

<style>
/* Page Transitions */
.page-enter-active, .page-leave-active { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.page-enter-from { opacity: 0; transform: translateY(15px); }
.page-leave-to { opacity: 0; transform: translateY(-15px); }

/* Global Fade */
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
