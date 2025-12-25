<template>
  <div class="emoji-picker relative" ref="containerRef">
    <!-- Trigger -->
    <button
      type="button"
      :class="[
        'flex items-center gap-2 px-4 py-3 rounded-xl border transition-all',
        isOpen
          ? 'border-blue-500 ring-2 ring-blue-500/20'
          : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600',
        disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer',
        themeClasses
      ]"
      :disabled="disabled"
      @click="toggle"
    >
      <span v-if="modelValue" class="text-2xl">{{ modelValue }}</span>
      <FaceSmileIcon v-else class="w-6 h-6 text-gray-400" />
      <span class="text-sm text-gray-500">{{ modelValue ? 'Change' : 'Pick emoji' }}</span>
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
          'absolute z-50 w-80 mt-2 rounded-xl border shadow-xl',
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
              placeholder="Search emoji..."
              class="w-full pl-9 pr-4 py-2 text-sm bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-gray-900 dark:text-white placeholder-gray-400"
            />
          </div>
        </div>

        <!-- Recent -->
        <div v-if="!searchQuery && recentEmojis.length > 0" class="p-3 border-b border-gray-200 dark:border-gray-700">
          <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Recently Used</p>
          <div class="flex flex-wrap gap-1">
            <button
              v-for="emoji in recentEmojis"
              :key="emoji"
              type="button"
              class="p-1.5 text-xl hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
              @click="selectEmoji(emoji)"
            >
              {{ emoji }}
            </button>
          </div>
        </div>

        <!-- Category Tabs -->
        <div class="flex gap-1 p-2 border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
          <button
            v-for="cat in categories"
            :key="cat.id"
            type="button"
            :class="[
              'p-2 rounded-lg transition-colors',
              activeCategory === cat.id
                ? 'bg-blue-100 dark:bg-blue-900/30'
                : 'hover:bg-gray-100 dark:hover:bg-gray-700'
            ]"
            :title="cat.label"
            @click="activeCategory = cat.id"
          >
            <span class="text-lg">{{ cat.icon }}</span>
          </button>
        </div>

        <!-- Emoji Grid -->
        <div class="p-3 max-h-64 overflow-auto">
          <template v-if="searchQuery">
            <div v-if="searchResults.length > 0" class="grid grid-cols-8 gap-1">
              <button
                v-for="emoji in searchResults"
                :key="emoji.emoji"
                type="button"
                class="p-1.5 text-xl hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                :title="emoji.name"
                @click="selectEmoji(emoji.emoji)"
              >
                {{ emoji.emoji }}
              </button>
            </div>
            <div v-else class="py-8 text-center">
              <FaceSmileIcon class="w-8 h-8 mx-auto text-gray-300 dark:text-gray-600 mb-2" />
              <p class="text-sm text-gray-500">No emoji found</p>
            </div>
          </template>
          <template v-else>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">{{ activeCategoryLabel }}</p>
            <div class="grid grid-cols-8 gap-1">
              <button
                v-for="emoji in activeEmojis"
                :key="emoji.emoji"
                type="button"
                class="p-1.5 text-xl hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                :title="emoji.name"
                @click="selectEmoji(emoji.emoji)"
              >
                {{ emoji.emoji }}
              </button>
            </div>
          </template>
        </div>

        <!-- Skin Tone Selector -->
        <div v-if="showSkinTones" class="flex items-center justify-center gap-2 p-3 border-t border-gray-200 dark:border-gray-700">
          <span class="text-xs text-gray-500 mr-2">Skin tone:</span>
          <button
            v-for="(tone, index) in skinTones"
            :key="index"
            type="button"
            :class="[
              'w-6 h-6 rounded-full transition-transform hover:scale-110',
              selectedSkinTone === index && 'ring-2 ring-blue-500 ring-offset-2'
            ]"
            :style="{ backgroundColor: tone.color }"
            @click="selectedSkinTone = index"
          />
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue'
import { MagnifyingGlassIcon, FaceSmileIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  modelValue: { type: String, default: '' },
  showSkinTones: { type: Boolean, default: true },
  maxRecent: { type: Number, default: 8 },
  disabled: { type: Boolean, default: false },
  theme: { type: String, default: 'light' }
})

const emit = defineEmits(['update:modelValue', 'change'])

const containerRef = ref(null)
const searchInputRef = ref(null)
const isOpen = ref(false)
const searchQuery = ref('')
const activeCategory = ref('smileys')
const selectedSkinTone = ref(0)
const recentEmojis = ref([])

const categories = [
  { id: 'smileys', label: 'Smileys & Emotion', icon: '😀' },
  { id: 'people', label: 'People & Body', icon: '👋' },
  { id: 'animals', label: 'Animals & Nature', icon: '🐶' },
  { id: 'food', label: 'Food & Drink', icon: '🍕' },
  { id: 'travel', label: 'Travel & Places', icon: '✈️' },
  { id: 'activities', label: 'Activities', icon: '⚽' },
  { id: 'objects', label: 'Objects', icon: '💡' },
  { id: 'symbols', label: 'Symbols', icon: '❤️' },
  { id: 'flags', label: 'Flags', icon: '🏳️' }
]

const skinTones = [
  { name: 'Default', color: '#FFCC4D' },
  { name: 'Light', color: '#FFDFBA' },
  { name: 'Medium Light', color: '#E9C197' },
  { name: 'Medium', color: '#C68642' },
  { name: 'Medium Dark', color: '#8D5524' },
  { name: 'Dark', color: '#5C3317' }
]

const emojiData = {
  smileys: [
    { emoji: '😀', name: 'grinning face' },
    { emoji: '😃', name: 'grinning face with big eyes' },
    { emoji: '😄', name: 'grinning face with smiling eyes' },
    { emoji: '😁', name: 'beaming face' },
    { emoji: '😆', name: 'grinning squinting face' },
    { emoji: '😅', name: 'grinning face with sweat' },
    { emoji: '🤣', name: 'rolling on floor laughing' },
    { emoji: '😂', name: 'face with tears of joy' },
    { emoji: '🙂', name: 'slightly smiling face' },
    { emoji: '🙃', name: 'upside down face' },
    { emoji: '😉', name: 'winking face' },
    { emoji: '😊', name: 'smiling face with smiling eyes' },
    { emoji: '😇', name: 'smiling face with halo' },
    { emoji: '🥰', name: 'smiling face with hearts' },
    { emoji: '😍', name: 'smiling face with heart eyes' },
    { emoji: '🤩', name: 'star struck' },
    { emoji: '😘', name: 'face blowing a kiss' },
    { emoji: '😗', name: 'kissing face' },
    { emoji: '😚', name: 'kissing face with closed eyes' },
    { emoji: '😙', name: 'kissing face with smiling eyes' },
    { emoji: '🥲', name: 'smiling face with tear' },
    { emoji: '😋', name: 'face savoring food' },
    { emoji: '😛', name: 'face with tongue' },
    { emoji: '😜', name: 'winking face with tongue' },
    { emoji: '🤪', name: 'zany face' },
    { emoji: '😝', name: 'squinting face with tongue' },
    { emoji: '🤑', name: 'money mouth face' },
    { emoji: '🤗', name: 'hugging face' },
    { emoji: '🤭', name: 'face with hand over mouth' },
    { emoji: '🤫', name: 'shushing face' },
    { emoji: '🤔', name: 'thinking face' },
    { emoji: '🤐', name: 'zipper mouth face' }
  ],
  people: [
    { emoji: '👋', name: 'waving hand' },
    { emoji: '🤚', name: 'raised back of hand' },
    { emoji: '🖐️', name: 'hand with fingers splayed' },
    { emoji: '✋', name: 'raised hand' },
    { emoji: '🖖', name: 'vulcan salute' },
    { emoji: '👌', name: 'ok hand' },
    { emoji: '🤌', name: 'pinched fingers' },
    { emoji: '🤏', name: 'pinching hand' },
    { emoji: '✌️', name: 'victory hand' },
    { emoji: '🤞', name: 'crossed fingers' },
    { emoji: '🤟', name: 'love you gesture' },
    { emoji: '🤘', name: 'sign of the horns' },
    { emoji: '🤙', name: 'call me hand' },
    { emoji: '👈', name: 'backhand index pointing left' },
    { emoji: '👉', name: 'backhand index pointing right' },
    { emoji: '👆', name: 'backhand index pointing up' },
    { emoji: '🖕', name: 'middle finger' },
    { emoji: '👇', name: 'backhand index pointing down' },
    { emoji: '☝️', name: 'index pointing up' },
    { emoji: '👍', name: 'thumbs up' },
    { emoji: '👎', name: 'thumbs down' },
    { emoji: '✊', name: 'raised fist' },
    { emoji: '👊', name: 'oncoming fist' },
    { emoji: '🤛', name: 'left facing fist' },
    { emoji: '🤜', name: 'right facing fist' },
    { emoji: '👏', name: 'clapping hands' },
    { emoji: '🙌', name: 'raising hands' },
    { emoji: '👐', name: 'open hands' },
    { emoji: '🤲', name: 'palms up together' },
    { emoji: '🤝', name: 'handshake' },
    { emoji: '🙏', name: 'folded hands' },
    { emoji: '💪', name: 'flexed biceps' }
  ],
  animals: [
    { emoji: '🐶', name: 'dog face' },
    { emoji: '🐱', name: 'cat face' },
    { emoji: '🐭', name: 'mouse face' },
    { emoji: '🐹', name: 'hamster' },
    { emoji: '🐰', name: 'rabbit face' },
    { emoji: '🦊', name: 'fox' },
    { emoji: '🐻', name: 'bear' },
    { emoji: '🐼', name: 'panda' },
    { emoji: '🐨', name: 'koala' },
    { emoji: '🐯', name: 'tiger face' },
    { emoji: '🦁', name: 'lion' },
    { emoji: '🐮', name: 'cow face' },
    { emoji: '🐷', name: 'pig face' },
    { emoji: '🐸', name: 'frog' },
    { emoji: '🐵', name: 'monkey face' },
    { emoji: '🐔', name: 'chicken' },
    { emoji: '🐧', name: 'penguin' },
    { emoji: '🐦', name: 'bird' },
    { emoji: '🐤', name: 'baby chick' },
    { emoji: '🦆', name: 'duck' },
    { emoji: '🦅', name: 'eagle' },
    { emoji: '🦉', name: 'owl' },
    { emoji: '🦇', name: 'bat' },
    { emoji: '🐺', name: 'wolf' },
    { emoji: '🐗', name: 'boar' },
    { emoji: '🐴', name: 'horse face' },
    { emoji: '🦄', name: 'unicorn' },
    { emoji: '🐝', name: 'honeybee' },
    { emoji: '🪱', name: 'worm' },
    { emoji: '🐛', name: 'bug' },
    { emoji: '🦋', name: 'butterfly' },
    { emoji: '🐌', name: 'snail' }
  ],
  food: [
    { emoji: '🍕', name: 'pizza' },
    { emoji: '🍔', name: 'hamburger' },
    { emoji: '🍟', name: 'french fries' },
    { emoji: '🌭', name: 'hot dog' },
    { emoji: '🍿', name: 'popcorn' },
    { emoji: '🧂', name: 'salt' },
    { emoji: '🥓', name: 'bacon' },
    { emoji: '🥚', name: 'egg' },
    { emoji: '🍳', name: 'cooking' },
    { emoji: '🥞', name: 'pancakes' },
    { emoji: '🧇', name: 'waffle' },
    { emoji: '🥐', name: 'croissant' },
    { emoji: '🍞', name: 'bread' },
    { emoji: '🥖', name: 'baguette bread' },
    { emoji: '🥨', name: 'pretzel' },
    { emoji: '🧀', name: 'cheese wedge' },
    { emoji: '🥗', name: 'green salad' },
    { emoji: '🥙', name: 'stuffed flatbread' },
    { emoji: '🌮', name: 'taco' },
    { emoji: '🌯', name: 'burrito' },
    { emoji: '🍱', name: 'bento box' },
    { emoji: '🍣', name: 'sushi' },
    { emoji: '🍜', name: 'steaming bowl' },
    { emoji: '🍝', name: 'spaghetti' },
    { emoji: '🍛', name: 'curry rice' },
    { emoji: '🍚', name: 'cooked rice' },
    { emoji: '☕', name: 'hot beverage' },
    { emoji: '🍵', name: 'teacup without handle' },
    { emoji: '🧃', name: 'beverage box' },
    { emoji: '🥤', name: 'cup with straw' },
    { emoji: '🍺', name: 'beer mug' },
    { emoji: '🍷', name: 'wine glass' }
  ],
  travel: [
    { emoji: '✈️', name: 'airplane' },
    { emoji: '🚀', name: 'rocket' },
    { emoji: '🚁', name: 'helicopter' },
    { emoji: '🚂', name: 'locomotive' },
    { emoji: '🚃', name: 'railway car' },
    { emoji: '🚄', name: 'high speed train' },
    { emoji: '🚅', name: 'bullet train' },
    { emoji: '🚆', name: 'train' },
    { emoji: '🚇', name: 'metro' },
    { emoji: '🚈', name: 'light rail' },
    { emoji: '🚗', name: 'automobile' },
    { emoji: '🚕', name: 'taxi' },
    { emoji: '🚌', name: 'bus' },
    { emoji: '🚎', name: 'trolleybus' },
    { emoji: '🏎️', name: 'racing car' },
    { emoji: '🚓', name: 'police car' },
    { emoji: '🚑', name: 'ambulance' },
    { emoji: '🚒', name: 'fire engine' },
    { emoji: '🛵', name: 'motor scooter' },
    { emoji: '🚲', name: 'bicycle' },
    { emoji: '🛴', name: 'kick scooter' },
    { emoji: '⛵', name: 'sailboat' },
    { emoji: '🚤', name: 'speedboat' },
    { emoji: '🛳️', name: 'passenger ship' },
    { emoji: '🏠', name: 'house' },
    { emoji: '🏡', name: 'house with garden' },
    { emoji: '🏢', name: 'office building' },
    { emoji: '🏨', name: 'hotel' },
    { emoji: '🏥', name: 'hospital' },
    { emoji: '🏦', name: 'bank' },
    { emoji: '🏪', name: 'convenience store' },
    { emoji: '🗼', name: 'Tokyo tower' }
  ],
  activities: [
    { emoji: '⚽', name: 'soccer ball' },
    { emoji: '🏀', name: 'basketball' },
    { emoji: '🏈', name: 'american football' },
    { emoji: '⚾', name: 'baseball' },
    { emoji: '🥎', name: 'softball' },
    { emoji: '🎾', name: 'tennis' },
    { emoji: '🏐', name: 'volleyball' },
    { emoji: '🏉', name: 'rugby football' },
    { emoji: '🥏', name: 'flying disc' },
    { emoji: '🎱', name: 'pool 8 ball' },
    { emoji: '🪀', name: 'yo-yo' },
    { emoji: '🏓', name: 'ping pong' },
    { emoji: '🏸', name: 'badminton' },
    { emoji: '🏒', name: 'ice hockey' },
    { emoji: '🏑', name: 'field hockey' },
    { emoji: '🥍', name: 'lacrosse' },
    { emoji: '🏏', name: 'cricket game' },
    { emoji: '🪃', name: 'boomerang' },
    { emoji: '🥅', name: 'goal net' },
    { emoji: '⛳', name: 'flag in hole' },
    { emoji: '🪁', name: 'kite' },
    { emoji: '🎣', name: 'fishing pole' },
    { emoji: '🤿', name: 'diving mask' },
    { emoji: '🎽', name: 'running shirt' },
    { emoji: '🎿', name: 'skis' },
    { emoji: '🛷', name: 'sled' },
    { emoji: '🥌', name: 'curling stone' },
    { emoji: '🎯', name: 'direct hit' },
    { emoji: '🎮', name: 'video game' },
    { emoji: '🎰', name: 'slot machine' },
    { emoji: '🎲', name: 'game die' },
    { emoji: '🧩', name: 'puzzle piece' }
  ],
  objects: [
    { emoji: '💡', name: 'light bulb' },
    { emoji: '🔦', name: 'flashlight' },
    { emoji: '🏮', name: 'red paper lantern' },
    { emoji: '🪔', name: 'diya lamp' },
    { emoji: '📱', name: 'mobile phone' },
    { emoji: '💻', name: 'laptop' },
    { emoji: '🖥️', name: 'desktop computer' },
    { emoji: '🖨️', name: 'printer' },
    { emoji: '⌨️', name: 'keyboard' },
    { emoji: '🖱️', name: 'computer mouse' },
    { emoji: '💾', name: 'floppy disk' },
    { emoji: '💿', name: 'optical disk' },
    { emoji: '📀', name: 'dvd' },
    { emoji: '🎥', name: 'movie camera' },
    { emoji: '📸', name: 'camera with flash' },
    { emoji: '📹', name: 'video camera' },
    { emoji: '📺', name: 'television' },
    { emoji: '📻', name: 'radio' },
    { emoji: '🎙️', name: 'studio microphone' },
    { emoji: '🎚️', name: 'level slider' },
    { emoji: '🎛️', name: 'control knobs' },
    { emoji: '⏱️', name: 'stopwatch' },
    { emoji: '⏲️', name: 'timer clock' },
    { emoji: '⏰', name: 'alarm clock' },
    { emoji: '📧', name: 'e-mail' },
    { emoji: '✉️', name: 'envelope' },
    { emoji: '📦', name: 'package' },
    { emoji: '🏷️', name: 'label' },
    { emoji: '✏️', name: 'pencil' },
    { emoji: '📝', name: 'memo' },
    { emoji: '📁', name: 'file folder' },
    { emoji: '🗂️', name: 'card index dividers' }
  ],
  symbols: [
    { emoji: '❤️', name: 'red heart' },
    { emoji: '🧡', name: 'orange heart' },
    { emoji: '💛', name: 'yellow heart' },
    { emoji: '💚', name: 'green heart' },
    { emoji: '💙', name: 'blue heart' },
    { emoji: '💜', name: 'purple heart' },
    { emoji: '🖤', name: 'black heart' },
    { emoji: '🤍', name: 'white heart' },
    { emoji: '🤎', name: 'brown heart' },
    { emoji: '💔', name: 'broken heart' },
    { emoji: '💯', name: 'hundred points' },
    { emoji: '💢', name: 'anger symbol' },
    { emoji: '💥', name: 'collision' },
    { emoji: '💫', name: 'dizzy' },
    { emoji: '💦', name: 'sweat droplets' },
    { emoji: '💨', name: 'dashing away' },
    { emoji: '✨', name: 'sparkles' },
    { emoji: '⭐', name: 'star' },
    { emoji: '🌟', name: 'glowing star' },
    { emoji: '💫', name: 'dizzy' },
    { emoji: '✅', name: 'check mark button' },
    { emoji: '❌', name: 'cross mark' },
    { emoji: '❓', name: 'question mark' },
    { emoji: '❗', name: 'exclamation mark' },
    { emoji: '➕', name: 'plus' },
    { emoji: '➖', name: 'minus' },
    { emoji: '➗', name: 'divide' },
    { emoji: '✖️', name: 'multiply' },
    { emoji: '🔴', name: 'red circle' },
    { emoji: '🟠', name: 'orange circle' },
    { emoji: '🟡', name: 'yellow circle' },
    { emoji: '🟢', name: 'green circle' }
  ],
  flags: [
    { emoji: '🏳️', name: 'white flag' },
    { emoji: '🏴', name: 'black flag' },
    { emoji: '🏁', name: 'chequered flag' },
    { emoji: '🚩', name: 'triangular flag' },
    { emoji: '🎌', name: 'crossed flags' },
    { emoji: '🏳️‍🌈', name: 'rainbow flag' },
    { emoji: '🇧🇩', name: 'flag: Bangladesh' },
    { emoji: '🇺🇸', name: 'flag: United States' },
    { emoji: '🇬🇧', name: 'flag: United Kingdom' },
    { emoji: '🇨🇦', name: 'flag: Canada' },
    { emoji: '🇦🇺', name: 'flag: Australia' },
    { emoji: '🇮🇳', name: 'flag: India' },
    { emoji: '🇯🇵', name: 'flag: Japan' },
    { emoji: '🇰🇷', name: 'flag: South Korea' },
    { emoji: '🇨🇳', name: 'flag: China' },
    { emoji: '🇩🇪', name: 'flag: Germany' },
    { emoji: '🇫🇷', name: 'flag: France' },
    { emoji: '🇮🇹', name: 'flag: Italy' },
    { emoji: '🇪🇸', name: 'flag: Spain' },
    { emoji: '🇧🇷', name: 'flag: Brazil' },
    { emoji: '🇲🇽', name: 'flag: Mexico' },
    { emoji: '🇷🇺', name: 'flag: Russia' },
    { emoji: '🇸🇦', name: 'flag: Saudi Arabia' },
    { emoji: '🇦🇪', name: 'flag: UAE' },
    { emoji: '🇸🇬', name: 'flag: Singapore' },
    { emoji: '🇲🇾', name: 'flag: Malaysia' },
    { emoji: '🇹🇭', name: 'flag: Thailand' },
    { emoji: '🇻🇳', name: 'flag: Vietnam' },
    { emoji: '🇵🇭', name: 'flag: Philippines' },
    { emoji: '🇮🇩', name: 'flag: Indonesia' },
    { emoji: '🇵🇰', name: 'flag: Pakistan' },
    { emoji: '🇳🇵', name: 'flag: Nepal' }
  ]
}

const themeClasses = computed(() =>
  props.theme === 'dark' ? 'bg-gray-800' : 'bg-white'
)

const activeCategoryLabel = computed(() => 
  categories.find(c => c.id === activeCategory.value)?.label || ''
)

const activeEmojis = computed(() => 
  emojiData[activeCategory.value] || []
)

const searchResults = computed(() => {
  if (!searchQuery.value) return []
  const query = searchQuery.value.toLowerCase()
  const results = []
  Object.values(emojiData).forEach(category => {
    category.forEach(emoji => {
      if (emoji.name.toLowerCase().includes(query)) {
        results.push(emoji)
      }
    })
  })
  return results.slice(0, 40)
})

const toggle = () => {
  if (props.disabled) return
  isOpen.value = !isOpen.value
  if (isOpen.value) {
    nextTick(() => searchInputRef.value?.focus())
  }
}

const selectEmoji = (emoji) => {
  emit('update:modelValue', emoji)
  emit('change', emoji)
  
  // Add to recent
  const index = recentEmojis.value.indexOf(emoji)
  if (index > -1) {
    recentEmojis.value.splice(index, 1)
  }
  recentEmojis.value.unshift(emoji)
  if (recentEmojis.value.length > props.maxRecent) {
    recentEmojis.value.pop()
  }
  
  isOpen.value = false
}

const handleClickOutside = (e) => {
  if (containerRef.value && !containerRef.value.contains(e.target)) {
    isOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  // Load recent from localStorage if available
  try {
    const stored = localStorage.getItem('recent-emojis')
    if (stored) recentEmojis.value = JSON.parse(stored)
  } catch (e) {}
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})

// Save recent to localStorage
watch(recentEmojis, (val) => {
  try {
    localStorage.setItem('recent-emojis', JSON.stringify(val))
  } catch (e) {}
}, { deep: true })
</script>
