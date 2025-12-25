<template>
  <div
    ref="containerRef"
    :class="['video-player relative group overflow-hidden', containerClasses]"
    @mousemove="showControls"
    @mouseleave="hideControlsDelayed"
    @click="handleContainerClick"
  >
    <!-- Video Element -->
    <video
      ref="videoRef"
      :src="src"
      :poster="poster"
      :preload="preload"
      :class="['w-full h-full object-contain', videoClasses]"
      @loadedmetadata="onLoadedMetadata"
      @timeupdate="onTimeUpdate"
      @progress="onProgress"
      @play="onPlay"
      @pause="onPause"
      @ended="onEnded"
      @waiting="isBuffering = true"
      @playing="isBuffering = false"
      @error="onError"
      @volumechange="onVolumeChange"
    >
      <track
        v-for="(track, index) in subtitles"
        :key="index"
        :src="track.src"
        :srclang="track.lang"
        :label="track.label"
        :kind="track.kind || 'subtitles'"
        :default="track.default"
      />
    </video>

    <!-- Loading Spinner -->
    <Transition
      enter-active-class="transition-opacity duration-200"
      enter-from-class="opacity-0"
      leave-active-class="transition-opacity duration-200"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isBuffering"
        class="absolute inset-0 flex items-center justify-center bg-black/30"
      >
        <div class="w-12 h-12 border-4 border-white/30 border-t-white rounded-full animate-spin" />
      </div>
    </Transition>

    <!-- Big Play Button (when paused) -->
    <Transition
      enter-active-class="transition-all duration-200"
      enter-from-class="opacity-0 scale-90"
      leave-active-class="transition-all duration-200"
      leave-to-class="opacity-0 scale-90"
    >
      <button
        v-if="!isPlaying && !isBuffering && showBigPlayButton"
        type="button"
        class="absolute inset-0 flex items-center justify-center"
        @click.stop="togglePlay"
      >
        <div class="w-20 h-20 rounded-full bg-white/90 flex items-center justify-center shadow-lg hover:scale-105 transition-transform">
          <PlayIcon class="w-10 h-10 text-gray-900 ml-1" />
        </div>
      </button>
    </Transition>

    <!-- Controls Overlay -->
    <Transition
      enter-active-class="transition-opacity duration-200"
      enter-from-class="opacity-0"
      leave-active-class="transition-opacity duration-200"
      leave-to-class="opacity-0"
    >
      <div
        v-if="controlsVisible || !isPlaying"
        class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent pt-16 pb-2 px-4"
      >
        <!-- Progress Bar -->
        <div
          ref="progressRef"
          class="relative h-1 bg-white/30 rounded-full cursor-pointer group/progress mb-3"
          @click="seek"
          @mousedown="startProgressDrag"
          @mousemove="showPreview"
          @mouseleave="hidePreview"
        >
          <!-- Buffered -->
          <div
            class="absolute inset-y-0 left-0 bg-white/40 rounded-full"
            :style="{ width: `${bufferedPercent}%` }"
          />
          
          <!-- Progress -->
          <div
            class="absolute inset-y-0 left-0 bg-white rounded-full"
            :style="{ width: `${progressPercent}%` }"
          />
          
          <!-- Thumb -->
          <div
            class="absolute top-1/2 -translate-y-1/2 w-4 h-4 bg-white rounded-full shadow-md opacity-0 group-hover/progress:opacity-100 transition-opacity"
            :style="{ left: `calc(${progressPercent}% - 8px)` }"
          />

          <!-- Preview Tooltip -->
          <div
            v-if="previewVisible"
            class="absolute bottom-full mb-2 -translate-x-1/2 px-2 py-1 bg-black/80 text-white text-xs rounded"
            :style="{ left: `${previewPosition}%` }"
          >
            {{ formatTime(previewTime) }}
          </div>
        </div>

        <!-- Controls Row -->
        <div class="flex items-center justify-between">
          <!-- Left Controls -->
          <div class="flex items-center gap-2">
            <button
              type="button"
              class="p-2 text-white hover:text-white/80 transition-colors"
              @click="togglePlay"
            >
              <PauseIcon v-if="isPlaying" class="w-6 h-6" />
              <PlayIcon v-else class="w-6 h-6" />
            </button>

            <button
              v-if="showSkipButtons"
              type="button"
              class="p-2 text-white hover:text-white/80 transition-colors"
              @click="skip(-10)"
            >
              <BackwardIcon class="w-5 h-5" />
            </button>

            <button
              v-if="showSkipButtons"
              type="button"
              class="p-2 text-white hover:text-white/80 transition-colors"
              @click="skip(10)"
            >
              <ForwardIcon class="w-5 h-5" />
            </button>

            <!-- Volume -->
            <div class="flex items-center gap-1 group/volume">
              <button
                type="button"
                class="p-2 text-white hover:text-white/80 transition-colors"
                @click="toggleMute"
              >
                <SpeakerXMarkIcon v-if="isMuted || volume === 0" class="w-5 h-5" />
                <SpeakerWaveIcon v-else class="w-5 h-5" />
              </button>
              
              <input
                type="range"
                min="0"
                max="1"
                step="0.01"
                :value="isMuted ? 0 : volume"
                class="w-0 group-hover/volume:w-20 transition-all duration-200 h-1 bg-white/30 rounded-full appearance-none cursor-pointer"
                @input="setVolume($event.target.value)"
              />
            </div>

            <!-- Time -->
            <div class="text-white text-sm ml-2">
              {{ formatTime(currentTime) }} / {{ formatTime(duration) }}
            </div>
          </div>

          <!-- Right Controls -->
          <div class="flex items-center gap-2">
            <!-- Subtitles -->
            <div v-if="subtitles.length > 0" class="relative">
              <button
                type="button"
                class="p-2 text-white hover:text-white/80 transition-colors"
                @click="showSubtitleMenu = !showSubtitleMenu"
              >
                <GlobeAltIcon class="w-5 h-5" />
              </button>
              
              <Transition
                enter-active-class="transition-all duration-150"
                enter-from-class="opacity-0 translate-y-2"
                leave-active-class="transition-all duration-100"
                leave-to-class="opacity-0 translate-y-2"
              >
                <div
                  v-if="showSubtitleMenu"
                  class="absolute bottom-full right-0 mb-2 bg-gray-900/95 rounded-lg shadow-xl py-2 min-w-32"
                >
                  <button
                    :class="[
                      'w-full px-4 py-2 text-left text-sm hover:bg-white/10 transition-colors',
                      !activeSubtitle ? 'text-blue-400' : 'text-white'
                    ]"
                    @click="setSubtitle(null)"
                  >
                    Off
                  </button>
                  <button
                    v-for="(track, index) in subtitles"
                    :key="index"
                    :class="[
                      'w-full px-4 py-2 text-left text-sm hover:bg-white/10 transition-colors',
                      activeSubtitle === index ? 'text-blue-400' : 'text-white'
                    ]"
                    @click="setSubtitle(index)"
                  >
                    {{ track.label }}
                  </button>
                </div>
              </Transition>
            </div>

            <!-- Playback Speed -->
            <div class="relative">
              <button
                type="button"
                class="p-2 text-white hover:text-white/80 transition-colors text-sm font-medium"
                @click="showSpeedMenu = !showSpeedMenu"
              >
                {{ playbackRate }}x
              </button>
              
              <Transition
                enter-active-class="transition-all duration-150"
                enter-from-class="opacity-0 translate-y-2"
                leave-active-class="transition-all duration-100"
                leave-to-class="opacity-0 translate-y-2"
              >
                <div
                  v-if="showSpeedMenu"
                  class="absolute bottom-full right-0 mb-2 bg-gray-900/95 rounded-lg shadow-xl py-2"
                >
                  <button
                    v-for="speed in speeds"
                    :key="speed"
                    :class="[
                      'w-full px-4 py-2 text-sm hover:bg-white/10 transition-colors',
                      playbackRate === speed ? 'text-blue-400' : 'text-white'
                    ]"
                    @click="setPlaybackRate(speed)"
                  >
                    {{ speed }}x
                  </button>
                </div>
              </Transition>
            </div>

            <!-- Picture in Picture -->
            <button
              v-if="supportsPiP"
              type="button"
              class="p-2 text-white hover:text-white/80 transition-colors"
              @click="togglePiP"
            >
              <TvIcon class="w-5 h-5" />
            </button>

            <!-- Fullscreen -->
            <button
              type="button"
              class="p-2 text-white hover:text-white/80 transition-colors"
              @click="toggleFullscreen"
            >
              <ArrowsPointingOutIcon v-if="!isFullscreen" class="w-5 h-5" />
              <ArrowsPointingInIcon v-else class="w-5 h-5" />
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Title Overlay (top) -->
    <Transition
      enter-active-class="transition-opacity duration-200"
      enter-from-class="opacity-0"
      leave-active-class="transition-opacity duration-200"
      leave-to-class="opacity-0"
    >
      <div
        v-if="title && (controlsVisible || !isPlaying)"
        class="absolute inset-x-0 top-0 bg-gradient-to-b from-black/60 to-transparent pt-4 pb-16 px-4"
      >
        <h3 class="text-white font-semibold text-lg">{{ title }}</h3>
        <p v-if="subtitle" class="text-white/70 text-sm">{{ subtitle }}</p>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { Transition } from 'vue'
import {
  PlayIcon,
  PauseIcon,
  ForwardIcon,
  BackwardIcon,
  SpeakerWaveIcon,
  SpeakerXMarkIcon,
  ArrowsPointingOutIcon,
  ArrowsPointingInIcon,
  GlobeAltIcon,
  TvIcon
} from '@heroicons/vue/24/solid'

const props = defineProps({
  src: {
    type: String,
    required: true
  },
  poster: {
    type: String,
    default: ''
  },
  title: {
    type: String,
    default: ''
  },
  subtitle: {
    type: String,
    default: ''
  },
  subtitles: {
    type: Array,
    default: () => []
  },
  autoplay: {
    type: Boolean,
    default: false
  },
  muted: {
    type: Boolean,
    default: false
  },
  loop: {
    type: Boolean,
    default: false
  },
  preload: {
    type: String,
    default: 'metadata'
  },
  aspectRatio: {
    type: String,
    default: '16/9'
  },
  showBigPlayButton: {
    type: Boolean,
    default: true
  },
  showSkipButtons: {
    type: Boolean,
    default: true
  },
  controlsHideDelay: {
    type: Number,
    default: 3000
  }
})

const emit = defineEmits(['play', 'pause', 'ended', 'timeupdate', 'error', 'fullscreen'])

const containerRef = ref(null)
const videoRef = ref(null)
const progressRef = ref(null)

const isPlaying = ref(false)
const isBuffering = ref(false)
const currentTime = ref(0)
const duration = ref(0)
const bufferedPercent = ref(0)
const volume = ref(1)
const isMuted = ref(props.muted)
const playbackRate = ref(1)
const isFullscreen = ref(false)
const controlsVisible = ref(true)
const controlsTimeout = ref(null)
const showSubtitleMenu = ref(false)
const showSpeedMenu = ref(false)
const activeSubtitle = ref(null)
const previewVisible = ref(false)
const previewPosition = ref(0)
const previewTime = ref(0)
const isDraggingProgress = ref(false)

const speeds = [0.5, 0.75, 1, 1.25, 1.5, 2]
const supportsPiP = ref(document.pictureInPictureEnabled)

const containerClasses = computed(() => [
  'bg-black',
  isFullscreen.value ? 'fixed inset-0 z-50' : 'rounded-xl'
])

const videoClasses = computed(() => [
  isFullscreen.value ? '' : `aspect-[${props.aspectRatio}]`
])

const progressPercent = computed(() => {
  return duration.value ? (currentTime.value / duration.value) * 100 : 0
})

const formatTime = (seconds) => {
  if (!seconds || isNaN(seconds)) return '0:00'
  const hours = Math.floor(seconds / 3600)
  const mins = Math.floor((seconds % 3600) / 60)
  const secs = Math.floor(seconds % 60)
  
  if (hours > 0) {
    return `${hours}:${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
  }
  return `${mins}:${secs.toString().padStart(2, '0')}`
}

const showControls = () => {
  controlsVisible.value = true
  clearTimeout(controlsTimeout.value)
  
  if (isPlaying.value) {
    controlsTimeout.value = setTimeout(() => {
      controlsVisible.value = false
      showSubtitleMenu.value = false
      showSpeedMenu.value = false
    }, props.controlsHideDelay)
  }
}

const hideControlsDelayed = () => {
  if (isPlaying.value) {
    controlsTimeout.value = setTimeout(() => {
      controlsVisible.value = false
      showSubtitleMenu.value = false
      showSpeedMenu.value = false
    }, 500)
  }
}

const handleContainerClick = (e) => {
  // Don't toggle play if clicking on controls
  if (e.target === videoRef.value) {
    togglePlay()
  }
}

const togglePlay = () => {
  if (!videoRef.value) return
  
  if (isPlaying.value) {
    videoRef.value.pause()
  } else {
    videoRef.value.play()
  }
}

const seek = (e) => {
  if (!progressRef.value || !videoRef.value) return
  
  const rect = progressRef.value.getBoundingClientRect()
  const percent = (e.clientX - rect.left) / rect.width
  videoRef.value.currentTime = percent * duration.value
}

const startProgressDrag = () => {
  isDraggingProgress.value = true
  document.addEventListener('mousemove', onProgressDrag)
  document.addEventListener('mouseup', stopProgressDrag)
}

const onProgressDrag = (e) => {
  if (!isDraggingProgress.value || !progressRef.value) return
  
  const rect = progressRef.value.getBoundingClientRect()
  const percent = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width))
  currentTime.value = percent * duration.value
}

const stopProgressDrag = () => {
  if (isDraggingProgress.value && videoRef.value) {
    videoRef.value.currentTime = currentTime.value
  }
  isDraggingProgress.value = false
  document.removeEventListener('mousemove', onProgressDrag)
  document.removeEventListener('mouseup', stopProgressDrag)
}

const showPreview = (e) => {
  if (!progressRef.value) return
  
  const rect = progressRef.value.getBoundingClientRect()
  const percent = Math.max(0, Math.min(100, ((e.clientX - rect.left) / rect.width) * 100))
  previewPosition.value = percent
  previewTime.value = (percent / 100) * duration.value
  previewVisible.value = true
}

const hidePreview = () => {
  previewVisible.value = false
}

const skip = (seconds) => {
  if (videoRef.value) {
    videoRef.value.currentTime = Math.max(0, Math.min(duration.value, videoRef.value.currentTime + seconds))
  }
}

const toggleMute = () => {
  if (videoRef.value) {
    isMuted.value = !isMuted.value
    videoRef.value.muted = isMuted.value
  }
}

const setVolume = (val) => {
  volume.value = parseFloat(val)
  isMuted.value = false
  if (videoRef.value) {
    videoRef.value.volume = volume.value
    videoRef.value.muted = false
  }
}

const setPlaybackRate = (rate) => {
  playbackRate.value = rate
  showSpeedMenu.value = false
  if (videoRef.value) {
    videoRef.value.playbackRate = rate
  }
}

const setSubtitle = (index) => {
  activeSubtitle.value = index
  showSubtitleMenu.value = false
  
  if (videoRef.value) {
    const tracks = videoRef.value.textTracks
    for (let i = 0; i < tracks.length; i++) {
      tracks[i].mode = i === index ? 'showing' : 'hidden'
    }
  }
}

const toggleFullscreen = async () => {
  if (!containerRef.value) return
  
  try {
    if (!document.fullscreenElement) {
      await containerRef.value.requestFullscreen()
      isFullscreen.value = true
    } else {
      await document.exitFullscreen()
      isFullscreen.value = false
    }
    emit('fullscreen', isFullscreen.value)
  } catch (err) {
    console.error('Fullscreen error:', err)
  }
}

const togglePiP = async () => {
  if (!videoRef.value) return
  
  try {
    if (document.pictureInPictureElement) {
      await document.exitPictureInPicture()
    } else {
      await videoRef.value.requestPictureInPicture()
    }
  } catch (err) {
    console.error('PiP error:', err)
  }
}

const onLoadedMetadata = () => {
  if (videoRef.value) {
    duration.value = videoRef.value.duration
    if (props.autoplay) {
      videoRef.value.play()
    }
  }
}

const onTimeUpdate = () => {
  if (!isDraggingProgress.value && videoRef.value) {
    currentTime.value = videoRef.value.currentTime
    emit('timeupdate', currentTime.value)
  }
}

const onProgress = () => {
  if (videoRef.value && videoRef.value.buffered.length > 0) {
    bufferedPercent.value = (videoRef.value.buffered.end(0) / duration.value) * 100
  }
}

const onPlay = () => {
  isPlaying.value = true
  emit('play')
}

const onPause = () => {
  isPlaying.value = false
  controlsVisible.value = true
  emit('pause')
}

const onEnded = () => {
  if (props.loop && videoRef.value) {
    videoRef.value.currentTime = 0
    videoRef.value.play()
  } else {
    isPlaying.value = false
  }
  emit('ended')
}

const onVolumeChange = () => {
  if (videoRef.value) {
    volume.value = videoRef.value.volume
    isMuted.value = videoRef.value.muted
  }
}

const onError = (e) => {
  emit('error', e)
}

const handleKeydown = (e) => {
  if (!containerRef.value?.contains(document.activeElement) && document.activeElement !== document.body) return
  
  switch (e.key) {
    case ' ':
    case 'k':
      e.preventDefault()
      togglePlay()
      break
    case 'ArrowLeft':
      e.preventDefault()
      skip(-10)
      break
    case 'ArrowRight':
      e.preventDefault()
      skip(10)
      break
    case 'ArrowUp':
      e.preventDefault()
      setVolume(Math.min(1, volume.value + 0.1))
      break
    case 'ArrowDown':
      e.preventDefault()
      setVolume(Math.max(0, volume.value - 0.1))
      break
    case 'm':
      toggleMute()
      break
    case 'f':
      toggleFullscreen()
      break
  }
}

const handleFullscreenChange = () => {
  isFullscreen.value = !!document.fullscreenElement
}

watch(() => props.src, () => {
  currentTime.value = 0
  bufferedPercent.value = 0
  isPlaying.value = false
  if (videoRef.value) {
    videoRef.value.load()
  }
})

onMounted(() => {
  document.addEventListener('keydown', handleKeydown)
  document.addEventListener('fullscreenchange', handleFullscreenChange)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
  document.removeEventListener('fullscreenchange', handleFullscreenChange)
  document.removeEventListener('mousemove', onProgressDrag)
  document.removeEventListener('mouseup', stopProgressDrag)
  clearTimeout(controlsTimeout.value)
})

defineExpose({
  play: () => videoRef.value?.play(),
  pause: () => videoRef.value?.pause(),
  seek: (time) => { if (videoRef.value) videoRef.value.currentTime = time },
  getCurrentTime: () => currentTime.value,
  getDuration: () => duration.value,
  isPlaying: () => isPlaying.value,
  toggleFullscreen,
  togglePiP
})
</script>

<style scoped>
input[type="range"] {
  -webkit-appearance: none;
}

input[type="range"]::-webkit-slider-thumb {
  -webkit-appearance: none;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: white;
  cursor: pointer;
}

input[type="range"]::-moz-range-thumb {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: white;
  cursor: pointer;
  border: none;
}
</style>
