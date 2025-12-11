<template>
    <div class="setting-input">
        <!-- Label -->
        <div class="flex items-center justify-between mb-2">
            <label :for="setting.key" class="block text-sm font-semibold text-neutral-900">
                {{ formatLabel(setting.key) }}
                <span
                    v-if="setting.is_public"
                    class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-success-100 text-success-700"
                >
                    Public
                </span>
            </label>
        </div>

        <!-- Description -->
        <p v-if="setting.description" class="text-xs text-neutral-500 mb-3">
            {{ setting.description }}
        </p>

        <!-- Boolean Toggle -->
        <div v-if="setting.type === 'boolean'" class="mt-3">
            <label class="relative inline-flex items-center cursor-pointer">
                <input
                    :id="setting.key"
                    type="checkbox"
                    :checked="value === true || value === '1' || value === 1"
                    @change="$emit('update', setting.key, $event.target.checked)"
                    class="sr-only peer"
                />
                <div class="w-11 h-6 bg-neutral-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-neutral-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                <span class="ml-3 text-sm font-medium text-neutral-900">
                    {{ value ? 'Enabled' : 'Disabled' }}
                </span>
            </label>
        </div>

        <!-- Textarea -->
        <div v-else-if="setting.type === 'textarea'" class="mt-3">
            <textarea
                :id="setting.key"
                :value="value"
                @input="$emit('update', setting.key, $event.target.value)"
                rows="4"
                class="block w-full rounded-lg border-neutral-300 shadow-sm focus:border-primary-600 focus:ring-primary-600 text-sm"
                :placeholder="`Enter ${formatLabel(setting.key).toLowerCase()}`"
            ></textarea>
        </div>

        <!-- Color Picker -->
        <div v-else-if="setting.type === 'color'" class="mt-3">
            <div class="flex items-center gap-3">
                <input
                    :id="setting.key"
                    type="color"
                    :value="value || '#3B82F6'"
                    @input="$emit('update', setting.key, $event.target.value)"
                    class="h-10 w-20 rounded border-neutral-300 cursor-pointer"
                />
                <input
                    type="text"
                    :value="value"
                    @input="$emit('update', setting.key, $event.target.value)"
                    placeholder="#3B82F6"
                    class="flex-1 rounded-lg border-neutral-300 shadow-sm focus:border-primary-600 focus:ring-primary-600 text-sm font-mono"
                />
            </div>
        </div>

        <!-- Password Input -->
        <div v-else-if="setting.type === 'password' || isSecret" class="mt-3">
            <div class="relative">
                <input
                    :id="setting.key"
                    :type="showPassword ? 'text' : 'password'"
                    :value="value"
                    @input="$emit('update', setting.key, $event.target.value)"
                    :placeholder="value ? '••••••••••••' : `Enter ${formatLabel(setting.key).toLowerCase()}`"
                    class="block w-full rounded-lg border-neutral-300 shadow-sm focus:border-primary-600 focus:ring-primary-600 text-sm pr-10 font-mono"
                />
                <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-neutral-400 hover:text-neutral-600"
                >
                    <EyeIcon v-if="!showPassword" class="h-5 w-5" />
                    <EyeSlashIcon v-else class="h-5 w-5" />
                </button>
            </div>
        </div>

        <!-- Number Input -->
        <div v-else-if="setting.type === 'number'" class="mt-3">
            <input
                :id="setting.key"
                type="number"
                :value="value"
                @input="$emit('update', setting.key, $event.target.value)"
                step="any"
                class="block w-full rounded-lg border-neutral-300 shadow-sm focus:border-primary-600 focus:ring-primary-600 text-sm"
                :placeholder="`Enter ${formatLabel(setting.key).toLowerCase()}`"
            />
        </div>

        <!-- Email Input -->
        <div v-else-if="setting.type === 'email'" class="mt-3">
            <input
                :id="setting.key"
                type="email"
                :value="value"
                @input="$emit('update', setting.key, $event.target.value)"
                class="block w-full rounded-lg border-neutral-300 shadow-sm focus:border-primary-600 focus:ring-primary-600 text-sm"
                placeholder="email@example.com"
            />
        </div>

        <!-- URL Input -->
        <div v-else-if="setting.type === 'url'" class="mt-3">
            <input
                :id="setting.key"
                type="url"
                :value="value"
                @input="$emit('update', setting.key, $event.target.value)"
                class="block w-full rounded-lg border-neutral-300 shadow-sm focus:border-primary-600 focus:ring-primary-600 text-sm"
                placeholder="https://example.com"
            />
        </div>

        <!-- Text Input (Default) -->
        <div v-else class="mt-3">
            <input
                :id="setting.key"
                type="text"
                :value="value"
                @input="$emit('update', setting.key, $event.target.value)"
                class="block w-full rounded-lg border-neutral-300 shadow-sm focus:border-primary-600 focus:ring-primary-600 text-sm"
                :placeholder="`Enter ${formatLabel(setting.key).toLowerCase()}`"
            />
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    setting: {
        type: Object,
        required: true
    },
    value: {
        type: [String, Number, Boolean],
        default: ''
    },
    isSecret: {
        type: Boolean,
        default: false
    }
});

defineEmits(['update']);

const showPassword = ref(false);

const formatLabel = (key) => {
    return key
        .split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
};
</script>
