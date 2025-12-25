<template>
  <div :class="['theme-customizer', themeClasses]">
    <!-- Header -->
    <div v-if="showHeader" class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center gap-3">
        <div class="p-2 bg-violet-100 dark:bg-violet-900/30 rounded-lg">
          <SwatchIcon class="w-5 h-5 text-violet-600 dark:text-violet-400" />
        </div>
        <div>
          <h3 class="font-semibold text-gray-900 dark:text-white">Theme Customizer</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400">Customize your theme</p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <button
          type="button"
          class="px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
          @click="resetToDefault"
        >
          Reset
        </button>
        <button
          type="button"
          class="px-3 py-1.5 text-sm font-medium text-white bg-violet-600 hover:bg-violet-700 rounded-lg transition-colors"
          @click="exportTheme"
        >
          Export CSS
        </button>
      </div>
    </div>

    <div class="flex" :style="{ height }">
      <!-- Controls Panel -->
      <div class="w-80 border-r border-gray-200 dark:border-gray-700 overflow-y-auto">
        <!-- Preset Themes -->
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
          <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Preset Themes</h4>
          <div class="grid grid-cols-4 gap-2">
            <button
              v-for="preset in presets"
              :key="preset.name"
              class="group relative"
              @click="applyPreset(preset)"
              :title="preset.name"
            >
              <div
                class="w-full aspect-square rounded-lg border-2 transition-all overflow-hidden"
                :class="currentPreset === preset.name ? 'border-violet-500 scale-105' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300'"
              >
                <div class="h-1/2" :style="{ backgroundColor: preset.colors.primary }"></div>
                <div class="h-1/2 flex">
                  <div class="w-1/2" :style="{ backgroundColor: preset.colors.background }"></div>
                  <div class="w-1/2" :style="{ backgroundColor: preset.colors.surface }"></div>
                </div>
              </div>
              <span class="block text-xs text-gray-600 dark:text-gray-400 mt-1 text-center truncate">
                {{ preset.name }}
              </span>
            </button>
          </div>
        </div>

        <!-- Color Settings -->
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
          <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Colors</h4>
          <div class="space-y-4">
            <div v-for="color in colorSettings" :key="color.key">
              <div class="flex items-center justify-between mb-1">
                <label class="text-sm text-gray-600 dark:text-gray-400">{{ color.label }}</label>
                <span class="text-xs font-mono text-gray-500">{{ customTheme[color.key] }}</span>
              </div>
              <div class="flex items-center gap-2">
                <input
                  type="color"
                  :value="customTheme[color.key]"
                  @input="updateColor(color.key, $event.target.value)"
                  class="w-10 h-10 rounded-lg cursor-pointer border-2 border-gray-200 dark:border-gray-600"
                />
                <input
                  type="text"
                  :value="customTheme[color.key]"
                  @input="updateColor(color.key, $event.target.value)"
                  class="flex-1 px-3 py-2 text-sm font-mono bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Typography Settings -->
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
          <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Typography</h4>
          <div class="space-y-4">
            <div>
              <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Font Family</label>
              <select
                v-model="customTheme.fontFamily"
                class="w-full px-3 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg"
              >
                <option v-for="font in fonts" :key="font.value" :value="font.value">
                  {{ font.label }}
                </option>
              </select>
            </div>
            <div>
              <div class="flex items-center justify-between mb-1">
                <label class="text-sm text-gray-600 dark:text-gray-400">Base Font Size</label>
                <span class="text-xs font-mono text-gray-500">{{ customTheme.fontSize }}px</span>
              </div>
              <input
                type="range"
                min="12"
                max="20"
                step="1"
                v-model.number="customTheme.fontSize"
                class="w-full"
              />
            </div>
            <div>
              <div class="flex items-center justify-between mb-1">
                <label class="text-sm text-gray-600 dark:text-gray-400">Line Height</label>
                <span class="text-xs font-mono text-gray-500">{{ customTheme.lineHeight }}</span>
              </div>
              <input
                type="range"
                min="1"
                max="2"
                step="0.1"
                v-model.number="customTheme.lineHeight"
                class="w-full"
              />
            </div>
          </div>
        </div>

        <!-- Spacing & Radius -->
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
          <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Spacing & Radius</h4>
          <div class="space-y-4">
            <div>
              <div class="flex items-center justify-between mb-1">
                <label class="text-sm text-gray-600 dark:text-gray-400">Border Radius</label>
                <span class="text-xs font-mono text-gray-500">{{ customTheme.borderRadius }}px</span>
              </div>
              <input
                type="range"
                min="0"
                max="24"
                step="2"
                v-model.number="customTheme.borderRadius"
                class="w-full"
              />
            </div>
            <div>
              <div class="flex items-center justify-between mb-1">
                <label class="text-sm text-gray-600 dark:text-gray-400">Spacing Scale</label>
                <span class="text-xs font-mono text-gray-500">{{ customTheme.spacingScale }}x</span>
              </div>
              <input
                type="range"
                min="0.5"
                max="2"
                step="0.1"
                v-model.number="customTheme.spacingScale"
                class="w-full"
              />
            </div>
          </div>
        </div>

        <!-- Shadows -->
        <div class="p-4">
          <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Shadows</h4>
          <div class="space-y-3">
            <label
              v-for="shadow in shadowOptions"
              :key="shadow.value"
              class="flex items-center gap-2 cursor-pointer"
            >
              <input
                type="radio"
                :value="shadow.value"
                v-model="customTheme.shadowStyle"
                class="w-4 h-4 text-violet-600 border-gray-300 focus:ring-violet-500"
              />
              <span class="text-sm text-gray-700 dark:text-gray-300">{{ shadow.label }}</span>
            </label>
          </div>
        </div>
      </div>

      <!-- Preview Panel -->
      <div class="flex-1 p-6 overflow-y-auto bg-gray-100 dark:bg-gray-800/50">
        <div
          class="max-w-lg mx-auto p-6 rounded-xl shadow-lg"
          :style="previewStyles"
        >
          <h2 class="text-2xl font-bold mb-4" :style="{ color: customTheme.textPrimary }">
            Theme Preview
          </h2>
          
          <p class="mb-6" :style="{ color: customTheme.textSecondary, lineHeight: customTheme.lineHeight }">
            This is a preview of your custom theme. You can see how your colors, typography, and spacing choices affect the overall appearance.
          </p>

          <!-- Sample Button Group -->
          <div class="flex flex-wrap gap-3 mb-6">
            <button
              class="px-4 py-2 font-medium text-white transition-colors"
              :style="{
                backgroundColor: customTheme.primary,
                borderRadius: `${customTheme.borderRadius}px`
              }"
            >
              Primary Button
            </button>
            <button
              class="px-4 py-2 font-medium border-2 transition-colors"
              :style="{
                color: customTheme.primary,
                borderColor: customTheme.primary,
                borderRadius: `${customTheme.borderRadius}px`
              }"
            >
              Secondary
            </button>
            <button
              class="px-4 py-2 font-medium transition-colors"
              :style="{
                backgroundColor: customTheme.accent,
                color: '#FFFFFF',
                borderRadius: `${customTheme.borderRadius}px`
              }"
            >
              Accent
            </button>
          </div>

          <!-- Sample Card -->
          <div
            class="p-4 mb-6"
            :style="{
              backgroundColor: customTheme.surface,
              borderRadius: `${customTheme.borderRadius}px`,
              boxShadow: getShadow()
            }"
          >
            <div class="flex items-center gap-3 mb-3">
              <div
                class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold"
                :style="{ backgroundColor: customTheme.primary }"
              >
                JD
              </div>
              <div>
                <h4 class="font-medium" :style="{ color: customTheme.textPrimary }">John Doe</h4>
                <p class="text-sm" :style="{ color: customTheme.textSecondary }">Developer</p>
              </div>
            </div>
            <p :style="{ color: customTheme.textSecondary }">
              Sample card content with custom theme styling.
            </p>
          </div>

          <!-- Sample Form -->
          <div class="space-y-4 mb-6">
            <div>
              <label class="block text-sm font-medium mb-1" :style="{ color: customTheme.textPrimary }">
                Input Field
              </label>
              <input
                type="text"
                placeholder="Enter text..."
                class="w-full px-3 py-2 border"
                :style="{
                  backgroundColor: customTheme.surface,
                  borderColor: customTheme.border,
                  borderRadius: `${customTheme.borderRadius}px`,
                  color: customTheme.textPrimary
                }"
              />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1" :style="{ color: customTheme.textPrimary }">
                Select Field
              </label>
              <select
                class="w-full px-3 py-2 border"
                :style="{
                  backgroundColor: customTheme.surface,
                  borderColor: customTheme.border,
                  borderRadius: `${customTheme.borderRadius}px`,
                  color: customTheme.textPrimary
                }"
              >
                <option>Option 1</option>
                <option>Option 2</option>
                <option>Option 3</option>
              </select>
            </div>
          </div>

          <!-- Sample Badges -->
          <div class="flex flex-wrap gap-2">
            <span
              class="px-2 py-1 text-xs font-medium"
              :style="{
                backgroundColor: customTheme.success + '20',
                color: customTheme.success,
                borderRadius: `${customTheme.borderRadius / 2}px`
              }"
            >
              Success
            </span>
            <span
              class="px-2 py-1 text-xs font-medium"
              :style="{
                backgroundColor: customTheme.warning + '20',
                color: customTheme.warning,
                borderRadius: `${customTheme.borderRadius / 2}px`
              }"
            >
              Warning
            </span>
            <span
              class="px-2 py-1 text-xs font-medium"
              :style="{
                backgroundColor: customTheme.error + '20',
                color: customTheme.error,
                borderRadius: `${customTheme.borderRadius / 2}px`
              }"
            >
              Error
            </span>
            <span
              class="px-2 py-1 text-xs font-medium"
              :style="{
                backgroundColor: customTheme.info + '20',
                color: customTheme.info,
                borderRadius: `${customTheme.borderRadius / 2}px`
              }"
            >
              Info
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, reactive, watch } from 'vue'
import { SwatchIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  height: {
    type: String,
    default: '600px'
  },
  theme: {
    type: String,
    default: 'light',
    validator: (v) => ['light', 'dark'].includes(v)
  },
  showHeader: {
    type: Boolean,
    default: true
  },
  initialTheme: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['change', 'export'])

const defaultTheme = {
  primary: '#8B5CF6',
  secondary: '#6366F1',
  accent: '#EC4899',
  background: '#FFFFFF',
  surface: '#F9FAFB',
  textPrimary: '#111827',
  textSecondary: '#6B7280',
  border: '#E5E7EB',
  success: '#10B981',
  warning: '#F59E0B',
  error: '#EF4444',
  info: '#3B82F6',
  fontFamily: 'Inter, system-ui, sans-serif',
  fontSize: 16,
  lineHeight: 1.5,
  borderRadius: 8,
  spacingScale: 1,
  shadowStyle: 'medium'
}

const customTheme = reactive({ ...defaultTheme, ...props.initialTheme })
const currentPreset = ref('Custom')

const presets = [
  {
    name: 'Default',
    colors: { primary: '#8B5CF6', background: '#FFFFFF', surface: '#F9FAFB' },
    theme: { ...defaultTheme }
  },
  {
    name: 'Ocean',
    colors: { primary: '#0EA5E9', background: '#F0F9FF', surface: '#E0F2FE' },
    theme: {
      ...defaultTheme,
      primary: '#0EA5E9',
      secondary: '#0284C7',
      accent: '#06B6D4',
      background: '#F0F9FF',
      surface: '#E0F2FE',
      border: '#BAE6FD'
    }
  },
  {
    name: 'Forest',
    colors: { primary: '#059669', background: '#F0FDF4', surface: '#DCFCE7' },
    theme: {
      ...defaultTheme,
      primary: '#059669',
      secondary: '#047857',
      accent: '#10B981',
      background: '#F0FDF4',
      surface: '#DCFCE7',
      border: '#BBF7D0'
    }
  },
  {
    name: 'Sunset',
    colors: { primary: '#EA580C', background: '#FFF7ED', surface: '#FFEDD5' },
    theme: {
      ...defaultTheme,
      primary: '#EA580C',
      secondary: '#C2410C',
      accent: '#F97316',
      background: '#FFF7ED',
      surface: '#FFEDD5',
      border: '#FED7AA'
    }
  },
  {
    name: 'Dark',
    colors: { primary: '#A78BFA', background: '#111827', surface: '#1F2937' },
    theme: {
      ...defaultTheme,
      primary: '#A78BFA',
      secondary: '#818CF8',
      accent: '#F472B6',
      background: '#111827',
      surface: '#1F2937',
      textPrimary: '#F9FAFB',
      textSecondary: '#9CA3AF',
      border: '#374151'
    }
  },
  {
    name: 'Midnight',
    colors: { primary: '#60A5FA', background: '#0F172A', surface: '#1E293B' },
    theme: {
      ...defaultTheme,
      primary: '#60A5FA',
      secondary: '#3B82F6',
      accent: '#38BDF8',
      background: '#0F172A',
      surface: '#1E293B',
      textPrimary: '#F1F5F9',
      textSecondary: '#94A3B8',
      border: '#334155'
    }
  },
  {
    name: 'Rose',
    colors: { primary: '#E11D48', background: '#FFF1F2', surface: '#FFE4E6' },
    theme: {
      ...defaultTheme,
      primary: '#E11D48',
      secondary: '#BE123C',
      accent: '#FB7185',
      background: '#FFF1F2',
      surface: '#FFE4E6',
      border: '#FECDD3'
    }
  },
  {
    name: 'Lavender',
    colors: { primary: '#7C3AED', background: '#FAF5FF', surface: '#F3E8FF' },
    theme: {
      ...defaultTheme,
      primary: '#7C3AED',
      secondary: '#6D28D9',
      accent: '#A78BFA',
      background: '#FAF5FF',
      surface: '#F3E8FF',
      border: '#E9D5FF'
    }
  }
]

const colorSettings = [
  { key: 'primary', label: 'Primary' },
  { key: 'secondary', label: 'Secondary' },
  { key: 'accent', label: 'Accent' },
  { key: 'background', label: 'Background' },
  { key: 'surface', label: 'Surface' },
  { key: 'textPrimary', label: 'Text Primary' },
  { key: 'textSecondary', label: 'Text Secondary' },
  { key: 'border', label: 'Border' },
  { key: 'success', label: 'Success' },
  { key: 'warning', label: 'Warning' },
  { key: 'error', label: 'Error' },
  { key: 'info', label: 'Info' }
]

const fonts = [
  { value: 'Inter, system-ui, sans-serif', label: 'Inter' },
  { value: 'system-ui, sans-serif', label: 'System' },
  { value: '"Segoe UI", sans-serif', label: 'Segoe UI' },
  { value: 'Roboto, sans-serif', label: 'Roboto' },
  { value: '"Open Sans", sans-serif', label: 'Open Sans' },
  { value: 'Poppins, sans-serif', label: 'Poppins' },
  { value: '"Source Sans Pro", sans-serif', label: 'Source Sans' },
  { value: 'Georgia, serif', label: 'Georgia' },
  { value: '"Fira Code", monospace', label: 'Fira Code' }
]

const shadowOptions = [
  { value: 'none', label: 'No shadow' },
  { value: 'subtle', label: 'Subtle' },
  { value: 'medium', label: 'Medium' },
  { value: 'strong', label: 'Strong' },
  { value: 'glow', label: 'Glow' }
]

const themeClasses = computed(() => [
  'rounded-xl border overflow-hidden',
  props.theme === 'dark'
    ? 'bg-gray-900 border-gray-700'
    : 'bg-white border-gray-200'
])

const previewStyles = computed(() => ({
  backgroundColor: customTheme.background,
  fontFamily: customTheme.fontFamily,
  fontSize: `${customTheme.fontSize}px`,
  lineHeight: customTheme.lineHeight
}))

const getShadow = () => {
  switch (customTheme.shadowStyle) {
    case 'none': return 'none'
    case 'subtle': return '0 1px 3px rgba(0,0,0,0.1)'
    case 'medium': return '0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06)'
    case 'strong': return '0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05)'
    case 'glow': return `0 0 20px ${customTheme.primary}30`
    default: return '0 4px 6px -1px rgba(0,0,0,0.1)'
  }
}

const updateColor = (key, value) => {
  customTheme[key] = value
  currentPreset.value = 'Custom'
}

const applyPreset = (preset) => {
  Object.assign(customTheme, preset.theme)
  currentPreset.value = preset.name
}

const resetToDefault = () => {
  Object.assign(customTheme, defaultTheme)
  currentPreset.value = 'Default'
}

const exportTheme = () => {
  const css = `:root {
  /* Colors */
  --color-primary: ${customTheme.primary};
  --color-secondary: ${customTheme.secondary};
  --color-accent: ${customTheme.accent};
  --color-background: ${customTheme.background};
  --color-surface: ${customTheme.surface};
  --color-text-primary: ${customTheme.textPrimary};
  --color-text-secondary: ${customTheme.textSecondary};
  --color-border: ${customTheme.border};
  --color-success: ${customTheme.success};
  --color-warning: ${customTheme.warning};
  --color-error: ${customTheme.error};
  --color-info: ${customTheme.info};
  
  /* Typography */
  --font-family: ${customTheme.fontFamily};
  --font-size-base: ${customTheme.fontSize}px;
  --line-height: ${customTheme.lineHeight};
  
  /* Spacing & Radius */
  --border-radius: ${customTheme.borderRadius}px;
  --spacing-scale: ${customTheme.spacingScale};
  
  /* Shadows */
  --shadow: ${getShadow()};
}`

  emit('export', css)
  
  navigator.clipboard.writeText(css)
    .then(() => alert('Theme CSS copied to clipboard!'))
    .catch(console.error)
}

watch(customTheme, (newTheme) => {
  emit('change', { ...newTheme })
}, { deep: true })
</script>
