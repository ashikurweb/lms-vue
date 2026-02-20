<template>
    <div class="space-y-8">
        <!-- MCQ / Single Choice / True-False / Image Choice -->
        <div v-if="['single_choice', 'true_false', 'image_choice'].includes(question.type)"
            class="grid grid-cols-1 gap-4">
            <button v-for="option in question.options" :key="option.id" @click="selectSingle(option.id)"
                class="flex items-center gap-4 p-5 rounded-2xl border transition-all duration-300 text-left group"
                :class="[
                    modelValue === option.id ? 'bg-indigo-600 border-indigo-500 shadow-xl shadow-indigo-600/20' : 'bg-white/5 border-white/10 hover:border-white/20'
                ]">
                <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center shrink-0 transition-colors"
                    :class="modelValue === option.id ? 'border-white bg-white text-indigo-600' : 'border-white/20'">
                    <div v-if="modelValue === option.id" class="w-2.5 h-2.5 bg-indigo-600 rounded-full"></div>
                </div>
                <div class="flex-1">
                    <p class="font-bold text-lg tracking-tight">{{ option.option_text }}</p>
                </div>
            </button>
        </div>

        <!-- Multiple Choice -->
        <div v-else-if="question.type === 'multiple_choice'" class="grid grid-cols-1 gap-4">
            <button v-for="option in question.options" :key="option.id" @click="toggleMultiple(option.id)"
                class="flex items-center gap-4 p-5 rounded-2xl border transition-all duration-300 text-left group"
                :class="[
                    isOptionSelected(option.id) ? 'bg-indigo-600 border-indigo-500 shadow-xl shadow-indigo-600/20' : 'bg-white/5 border-white/10 hover:border-white/20'
                ]">
                <div class="w-6 h-6 rounded-lg border-2 flex items-center justify-center shrink-0 transition-colors"
                    :class="isOptionSelected(option.id) ? 'border-white bg-white text-indigo-600' : 'border-white/20'">
                    <svg v-if="isOptionSelected(option.id)" class="w-4 h-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-bold text-lg tracking-tight">{{ option.option_text }}</p>
                </div>
            </button>
        </div>

        <!-- Short Answer / Fill in the Blank -->
        <div v-else-if="['short_answer', 'fill_blank'].includes(question.type)" class="space-y-4">
            <input type="text" :value="modelValue" @input="emitValue($event.target.value)"
                placeholder="Type your answer here..."
                class="w-full h-20 bg-white/5 border border-white/10 rounded-3xl px-8 text-xl font-bold focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all" />
            <p class="text-xs font-bold text-white/30 uppercase tracking-widest px-4">Case sensitivity depends on quiz
                settings.</p>
        </div>

        <!-- Long Answer / Essay -->
        <div v-else-if="['long_answer', 'essay'].includes(question.type)" class="space-y-4">
            <textarea :value="modelValue" @input="emitValue($event.target.value)"
                placeholder="Write your detailed response here..."
                class="w-full h-64 bg-white/5 border border-white/10 rounded-3xl p-8 text-lg font-medium focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all resize-none overflow-y-auto custom-scrollbar"></textarea>
            <div class="flex justify-between px-4">
                <p class="text-xs font-bold text-white/30 uppercase tracking-widest">Supports multiple paragraphs.</p>
                <p class="text-xs font-bold text-white/30 uppercase tracking-widest">{{ modelValue?.length || 0 }}
                    characters</p>
            </div>
        </div>

        <!-- Matching -->
        <div v-else-if="question.type === 'matching'" class="space-y-6">
            <div v-for="option in question.options" :key="option.id"
                class="flex flex-col md:flex-row items-stretch gap-4">
                <div class="flex-1 p-5 rounded-2xl bg-white/5 border border-white/10 flex items-center">
                    <p class="font-bold tracking-tight">{{ option.option_text }}</p>
                </div>
                <div class="flex items-center justify-center p-2 hidden md:flex">
                    <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </div>
                <div class="flex-1">
                    <select @change="updateMatching(option.id, $event.target.value)"
                        class="w-full h-full p-5 rounded-2xl bg-white/5 border border-white/10 text-white font-bold outline-none focus:border-indigo-500 transition-all appearance-none cursor-pointer">
                        <option value="" class="bg-slate-900">Select Match...</option>
                        <option v-for="o in question.options" :key="'m-' + o.id" :value="o.match_with"
                            class="bg-slate-900" :selected="modelValue?.[option.id] === o.match_with">
                            {{ o.match_with }}
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Fallback -->
        <div v-else class="p-8 rounded-3xl bg-rose-500/10 border border-rose-500/20 flex items-center gap-4">
            <svg class="w-8 h-8 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div>
                <h4 class="font-bold text-rose-500">Unsupported Question Type</h4>
                <p class="text-sm text-rose-500/60">The question type "{{ question.type }}" is not yet implemented or
                    misconfigured.</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    question: { type: Object, required: true },
    modelValue: { type: [String, Number, Array, Object], default: null }
});

const emit = defineEmits(['update:modelValue']);

const selectSingle = (id) => {
    emit('update:modelValue', id);
};

const toggleMultiple = (id) => {
    let values = Array.isArray(props.modelValue) ? [...props.modelValue] : [];
    const index = values.indexOf(id);
    if (index === -1) {
        values.push(id);
    } else {
        values.splice(index, 1);
    }
    emit('update:modelValue', values);
};

const isOptionSelected = (id) => {
    if (Array.isArray(props.modelValue)) {
        return props.modelValue.includes(id);
    }
    return props.modelValue === id;
};

const emitValue = (val) => {
    emit('update:modelValue', val);
};

const updateMatching = (optionId, match) => {
    const current = typeof props.modelValue === 'object' && props.modelValue !== null ? { ...props.modelValue } : {};
    current[optionId] = match;
    emit('update:modelValue', current);
};

</script>

<style scoped>
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
</style>
