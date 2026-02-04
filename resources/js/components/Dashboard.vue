<template>
  <div class="space-y-10 animate-in fade-in slide-in-from-bottom-6 duration-1000 theme-text-main">
    <!-- Header Section -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
      <div class="space-y-2">
        <h1 class="text-4xl font-extrabold tracking-tight theme-text-main sm:text-5xl">
          Progress Overview <span class="text-indigo-600 dark:text-indigo-400">.</span>
        </h1>
        <p class="text-lg font-medium theme-text-muted max-w-2xl">
          Welcome back, Ashikur. You've completed <span class="text-indigo-600 dark:text-indigo-300 font-bold underline decoration-indigo-500/30 underline-offset-4">85%</span> of your weekly goals.
        </p>
      </div>
      <div class="flex items-center gap-4">
        <button class="px-6 py-3 theme-bg-card border theme-border theme-text-main rounded-2xl font-bold shadow-sm hover:border-indigo-500/50 transition-all active:scale-95">
          Schedule Session
        </button>
        <button class="px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-2xl font-bold shadow-lg shadow-indigo-600/20 transition-all active:scale-95 flex items-center gap-2">
          <span>Start Learning</span>
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
        </button>
      </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">
      <div v-for="stat in stats" :key="stat.label" 
           class="group p-8 rounded-[2rem] theme-bg-card border theme-border hover:border-indigo-500/50 transition-all duration-500 relative overflow-hidden shadow-sm hover:shadow-xl">
        <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/5 rounded-full blur-3xl -mr-16 -mt-16 group-hover:bg-indigo-500/10 transition-colors"></div>
        <div class="relative z-10 flex flex-col gap-6">
          <div :class="['w-14 h-14 rounded-2xl flex items-center justify-center shadow-inner transition-transform duration-500 group-hover:rotate-12', stat.bgClass]">
            <div v-html="stat.icon" class="w-7 h-7" :class="stat.iconColor"></div>
          </div>
          <div>
            <p class="text-[11px] font-bold theme-text-dim uppercase tracking-[0.2em] mb-1">{{ stat.label }}</p>
            <div class="flex items-baseline gap-2">
                <h3 class="text-3xl font-black theme-text-main">{{ stat.value }}</h3>
                <span :class="stat.trendUp ? 'text-emerald-500' : 'text-rose-500'" class="text-xs font-bold leading-none">
                  {{ stat.trendUp ? '↑' : '↓' }} {{ stat.trend }}
                </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-10">
      <!-- Left Column -->
      <div class="xl:col-span-8 space-y-10">
        <section>
          <div class="flex items-center justify-between mb-8">
            <h2 class="text-2xl font-bold theme-text-main flex items-center gap-3">
              <span class="w-2 h-8 bg-indigo-600 rounded-full shadow-lg shadow-indigo-500/50"></span>
              Enrolled Courses
            </h2>
            <button class="text-sm font-bold text-indigo-600 dark:text-indigo-400 hover:underline underline-offset-4">View Curriculum</button>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div v-for="course in recentCourses" :key="course.id" 
                 class="group theme-bg-card border theme-border rounded-[2.5rem] overflow-hidden hover:border-indigo-500/30 transition-all hover:shadow-2xl">
              <div class="h-56 relative overflow-hidden">
                <img :src="course.image" :alt="course.title" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/20 to-transparent"></div>
                <div class="absolute top-6 left-6 flex gap-2">
                  <span class="px-3 py-1 bg-white/10 backdrop-blur-md text-[10px] uppercase font-bold text-white rounded-lg border border-white/20 tracking-widest">{{ course.category }}</span>
                </div>
                <div class="absolute bottom-6 left-6 right-6">
                  <div class="flex items-center justify-between text-[11px] font-bold text-indigo-200 uppercase tracking-widest mb-3">
                    <span>Course Progress</span>
                    <span>{{ course.progress }}%</span>
                  </div>
                  <div class="w-full h-1.5 bg-white/20 rounded-full overflow-hidden backdrop-blur-sm">
                    <div class="h-full bg-gradient-to-r from-indigo-400 to-violet-400 rounded-full transition-all duration-1000" :style="{ width: course.progress + '%' }"></div>
                  </div>
                </div>
              </div>
              <div class="p-8 space-y-6">
                <h3 class="text-xl font-bold theme-text-main group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors leading-tight">
                    {{ course.title }}
                </h3>
                <div class="flex items-center justify-between py-4 border-y theme-border">
                    <div class="flex items-center gap-4">
                        <div class="flex -space-x-3">
                            <img v-for="i in 3" :key="i" :src="`https://i.pravatar.cc/100?u=${course.id}${i}`" class="w-8 h-8 rounded-full border-4 theme-border theme-bg-card">
                        </div>
                        <span class="text-[11px] font-bold theme-text-dim uppercase tracking-widest">+1.2k Students</span>
                    </div>
                </div>
                <div class="flex items-center justify-between gap-4 pt-2">
                     <div class="flex items-center gap-2 theme-text-muted text-xs font-bold uppercase tracking-wider">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>2h remaining</span>
                     </div>
                     <button class="px-6 py-2 theme-bg-element hover:theme-bg-hover theme-text-main text-xs font-black uppercase tracking-widest rounded-xl transition-all">Resume</button>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Chart Section -->
        <section class="p-10 rounded-[2.5rem] theme-bg-card border theme-border shadow-sm overflow-hidden relative">
            <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-500/5 blur-[100px] -mr-32 -mt-32"></div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 mb-12 relative z-10">
                <div>
                    <h2 class="text-2xl font-bold theme-text-main">Learning Performance</h2>
                    <p class="text-sm theme-text-muted font-medium">Daily study time and assignment scores.</p>
                </div>
                <div class="flex theme-bg-element p-1.5 rounded-2xl border theme-border">
                    <button class="px-6 py-2 text-xs font-black uppercase tracking-wider rounded-xl theme-bg-card text-indigo-600 dark:text-indigo-400 shadow-sm transition-all border theme-border">Week</button>
                    <button class="px-6 py-2 text-xs font-black uppercase tracking-wider rounded-xl theme-text-muted hover:theme-text-main transition-all">Month</button>
                </div>
            </div>
            <div class="h-80 flex items-end justify-between gap-4 px-2 relative z-10">
                <div v-for="(val, i) in [45, 60, 30, 85, 55, 75, 90]" :key="i" class="flex-1 flex flex-col items-center gap-6 group">
                    <div class="w-full relative flex flex-col items-center justify-end h-64">
                        <div class="w-full max-w-[48px] theme-bg-element rounded-2xl transition-all duration-500 group-hover:theme-bg-hover cursor-pointer overflow-hidden relative" :style="{ height: val + '%' }">
                            <div class="h-full w-full bg-gradient-to-t from-indigo-600 via-indigo-500 to-violet-400 rounded-2xl origin-bottom transition-transform duration-1000 scale-y-100 shadow-lg shadow-indigo-500/20"></div>
                        </div>
                    </div>
                    <span class="text-[11px] font-bold theme-text-dim uppercase tracking-[0.2em]">{{ ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'][i] }}</span>
                </div>
            </div>
        </section>
      </div>

      <!-- Right Column -->
      <div class="xl:col-span-4 space-y-10">
        <section class="p-8 rounded-[2.5rem] bg-gradient-to-br from-slate-900 to-indigo-900 dark:from-indigo-900 dark:to-violet-900 text-white overflow-hidden relative shadow-2xl group">
             <div class="absolute top-0 right-0 p-10 transform translate-x-1/4 -translate-y-1/4 opacity-10">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-48 h-48" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div class="relative z-10 space-y-8">
                <span class="px-4 py-1.5 bg-white/10 backdrop-blur-md rounded-full text-[10px] font-bold uppercase tracking-widest border border-white/20">Affiliate Stats</span>
                <div>
                    <h3 class="text-sm font-bold text-indigo-200 uppercase tracking-widest mb-2">Total Earnings</h3>
                    <div class="flex items-baseline gap-3">
                        <span class="text-5xl font-black tracking-tight">$4,245.00</span>
                    </div>
                </div>
                <button class="w-full py-4 bg-white text-indigo-900 rounded-[1.5rem] font-black uppercase text-xs tracking-[0.2em] shadow-xl hover:bg-indigo-50 transition-all">Withdraw</button>
            </div>
        </section>

        <!-- Mentors Card -->
        <section class="p-10 rounded-[2.5rem] theme-bg-card border theme-border shadow-sm">
            <h3 class="text-xl font-bold theme-text-main mb-8 flex items-center justify-between">
                <span>Top Mentors</span>
                <span class="text-xs font-bold text-indigo-600 uppercase tracking-widest cursor-pointer">All</span>
            </h3>
            <div class="space-y-6">
               <div v-for="mentor in mentors" :key="mentor.name" class="flex items-center gap-5 group cursor-pointer p-2 hover:theme-bg-hover rounded-2xl transition-all">
                  <div class="w-14 h-14 rounded-2xl overflow-hidden ring-4 theme-border group-hover:ring-indigo-500/30 transition-all shadow-lg">
                    <img :src="mentor.avatar" class="w-full h-full object-cover" :alt="mentor.name">
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-base font-black theme-text-main truncate group-hover:text-indigo-600 transition-colors">{{ mentor.name }}</p>
                    <p class="text-[10px] font-bold theme-text-dim truncate uppercase tracking-widest">{{ mentor.specialty }}</p>
                  </div>
               </div>
            </div>
        </section>

        <!-- Notes Card -->
        <section class="p-10 rounded-[2.5rem] theme-bg-card border theme-border shadow-sm relative overflow-hidden">
            <h3 class="text-xl font-bold theme-text-main mb-8">Recent Notes</h3>
            <div class="space-y-6">
              <div v-for="note in notes" :key="note.id" class="group p-6 rounded-3xl theme-bg-element border theme-border hover:theme-bg-hover hover:border-indigo-500/30 transition-all cursor-pointer shadow-sm">
                <div class="flex items-center justify-between mb-3">
                  <h4 class="text-sm font-black theme-text-main truncate group-hover:text-indigo-600">{{ note.title }}</h4>
                  <span class="text-[10px] theme-text-dim font-bold uppercase tracking-widest shrink-0 ml-4">{{ note.date }}</span>
                </div>
                <p class="text-xs theme-text-muted line-clamp-2 leading-relaxed font-medium">{{ note.content }}</p>
              </div>
            </div>
            <button class="w-full mt-8 py-4 border-2 border-dashed theme-border hover:border-indigo-500/50 hover:theme-bg-element theme-text-dim hover:theme-text-main text-[10px] font-black uppercase tracking-[0.2em] rounded-3xl transition-all">Go to Study Room</button>
        </section>
      </div>
    </div>
  </div>
</template>

<script setup>
const stats = [
  { label: 'Courses Enrolled', value: '12', trend: '15.2%', trendUp: true, icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>', bgClass: 'bg-indigo-500/10', iconColor: 'text-indigo-600 dark:text-indigo-400' },
  { label: 'Completed Certs', value: '04', trend: '2.4%', trendUp: true, icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138z"/></svg>', bgClass: 'bg-emerald-500/10', iconColor: 'text-emerald-600 dark:text-emerald-400' },
  { label: 'Affiliate Credit', value: '$4.2k', trend: '8.1%', trendUp: true, icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>', bgClass: 'bg-violet-500/10', iconColor: 'text-violet-600 dark:text-violet-400' },
  { label: 'Total Quiz Score', value: '92%', trend: '1.2%', trendUp: false, icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>', bgClass: 'bg-amber-500/10', iconColor: 'text-amber-600 dark:text-amber-400' }
];

const recentCourses = [
  { id: 1, title: 'Advanced Vue 3 Lifecycle & Performance Mastery', category: 'Architecture', progress: 75, image: 'https://images.unsplash.com/photo-1633356122544-f134324a6cee?auto=format&fit=crop&q=80&w=800' },
  { id: 2, title: 'Modern UI Design with Tailwind & Framer Motion', category: 'UI/UX Design', progress: 32, image: 'https://images.unsplash.com/photo-1558655146-d09347e92766?auto=format&fit=crop&q=80&w=800' }
];

const mentors = [
  { name: 'Sarah Drasner', specialty: 'Architecture Guru', avatar: 'https://i.pravatar.cc/150?u=sarah' },
  { name: 'Josh Comeau', specialty: 'CSS Specialist', avatar: 'https://i.pravatar.cc/150?u=josh' },
  { name: 'Adam Wathan', specialty: 'Utility Design', avatar: 'https://i.pravatar.cc/150?u=adam' }
];

const notes = [
  { id: 1, title: 'Composition API Best Practices', date: '2h ago', content: 'Always use script setup for better performance and DX. Ensure provide/inject is used...', tags: ['Vue', 'Best Practices'] },
  { id: 2, title: 'Tailwind V4 Breaking Changes', date: 'Yesterday', content: 'Tailwind 4 uses native CSS variables for theme customization. No more giant config files...', tags: ['CSS', 'Tailwind'] }
];
</script>
