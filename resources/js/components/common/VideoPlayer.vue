<template>
  <div class="relative bg-black shadow-6xl border-4 border-white/5 transition-transform duration-500 group">
    <div class="aspect-video w-full relative">
      <video
        ref="videoRef"
        class="plyr-player"
        playsinline
        controls
        :poster="poster"
      >
        <!-- Single Source -->
        <source v-if="!isHLS && typeof src === 'string'" :src="src" type="video/mp4" />
        
        <!-- Multiple Sources for Resolution Switching -->
        <template v-else-if="Array.isArray(src)">
            <source v-for="source in src" :key="source.src" :src="source.src" :type="source.type" :size="source.size" />
        </template>
      </video>

      <!-- Custom Animation Overlay -->
      <div v-if="isPlayerReady" class="absolute inset-0 pointer-events-none flex items-center justify-center z-20">
        <div
          ref="playPauseIcon"
          class="w-24 h-24 rounded-full bg-indigo-600/90 text-white flex items-center justify-center shadow-2xl scale-0 opacity-0 backdrop-blur-md border border-white/20"
        >
          <svg v-if="isPlaying" class="w-10 h-10 ml-1" fill="currentColor" viewBox="0 0 20 20">
            <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z" />
          </svg>
          <svg v-else class="w-10 h-10" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, nextTick, watch, computed } from 'vue';
import gsap from 'gsap';

const props = defineProps({
  src: {
    type: [String, Array],
    required: true
  },
  poster: {
    type: String,
    default: ''
  },
  options: {
    type: Object,
    default: () => ({})
  }
});

const videoRef = ref(null);
const playPauseIcon = ref(null);
const isPlaying = ref(false);
const isPlayerReady = ref(false);
let playerInstance = null;
let hlsInstance = null;

const isHLS = computed(() => {
    if (typeof props.src !== 'string') return false;
    return props.src.endsWith('.m3u8');
});

const loadPlugin = (url, type = 'script') => {
    return new Promise((resolve) => {
        if (type === 'script') {
            const script = document.createElement('script');
            script.src = url;
            script.onload = resolve;
            document.head.appendChild(script);
        } else {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = url;
            link.onload = resolve;
            document.head.appendChild(link);
        }
    });
};

const initPlayer = async () => {
    if (!window.Plyr) {
        await loadPlugin('https://cdn.plyr.io/3.7.8/plyr.css', 'link');
        await loadPlugin('https://cdn.plyr.io/3.7.8/plyr.js');
    }

    if (isHLS.value && !window.Hls) {
        await loadPlugin('https://cdn.jsdelivr.net/npm/hls.js@latest');
    }

    createPlayer();
};

const createPlayer = () => {
    if (!videoRef.value) return;

    const defaultOptions = {
        controls: ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'captions', 'settings', 'pip', 'airplay', 'fullscreen'],
        settings: ['quality', 'speed', 'loop'],
        quality: { 
            default: 1080, 
            options: [1080, 720, 480, 360, 240, 144], // সব রেজোলিউশন অ্যাড করা হয়েছে
            forced: true,
            onChange: (q) => console.log('Quality changed to:', q)
        },
        keyboard: { focused: true, global: true },
        tooltips: { controls: true, seek: true },
        ...props.options
    };

    if (isHLS.value && window.Hls && window.Hls.isSupported()) {
        hlsInstance = new window.Hls();
        hlsInstance.loadSource(props.src);
        hlsInstance.attachMedia(videoRef.value);
        
        hlsInstance.on(window.Hls.Events.MANIFEST_PARSED, () => {
            const levels = hlsInstance.levels.map(l => l.height);
            defaultOptions.quality.options = levels;
            defaultOptions.quality.default = levels[levels.length - 1];
            
            playerInstance = new window.Plyr(videoRef.value, defaultOptions);
            bindEvents();
        });
    } else {
        playerInstance = new window.Plyr(videoRef.value, defaultOptions);
        bindEvents();
    }
};

const emit = defineEmits(['play', 'pause', 'ended', 'timeupdate', 'ready']);

const bindEvents = () => {
    playerInstance.on('play', () => {
        isPlaying.value = true;
        animateIcon();
        emit('play');
    });

    playerInstance.on('pause', () => {
        isPlaying.value = false;
        animateIcon();
        emit('pause');
    });

    playerInstance.on('ended', () => {
        emit('ended');
    });

    playerInstance.on('timeupdate', () => {
        emit('timeupdate', {
            currentTime: playerInstance.currentTime,
            duration: playerInstance.duration,
            percentage: (playerInstance.currentTime / playerInstance.duration) * 100
        });
    });

    playerInstance.on('ready', () => {
        emit('ready', playerInstance);
    });

    isPlayerReady.value = true;
};

const animateIcon = () => {
    if (!playPauseIcon.value) return;
    gsap.fromTo(playPauseIcon.value, { scale: 0, opacity: 0 }, {
        scale: 1, opacity: 1, duration: 0.4, ease: 'back.out(1.7)',
        onComplete: () => {
            gsap.to(playPauseIcon.value, { scale: 1.5, opacity: 0, duration: 0.4, delay: 0.2, ease: 'power2.in' });
        }
    });
};

watch(() => props.src, () => {
    if (playerInstance) {
        playerInstance.destroy();
        if (hlsInstance) hlsInstance.destroy();
        nextTick(initPlayer);
    }
});

onMounted(initPlayer);

onBeforeUnmount(() => {
    if (playerInstance) playerInstance.destroy();
    if (hlsInstance) hlsInstance.destroy();
});
</script>

<style>
:root {
  --plyr-color-main: #6366f1;
  --plyr-video-control-background-hover: rgba(99, 102, 241, 0.2);
  --plyr-video-controls-background: linear-gradient(rgba(0, 0, 0, 0), rgba(0, 0, 0, 0.85));
  --plyr-range-thumb-height: 16px;
  --plyr-range-track-height: 6px;
  --plyr-control-radius: 16px;
}

.plyr {
  height: 100% !important;
  width: 100% !important;
}

.shadow-6xl {
  box-shadow: 0 40px 100px -20px rgba(0, 0, 0, 0.3), 0 20px 40px -20px rgba(0, 0, 0, 0.15);
}
</style>
