<template>
  <div :class="containerClasses">
    <!-- Label -->
    <label v-if="label" class="block text-sm font-medium mb-1.5" :class="labelClasses">
      {{ label }}
      <span v-if="required" class="text-red-500 ml-0.5">*</span>
    </label>

    <!-- Map container -->
    <div
      ref="mapContainer"
      class="relative rounded-lg overflow-hidden"
      :class="[sizeClasses, { 'ring-2 ring-red-300': error }]"
      :style="{ height: height }"
    >
      <!-- Static map image (placeholder when no interactive map) -->
      <div
        v-if="!mapLoaded"
        class="absolute inset-0 bg-gray-100 flex items-center justify-center"
      >
        <div class="text-center">
          <MapPinIcon class="h-12 w-12 text-gray-400 mx-auto" />
          <p class="mt-2 text-sm text-gray-500">Loading map...</p>
        </div>
      </div>

      <!-- Map iframe (OpenStreetMap) -->
      <iframe
        v-if="showMap"
        :src="mapUrl"
        class="absolute inset-0 w-full h-full border-0"
        allowfullscreen
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade"
        @load="mapLoaded = true"
      />

      <!-- Marker overlay for custom position -->
      <div
        v-if="showCrosshair && !readonly"
        class="absolute inset-0 pointer-events-none flex items-center justify-center"
      >
        <div class="relative">
          <MapPinIcon class="h-8 w-8 text-primary-600 drop-shadow-lg" />
          <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1.5 h-1.5 bg-primary-600 rounded-full" />
        </div>
      </div>

      <!-- Controls overlay -->
      <div v-if="showControls && !readonly" class="absolute top-3 right-3 flex flex-col gap-2">
        <button
          type="button"
          class="rounded-lg bg-white p-2 shadow-md hover:bg-gray-50 transition-colors"
          title="Zoom in"
          @click="zoomIn"
        >
          <PlusIcon class="h-4 w-4 text-gray-600" />
        </button>
        <button
          type="button"
          class="rounded-lg bg-white p-2 shadow-md hover:bg-gray-50 transition-colors"
          title="Zoom out"
          @click="zoomOut"
        >
          <MinusIcon class="h-4 w-4 text-gray-600" />
        </button>
        <button
          v-if="geolocationEnabled"
          type="button"
          class="rounded-lg bg-white p-2 shadow-md hover:bg-gray-50 transition-colors"
          title="My location"
          @click="getCurrentLocation"
        >
          <MapPinIcon class="h-4 w-4 text-gray-600" />
        </button>
      </div>
    </div>

    <!-- Coordinates display -->
    <div v-if="showCoordinates && (latitude || longitude)" class="mt-2 flex items-center gap-4 text-sm text-gray-600">
      <span>Lat: {{ latitude?.toFixed(6) }}</span>
      <span>Lng: {{ longitude?.toFixed(6) }}</span>
      <button
        v-if="!readonly"
        type="button"
        class="text-primary-600 hover:text-primary-700"
        @click="copyCoordinates"
      >
        Copy
      </button>
    </div>

    <!-- Location search -->
    <div v-if="searchEnabled && !readonly" class="mt-3">
      <div class="relative">
        <input
          v-model="searchQuery"
          type="text"
          :placeholder="searchPlaceholder"
          class="block w-full rounded-lg border-gray-300 pl-10 pr-4 py-2 text-sm focus:border-primary-500 focus:ring-primary-500"
          @keydown.enter="searchLocation"
        />
        <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
        <button
          v-if="searchQuery"
          type="button"
          class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
          @click="clearSearch"
        >
          <XMarkIcon class="h-4 w-4" />
        </button>
      </div>

      <!-- Search results -->
      <ul
        v-if="searchResults.length > 0"
        class="mt-1 rounded-lg border border-gray-200 bg-white shadow-lg max-h-48 overflow-y-auto"
      >
        <li
          v-for="(result, index) in searchResults"
          :key="index"
          class="px-3 py-2 text-sm hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0"
          @click="selectLocation(result)"
        >
          <p class="font-medium text-gray-900">{{ result.display_name }}</p>
        </li>
      </ul>
    </div>

    <!-- Manual input -->
    <div v-if="manualInput && !readonly" class="mt-3 grid grid-cols-2 gap-3">
      <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Latitude</label>
        <input
          :value="latitude"
          type="number"
          step="0.000001"
          :min="-90"
          :max="90"
          class="block w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
          placeholder="23.8103"
          @input="updateLatitude"
        />
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Longitude</label>
        <input
          :value="longitude"
          type="number"
          step="0.000001"
          :min="-180"
          :max="180"
          class="block w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
          placeholder="90.4125"
          @input="updateLongitude"
        />
      </div>
    </div>

    <!-- Helper text / Error -->
    <p
      v-if="error || helperText"
      class="mt-1.5 text-sm"
      :class="error ? 'text-red-600' : 'text-gray-500'"
    >
      {{ error || helperText }}
    </p>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import {
  MapPinIcon,
  PlusIcon,
  MinusIcon,
  MagnifyingGlassIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  modelValue: {
    type: Object, // { lat, lng }
    default: null
  },
  label: {
    type: String,
    default: ''
  },
  helperText: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  },
  height: {
    type: String,
    default: '300px'
  },
  defaultCenter: {
    type: Object,
    default: () => ({ lat: 23.8103, lng: 90.4125 }) // Dhaka, Bangladesh
  },
  defaultZoom: {
    type: Number,
    default: 13
  },
  showControls: {
    type: Boolean,
    default: true
  },
  showCoordinates: {
    type: Boolean,
    default: true
  },
  showCrosshair: {
    type: Boolean,
    default: false
  },
  searchEnabled: {
    type: Boolean,
    default: true
  },
  searchPlaceholder: {
    type: String,
    default: 'Search location in Bangladesh...'
  },
  geolocationEnabled: {
    type: Boolean,
    default: true
  },
  manualInput: {
    type: Boolean,
    default: false
  },
  readonly: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  required: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue', 'change', 'location-selected'])

// State
const mapContainer = ref(null)
const mapLoaded = ref(false)
const showMap = ref(true)
const zoom = ref(props.defaultZoom)
const searchQuery = ref('')
const searchResults = ref([])

// Coordinates
const latitude = ref(props.modelValue?.lat || props.defaultCenter.lat)
const longitude = ref(props.modelValue?.lng || props.defaultCenter.lng)

// Watch for external changes
watch(() => props.modelValue, (newVal) => {
  if (newVal) {
    latitude.value = newVal.lat
    longitude.value = newVal.lng
  }
}, { immediate: true })

// Map URL for OpenStreetMap embed
const mapUrl = computed(() => {
  const lat = latitude.value
  const lng = longitude.value
  const z = zoom.value
  
  // Using OpenStreetMap embed with marker
  return `https://www.openstreetmap.org/export/embed.html?bbox=${lng - 0.01},${lat - 0.01},${lng + 0.01},${lat + 0.01}&layer=mapnik&marker=${lat},${lng}`
})

// Zoom controls
function zoomIn() {
  if (zoom.value < 19) {
    zoom.value += 1
    refreshMap()
  }
}

function zoomOut() {
  if (zoom.value > 1) {
    zoom.value -= 1
    refreshMap()
  }
}

function refreshMap() {
  showMap.value = false
  mapLoaded.value = false
  setTimeout(() => {
    showMap.value = true
  }, 100)
}

// Get current location
function getCurrentLocation() {
  if (!navigator.geolocation) {
    alert('Geolocation is not supported by your browser')
    return
  }
  
  navigator.geolocation.getCurrentPosition(
    (position) => {
      latitude.value = position.coords.latitude
      longitude.value = position.coords.longitude
      emitUpdate()
      refreshMap()
    },
    (error) => {
      console.error('Geolocation error:', error)
      alert('Unable to get your location')
    }
  )
}

// Search location using Nominatim
async function searchLocation() {
  if (!searchQuery.value.trim()) return
  
  try {
    const query = encodeURIComponent(searchQuery.value + ', Bangladesh')
    const response = await fetch(
      `https://nominatim.openstreetmap.org/search?format=json&q=${query}&limit=5`
    )
    searchResults.value = await response.json()
  } catch (error) {
    console.error('Search error:', error)
    searchResults.value = []
  }
}

// Select location from search
function selectLocation(result) {
  latitude.value = parseFloat(result.lat)
  longitude.value = parseFloat(result.lon)
  searchQuery.value = ''
  searchResults.value = []
  emitUpdate()
  refreshMap()
  
  emit('location-selected', {
    lat: latitude.value,
    lng: longitude.value,
    displayName: result.display_name
  })
}

// Clear search
function clearSearch() {
  searchQuery.value = ''
  searchResults.value = []
}

// Manual coordinate input
function updateLatitude(event) {
  const val = parseFloat(event.target.value)
  if (!isNaN(val) && val >= -90 && val <= 90) {
    latitude.value = val
    emitUpdate()
    refreshMap()
  }
}

function updateLongitude(event) {
  const val = parseFloat(event.target.value)
  if (!isNaN(val) && val >= -180 && val <= 180) {
    longitude.value = val
    emitUpdate()
    refreshMap()
  }
}

// Copy coordinates
function copyCoordinates() {
  const text = `${latitude.value}, ${longitude.value}`
  navigator.clipboard.writeText(text)
}

// Emit update
function emitUpdate() {
  const value = { lat: latitude.value, lng: longitude.value }
  emit('update:modelValue', value)
  emit('change', value)
}

// Styling
const containerClasses = computed(() => {
  return props.disabled ? 'opacity-60 pointer-events-none' : ''
})

const labelClasses = computed(() => {
  return props.error ? 'text-red-700' : 'text-gray-700'
})

const sizeClasses = 'border border-gray-300'

onMounted(() => {
  // Map will load via iframe
})
</script>
