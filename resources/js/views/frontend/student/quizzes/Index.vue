<template>
    <div class="min-h-screen pt-24 pb-12 px-4 sm:px-6 lg:px-8 theme-bg-main transition-colors duration-500">
        <div class="max-w-6xl mx-auto space-y-12">
            <!-- Premium Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div>
                    <h1 class="text-4xl font-black theme-text-main tracking-tighter leading-none mb-4">My Quiz Lab</h1>
                    <p class="theme-text-dim font-bold text-lg max-w-xl">Track your knowledge progression, review past
                        attempts, and perfect your skills.</p>
                </div>

                <div
                    class="flex items-center gap-4 bg-white/5 backdrop-blur-xl border theme-border rounded-3xl p-2 h-fit">
                    <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id"
                        class="px-6 py-3 rounded-2xl font-black text-xs uppercase tracking-widest transition-all"
                        :class="activeTab === tab.id ? 'bg-indigo-600 text-white shadow-xl shadow-indigo-600/20' : 'theme-text-dim hover:theme-text-main'">
                        {{ tab.name }}
                    </button>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div v-for="stat in stats" :key="stat.label"
                    class="theme-bg-card border theme-border rounded-[2.5rem] p-8 group hover:border-indigo-500/30 transition-all duration-500 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-black theme-text-dim uppercase tracking-[0.2em] mb-2 block">{{
                            stat.label }}</span>
                        <span class="text-3xl font-black theme-text-main tracking-tight">{{ stat.value }}</span>
                    </div>
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center transition-all duration-500 group-hover:scale-110"
                        :class="stat.bg">
                        <div v-html="stat.icon" class="w-6 h-6" :class="stat.color"></div>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div v-if="loading" class="space-y-6">
                <div v-for="i in 3" :key="i"
                    class="h-32 w-full animate-pulse theme-bg-card border theme-border rounded-[2rem]"></div>
            </div>
            <div v-else class="space-y-6">
                <template v-if="activeTab === 'available'">
                    <div v-if="availableQuizzes.length === 0" class="py-20 text-center space-y-4">
                        <div class="w-20 h-20 bg-white/5 rounded-3xl mx-auto flex items-center justify-center">
                            <svg class="w-10 h-10 theme-text-dim" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold theme-text-main">No Pending Quizzes</h3>
                        <p class="theme-text-dim max-w-sm mx-auto">You've cleared all available quizzes in your enrolled
                            courses. Great job!</p>
                    </div>
                    <div v-for="quiz in availableQuizzes" :key="quiz.id"
                        class="theme-bg-card border theme-border rounded-[2rem] p-8 flex flex-col md:flex-row md:items-center justify-between gap-8 group hover:border-indigo-500/50 transition-all duration-500 shadow-sm hover:shadow-2xl hover:shadow-indigo-500/5">
                        <div class="flex-1 space-y-3">
                            <div class="flex items-center gap-3">
                                <span
                                    class="px-3 py-1 bg-indigo-500/10 text-indigo-500 text-[10px] font-black uppercase tracking-widest rounded-full border border-indigo-500/20">{{
                                        quiz.course?.title || 'Course' }}</span>
                                <span v-if="quiz.is_required"
                                    class="text-[10px] font-bold text-rose-400 uppercase tracking-widest flex items-center gap-1.5">
                                    <div class="w-1.5 h-1.5 rounded-full bg-rose-500"></div> Required
                                </span>
                            </div>
                            <h3
                                class="text-2xl font-black theme-text-main tracking-tight group-hover:text-indigo-500 transition-colors">
                                {{ quiz.title }}</h3>
                            <div class="flex flex-wrap items-center gap-6 text-sm font-bold theme-text-dim">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ quiz.time_limit ? quiz.time_limit + ' mins' : 'No time limit' }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ quiz.total_questions }} Questions
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Pass: {{ quiz.passing_score }}%
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <router-link :to="{ name: 'student.quizzes.take', params: { uuid: quiz.uuid } }"
                                class="px-8 py-4 bg-indigo-600 hover:bg-indigo-500 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-indigo-600/20 active:scale-95 transition-all flex items-center gap-3">
                                Start Challenge
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </router-link>
                        </div>
                    </div>
                </template>

                <template v-else-if="activeTab === 'attempts'">
                    <div v-if="attempts.length === 0" class="py-20 text-center space-y-4">
                        <div class="w-20 h-20 bg-white/5 rounded-3xl mx-auto flex items-center justify-center">
                            <svg class="w-10 h-10 theme-text-dim" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold theme-text-main">No Attempts Yet</h2>
                        <p class="theme-text-dim">You haven't completed any quizzes yet. Start learning to see your
                            results here.</p>
                    </div>
                    <div v-for="attempt in attempts" :key="attempt.id"
                        class="theme-bg-card border theme-border rounded-[2rem] p-8 flex flex-col md:flex-row md:items-center justify-between gap-8 group hover:border-white/20 transition-all duration-500">
                        <div class="flex-1 space-y-2">
                            <div class="flex items-center gap-3">
                                <span
                                    class="px-2 py-0.5 bg-white/5 theme-text-dim text-[9px] font-black uppercase tracking-widest rounded-lg border theme-border">Attempt
                                    #{{ attempt.attempt_number }}</span>
                                <span class="theme-text-muted text-xs font-bold">{{ formatDate(attempt.submitted_at)
                                }}</span>
                            </div>
                            <h3 class="text-xl font-bold theme-text-main tracking-tight">{{ attempt.quiz?.title }}</h3>
                            <div class="flex items-center gap-6">
                                <div class="flex flex-col">
                                    <span
                                        class="text-[9px] font-black theme-text-dim uppercase tracking-widest">Score</span>
                                    <span class="text-lg font-black"
                                        :class="attempt.is_passed ? 'text-emerald-500' : 'text-rose-500'">{{
                                            attempt.percentage }}%</span>
                                </div>
                                <div class="w-[1px] h-8 bg-white/10"></div>
                                <div class="flex flex-col">
                                    <span
                                        class="text-[9px] font-black theme-text-dim uppercase tracking-widest">Correct</span>
                                    <span class="text-lg font-black theme-text-main">{{ attempt.correct_answers }}/{{
                                        attempt.total_questions }}</span>
                                </div>
                                <div class="w-[1px] h-8 bg-white/10"></div>
                                <div class="flex flex-col">
                                    <span class="text-[9px] font-black theme-text-dim uppercase tracking-widest">Time
                                        Spent</span>
                                    <span class="text-lg font-black theme-text-main">{{
                                        formatSpentTime(attempt.time_spent) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="px-5 py-2 rounded-xl font-black text-[10px] uppercase tracking-widest border"
                                :class="attempt.is_passed ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-500' : 'bg-rose-500/10 border-rose-500/20 text-rose-500'">
                                {{ attempt.is_passed ? 'Passed' : 'Failed' }}
                            </div>
                            <button
                                class="w-12 h-12 rounded-xl bg-white/5 border theme-border flex items-center justify-center hover:bg-white/10 transition-colors">
                                <svg class="w-5 h-5 theme-text-main" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { quizService } from '../../../../services/quizService';
import { useToast } from '../../../../composables/useToast';

const toast = useToast();
const loading = ref(true);
const activeTab = ref('available');
const availableQuizzes = ref([]);
const attempts = ref([]);

const tabs = [
    { id: 'available', name: 'Available Quizzes' },
    { id: 'attempts', name: 'My History' }
];

const fetchQuizzes = async () => {
    loading.value = true;
    try {
        const [availableRes, attemptRes] = await Promise.all([
            quizService.availableQuizzes(),
            quizService.myQuizzes()
        ]);

        availableQuizzes.value = availableRes.data.data || [];
        attempts.value = attemptRes.data.data || [];
    } catch (error) {
        toast.error('Failed to fetch quizzes');
    } finally {
        loading.value = false;
    }
};

const stats = computed(() => [
    {
        label: 'Total Attempts',
        value: attempts.value.length,
        icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        bg: 'bg-indigo-500/10',
        color: 'text-indigo-500'
    },
    {
        label: 'Success Rate',
        value: attempts.value.length > 0 ? (attempts.value.filter(a => a.is_passed).length / attempts.value.length * 100).toFixed(0) + '%' : '0%',
        icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        bg: 'bg-emerald-500/10',
        color: 'text-emerald-500'
    },
    {
        label: 'Avg. Score',
        value: attempts.value.length > 0 ? (attempts.value.reduce((acc, curr) => acc + parseFloat(curr.percentage), 0) / attempts.value.length).toFixed(1) + '%' : '0%',
        icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>',
        bg: 'bg-violet-500/10',
        color: 'text-violet-500'
    }
]);

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString();
};

const formatSpentTime = (seconds) => {
    const m = Math.floor(seconds / 60);
    const s = seconds % 60;
    return `${m}m ${s}s`;
};

onMounted(() => {
    fetchQuizzes();
});
</script>

<style scoped>
.theme-shadow {
    box-shadow: 0 20px 50px -20px rgba(0, 0, 0, 0.5);
}
</style>
