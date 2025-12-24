<script setup>
import { useConfirm } from '@/Composables/useConfirm'
import ConfirmModal from './ConfirmModal.vue'

const { state } = useConfirm()

const handleConfirm = () => {
    if (state.onConfirm) {
        state.onConfirm()
    }
}

const handleCancel = () => {
    if (state.onCancel) {
        state.onCancel()
    }
}
</script>

<template>
    <ConfirmModal
        :show="state.show"
        :type="state.type"
        :title="state.title"
        :message="state.message"
        :confirm-text="state.confirmText"
        :cancel-text="state.cancelText"
        :processing="state.processing"
        @confirm="handleConfirm"
        @cancel="handleCancel"
        @update:show="state.show = $event"
    />
</template>
