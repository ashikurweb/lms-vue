<template>
  <div class="fixed top-6 left-1/2 -translate-x-1/2 z-[9999] flex flex-col gap-4 pointer-events-none items-center w-full max-w-[320px] px-4 sm:px-0">
    <TransitionGroup
      enter-active-class="transition-all duration-500 ease-out"
      enter-from-class="opacity-0 -translate-y-12 rotate-6"
      enter-to-class="opacity-100 translate-y-0 rotate-0"
      leave-active-class="transition-all duration-500 ease-in absolute"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-12 scale-90"
      move-class="transition-all duration-500 ease-in-out"
    >
      <div
        v-for="toast in toasts" 
        :key="toast.id"
        class="relative w-full overflow-hidden rounded-3xl backdrop-blur-2xl border-2 shadow-2xl pointer-events-auto transform transition-all duration-300 hover:scale-[1.02]"
        :class="getToastClasses(toast.type)"
        @click="removeToast(toast.id)"
      >
        <!-- Animated mesh background -->
        <div class="absolute inset-0 opacity-20">
          <div 
            class="absolute inset-0 mesh-bg animate-mesh-flow"
            :class="getMeshClass(toast.type)"
          ></div>
        </div>
        
        <!-- Multiple floating particles -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
          <div 
            v-for="i in 6" 
            :key="i"
            class="absolute w-2 h-2 rounded-full animate-float"
            :class="getParticleClass(toast.type)"
            :style="{
              left: Math.random() * 100 + '%',
              top: Math.random() * 100 + '%',
              animationDelay: (i * 0.5) + 's',
              animationDuration: (3 + Math.random() * 2) + 's'
            }"
          ></div>
        </div>

        <!-- Main glow effect -->
        <div 
          class="absolute -inset-2 rounded-3xl blur-xl opacity-20 animate-glow-pulse"
          :class="getGlowClass(toast.type)"
        ></div>

        <!-- Content -->
        <div class="relative z-20 flex items-start gap-3 py-4 px-4">
          <!-- Enhanced Icon Container -->
          <div class="flex-shrink-0 relative mt-0.5 icon-container">
            
            <!-- Processing Type -->
            <div
              v-if="toast.type === 'processing'"
              class="relative w-10 h-10 flex items-center justify-center"
            >
              <!-- Triple spinning rings -->
              <div class="absolute w-10 h-10 border-2 border-blue-300/20 rounded-full"></div>
              <div class="absolute w-8 h-8 border-[2.5px] border-transparent border-t-blue-400 border-r-blue-300 rounded-full animate-spin-fast"></div>
              <div class="absolute w-6 h-6 border-2 border-transparent border-t-blue-500 border-l-blue-400 rounded-full animate-spin-reverse"></div>
              
            </div>
            
            <!-- Success Icon -->
            <div
              v-else-if="toast.type === 'success' || toast.type === 'updated'"
              class="w-10 h-10 bg-gradient-to-br from-emerald-400 via-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg relative overflow-hidden group-hover:shadow-emerald-500/50 transition-shadow"
            >
              <div class="absolute inset-0 bg-white/20 rounded-xl animate-shimmer"></div>
              
              <div class="relative w-full h-full flex items-center justify-center">
                 <!-- Spinner rings fade out -->
                <div class="absolute inset-0 flex items-center justify-center animate-spinner-to-check">
                  <div class="absolute w-6 h-6 border-2 border-transparent border-t-white border-r-white/60 rounded-full animate-spin-fast"></div>
                </div>
                 <!-- Check mark -->
                <svg class="w-5 h-5 text-white animate-check-appear relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" class="check-draw-path"></path>
                </svg>
              </div>
            </div>
            
            <!-- Error/Delete Icon -->
            <div
              v-else-if="toast.type === 'error' || toast.type === 'delete'"
              class="w-10 h-10 bg-gradient-to-br from-red-400 via-pink-500 to-red-600 rounded-xl flex items-center justify-center shadow-lg relative overflow-hidden group-hover:shadow-red-500/50 transition-shadow error-icon"
            >
               <div class="absolute inset-0 bg-white/20 rounded-xl animate-shimmer"></div>
               <svg class="w-5 h-5 text-white animate-error-shake relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path>
               </svg>
            </div>

            <!-- Info/Warning Default -->
            <div
              v-else
              class="w-10 h-10 bg-gradient-to-br from-blue-400 via-indigo-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg relative overflow-hidden"
            >
               <div class="absolute inset-0 bg-white/20 rounded-xl animate-shimmer"></div>
               <svg class="w-5 h-5 text-white relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
               </svg>
            </div>

          </div>

          <!-- Enhanced Text Content -->
          <div class="flex-1 min-w-0 flex flex-col justify-center pt-0.5">
            <h4 class="font-bold text-sm leading-tight mb-1 text-white drop-shadow-md animate-text-reveal">
              {{ getTitle(toast.type) }}
            </h4>
            <p class="text-xs font-medium leading-normal text-white/90 drop-shadow-sm break-words animate-text-reveal-delay">
              {{ toast.message }}
            </p>
          </div>
          
           <!-- Close Button (Subtle) -->
           <button @click.stop="removeToast(toast.id)" class="shrink-0 text-white/50 hover:text-white transition-colors relative z-30">
               <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
           </button>
        </div>

        <!-- Border glow animation -->
        <div 
          class="absolute inset-0 rounded-3xl border border-white/10 animate-border-glow pointer-events-none"
          :class="getBorderGlowClass(toast.type)"
        ></div>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup>
import { useToast } from '../../composables/useToast';

const { toasts, removeToast } = useToast();

const getTitle = (type) => {
    switch (type) {
        case 'success': return 'Success!';
        case 'updated': return 'Updated Successfully';
        case 'error': return 'Error Occurred';
        case 'warning': return 'Attention';
        case 'delete': return 'Deleted';
        case 'processing': return 'Processing...';
        default: return 'Notification';
    }
};

const getToastClasses = (type) => {
    switch(type) {
        case 'success': 
        case 'updated':
            return 'bg-gradient-to-br from-slate-900/95 via-emerald-900/95 to-slate-900/95 border-emerald-500/30 shadow-emerald-500/20';
        case 'error': 
        case 'delete':
            return 'bg-gradient-to-br from-slate-900/95 via-red-900/95 to-slate-900/95 border-red-500/30 shadow-red-500/20';
        case 'processing':
            return 'bg-gradient-to-br from-slate-900/95 via-blue-900/95 to-slate-900/95 border-blue-500/30 shadow-blue-500/20';
        default:
             return 'bg-gradient-to-br from-slate-900/95 via-indigo-900/95 to-slate-900/95 border-indigo-500/30 shadow-indigo-500/20';
    }
};

const getMeshClass = (type) => {
     if (['success', 'updated'].includes(type)) return 'mesh-success';
     if (['error', 'delete'].includes(type)) return 'mesh-error';
     return 'mesh-processing';
};

const getParticleClass = (type) => {
     if (['success', 'updated'].includes(type)) return 'bg-emerald-400/30';
     if (['error', 'delete'].includes(type)) return 'bg-red-400/30';
     return 'bg-blue-400/30';
};

const getGlowClass = (type) => {
     if (['success', 'updated'].includes(type)) return 'bg-emerald-500';
     if (['error', 'delete'].includes(type)) return 'bg-red-500';
     return 'bg-blue-500';
};

const getBorderGlowClass = (type) => {
     if (['success', 'updated'].includes(type)) return 'border-glow-success';
     if (['error', 'delete'].includes(type)) return 'border-glow-error';
     return 'border-glow-processing';
};

</script>

<style scoped>
/* Keyframe Animations */

@keyframes spinner-to-check {
  0%, 60% { opacity: 1; transform: scale(1); }
  70%, 100% { opacity: 0; transform: scale(0.8); }
}

@keyframes check-appear {
  0%, 60% { opacity: 0; transform: scale(0.5) rotate(-45deg); }
  70%, 100% { opacity: 1; transform: scale(1) rotate(0deg); }
}

@keyframes check-draw {
  0% { stroke-dasharray: 0 20; stroke-dashoffset: 0; }
  100% { stroke-dasharray: 20 0; stroke-dashoffset: 0; }
}

.animate-spinner-to-check { animation: spinner-to-check 1.5s ease-out forwards; }
.animate-check-appear { animation: check-appear 1.5s ease-out forwards; }
.check-draw-path { stroke-dasharray: 20; stroke-dashoffset: 20; animation: check-draw 0.6s ease-out 0.9s forwards; }

@keyframes spin-fast { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
@keyframes spin-reverse { from { transform: rotate(360deg); } to { transform: rotate(0deg); } }
.animate-spin-fast { animation: spin-fast 1s linear infinite; }
.animate-spin-reverse { animation: spin-reverse 1.5s linear infinite; }

@keyframes float {
  0%, 100% { transform: translateY(0) rotate(0deg); opacity: 0.3; }
  25% { transform: translateY(-10px) rotate(90deg); opacity: 0.6; }
  50% { transform: translateY(-5px) rotate(180deg); opacity: 0.8; }
  75% { transform: translateY(-15px) rotate(270deg); opacity: 0.4; }
}
.animate-float { animation: float linear infinite; }

@keyframes mesh-flow {
  0%, 100% { background-position: 0% 0%; filter: hue-rotate(0deg); }
  50% { background-position: 100% 100%; filter: hue-rotate(15deg); }
}
.animate-mesh-flow { animation: mesh-flow 10s ease-in-out infinite; }

@keyframes glow-pulse {
  0%, 100% { opacity: 0.1; transform: scale(1); }
  50% { opacity: 0.3; transform: scale(1.02); }
}
.animate-glow-pulse { animation: glow-pulse 3s ease-in-out infinite; }

@keyframes shimmer {
  0% { transform: translateX(-150%) rotate(45deg); }
  100% { transform: translateX(250%) rotate(45deg); }
}
.animate-shimmer { animation: shimmer 2.5s ease-in-out infinite; }

@keyframes error-shake {
  0%, 100% { transform: translateX(0); }
  10%, 30%, 50%, 70%, 90% { transform: translateX(-2px); }
  20%, 40%, 60%, 80% { transform: translateX(2px); }
}
.animate-error-shake { animation: error-shake 0.6s ease-in-out; }

@keyframes text-reveal {
  0% { opacity: 0; transform: translateY(5px); }
  100% { opacity: 1; transform: translateY(0); }
}
.animate-text-reveal { animation: text-reveal 0.5s ease-out; }
.animate-text-reveal-delay { animation: text-reveal 0.6s ease-out 0.1s backwards; }

@keyframes progress-flow {
  from { width: 100%; }
  to { width: 0%; }
}
.animate-progress-flow { animation: progress-flow linear forwards; }

@keyframes border-glow-anim {
  0%, 100% { opacity: 0.3; }
  50% { opacity: 0.6; }
}
.animate-border-glow { animation: border-glow-anim 2s ease-in-out infinite; }

/* Styles */
.mesh-bg {
  background-image: 
    radial-gradient(circle at 50% 50%, currentColor 1px, transparent 1px);
  background-size: 20px 20px;
}
.mesh-success { color: #10b981; }
.mesh-error { color: #ef4444; }
.mesh-processing { color: #3b82f6; }

.border-glow-success { box-shadow: inset 0 0 15px rgba(16, 185, 129, 0.2); }
.border-glow-error { box-shadow: inset 0 0 15px rgba(239, 68, 68, 0.2); }
.border-glow-processing { box-shadow: inset 0 0 15px rgba(59, 130, 246, 0.2); }

.backdrop-blur-2xl { backdrop-filter: blur(24px); }
</style>