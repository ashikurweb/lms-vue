<template>
  <div class="min-h-screen theme-bg-main pb-20 animate-in fade-in duration-1000">
    <!-- Fluid Decoration Background -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
      <div class="absolute -top-[10%] -left-[5%] w-[40%] h-[40%] bg-indigo-500/5 blur-[120px] rounded-full animate-pulse"></div>
      <div class="absolute top-[20%] -right-[10%] w-[35%] h-[45%] bg-blue-500/5 blur-[100px] rounded-full animate-float"></div>
      <div class="absolute -bottom-[10%] left-[20%] w-[50%] h-[30%] bg-violet-500/5 blur-[120px] rounded-full"></div>
    </div>

    <!-- Header & Navigation -->
    <div class="relative pt-12 pb-12 px-8">
      <div class="max-w-7xl mx-auto flex flex-col md:flex-row md:items-center justify-between gap-8">
        <div class="space-y-4">
          <router-link 
            to="/admin/instructors" 
            class="inline-flex items-center gap-2 theme-text-dim hover:theme-text-main transition-all text-[10px] font-black uppercase tracking-[0.2em] group"
          >
            <div class="w-8 h-8 rounded-full theme-bg-element flex items-center justify-center border theme-border group-hover:border-indigo-500/50 transition-colors">
              <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </div>
            Registry Directory
          </router-link>
          <div>
            <h1 class="text-6xl font-black theme-text-main tracking-tighter leading-none mb-2">
              Instructor <span class="bg-linear-to-r from-indigo-500 to-violet-500 bg-clip-text text-transparent">Profile</span>
            </h1>
            <p v-if="instructor.id" class="text-lg theme-text-muted font-medium max-w-xl">Deep analysis and credentials for <span class="theme-text-main font-black">{{ instructor.name }}</span>.</p>
          </div>
        </div>

        <div class="flex items-center gap-4">
          <button 
            @click="toggleFeatured"
            class="w-14 h-14 rounded-2xl border-2 flex items-center justify-center transition-all active:scale-95 shadow-xl group"
            :class="instructor.is_featured ? 'bg-amber-500/10 border-amber-500/30 text-amber-500 shadow-amber-500/10' : 'theme-bg-card border-theme-border theme-text-dim hover:border-indigo-500/30'"
          >
            <svg class="w-6 h-6 transition-transform group-hover:scale-110" :fill="instructor.is_featured ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
          </button>
          <router-link 
            :to="{ name: 'instructors.edit', params: { id: instructor.id } }"
            class="hidden md:flex items-center gap-3 bg-indigo-600 hover:bg-indigo-500 text-white px-8 py-4 rounded-2xl font-black shadow-2xl shadow-indigo-600/30 active:scale-95 transition-all text-[10px] uppercase tracking-widest"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Edit Professional Data
          </router-link>
        </div>
      </div>
    </div>

    <!-- Main Content Bento Grid -->
    <div class="max-w-7xl mx-auto px-8 relative z-10">
      <div v-if="instructor.id" class="grid grid-cols-12 gap-10">
        
        <!-- Sidebar: Personal Info & Identity -->
        <div class="col-span-12 lg:col-span-4 space-y-10">
          <div class="theme-bg-card border theme-border rounded-[3rem] p-10 shadow-2xl relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/5 rounded-full blur-3xl transition-transform group-hover:scale-150 duration-700"></div>
            
            <div class="relative flex flex-col items-center text-center space-y-6">
              <div class="relative group">
                <div class="absolute -inset-4 bg-linear-to-tr from-indigo-500 to-violet-500 rounded-[3rem] blur-xl opacity-20 group-hover:opacity-40 transition duration-700"></div>
                <img 
                  v-if="instructor.avatar_url" 
                  :src="instructor.avatar_url" 
                  class="relative w-48 h-48 rounded-[2.5rem] object-cover border-4 theme-border shadow-2xl"
                >
                <div 
                  v-else
                  class="relative w-48 h-48 rounded-[2.5rem] flex items-center justify-center text-7xl font-black bg-indigo-600 text-white shadow-2xl"
                >
                  {{ instructor.name?.charAt(0) }}
                </div>
                <div 
                  class="absolute -bottom-4 left-1/2 -translate-x-1/2 px-6 py-2 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] border-2 theme-border shadow-2xl transition-all duration-500 group-hover:scale-110"
                  :class="statusColors[instructor.status]"
                >
                  {{ instructor.status || 'Active' }}
                </div>
              </div>

              <div class="space-y-2">
                <h2 class="text-3xl font-black theme-text-main tracking-tight">{{ instructor.name }}</h2>
                <p class="text-sm theme-text-dim font-bold tracking-tight italic opacity-80">{{ instructor.headline || 'Professional Instructor' }}</p>
              </div>

              <!-- Contact Quickinfo -->
              <div class="w-full space-y-3 pt-6">
                <div class="flex items-center gap-4 p-4 theme-bg-element rounded-2xl border theme-border">
                  <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-500 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                  </div>
                  <div class="text-left">
                    <p class="text-[9px] font-black theme-text-dim uppercase tracking-widest">Email Address</p>
                    <p class="text-xs font-black theme-text-main break-all">{{ instructor.email }}</p>
                  </div>
                </div>
                <div v-if="instructor.phone" class="flex items-center gap-4 p-4 theme-bg-element rounded-2xl border theme-border">
                  <div class="w-10 h-10 rounded-xl bg-violet-500/10 text-violet-500 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                  </div>
                  <div class="text-left">
                    <p class="text-[9px] font-black theme-text-dim uppercase tracking-widest">Phone / WhatsApp</p>
                    <p class="text-xs font-black theme-text-main">{{ instructor.phone }}</p>
                  </div>
                </div>
              </div>

              <!-- Social Footprint -->
              <div class="flex items-center justify-center gap-3 pt-4">
                 <a v-for="social in socialLinks" :key="social.icon" :href="social.link" target="_blank" 
                    class="w-14 h-14 rounded-2xl theme-bg-element border theme-border flex items-center justify-center theme-text-dim hover:theme-text-main hover:border-indigo-500/50 transition-all hover:scale-110 active:scale-95"
                    v-show="social.link"
                 >
                    <div class="w-6 h-6" v-html="social.svg"></div>
                 </a>
              </div>
            </div>
          </div>

          <!-- Financial Snapshot -->
          <div class="theme-bg-card border theme-border rounded-[3rem] p-10 shadow-xl space-y-8">
            <h4 class="text-[10px] font-black theme-text-dim uppercase tracking-[0.3em] flex items-center gap-3">
               <div class="w-1.5 h-1.5 rounded-full bg-indigo-500"></div>
               Financial Snapshot
            </h4>
            
            <div class="space-y-6">
              <div class="flex items-center justify-between p-6 theme-bg-element rounded-3xl border theme-border shadow-inner">
                 <div class="space-y-1">
                    <span class="text-[9px] font-black theme-text-dim uppercase tracking-widest">Revenue Generated</span>
                    <p class="text-3xl font-black theme-text-main tabular-nums">${{ instructor.total_earnings?.toLocaleString() || '0.00' }}</p>
                 </div>
                 <div class="p-3 bg-emerald-500/10 text-emerald-500 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                 </div>
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div class="p-5 theme-bg-element rounded-3xl border theme-border">
                  <span class="text-[8px] font-black theme-text-dim uppercase tracking-widest block mb-2">Platform Cut</span>
                  <p class="text-xl font-black theme-text-main">{{ instructor.commission_rate }}%</p>
                </div>
                <div class="p-5 theme-bg-element rounded-3xl border theme-border">
                  <span class="text-[8px] font-black theme-text-dim uppercase tracking-widest block mb-2">Net Earnings</span>
                  <p class="text-xl font-black text-amber-500">${{ instructor.pending_earnings?.toLocaleString() || '0' }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column: Biography, Expertise & Accolades -->
        <div class="col-span-12 lg:col-span-8 space-y-10">
          <!-- Performance Overview -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="theme-bg-card border theme-border rounded-[2.5rem] p-8 shadow-xl text-center group hover:-translate-y-1 transition-all">
              <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-500 flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
              </div>
              <p class="text-3xl font-black theme-text-main tracking-tighter">{{ instructor.total_students || 0 }}</p>
              <p class="text-[10px] font-black theme-text-dim uppercase tracking-[0.2em] mt-1">Global Students</p>
            </div>
            <div class="theme-bg-card border theme-border rounded-[2.5rem] p-8 shadow-xl text-center group hover:-translate-y-1 transition-all">
              <div class="w-12 h-12 rounded-2xl bg-violet-500/10 text-violet-500 flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
              </div>
              <p class="text-3xl font-black theme-text-main tracking-tighter">{{ instructor.total_courses || 0 }}</p>
              <p class="text-[10px] font-black theme-text-dim uppercase tracking-[0.2em] mt-1">Premium Courses</p>
            </div>
            <div class="theme-bg-card border theme-border rounded-[2.5rem] p-8 shadow-xl text-center group hover:-translate-y-1 transition-all">
              <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-500 flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
              </div>
              <p class="text-3xl font-black theme-text-main tracking-tighter">{{ instructor.rating || '0.0' }}</p>
              <p class="text-[10px] font-black theme-text-dim uppercase tracking-[0.2em] mt-1">Average Rating</p>
            </div>
          </div>

          <!-- Biography -->
          <div class="theme-bg-card border theme-border rounded-[3rem] p-12 shadow-2xl relative overflow-hidden group">
            <h3 class="text-xl font-black theme-text-main mb-8 flex items-center gap-4">
               <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-500 flex items-center justify-center">
                 <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
               </div>
               Biography & Journey
            </h3>
            <p class="text-lg theme-text-main leading-relaxed tracking-tight whitespace-pre-wrap font-medium">
              {{ instructor.bio || 'This professional educator is currently refining their profile details for the community.' }}
            </p>

            <div v-if="instructor.expertise" class="pt-10 mt-10 border-t theme-border">
              <h4 class="text-[10px] font-black theme-text-dim uppercase tracking-[0.2em] mb-6">Expertise Stack</h4>
              <div class="flex flex-wrap gap-3">
                <span 
                  v-for="skill in instructor.expertise.split(',')" 
                  :key="skill"
                  class="px-5 py-3 theme-bg-element border theme-border rounded-xl text-xs font-black theme-text-main uppercase tracking-widest hover:border-indigo-500/50 transition-all cursor-default"
                >
                  {{ skill.trim() }}
                </span>
              </div>
            </div>
          </div>

          <!-- Accolades Section -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
            <!-- Certifications -->
            <div class="theme-bg-card border theme-border rounded-[3rem] p-10 shadow-xl space-y-8">
              <h3 class="text-sm font-black theme-text-main uppercase tracking-[0.2em] flex items-center gap-3">
                 <div class="w-10 h-10 rounded-xl bg-violet-500/10 text-violet-500 flex items-center justify-center">
                   <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                 </div>
                 Certifications
              </h3>

              <div class="space-y-4">
                <div v-for="cert in instructor.certifications" :key="cert.name" class="p-5 theme-bg-element rounded-3xl border theme-border hover:border-violet-500/30 transition-all group">
                   <p class="text-sm font-black theme-text-main group-hover:text-violet-500 transition-colors">{{ cert.name }}</p>
                   <p class="text-[10px] font-black theme-text-dim uppercase tracking-widest mt-1">{{ cert.issuer }}</p>
                </div>
                <div v-if="!instructor.certifications?.length" class="py-12 text-center border-2 border-dashed theme-border rounded-3xl">
                   <p class="text-[10px] font-black theme-text-dim uppercase tracking-widest">No certifications listed</p>
                </div>
              </div>
            </div>

            <!-- Achievements -->
            <div class="theme-bg-card border theme-border rounded-[3rem] p-10 shadow-xl space-y-8">
              <h3 class="text-sm font-black theme-text-main uppercase tracking-[0.2em] flex items-center gap-3">
                 <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
                   <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                 </div>
                 Milestones
              </h3>

              <div class="space-y-4">
                <div v-for="ach in instructor.achievements" :key="ach" class="flex items-start gap-4 p-5 theme-bg-element rounded-3xl border theme-border">
                   <div class="w-8 h-8 rounded-full bg-emerald-500/10 text-emerald-500 flex items-center justify-center shrink-0 mt-0.5">
                      <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                   </div>
                   <p class="text-sm font-bold theme-text-main leading-relaxed tracking-tight">{{ ach }}</p>
                </div>
                <div v-if="!instructor.achievements?.length" class="py-12 text-center border-2 border-dashed theme-border rounded-3xl">
                   <p class="text-[10px] font-black theme-text-dim uppercase tracking-widest">No milestones recorded</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-else class="grid grid-cols-12 gap-10">
         <div class="col-span-12 lg:col-span-4 space-y-10">
            <div class="h-[600px] theme-bg-card border theme-border rounded-[3rem] animate-pulse"></div>
         </div>
         <div class="col-span-12 lg:col-span-8 space-y-10">
            <div class="grid grid-cols-3 gap-6">
              <div v-for="i in 3" :key="i" class="h-40 theme-bg-card border theme-border rounded-[2.5rem] animate-pulse"></div>
            </div>
            <div class="h-96 theme-bg-card border theme-border rounded-[3rem] animate-pulse"></div>
         </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { instructorService } from '../../../services/instructorService';
import { useToast } from '../../../composables/useToast';

const route = useRoute();
const router = useRouter();
const toast = useToast();
const instructor = ref({});

const statusColors = {
  pending: 'bg-amber-500 text-white shadow-amber-500/20',
  approved: 'bg-emerald-500 text-white shadow-emerald-500/20',
  rejected: 'bg-rose-500 text-white shadow-rose-500/20',
  suspended: 'bg-slate-700 text-white shadow-slate-700/20'
};

const socialLinks = computed(() => [
  { icon: 'website', link: instructor.value.website, svg: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9-3-9m-9 9a9 9 0 019-9"/></svg>' },
  { icon: 'linkedin', link: instructor.value.linkedin, svg: '<svg fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>' },
  { icon: 'twitter', link: instructor.value.twitter, svg: '<svg fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.84 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>' },
  { icon: 'github', link: instructor.value.github, svg: '<svg fill="currentColor" viewBox="0 0 24 24"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>' },
]);

const fetchInstructor = async () => {
  try {
    const data = await instructorService.show(route.params.id);
    instructor.value = data.data;
  } catch (error) {
    toast.error('Instructor records not found');
    router.push({ name: 'instructors' });
  }
};

const toggleFeatured = async () => {
  try {
    await instructorService.toggleFeatured(instructor.value.id);
    instructor.value.is_featured = !instructor.value.is_featured;
    toast.success(instructor.value.is_featured ? 'Elevated to Featured status' : 'Removed from Featured listing');
  } catch (error) {
    toast.error('System synchronization failed');
  }
};

onMounted(fetchInstructor);
</script>

<style scoped>
.animate-float {
  animation: float 6s ease-in-out infinite;
}

@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-20px); }
}

.active-step {
  animation: pulse-ring 2s infinite;
}

@keyframes pulse-ring {
  0% { box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.4); }
  70% { box-shadow: 0 0 0 15px rgba(99, 102, 241, 0); }
  100% { box-shadow: 0 0 0 0 rgba(99, 102, 241, 0); }
}

.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(99, 102, 241, 0.2);
  border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(99, 102, 241, 0.4);
}
</style>
