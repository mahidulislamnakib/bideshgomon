<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { HomeIcon, ChevronRightIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    items: {
        type: Array,
        default: () => []
    },
    homeRoute: {
        type: String,
        default: 'admin.dashboard'
    },
    showHome: {
        type: Boolean,
        default: true
    }
})

const page = usePage()

// Auto-generate breadcrumbs from URL if not provided
const breadcrumbs = computed(() => {
    if (props.items.length > 0) {
        return props.items
    }
    
    // Auto-generate from current URL
    const url = page.url
    const parts = url.split('/').filter(p => p && p !== 'admin')
    
    return parts.map((part, index) => {
        const isLast = index === parts.length - 1
        const label = part
            .replace(/-/g, ' ')
            .replace(/\d+/g, '') // Remove IDs
            .trim()
            .split(' ')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ')
        
        return {
            label: label || 'Item',
            href: isLast ? null : `/${parts.slice(0, index + 1).join('/')}`
        }
    }).filter(b => b.label)
})
</script>

<template>
    <nav class="flex items-center text-sm" aria-label="Breadcrumb">
        <ol class="flex items-center flex-wrap gap-1">
            <!-- Home -->
            <li v-if="showHome" class="flex items-center">
                <Link
                    :href="route(homeRoute)"
                    class="flex items-center gap-1 text-neutral-500 dark:text-neutral-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
                >
                    <HomeIcon class="h-4 w-4" />
                    <span class="sr-only sm:not-sr-only">Dashboard</span>
                </Link>
            </li>

            <!-- Separator after home -->
            <li v-if="showHome && breadcrumbs.length > 0" class="flex items-center">
                <ChevronRightIcon class="h-4 w-4 text-neutral-400 dark:text-neutral-500 mx-1" />
            </li>

            <!-- Breadcrumb items -->
            <template v-for="(item, index) in breadcrumbs" :key="index">
                <li class="flex items-center">
                    <!-- Link or current page -->
                    <Link
                        v-if="item.href && index < breadcrumbs.length - 1"
                        :href="item.href"
                        class="text-neutral-500 dark:text-neutral-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
                    >
                        {{ item.label }}
                    </Link>
                    <span
                        v-else
                        class="font-medium text-neutral-900 dark:text-white"
                        :class="{ 'max-w-[200px] truncate': item.label.length > 30 }"
                        :title="item.label"
                    >
                        {{ item.label }}
                    </span>
                </li>

                <!-- Separator -->
                <li v-if="index < breadcrumbs.length - 1" class="flex items-center">
                    <ChevronRightIcon class="h-4 w-4 text-neutral-400 dark:text-neutral-500 mx-1" />
                </li>
            </template>
        </ol>
    </nav>
</template>
