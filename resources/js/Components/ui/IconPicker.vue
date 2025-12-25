<template>
  <div class="icon-picker relative" ref="containerRef">
    <!-- Trigger -->
    <button
      type="button"
      :class="[
        'flex items-center gap-3 px-4 py-3 rounded-xl border transition-all',
        isOpen
          ? 'border-blue-500 ring-2 ring-blue-500/20'
          : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600',
        disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer',
        themeClasses
      ]"
      :disabled="disabled"
      @click="toggle"
    >
      <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
        <component v-if="selectedIcon" :is="selectedIcon" class="w-6 h-6 text-gray-700 dark:text-gray-300" />
        <PhotoIcon v-else class="w-6 h-6 text-gray-400" />
      </div>
      <div class="flex-1 text-left">
        <p class="text-sm font-medium text-gray-900 dark:text-white">
          {{ modelValue || 'Select Icon' }}
        </p>
        <p class="text-xs text-gray-500">{{ activeCategory }}</p>
      </div>
      <ChevronDownIcon :class="['w-5 h-5 text-gray-400 transition-transform', isOpen && 'rotate-180']" />
    </button>

    <!-- Dropdown -->
    <Transition
      enter-active-class="transition-all duration-200 ease-out"
      enter-from-class="opacity-0 scale-95 translate-y-1"
      enter-to-class="opacity-100 scale-100 translate-y-0"
      leave-active-class="transition-all duration-150 ease-in"
      leave-from-class="opacity-100 scale-100 translate-y-0"
      leave-to-class="opacity-0 scale-95 translate-y-1"
    >
      <div
        v-if="isOpen"
        :class="[
          'absolute z-50 w-96 mt-2 rounded-xl border shadow-xl',
          theme === 'dark' ? 'bg-gray-800 border-gray-700' : 'bg-white border-gray-200'
        ]"
      >
        <!-- Search -->
        <div class="p-3 border-b border-gray-200 dark:border-gray-700">
          <div class="relative">
            <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input
              ref="searchInputRef"
              v-model="searchQuery"
              type="text"
              placeholder="Search icons..."
              class="w-full pl-9 pr-4 py-2 text-sm bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-gray-900 dark:text-white placeholder-gray-400"
            />
          </div>
        </div>

        <!-- Categories -->
        <div class="flex gap-1 p-2 border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
          <button
            v-for="cat in categories"
            :key="cat.id"
            type="button"
            :class="[
              'px-3 py-1.5 text-xs font-medium rounded-lg whitespace-nowrap transition-colors',
              activeCategory === cat.id
                ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400'
                : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700'
            ]"
            @click="activeCategory = cat.id"
          >
            {{ cat.label }}
          </button>
        </div>

        <!-- Icons Grid -->
        <div class="p-3 max-h-64 overflow-auto">
          <div v-if="filteredIcons.length > 0" class="grid grid-cols-8 gap-1">
            <button
              v-for="icon in filteredIcons"
              :key="icon.name"
              type="button"
              :class="[
                'p-2 rounded-lg transition-all',
                modelValue === icon.name
                  ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600'
                  : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400'
              ]"
              :title="icon.name"
              @click="selectIcon(icon)"
            >
              <component :is="icon.component" class="w-5 h-5" />
            </button>
          </div>
          <div v-else class="py-8 text-center">
            <MagnifyingGlassIcon class="w-8 h-8 mx-auto text-gray-300 dark:text-gray-600 mb-2" />
            <p class="text-sm text-gray-500">No icons found</p>
          </div>
        </div>

        <!-- Selected Preview -->
        <div v-if="modelValue" class="flex items-center justify-between p-3 border-t border-gray-200 dark:border-gray-700">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
              <component v-if="selectedIcon" :is="selectedIcon" class="w-7 h-7 text-gray-700 dark:text-gray-300" />
            </div>
            <div>
              <p class="text-sm font-medium text-gray-900 dark:text-white">{{ modelValue }}</p>
              <p class="text-xs text-gray-500">Click to copy name</p>
            </div>
          </div>
          <button
            type="button"
            class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg"
            @click="clearSelection"
          >
            <XMarkIcon class="w-5 h-5" />
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue'
import {
  ChevronDownIcon,
  MagnifyingGlassIcon,
  PhotoIcon,
  XMarkIcon,
  HomeIcon,
  UserIcon,
  CogIcon,
  BellIcon,
  EnvelopeIcon,
  CalendarIcon,
  FolderIcon,
  DocumentIcon,
  ChartBarIcon,
  ShoppingCartIcon,
  HeartIcon,
  StarIcon,
  BookmarkIcon,
  ShareIcon,
  ArrowDownTrayIcon,
  ArrowUpTrayIcon,
  PencilIcon,
  TrashIcon,
  PlusIcon,
  MinusIcon,
  CheckIcon,
  XCircleIcon,
  ExclamationTriangleIcon,
  InformationCircleIcon,
  QuestionMarkCircleIcon,
  LockClosedIcon,
  LockOpenIcon,
  EyeIcon,
  EyeSlashIcon,
  MagnifyingGlassPlusIcon,
  MagnifyingGlassMinusIcon,
  ArrowLeftIcon,
  ArrowRightIcon,
  ArrowUpIcon,
  ArrowDownIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  ChevronUpIcon,
  PlayIcon,
  PauseIcon,
  StopIcon,
  SpeakerWaveIcon,
  SpeakerXMarkIcon,
  MicrophoneIcon,
  VideoCameraIcon,
  PhotoIcon as CameraIcon,
  GlobeAltIcon,
  MapPinIcon,
  PhoneIcon,
  DevicePhoneMobileIcon,
  ComputerDesktopIcon,
  PrinterIcon,
  WifiIcon,
  CreditCardIcon,
  BanknotesIcon,
  GiftIcon,
  TagIcon,
  ClipboardIcon,
  ClipboardDocumentIcon,
  CloudIcon,
  CloudArrowUpIcon,
  CloudArrowDownIcon,
  ServerIcon,
  CubeIcon,
  Squares2X2Icon,
  Bars3Icon,
  AdjustmentsHorizontalIcon,
  FunnelIcon,
  SparklesIcon,
  FireIcon,
  BoltIcon,
  SunIcon,
  MoonIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  modelValue: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
  theme: { type: String, default: 'light' }
})

const emit = defineEmits(['update:modelValue', 'change'])

const containerRef = ref(null)
const searchInputRef = ref(null)
const isOpen = ref(false)
const searchQuery = ref('')
const activeCategory = ref('all')

const categories = [
  { id: 'all', label: 'All' },
  { id: 'navigation', label: 'Navigation' },
  { id: 'actions', label: 'Actions' },
  { id: 'media', label: 'Media' },
  { id: 'status', label: 'Status' },
  { id: 'objects', label: 'Objects' }
]

const iconLibrary = [
  // Navigation
  { name: 'HomeIcon', component: HomeIcon, category: 'navigation' },
  { name: 'ArrowLeftIcon', component: ArrowLeftIcon, category: 'navigation' },
  { name: 'ArrowRightIcon', component: ArrowRightIcon, category: 'navigation' },
  { name: 'ArrowUpIcon', component: ArrowUpIcon, category: 'navigation' },
  { name: 'ArrowDownIcon', component: ArrowDownIcon, category: 'navigation' },
  { name: 'ChevronLeftIcon', component: ChevronLeftIcon, category: 'navigation' },
  { name: 'ChevronRightIcon', component: ChevronRightIcon, category: 'navigation' },
  { name: 'ChevronUpIcon', component: ChevronUpIcon, category: 'navigation' },
  { name: 'ChevronDownIcon', component: ChevronDownIcon, category: 'navigation' },
  { name: 'Bars3Icon', component: Bars3Icon, category: 'navigation' },
  { name: 'Squares2X2Icon', component: Squares2X2Icon, category: 'navigation' },
  // Actions
  { name: 'PencilIcon', component: PencilIcon, category: 'actions' },
  { name: 'TrashIcon', component: TrashIcon, category: 'actions' },
  { name: 'PlusIcon', component: PlusIcon, category: 'actions' },
  { name: 'MinusIcon', component: MinusIcon, category: 'actions' },
  { name: 'CheckIcon', component: CheckIcon, category: 'actions' },
  { name: 'ShareIcon', component: ShareIcon, category: 'actions' },
  { name: 'ArrowDownTrayIcon', component: ArrowDownTrayIcon, category: 'actions' },
  { name: 'ArrowUpTrayIcon', component: ArrowUpTrayIcon, category: 'actions' },
  { name: 'ClipboardIcon', component: ClipboardIcon, category: 'actions' },
  { name: 'ClipboardDocumentIcon', component: ClipboardDocumentIcon, category: 'actions' },
  { name: 'FunnelIcon', component: FunnelIcon, category: 'actions' },
  { name: 'AdjustmentsHorizontalIcon', component: AdjustmentsHorizontalIcon, category: 'actions' },
  { name: 'MagnifyingGlassIcon', component: MagnifyingGlassIcon, category: 'actions' },
  { name: 'MagnifyingGlassPlusIcon', component: MagnifyingGlassPlusIcon, category: 'actions' },
  { name: 'MagnifyingGlassMinusIcon', component: MagnifyingGlassMinusIcon, category: 'actions' },
  // Media
  { name: 'PlayIcon', component: PlayIcon, category: 'media' },
  { name: 'PauseIcon', component: PauseIcon, category: 'media' },
  { name: 'StopIcon', component: StopIcon, category: 'media' },
  { name: 'SpeakerWaveIcon', component: SpeakerWaveIcon, category: 'media' },
  { name: 'SpeakerXMarkIcon', component: SpeakerXMarkIcon, category: 'media' },
  { name: 'MicrophoneIcon', component: MicrophoneIcon, category: 'media' },
  { name: 'VideoCameraIcon', component: VideoCameraIcon, category: 'media' },
  { name: 'CameraIcon', component: CameraIcon, category: 'media' },
  { name: 'PhotoIcon', component: PhotoIcon, category: 'media' },
  { name: 'EyeIcon', component: EyeIcon, category: 'media' },
  { name: 'EyeSlashIcon', component: EyeSlashIcon, category: 'media' },
  // Status
  { name: 'CheckIcon', component: CheckIcon, category: 'status' },
  { name: 'XCircleIcon', component: XCircleIcon, category: 'status' },
  { name: 'ExclamationTriangleIcon', component: ExclamationTriangleIcon, category: 'status' },
  { name: 'InformationCircleIcon', component: InformationCircleIcon, category: 'status' },
  { name: 'QuestionMarkCircleIcon', component: QuestionMarkCircleIcon, category: 'status' },
  { name: 'BellIcon', component: BellIcon, category: 'status' },
  { name: 'SparklesIcon', component: SparklesIcon, category: 'status' },
  { name: 'FireIcon', component: FireIcon, category: 'status' },
  { name: 'BoltIcon', component: BoltIcon, category: 'status' },
  // Objects
  { name: 'UserIcon', component: UserIcon, category: 'objects' },
  { name: 'CogIcon', component: CogIcon, category: 'objects' },
  { name: 'EnvelopeIcon', component: EnvelopeIcon, category: 'objects' },
  { name: 'CalendarIcon', component: CalendarIcon, category: 'objects' },
  { name: 'FolderIcon', component: FolderIcon, category: 'objects' },
  { name: 'DocumentIcon', component: DocumentIcon, category: 'objects' },
  { name: 'ChartBarIcon', component: ChartBarIcon, category: 'objects' },
  { name: 'ShoppingCartIcon', component: ShoppingCartIcon, category: 'objects' },
  { name: 'HeartIcon', component: HeartIcon, category: 'objects' },
  { name: 'StarIcon', component: StarIcon, category: 'objects' },
  { name: 'BookmarkIcon', component: BookmarkIcon, category: 'objects' },
  { name: 'LockClosedIcon', component: LockClosedIcon, category: 'objects' },
  { name: 'LockOpenIcon', component: LockOpenIcon, category: 'objects' },
  { name: 'GlobeAltIcon', component: GlobeAltIcon, category: 'objects' },
  { name: 'MapPinIcon', component: MapPinIcon, category: 'objects' },
  { name: 'PhoneIcon', component: PhoneIcon, category: 'objects' },
  { name: 'DevicePhoneMobileIcon', component: DevicePhoneMobileIcon, category: 'objects' },
  { name: 'ComputerDesktopIcon', component: ComputerDesktopIcon, category: 'objects' },
  { name: 'PrinterIcon', component: PrinterIcon, category: 'objects' },
  { name: 'WifiIcon', component: WifiIcon, category: 'objects' },
  { name: 'CreditCardIcon', component: CreditCardIcon, category: 'objects' },
  { name: 'BanknotesIcon', component: BanknotesIcon, category: 'objects' },
  { name: 'GiftIcon', component: GiftIcon, category: 'objects' },
  { name: 'TagIcon', component: TagIcon, category: 'objects' },
  { name: 'CloudIcon', component: CloudIcon, category: 'objects' },
  { name: 'CloudArrowUpIcon', component: CloudArrowUpIcon, category: 'objects' },
  { name: 'CloudArrowDownIcon', component: CloudArrowDownIcon, category: 'objects' },
  { name: 'ServerIcon', component: ServerIcon, category: 'objects' },
  { name: 'CubeIcon', component: CubeIcon, category: 'objects' },
  { name: 'SunIcon', component: SunIcon, category: 'objects' },
  { name: 'MoonIcon', component: MoonIcon, category: 'objects' }
]

const themeClasses = computed(() =>
  props.theme === 'dark' ? 'bg-gray-800' : 'bg-white'
)

const filteredIcons = computed(() => {
  let icons = iconLibrary
  
  if (activeCategory.value !== 'all') {
    icons = icons.filter(icon => icon.category === activeCategory.value)
  }
  
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    icons = icons.filter(icon => icon.name.toLowerCase().includes(query))
  }
  
  return icons
})

const selectedIcon = computed(() => {
  const icon = iconLibrary.find(i => i.name === props.modelValue)
  return icon?.component
})

const toggle = () => {
  if (props.disabled) return
  isOpen.value = !isOpen.value
  if (isOpen.value) {
    nextTick(() => searchInputRef.value?.focus())
  }
}

const selectIcon = (icon) => {
  emit('update:modelValue', icon.name)
  emit('change', icon.name)
  isOpen.value = false
}

const clearSelection = () => {
  emit('update:modelValue', '')
  emit('change', '')
}

const handleClickOutside = (e) => {
  if (containerRef.value && !containerRef.value.contains(e.target)) {
    isOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
