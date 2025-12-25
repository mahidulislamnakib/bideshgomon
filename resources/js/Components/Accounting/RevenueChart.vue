<template>
  <div class="w-full h-full">
    <div class="flex h-full items-end gap-1">
      <div
        v-for="(item, index) in data"
        :key="index"
        class="flex-1 flex flex-col items-center gap-1"
      >
        <!-- Bars -->
        <div class="w-full flex gap-0.5 justify-center items-end h-48">
          <div
            class="w-1/3 bg-blue-500 rounded-t transition-all duration-500"
            :style="{ height: getBarHeight(item.total) }"
            :title="`Total: ৳${formatNumber(item.total)}`"
          ></div>
          <div
            class="w-1/3 bg-green-500 rounded-t transition-all duration-500"
            :style="{ height: getBarHeight(item.paid) }"
            :title="`Paid: ৳${formatNumber(item.paid)}`"
          ></div>
        </div>
        <!-- Label -->
        <span class="text-xs text-gray-500">{{ item.month }}</span>
      </div>
    </div>
    
    <!-- Legend -->
    <div class="flex justify-center gap-6 mt-4">
      <div class="flex items-center gap-2">
        <div class="w-3 h-3 bg-blue-500 rounded"></div>
        <span class="text-xs text-gray-500">Total Revenue</span>
      </div>
      <div class="flex items-center gap-2">
        <div class="w-3 h-3 bg-green-500 rounded"></div>
        <span class="text-xs text-gray-500">Collected</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  data: {
    type: Array,
    required: true,
  },
})

const maxValue = computed(() => {
  return Math.max(...props.data.map(d => Math.max(d.total, d.paid)), 1)
})

function getBarHeight(value) {
  const percentage = (value / maxValue.value) * 100
  return `${Math.max(percentage, 2)}%`
}

function formatNumber(value) {
  return new Intl.NumberFormat('en-BD').format(value)
}
</script>
