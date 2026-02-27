<script setup lang="ts">
import { computed } from 'vue'

import { getProductIcon } from '@/features/product/utils/getProductIcon'

const props = withDefaults(
  defineProps<{
    categories: string[]
    modelValue: string
  }>(),
  {
    modelValue: 'all',
  },
)

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

const normalizedSelected = computed(() => props.modelValue.trim().toLowerCase())
const railItems = computed(() => ['all', ...props.categories])

function normalize(value: string): string {
  return value.trim().toLowerCase()
}

function isSelected(category: string): boolean {
  return normalize(category) === normalizedSelected.value
}

function categoryLabel(category: string): string {
  if (category === 'all') {
    return 'All products'
  }

  return category
    .split(/[\s_-]+/)
    .map((token) => token.charAt(0).toUpperCase() + token.slice(1))
    .join(' ')
}

function categoryIcon(category: string): string {
  if (category === 'all') {
    return getProductIcon('basket', 'basket', 'pantry')
  }

  return getProductIcon(category, category, category)
}

function categoryClass(category: string): string {
  if (category === 'all') {
    return 'border-slate-900 bg-slate-900 text-white'
  }

  return 'border-slate-300 bg-white text-slate-700'
}
</script>

<template>
  <section class="space-y-3">
    <div class="flex items-center justify-between gap-3">
      <h3 class="text-heading">Browse by category</h3>
      <p class="text-xs text-slate-500">Filters popular and trending products</p>
    </div>

    <div class="overflow-x-auto pb-1">
      <div class="flex min-w-max items-center gap-2">
        <button
          v-for="category in railItems"
          :key="category"
          type="button"
          class="inline-flex items-center gap-2 rounded-xl border px-3 py-2 text-sm font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-300"
          :class="[
            categoryClass(category),
            isSelected(category)
              ? 'ring-2 ring-slate-300 shadow-sm'
              : 'hover:-translate-y-0.5 hover:shadow-sm',
          ]"
          @click="emit('update:modelValue', normalize(category))"
        >
          <img
            :src="categoryIcon(category)"
            :alt="`${categoryLabel(category)} icon`"
            class="h-6 w-6 rounded-md border border-slate-200 bg-white p-0.5 object-contain"
          />
          <span>{{ categoryLabel(category) }}</span>
        </button>
      </div>
    </div>
  </section>
</template>
