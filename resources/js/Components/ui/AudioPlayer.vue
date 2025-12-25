<template>
  <div :class="['audio-player rounded-xl overflow-hidden', containerClasses]">
    <!-- Cover Art & Info -->
    <div
      v-if="showCover"
      :class="['flex items-center gap-4 p-4', variant === 'minimal' && 'p-3']"
    >
      <!-- Album Art -->
      <div
        :class="[
          'relative flex-shrink-0 rounded-lg overflow-hidden bg-gradient-to-br from-purple-500 to-pink-500',
          coverSizeClasses
        ]"
      >
        <img
          v-if="coverArt"
          :src="coverArt"
          :alt="title"
          class="w-full h-full object-cover"
        />
        <div
          v-else
          class="w-full h-full flex items-center justify-center"
        >
          <MusicalNoteIcon class="w-8 h-8 text-white/80" />
        </div>
        
        <!-- Playing Animation -->
        <div
          v-if="isPlaying && showPlayingAnimation"
          class="absolute inset-0 flex items-end justify-center gap-0.5 pb-2 bg-black/30"
        >
          <span
            v-for="i in 4"
            :key="i"
            class="w-1 bg-white rounded-full animate-equalizer"
            :style="{ animationDelay: `${i * 0.1}s` }"
          />
        </div>
      </div>

      <!-- Track Info -->
      <div class="flex-1 min-w-0">
        <h3
          :class="[
            'font-semibold truncate',
            variant === 'minimal' ? 'text-sm' : 'text-base',
            themeClasses.title
          ]"
        >
          {{ title || 'Unknown Track' }}
        </h3>
        <p
          :class="[
            'truncate',
            variant === 'minimal' ? 'text-xs' : 'text-sm',
            themeClasses.artist
          ]"
        >
          {{ artist || 'Unknown Artist' }}
        </p>
        <p
          v-if="album && variant !== 'minimal'"
          :class="['text-xs truncate mt-0.5', themeClasses.album]"
        >
          {{ album }}
        </p>
      </div>

      <!-- Mini Controls (for minimal variant) -->
      <div v-if="variant === 'minimal'" class="flex items-center gap-2">
        <button
          type="button"
          :class="['p-2 rounded-full transition-colors', themeClasses.button]"
          @click="togglePlay"
        >
          <PauseIcon v-if="isPlaying" class="w-5 h-5" />
          <PlayIcon v-else class="w-5 h-5" />
        </button>
      </div>
    </div>

    <!-- Progress Bar -->
    <div :class="['px-4', variant === 'minimal' && 'px-3']">
      <div
        ref="progressRef"
        :class="['relative h-1.5 rounded-full cursor-pointer group', themeClasses.progressBg]"
        @click="seek"
        @mousedown="startDrag"
      >
        <!-- Buffered -->
        <div
          :class="['absolute inset-y-0 left-0 rounded-full', themeClasses.buffered]"
          :style="{ width: `${bufferedPercent}%` }"
        />
        
        <!-- Progress -->
        <div
          :class="['absolute inset-y-0 left-0 rounded-full transition-all', themeClasses.progress]"
          :style="{ width: `${progressPercent}%` }"
        />
        
        <!-- Thumb -->
        <div
          :class="[
            'absolute top-1/2 -translate-y-1/2 w-3 h-3 rounded-full shadow-md transition-opacity',
            'opacity-0 group-hover:opacity-100',
            themeClasses.thumb
          ]"
          :style="{ left: `calc(${progressPercent}% - 6px)` }"
        />
      </div>
      
      <!-- Time Display -->
      <div :class="['flex justify-between mt-1 text-xs', themeClasses.time]">
        <span>{{ formatTime(currentTime) }}</span>
        <span>{{ formatTime(duration) }}</span>
      </div>
    </div>

    <!-- Controls -->
    <div
      v-if="variant !== 'minimal'"
      :class="['flex items-center justify-between px-4 pb-4 pt-2']"
    >
      <!-- Left Controls -->
      <div class="flex items-center gap-2">
        <button
          v-if="showShuffle"
          type="button"
          :class="[
            'p-2 rounded-full transition-colors',
            shuffle ? themeClasses.buttonActive : themeClasses.button
          ]"
          @click="shuffle = !shuffle; $emit('shuffle', shuffle)"
        >
          <ArrowPathRoundedSquareIcon class="w-5 h-5" />
        </button>
        
        <button
          v-if="showSkip"
          type="button"
          :class="['p-2 rounded-full transition-colors', themeClasses.button]"
          @click="$emit('previous')"
        >
          <BackwardIcon class="w-5 h-5" />
        </button>
      </div>

      <!-- Center Controls -->
      <div class="flex items-center gap-3">
        <button
          type="button"
          :class="['p-2 rounded-full transition-colors', themeClasses.button]"
          @click="skipBack"
        >
          <span class="text-xs font-medium">-{{ skipSeconds }}</span>
        </button>

        <button
          type="button"
          :class="[
            'p-4 rounded-full transition-all transform hover:scale-105',
            themeClasses.playButton
          ]"
          @click="togglePlay"
        >
          <PauseIcon v-if="isPlaying" class="w-6 h-6" />
          <PlayIcon v-else class="w-6 h-6 ml-0.5" />
        </button>

        <button
          type="button"
          :class="['p-2 rounded-full transition-colors', themeClasses.button]"
          @click="skipForward"
        >
          <span class="text-xs font-medium">+{{ skipSeconds }}</span>
        </button>
      </div>

      <!-- Right Controls -->
      <div class="flex items-center gap-2">
        <button
          v-if="showSkip"
          type="button"
          :class="['p-2 rounded-full transition-colors', themeClasses.button]"
          @click="$emit('next')"
        >
          <ForwardIcon class="w-5 h-5" />
        </button>
        
        <button
          v-if="showRepeat"
          type="button"
          :class="[
            'p-2 rounded-full transition-colors',
            repeat !== 'none' ? themeClasses.buttonActive : themeClasses.button
          ]"
          @click="cycleRepeat"
        >
          <ArrowPathIcon class="w-5 h-5" />
          <span
            v-if="repeat === 'one'"
            class="absolute -bottom-0.5 -right-0.5 text-[8px] font-bold"
          >
            1
          </span>
        </button>
      </div>
    </div>

    <!-- Volume & Speed -->
    <div
      v-if="showVolumeControl && variant !== 'minimal'"
      :class="['flex items-center justify-between px-4 pb-4', themeClasses.footer]"
    >
      <!-- Volume -->
      <div class="flex items-center gap-2">
        <button
          type="button"
          :class="['p-1.5 rounded transition-colors', themeClasses.button]"
          @click="toggleMute"
        >
          <SpeakerXMarkIcon v-if="isMuted || volume === 0" class="w-4 h-4" />
          <SpeakerWaveIcon v-else-if="volume > 0.5" class="w-4 h-4" />
          <SpeakerWaveIcon v-else class="w-4 h-4 opacity-70" />
        </button>
        
        <input
          type="range"
          min="0"
          max="1"
          step="0.01"
          :value="isMuted ? 0 : volume"
          :class="['w-20 h-1 rounded-full appearance-none cursor-pointer', themeClasses.volumeSlider]"
          @input="setVolume($event.target.value)"
        />
      </div>

      <!-- Playback Speed -->
      <div v-if="showSpeedControl" class="flex items-center gap-2">
        <span :class="['text-xs', themeClasses.time]">Speed</span>
        <select
          :value="playbackRate"
          :class="['text-xs rounded px-2 py-1 cursor-pointer', themeClasses.select]"
          @change="setPlaybackRate(parseFloat($event.target.value))"
        >
          <option value="0.5">0.5x</option>
          <option value="0.75">0.75x</option>
          <option value="1">1x</option>
          <option value="1.25">1.25x</option>
          <option value="1.5">1.5x</option>
          <option value="2">2x</option>
        </select>
      </div>
    </div>

    <!-- Hidden Audio Element -->
    <audio
      ref="audioRef"
      :src="src"
      :preload="preload"
      @loadedmetadata="onLoadedMetadata"
      @timeupdate="onTimeUpdate"
      @progress="onProgress"
      @ended="onEnded"
      @play="isPlaying = true"
      @pause="isPlaying = false"
      @error="onError"
    />
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import {
  PlayIcon,
  PauseIcon,
  ForwardIcon,
  BackwardIcon,
  SpeakerWaveIcon,
  SpeakerXMarkIcon,
  ArrowPathIcon,
  ArrowPathRoundedSquareIcon,
  MusicalNoteIcon
} from '@heroicons/vue/24/solid'

const props = defineProps({
  src: {
    type: String,
    required: true
  },
  title: {
    type: String,
    default: ''
  },
  artist: {
    type: String,
    default: ''
  },
  album: {
    type: String,
    default: ''
  },
  coverArt: {
    type: String,
    default: ''
  },
  autoplay: {
    type: Boolean,
    default: false
  },
  preload: {
    type: String,
    default: 'metadata',
    validator: v => ['none', 'metadata', 'auto'].includes(v)
  },
  variant: {
    type: String,
    default: 'default',
    validator: v => ['default', 'minimal', 'full'].includes(v)
  },
  theme: {
    type: String,
    default: 'light',
    validator: v => ['light', 'dark'].includes(v)
  },
  color: {
    type: String,
    default: 'blue',
    validator: v => ['blue', 'purple', 'pink', 'green', 'orange'].includes(v)
  },
  showCover: {
    type: Boolean,
    default: true
  },
  showShuffle: {
    type: Boolean,
    default: false
  },
  showRepeat: {
    type: Boolean,
    default: true
  },
  showSkip: {
    type: Boolean,
    default: false
  },
  showVolumeControl: {
    type: Boolean,
    default: true
  },
  showSpeedControl: {
    type: Boolean,
    default: true
  },
  showPlayingAnimation: {
    type: Boolean,
    default: true
  },
  skipSeconds: {
    type: Number,
    default: 10
  }
})

const emit = defineEmits(['play', 'pause', 'ended', 'timeupdate', 'error', 'next', 'previous', 'shuffle'])

const audioRef = ref(null)
const progressRef = ref(null)
const isPlaying = ref(false)
const currentTime = ref(0)
const duration = ref(0)
const bufferedPercent = ref(0)
const volume = ref(1)
const isMuted = ref(false)
const playbackRate = ref(1)
const shuffle = ref(false)
const repeat = ref('none') // none, all, one
const isDragging = ref(false)

const colorClasses = {
  blue: { progress: 'bg-blue-500', thumb: 'bg-blue-500', active: 'text-blue-500', playBtn: 'bg-blue-500 hover:bg-blue-600' },
  purple: { progress: 'bg-purple-500', thumb: 'bg-purple-500', active: 'text-purple-500', playBtn: 'bg-purple-500 hover:bg-purple-600' },
  pink: { progress: 'bg-pink-500', thumb: 'bg-pink-500', active: 'text-pink-500', playBtn: 'bg-pink-500 hover:bg-pink-600' },
  green: { progress: 'bg-green-500', thumb: 'bg-green-500', active: 'text-green-500', playBtn: 'bg-green-500 hover:bg-green-600' },
  orange: { progress: 'bg-orange-500', thumb: 'bg-orange-500', active: 'text-orange-500', playBtn: 'bg-orange-500 hover:bg-orange-600' }
}

const containerClasses = computed(() => {
  return props.theme === 'dark'
    ? 'bg-gray-900 border border-gray-700'
    : 'bg-white border border-gray-200 shadow-sm'
})

const themeClasses = computed(() => {
  const colors = colorClasses[props.color]
  
  if (props.theme === 'dark') {
    return {
      title: 'text-white',
      artist: 'text-gray-400',
      album: 'text-gray-500',
      progressBg: 'bg-gray-700',
      buffered: 'bg-gray-600',
      progress: colors.progress,
      thumb: colors.thumb,
      time: 'text-gray-400',
      button: 'text-gray-400 hover:text-white hover:bg-gray-800',
      buttonActive: `${colors.active} hover:bg-gray-800`,
      playButton: `${colors.playBtn} text-white`,
      footer: 'border-t border-gray-800 pt-3',
      volumeSlider: 'bg-gray-700',
      select: 'bg-gray-800 text-gray-300 border-gray-700'
    }
  }
  
  return {
    title: 'text-gray-900',
    artist: 'text-gray-600',
    album: 'text-gray-500',
    progressBg: 'bg-gray-200',
    buffered: 'bg-gray-300',
    progress: colors.progress,
    thumb: colors.thumb,
    time: 'text-gray-500',
    button: 'text-gray-500 hover:text-gray-900 hover:bg-gray-100',
    buttonActive: `${colors.active} hover:bg-gray-100`,
    playButton: `${colors.playBtn} text-white`,
    footer: 'border-t border-gray-100 pt-3',
    volumeSlider: 'bg-gray-200',
    select: 'bg-gray-100 text-gray-700 border-gray-200'
  }
})

const coverSizeClasses = computed(() => ({
  minimal: 'w-12 h-12',
  default: 'w-16 h-16',
  full: 'w-20 h-20'
})[props.variant])

const progressPercent = computed(() => {
  return duration.value ? (currentTime.value / duration.value) * 100 : 0
})

const formatTime = (seconds) => {
  if (!seconds || isNaN(seconds)) return '0:00'
  const mins = Math.floor(seconds / 60)
  const secs = Math.floor(seconds % 60)
  return `${mins}:${secs.toString().padStart(2, '0')}`
}

const togglePlay = () => {
  if (!audioRef.value) return
  
  if (isPlaying.value) {
    audioRef.value.pause()
    emit('pause')
  } else {
    audioRef.value.play()
    emit('play')
  }
}

const seek = (e) => {
  if (!progressRef.value || !audioRef.value) return
  
  const rect = progressRef.value.getBoundingClientRect()
  const percent = (e.clientX - rect.left) / rect.width
  audioRef.value.currentTime = percent * duration.value
}

const startDrag = () => {
  isDragging.value = true
  document.addEventListener('mousemove', onDrag)
  document.addEventListener('mouseup', stopDrag)
}

const onDrag = (e) => {
  if (!isDragging.value || !progressRef.value) return
  
  const rect = progressRef.value.getBoundingClientRect()
  const percent = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width))
  currentTime.value = percent * duration.value
}

const stopDrag = () => {
  if (isDragging.value && audioRef.value) {
    audioRef.value.currentTime = currentTime.value
  }
  isDragging.value = false
  document.removeEventListener('mousemove', onDrag)
  document.removeEventListener('mouseup', stopDrag)
}

const skipBack = () => {
  if (audioRef.value) {
    audioRef.value.currentTime = Math.max(0, audioRef.value.currentTime - props.skipSeconds)
  }
}

const skipForward = () => {
  if (audioRef.value) {
    audioRef.value.currentTime = Math.min(duration.value, audioRef.value.currentTime + props.skipSeconds)
  }
}

const toggleMute = () => {
  isMuted.value = !isMuted.value
  if (audioRef.value) {
    audioRef.value.muted = isMuted.value
  }
}

const setVolume = (val) => {
  volume.value = parseFloat(val)
  isMuted.value = false
  if (audioRef.value) {
    audioRef.value.volume = volume.value
    audioRef.value.muted = false
  }
}

const setPlaybackRate = (rate) => {
  playbackRate.value = rate
  if (audioRef.value) {
    audioRef.value.playbackRate = rate
  }
}

const cycleRepeat = () => {
  const modes = ['none', 'all', 'one']
  const currentIndex = modes.indexOf(repeat.value)
  repeat.value = modes[(currentIndex + 1) % modes.length]
}

const onLoadedMetadata = () => {
  if (audioRef.value) {
    duration.value = audioRef.value.duration
    if (props.autoplay) {
      audioRef.value.play()
    }
  }
}

const onTimeUpdate = () => {
  if (!isDragging.value && audioRef.value) {
    currentTime.value = audioRef.value.currentTime
    emit('timeupdate', currentTime.value)
  }
}

const onProgress = () => {
  if (audioRef.value && audioRef.value.buffered.length > 0) {
    bufferedPercent.value = (audioRef.value.buffered.end(0) / duration.value) * 100
  }
}

const onEnded = () => {
  if (repeat.value === 'one') {
    audioRef.value.currentTime = 0
    audioRef.value.play()
  } else if (repeat.value === 'all') {
    emit('next')
  } else {
    isPlaying.value = false
  }
  emit('ended')
}

const onError = (e) => {
  emit('error', e)
}

watch(() => props.src, () => {
  currentTime.value = 0
  bufferedPercent.value = 0
  if (audioRef.value) {
    audioRef.value.load()
  }
})

onUnmounted(() => {
  document.removeEventListener('mousemove', onDrag)
  document.removeEventListener('mouseup', stopDrag)
})

defineExpose({
  play: () => audioRef.value?.play(),
  pause: () => audioRef.value?.pause(),
  stop: () => {
    if (audioRef.value) {
      audioRef.value.pause()
      audioRef.value.currentTime = 0
    }
  },
  seek: (time) => {
    if (audioRef.value) audioRef.value.currentTime = time
  },
  getCurrentTime: () => currentTime.value,
  getDuration: () => duration.value,
  isPlaying: () => isPlaying.value
})
</script>

<style scoped>
@keyframes equalizer {
  0%, 100% { height: 4px; }
  50% { height: 16px; }
}

.animate-equalizer {
  animation: equalizer 0.5s ease-in-out infinite;
}

input[type="range"] {
  -webkit-appearance: none;
}

input[type="range"]::-webkit-slider-thumb {
  -webkit-appearance: none;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: currentColor;
  cursor: pointer;
}

input[type="range"]::-moz-range-thumb {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: currentColor;
  cursor: pointer;
  border: none;
}
</style>
