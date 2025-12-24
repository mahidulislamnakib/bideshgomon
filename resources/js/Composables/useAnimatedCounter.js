import { ref, watch, onMounted } from 'vue'

/**
 * Animated counter composable for smooth number transitions
 * @param {number|Ref} targetValue - The target number to animate to
 * @param {Object} options - Animation options
 * @returns {Object} - { displayValue, isAnimating, animate }
 */
export function useAnimatedCounter(targetValue, options = {}) {
    const {
        duration = 1000,        // Animation duration in ms
        delay = 0,              // Delay before starting
        easing = 'easeOutExpo', // Easing function
        decimals = 0,           // Decimal places
        separator = ',',        // Thousands separator
        prefix = '',            // Prefix (e.g., '৳')
        suffix = '',            // Suffix (e.g., '%')
        startOnMount = true,    // Auto-start on mount
    } = options

    const displayValue = ref(0)
    const formattedValue = ref(prefix + '0' + suffix)
    const isAnimating = ref(false)
    let animationFrame = null

    // Easing functions
    const easingFunctions = {
        linear: t => t,
        easeOutQuad: t => t * (2 - t),
        easeOutCubic: t => (--t) * t * t + 1,
        easeOutQuart: t => 1 - (--t) * t * t * t,
        easeOutExpo: t => t === 1 ? 1 : 1 - Math.pow(2, -10 * t),
        easeOutElastic: t => {
            const c4 = (2 * Math.PI) / 3
            return t === 0 ? 0 : t === 1 ? 1 : Math.pow(2, -10 * t) * Math.sin((t * 10 - 0.75) * c4) + 1
        },
        easeOutBounce: t => {
            const n1 = 7.5625
            const d1 = 2.75
            if (t < 1 / d1) return n1 * t * t
            if (t < 2 / d1) return n1 * (t -= 1.5 / d1) * t + 0.75
            if (t < 2.5 / d1) return n1 * (t -= 2.25 / d1) * t + 0.9375
            return n1 * (t -= 2.625 / d1) * t + 0.984375
        }
    }

    // Format number with separators and decimals
    const formatNumber = (num) => {
        const fixed = num.toFixed(decimals)
        const parts = fixed.split('.')
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, separator)
        return prefix + parts.join('.') + suffix
    }

    // Animate to target value
    const animate = (to, from = 0) => {
        if (animationFrame) {
            cancelAnimationFrame(animationFrame)
        }

        const target = typeof to === 'object' && to.value !== undefined ? to.value : to
        const start = from
        const startTime = performance.now()
        const easeFn = easingFunctions[easing] || easingFunctions.easeOutExpo

        isAnimating.value = true

        const step = (currentTime) => {
            const elapsed = currentTime - startTime
            const progress = Math.min(elapsed / duration, 1)
            const easedProgress = easeFn(progress)
            
            displayValue.value = start + (target - start) * easedProgress
            formattedValue.value = formatNumber(displayValue.value)

            if (progress < 1) {
                animationFrame = requestAnimationFrame(step)
            } else {
                displayValue.value = target
                formattedValue.value = formatNumber(target)
                isAnimating.value = false
            }
        }

        if (delay > 0) {
            setTimeout(() => requestAnimationFrame(step), delay)
        } else {
            requestAnimationFrame(step)
        }
    }

    // Watch for target value changes
    if (typeof targetValue === 'object' && targetValue.value !== undefined) {
        watch(targetValue, (newVal, oldVal) => {
            animate(newVal, oldVal || 0)
        })
    }

    // Auto-start on mount
    onMounted(() => {
        if (startOnMount) {
            const target = typeof targetValue === 'object' ? targetValue.value : targetValue
            setTimeout(() => animate(target, 0), delay)
        }
    })

    return {
        displayValue,
        formattedValue,
        isAnimating,
        animate
    }
}

/**
 * Simple animated number component helper
 * Returns a formatted animated value
 */
export function useCountUp(target, options = {}) {
    const { formattedValue, isAnimating } = useAnimatedCounter(target, options)
    return { value: formattedValue, isAnimating }
}

/**
 * Bangladesh currency counter (৳)
 */
export function useBDTCounter(target, options = {}) {
    return useAnimatedCounter(target, {
        prefix: '৳',
        separator: ',',
        decimals: 0,
        ...options
    })
}

/**
 * Percentage counter
 */
export function usePercentCounter(target, options = {}) {
    return useAnimatedCounter(target, {
        suffix: '%',
        decimals: 1,
        ...options
    })
}
