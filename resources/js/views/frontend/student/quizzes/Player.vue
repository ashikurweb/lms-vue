<template>
    <div
        class="min-h-screen bg-slate-950 text-white font-sans selection:bg-indigo-500/30 overflow-hidden flex flex-col">
        <!-- Quiz Pro Header -->
        <header
            class="h-20 border-b border-white/10 bg-slate-900/50 backdrop-blur-xl px-8 flex items-center justify-between sticky top-0 z-50">
            <div class="flex items-center gap-6">
                <button @click="confirmExit"
                    class="w-12 h-12 rounded-2xl bg-white/5 hover:bg-white/10 border border-white/10 flex items-center justify-center transition-all group">
                    <svg class="w-5 h-5 text-white/50 group-hover:text-white transition-colors" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <div class="h-8 w-[1px] bg-white/10"></div>
                <div>
                    <h1 class="text-lg font-black tracking-tight flex items-center gap-2">
                        <span>{{ quiz?.title }}</span>
                        <span v-if="quiz?.is_required"
                            class="text-[10px] uppercase tracking-[0.2em] px-2 py-0.5 rounded-full bg-indigo-500/20 text-indigo-400 border border-indigo-500/30">Required</span>
                    </h1>
                    <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest mt-0.5">Attempt #{{
                        attempt?.attempt_number }} • Passing Score: {{ quiz?.passing_score }}%</p>
                </div>
            </div>

            <!-- Timer & Progress -->
            <div class="flex items-center gap-8">
                <!-- Timer -->
                <div v-if="quiz?.time_limit"
                    class="flex items-center gap-4 bg-white/5 border border-white/10 rounded-2xl px-5 py-2.5">
                    <div class="flex flex-col items-end">
                        <span class="text-[10px] font-black text-white/40 uppercase tracking-widest">Time
                            Remaining</span>
                        <span class="text-lg font-black tabular-nums"
                            :class="timeRemaining < 60 ? 'text-rose-500 animate-pulse' : 'text-indigo-400'">
                            {{ formatTime(timeRemaining) }}
                        </span>
                    </div>
                    <div
                        class="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Progress Ring -->
                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <span
                            class="text-[10px] font-black text-white/40 uppercase tracking-widest block">Progress</span>
                        <span class="text-lg font-black">{{ answeredCount }}/{{ questions.length }}</span>
                    </div>
                    <div class="relative w-12 h-12">
                        <svg class="w-full h-full transform -rotate-90">
                            <circle cx="24" cy="24" r="20" stroke="currentColor" stroke-width="4" fill="transparent"
                                class="text-white/5" />
                            <circle cx="24" cy="24" r="20" stroke="currentColor" stroke-width="4" fill="transparent"
                                class="text-indigo-500 transition-all duration-500" :stroke-dasharray="2 * Math.PI * 20"
                                :stroke-dashoffset="2 * Math.PI * 20 * (1 - (answeredCount / questions.length))" />
                        </svg>
                    </div>
                </div>
            </div>
        </header>

        <div class="flex-1 flex overflow-hidden">
            <!-- Questions Sidebar -->
            <aside
                class="w-80 border-r border-white/10 bg-slate-900/30 overflow-y-auto custom-scrollbar p-6 hidden lg:block">
                <h3 class="text-[10px] font-black text-white/40 uppercase tracking-[0.2em] mb-6">Question Map</h3>
                <div class="grid grid-cols-4 gap-3">
                    <button v-for="(q, idx) in questions" :key="q.id" @click="currentQuestionIndex = idx"
                        class="h-12 rounded-xl flex items-center justify-center text-sm font-black transition-all border transition-all duration-300"
                        :class="[
                            currentQuestionIndex === idx ? 'bg-indigo-600 border-indigo-500 shadow-lg shadow-indigo-600/20 text-white scale-110' :
                                answers[q.id] ? 'bg-emerald-500/20 border-emerald-500/30 text-emerald-400' :
                                    'bg-white/5 border-white/10 text-white/40 hover:border-white/30'
                        ]">
                        {{ idx + 1 }}
                    </button>
                </div>

                <div class="mt-8 pt-8 border-t border-white/5 space-y-4">
                    <div class="flex items-center gap-3 text-xs font-bold text-white/40">
                        <div class="w-2 h-2 rounded-full bg-indigo-500"></div>
                        <span>Current Question</span>
                    </div>
                    <div class="flex items-center gap-3 text-xs font-bold text-white/40">
                        <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                        <span>Answered</span>
                    </div>
                    <div class="flex items-center gap-3 text-xs font-bold text-white/40">
                        <div class="w-2 h-2 rounded-full bg-white/10"></div>
                        <span>Not Visited</span>
                    </div>
                </div>
            </aside>

            <!-- Main Question Area -->
            <main class="flex-1 overflow-y-auto custom-scrollbar p-6 lg:p-12 relative">
                <transition name="question" mode="out-in">
                    <div v-if="currentQuestion" :key="currentQuestion.id" class="max-w-3xl mx-auto space-y-12 pb-24">
                        <!-- Question Title -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-4">
                                <span
                                    class="px-3 py-1 bg-white/5 border border-white/10 rounded-lg text-xs font-bold text-indigo-400">Question
                                    {{ currentQuestionIndex + 1 }}</span>
                                <span class="text-xs font-bold text-white/30 uppercase tracking-widest">{{
                                    currentQuestion.points }} Points</span>
                                <span v-if="currentQuestion.is_required"
                                    class="text-xs font-bold text-rose-400 uppercase tracking-widest">* Required</span>
                            </div>
                            <h2 class="text-3xl font-black theme-text-main leading-tight tracking-tight">
                                {{ currentQuestion.question }}
                            </h2>
                            <div v-if="currentQuestion.image"
                                class="rounded-3xl border border-white/10 overflow-hidden bg-white/5 p-2">
                                <img :src="currentQuestion.image"
                                    class="w-full h-auto rounded-2xl max-h-[400px] object-cover" alt="Question">
                            </div>
                        </div>

                        <!-- Options Rendering -->
                        <div class="space-y-4">
                            <QuizQuestionComponent :question="currentQuestion" v-model="answers[currentQuestion.id]"
                                @update:modelValue="saveAnswer" />
                        </div>
                    </div>
                    <div v-else-if="loading" class="flex flex-col items-center justify-center h-full gap-4">
                        <div
                            class="w-16 h-16 border-4 border-indigo-600/20 border-t-indigo-600 rounded-full animate-spin">
                        </div>
                        <p class="text-white/40 font-bold uppercase tracking-widest text-xs">Initializing Secure
                            Environment...</p>
                    </div>
                </transition>

                <!-- Navigation Controls -->
                <div
                    class="fixed bottom-0 left-0 lg:left-80 right-0 h-24 bg-slate-950/80 backdrop-blur-xl border-t border-white/10 px-6 lg:px-12 flex items-center justify-between z-40">
                    <button @click="prevQuestion" :disabled="currentQuestionIndex === 0"
                        class="flex items-center gap-3 px-6 py-4 rounded-2xl bg-white/5 border border-white/10 font-black text-xs uppercase tracking-widest transition-all hover:bg-white/10 active:scale-95 disabled:opacity-20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Previous
                    </button>

                    <div class="flex items-center gap-4">
                        <button @click="submitQuiz"
                            class="px-8 py-4 bg-emerald-600 hover:bg-emerald-500 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-emerald-500/20 active:scale-95 transition-all flex items-center gap-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Finish & Submit
                        </button>

                        <button @click="nextQuestion" v-if="currentQuestionIndex < questions.length - 1"
                            class="flex items-center gap-3 px-8 py-4 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white border border-indigo-500/50 font-black text-xs uppercase tracking-widest shadow-xl shadow-indigo-600/20 active:scale-95 transition-all">
                            Next
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </main>
        </div>

        <!-- Modals -->
        <ActionDialog ref="exitModal" title="Exit Quiz?"
            message="Are you sure you want to exit? Your progress will be saved, but the timer will continue to run."
            confirm-text="Exit Anyway" cancel-text="Keep Taking" @confirm="exitQuiz" />

        <ActionDialog ref="submitModal" title="Complete Quiz?"
            :message="`You have answered ${answeredCount} out of ${questions.length} questions. Once you submit, you can't change your answers.`"
            confirm-text="Submit Now" @confirm="processSubmission" />
    </div>
</template>

<script setup>
import { ref, onMounted, computed, onUnmounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { quizService } from '../../../../services/quizService';
import { useToast } from '../../../../composables/useToast';
import QuizQuestionComponent from '../../../../components/student/QuizQuestion.vue';
import ActionDialog from '../../../../components/common/ActionDialog.vue';

const route = useRoute();
const router = useRouter();
const toast = useToast();

const loading = ref(true);
const quiz = ref(null);
const attempt = ref(null);
const questions = ref([]);
const currentQuestionIndex = ref(0);
const answers = ref({});
const timeRemaining = ref(0);
let timerInterval = null;

const exitModal = ref(null);
const submitModal = ref(null);

const currentQuestion = computed(() => questions.value[currentQuestionIndex.value]);
const answeredCount = computed(() => Object.keys(answers.value).filter(key => answers.value[key] !== null).length);

const startQuiz = async () => {
    try {
        const response = await quizService.startAttempt(route.params.uuid);
        quiz.value = response.data.quiz;
        attempt.value = response.data.attempt;
        questions.value = response.data.questions;
        timeRemaining.value = attempt.value.remaining_time || (quiz.value.time_limit * 60);

        // Restore previous answers if any
        if (attempt.value.answers) {
            attempt.value.answers.forEach(a => {
                answers.value[a.question_id] = a.selected_options || a.text_answer || a.order_answer || a.matching_answer;
            });
        }

        startTimer();
    } catch (error) {
        toast.error(error.response?.data?.message || 'Failed to start quiz');
        router.back();
    } finally {
        loading.value = false;
    }
};

const startTimer = () => {
    if (!quiz.value.time_limit) return;

    timerInterval = setInterval(() => {
        if (timeRemaining.value <= 0) {
            clearInterval(timerInterval);
            handleTimeUp();
            return;
        }
        timeRemaining.value--;
    }, 1000);
};

const formatTime = (seconds) => {
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = seconds % 60;
    return `${h > 0 ? h + ':' : ''}${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
};

const handleTimeUp = () => {
    toast.warning('Time is up! Submitting your answers...');
    processSubmission();
};

const nextQuestion = () => {
    if (currentQuestionIndex.value < questions.value.length - 1) {
        currentQuestionIndex.value++;
    }
};

const prevQuestion = () => {
    if (currentQuestionIndex.value > 0) {
        currentQuestionIndex.value--;
    }
};

const saveAnswer = async (val) => {
    try {
        const qId = currentQuestion.value.id;
        const payload = {
            selected_options: Array.isArray(val) ? val : (typeof val === 'object' ? null : val),
            text_answer: typeof val === 'string' ? val : null,
            order_answer: Array.isArray(val) && currentQuestion.value.type === 'ordering' ? val : null,
            matching_answer: typeof val === 'object' && currentQuestion.value.type === 'matching' ? val : null,
        };

        // Match payloads to Controller expectations
        if (currentQuestion.value.type === 'multiple_choice' || currentQuestion.value.type === 'single_choice') {
            payload.selected_options = Array.isArray(val) ? val : [val];
        }

        await quizService.saveAnswer(attempt.value.id, qId, payload);
    } catch (error) {
        console.error('Failed to save answer', error);
    }
};

const confirmExit = () => {
    exitModal.value.open();
};

const exitQuiz = () => {
    router.back();
};

const submitQuiz = () => {
    submitModal.value.open();
};

const processSubmission = async () => {
    try {
        loading.value = true;
        await quizService.submitAttempt(attempt.value.id);
        toast.success('Quiz submitted successfully!');
        router.push({ name: 'student.quizzes' }); // Redirect to results
    } catch (error) {
        toast.error('Submission failed');
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    startQuiz();
});

onUnmounted(() => {
    if (timerInterval) clearInterval(timerInterval);
});

// Auto-save progress every 30 seconds as additional backup
const autoSaveInterval = setInterval(() => {
    // Logic for bulk save if needed
}, 30000);

onUnmounted(() => clearInterval(autoSaveInterval));

</script>

<style scoped>
.question-enter-active,
.question-leave-active {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.question-enter-from {
    opacity: 0;
    transform: translateX(30px);
}

.question-leave-to {
    opacity: 0;
    transform: translateX(-30px);
}

.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.2);
}
</style>
