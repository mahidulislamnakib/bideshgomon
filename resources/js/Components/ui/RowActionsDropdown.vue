<template>
    <Menu as="div" class="relative inline-block text-left">
        <MenuButton 
            :class="[
                'inline-flex items-center justify-center rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500',
                buttonClass
            ]"
        >
            <slot name="trigger">
                <EllipsisVerticalIcon class="h-5 w-5" />
                <span class="sr-only">Actions</span>
            </slot>
        </MenuButton>

        <Transition
            enter-active-class="transition duration-100 ease-out"
            enter-from-class="transform scale-95 opacity-0"
            enter-to-class="transform scale-100 opacity-100"
            leave-active-class="transition duration-75 ease-in"
            leave-from-class="transform scale-100 opacity-100"
            leave-to-class="transform scale-95 opacity-0"
        >
            <MenuItems 
                :class="[
                    'absolute z-50 mt-2 w-48 origin-top-right rounded-xl bg-white dark:bg-neutral-800 shadow-lg ring-1 ring-black/5 dark:ring-white/10 focus:outline-none divide-y divide-neutral-100 dark:divide-neutral-700',
                    positionClass
                ]"
            >
                <!-- Default slot for custom content -->
                <slot>
                    <!-- Actions Groups -->
                    <div v-for="(group, groupIndex) in actionGroups" :key="groupIndex" class="py-1">
                        <MenuItem 
                            v-for="action in group" 
                            :key="action.key || action.label"
                            v-slot="{ active }"
                            :disabled="action.disabled"
                        >
                            <component
                                :is="action.href ? 'a' : action.route ? Link : 'button'"
                                :href="action.href || (action.route ? route(action.route, action.routeParams) : undefined)"
                                :type="!action.href && !action.route ? 'button' : undefined"
                                :class="[
                                    'group flex w-full items-center gap-2 px-3 py-2 text-sm',
                                    active && !action.disabled ? 'bg-neutral-100 dark:bg-neutral-700' : '',
                                    action.disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer',
                                    action.danger ? 'text-red-600 dark:text-red-400' : 'text-neutral-700 dark:text-neutral-200',
                                ]"
                                @click="action.disabled ? null : handleAction(action)"
                            >
                                <component 
                                    v-if="action.icon" 
                                    :is="action.icon" 
                                    :class="[
                                        'h-4 w-4 flex-shrink-0',
                                        action.danger ? 'text-red-500' : 'text-neutral-400 group-hover:text-neutral-500',
                                    ]"
                                />
                                <span>{{ action.label }}</span>
                                <span 
                                    v-if="action.badge" 
                                    :class="[
                                        'ml-auto inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium',
                                        action.badgeClass || 'bg-neutral-100 text-neutral-600 dark:bg-neutral-700 dark:text-neutral-300'
                                    ]"
                                >
                                    {{ action.badge }}
                                </span>
                            </component>
                        </MenuItem>
                    </div>
                </slot>
            </MenuItems>
        </Transition>
    </Menu>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue';
import { EllipsisVerticalIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    /** Array of action objects or array of action groups */
    actions: {
        type: Array,
        default: () => [],
    },
    /** Position of dropdown: 'left' or 'right' */
    position: {
        type: String,
        default: 'right',
        validator: (value) => ['left', 'right'].includes(value),
    },
    /** Button styling */
    buttonClass: {
        type: String,
        default: 'p-1.5 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-700',
    },
});

const emit = defineEmits(['action']);

// Compute position class
const positionClass = computed(() => {
    return props.position === 'left' ? 'left-0' : 'right-0';
});

// Normalize actions into groups
const actionGroups = computed(() => {
    if (props.actions.length === 0) return [];
    
    // Check if first item is an array (already grouped)
    if (Array.isArray(props.actions[0])) {
        return props.actions;
    }
    
    // Check if actions have group property
    const hasGroups = props.actions.some(action => action.group !== undefined);
    if (hasGroups) {
        const groups = {};
        props.actions.forEach(action => {
            const groupKey = action.group || 'default';
            if (!groups[groupKey]) groups[groupKey] = [];
            groups[groupKey].push(action);
        });
        return Object.values(groups);
    }
    
    // Single group
    return [props.actions];
});

// Handle action click
function handleAction(action) {
    if (action.disabled) return;
    
    if (action.onClick) {
        action.onClick();
    }
    
    emit('action', action);
}
</script>
