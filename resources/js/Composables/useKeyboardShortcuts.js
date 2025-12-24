import { ref, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'

/**
 * useKeyboardShortcuts Composable
 * Provides keyboard navigation and shortcuts for admin pages
 *
 * Usage:
 * const { showHelp, registerShortcuts } = useKeyboardShortcuts()
 *
 * registerShortcuts([
 *   { key: 'c', description: 'Create new item', action: () => router.visit('/create') },
 *   { key: 's', description: 'Search', action: () => focusSearch() },
 * ])
 */

export function useKeyboardShortcuts(options = {}) {
  const showHelp = ref(false)
  const shortcuts = ref([])
  const isEnabled = ref(true)

  // Default global shortcuts
  const globalShortcuts = [
    { key: '?', description: 'Show keyboard shortcuts', action: () => showHelp.value = true },
    { key: 'Escape', description: 'Close dialogs/modals', action: () => showHelp.value = false },
    { key: 'g h', description: 'Go to Dashboard', action: () => router.visit(route('admin.dashboard')) },
    { key: 'g u', description: 'Go to Users', action: () => router.visit(route('admin.users.index')) },
    { key: 'g j', description: 'Go to Jobs', action: () => router.visit(route('admin.jobs.index')) },
    { key: 'g v', description: 'Go to Visa Applications', action: () => router.visit(route('admin.visa.index')) },
    { key: 'g w', description: 'Go to Wallets', action: () => router.visit(route('admin.wallets.index')) },
    { key: 'g e', description: 'Go to Events', action: () => router.visit(route('admin.events.index')) },
    { key: 'g s', description: 'Go to Settings', action: () => router.visit(route('admin.settings.index')) },
  ]

  // Track key sequence for multi-key shortcuts (e.g., 'g h')
  let keySequence = ''
  let sequenceTimeout = null

  const handleKeyDown = (event) => {
    // Don't trigger shortcuts when typing in inputs
    if (
      !isEnabled.value ||
      event.target.tagName === 'INPUT' ||
      event.target.tagName === 'TEXTAREA' ||
      event.target.tagName === 'SELECT' ||
      event.target.isContentEditable
    ) {
      // Allow Escape to still work
      if (event.key === 'Escape') {
        showHelp.value = false
      }
      return
    }

    // Build key string
    let keyString = ''
    if (event.ctrlKey || event.metaKey) keyString += 'ctrl+'
    if (event.altKey) keyString += 'alt+'
    if (event.shiftKey && event.key !== '?') keyString += 'shift+'
    keyString += event.key.toLowerCase()

    // Handle ? key (shift+/)
    if (event.key === '?') {
      event.preventDefault()
      showHelp.value = !showHelp.value
      return
    }

    // Handle Escape
    if (event.key === 'Escape') {
      showHelp.value = false
      return
    }

    // Handle key sequences (e.g., 'g h')
    clearTimeout(sequenceTimeout)
    keySequence += (keySequence ? ' ' : '') + keyString
    
    // Check for matching shortcut
    const allShortcuts = [...globalShortcuts, ...shortcuts.value]
    const match = allShortcuts.find(s => s.key.toLowerCase() === keySequence)
    
    if (match) {
      event.preventDefault()
      match.action()
      keySequence = ''
      return
    }

    // Check if current sequence could lead to a match
    const possibleMatch = allShortcuts.some(s => s.key.toLowerCase().startsWith(keySequence))
    
    if (possibleMatch) {
      // Wait for next key
      sequenceTimeout = setTimeout(() => {
        keySequence = ''
      }, 1000)
    } else {
      // No possible match, reset
      keySequence = ''
    }
  }

  const registerShortcuts = (newShortcuts) => {
    shortcuts.value = newShortcuts
  }

  const enable = () => {
    isEnabled.value = true
  }

  const disable = () => {
    isEnabled.value = false
  }

  onMounted(() => {
    window.addEventListener('keydown', handleKeyDown)
  })

  onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown)
    clearTimeout(sequenceTimeout)
  })

  return {
    showHelp,
    shortcuts,
    globalShortcuts,
    registerShortcuts,
    enable,
    disable,
  }
}

export default useKeyboardShortcuts
