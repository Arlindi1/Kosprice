<script setup lang="ts">
import { computed } from 'vue'

const props = withDefaults(
  defineProps<{
    active?: boolean
  }>(),
  {
    active: false,
  },
)

const className = computed(() => [
  'inline-flex h-9 items-center rounded-full border px-4 text-sm font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-200',
  props.active
    ? 'border-indigo-600 bg-indigo-600 text-white shadow-sm'
    : 'border-slate-200 bg-white text-slate-700 hover:border-indigo-200 hover:bg-indigo-50',
])
</script>

<template>
  <button type="button" :class="className">
    <slot />
  </button>
</template>
