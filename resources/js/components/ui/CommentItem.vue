<template>
  <div class="flex gap-4 group" :style="{ marginLeft: depth > 0 ? `${depth * 32}px` : '0' }">
    <img 
      :src="comment.author?.avatar || `https://ui-avatars.com/api/?name=${comment.author?.name}&background=random`" 
      :class="[
        'rounded-full shadow-sm shrink-0 border-2',
        depth > 0 ? 'w-8 h-8 border-white dark:border-slate-900' : 'w-10 h-10'
      ]"
    >
    <div class="flex-1">
      <!-- Comment Box -->
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/50 rounded-2xl p-4">
        <div class="flex items-center justify-between mb-2">
          <div class="flex items-center gap-2">
            <h4 :class="[
              'font-bold',
              depth > 0 ? 'text-xs' : 'text-sm',
              'text-slate-900 dark:text-white'
            ]">{{ comment.author?.name }}</h4>
            <span v-if="depth > 0" class="text-[10px] text-violet-600 dark:text-violet-400 bg-violet-100 dark:bg-violet-900/30 px-2 py-0.5 rounded-full">Reply</span>
          </div>
          <span class="text-xs text-slate-400">{{ formatDate(comment.created_at) }}</span>
        </div>
        <p :class="[
          'leading-relaxed',
          depth > 0 ? 'text-sm' : 'text-sm',
          'text-slate-600 dark:text-slate-300'
        ]">{{ comment.content }}</p>
      </div>
      
      <!-- Reply Button -->
      <div class="flex items-center gap-4 mt-2 pl-2">
        <button 
          @click="toggleReplyForm" 
          class="text-xs font-semibold text-slate-500 hover:text-violet-600 transition-colors flex items-center gap-1"
        >
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
          </svg>
          Reply
        </button>
      </div>

      <!-- Reply Form -->
      <div v-if="showReplyForm" class="mt-4 flex gap-3 animate-in fade-in slide-in-from-top-2">
        <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-800 shrink-0"></div>
        <div class="flex-1">
          <div class="bg-violet-50 dark:bg-violet-900/20 border border-violet-200 dark:border-violet-800/50 rounded-xl p-3">
            <div class="text-xs text-violet-600 dark:text-violet-400 font-medium mb-2">Replying to {{ comment.author?.name }}</div>
            <textarea 
              v-model="replyContent"
              placeholder="Write a reply..."
              class="w-full p-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-violet-500/50 min-h-[80px] resize-none"
            ></textarea>
            <div class="mt-2 flex justify-end gap-2">
              <button 
                @click="cancelReply" 
                class="px-3 py-1.5 text-xs font-medium text-slate-500 hover:text-slate-700 dark:hover:text-slate-300"
              >
                Cancel
              </button>
              <button 
                @click="submitReply" 
                class="px-4 py-1.5 bg-violet-600 text-white text-xs font-bold rounded-lg hover:bg-violet-700"
              >
                Reply
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Nested Replies -->
      <div v-if="comment.replies?.length" class="mt-4 space-y-4">
        <CommentItem 
          v-for="reply in comment.replies" 
          :key="reply.id" 
          :comment="reply" 
          :depth="depth + 1"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, inject } from 'vue'

const props = defineProps({
  comment: {
    type: Object,
    required: true
  },
  depth: {
    type: Number,
    default: 0
  }
})

// Inject parent methods
const handleCommentReply = inject('handleCommentReply')
const replyingTo = inject('replyingTo')

const replyContent = ref('')
const showReplyForm = ref(false)

// Watch for replyingTo changes to show/hide reply form
watch(() => replyingTo.value, (newValue) => {
  if (newValue === props.comment.id) {
    showReplyForm.value = true
  } else {
    showReplyForm.value = false
    replyContent.value = ''
  }
})

const formatDate = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const toggleReplyForm = () => {
  if (showReplyForm.value) {
    handleCommentReply(null) // Close reply form
  } else {
    handleCommentReply(props.comment.id) // Open reply form for this comment
  }
}

const cancelReply = () => {
  showReplyForm.value = false
  replyContent.value = ''
  handleCommentReply(null)
}

const submitReply = () => {
  if (!replyContent.value.trim()) return
  
  handleCommentReply({
    parentId: props.comment.id,
    content: replyContent.value.trim()
  })
  
  replyContent.value = ''
  showReplyForm.value = false
}
</script>
