<script setup>
import { computed, ref, onMounted, watch } from 'vue'

const props = defineProps({
    value: {
        type: [Number, String],
        default: 0
    },
    prefix: {
        type: String,
        default: ''
    },
    suffix: {
        type: String,
        default: ''
    },
    decimals: {
        type: Number,
        default: 0
    },
    duration: {
        type: Number,
        default: 1000
    },
    delay: {
        type: Number,
        default: 0
    },
    separator: {
        type: String,
        default: ','
    },
    easing: {
        type: String,
        default: 'easeOutExpo'
    }
})

const displayValue = ref(0)
const isAnimating = ref(false)
let animationFrame = null

// Easing functions
const easingFunctions = {
    linear: t => t,
    easeOutQuad: t => t * (2 - t),
    easeOutCubic: t => (--t) * t * t + 1,
    easeOutQuart: t => 1 - (--t) * t * t * t,
    easeOutExpo: t => t === 1 ? 1 : 1 - Math.pow(2, -10 * t),
}

// Format number with separators
const formatNumber = (num) => {
    const fixed = Number(num).toFixed(props.decimals)
    const parts = fixed.split('.')
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, props.separator)
    return parts.join('.')
}

const formattedValue = computed(() => {
    return props.prefix + formatNumber(displayValue.value) + props.suffix
})

// Animate to target
const animate = (to, from = 0) => {
    if (animationFrame) {
        cancelAnimationFrame(animationFrame)
    }

    const target = Number(to) || 0
    const start = Number(from) || 0
    const startTime = performance.now()
    const easeFn = easingFunctions[props.easing] || easingFunctions.easeOutExpo

    isAnimating.value = true

    const step = (currentTime) => {
        const elapsed = currentTime - startTime
        const progress = Math.min(elapsed / props.duration, 1)
        const easedProgress = easeFn(progress)
        
        displayValue.value = start + (target - start) * easedProgress

        if (progress < 1) {
            animationFrame = requestAnimationFrame(step)
        } else {
            displayValue.value = target
            isAnimating.value = false
        }
    }

    requestAnimationFrame(step)
}

// Watch for value changes
watch(() => props.value, (newVal, oldVal) => {
    animate(newVal, oldVal)
})

// Initial animation on mount
onMounted(() => {
    setTimeout(() => {
        animate(props.value, 0)
    }, props.delay)
})
</script>

<template>
    <span class="tabular-nums" :class="{ 'animate-pulse': isAnimating }">
        {{ formattedValue }}
    </span>
</template>
