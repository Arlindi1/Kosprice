<script setup lang="ts">
import { computed, ref, watch } from 'vue'

import { getProductIcon } from '@/features/product/utils/getProductIcon'
import { getCategoryChipClass } from '@/features/products/utils/getCategoryAccent'
import type { Product } from '@/lib/types/api'
import Drawer from '@/shared/ui/Drawer.vue'
import EmptyState from '@/shared/ui/EmptyState.vue'
import SearchInput from '@/shared/ui/SearchInput.vue'
import Skeleton from '@/shared/ui/Skeleton.vue'

const props = withDefaults(
  defineProps<{
    modelValue: boolean
    products: Product[]
    selectedProductId: number | null
    isLoading: boolean
    error: string | null
  }>(),
  {
    selectedProductId: null,
    error: null,
  },
)

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  select: [productId: number]
  retry: []
}>()

const searchTerm = ref('')

const filteredProducts = computed(() => {
  const query = searchTerm.value.trim().toLowerCase()

  if (query.length === 0) {
    return props.products
  }

  return props.products.filter((product) => {
    const searchable = `${product.name} ${product.category} ${product.unit_label ?? ''} ${product.unit}`
      .toLowerCase()
    return searchable.includes(query)
  })
})

function categoryChipClass(category: string): string {
  return getCategoryChipClass(category)
}

function closeDrawer(): void {
  emit('update:modelValue', false)
}

function selectProduct(productId: number): void {
  emit('select', productId)
  closeDrawer()
}

watch(
  () => props.modelValue,
  (isOpen) => {
    if (!isOpen) {
      searchTerm.value = ''
    }
  },
)
</script>

<template>
  <Drawer
    :model-value="props.modelValue"
    title="Select Any Product"
    subtitle="Search by name, category, or unit to find the cheapest nearby offer."
    width-class="max-w-2xl"
    @update:model-value="(value) => emit('update:modelValue', value)"
  >
    <div class="space-y-4">
      <SearchInput
        v-model="searchTerm"
        placeholder="Search all products..."
        :disabled="props.isLoading"
      />

      <div v-if="props.isLoading" class="space-y-2">
        <Skeleton v-for="index in 8" :key="index" height="3.75rem" rounded="0.85rem" />
      </div>

      <div v-else-if="props.error" class="status-wrap">
        <EmptyState
          title="Product list unavailable"
          :message="props.error"
          cta-label="Retry"
          @retry="emit('retry')"
        />
      </div>

      <div v-else-if="filteredProducts.length === 0" class="status-wrap">
        <EmptyState
          title="No products found"
          message="Try another keyword to broaden your search."
        />
      </div>

      <ul v-else class="grid gap-2">
        <li v-for="product in filteredProducts" :key="product.id">
          <button
            type="button"
            class="w-full rounded-xl border bg-white px-3 py-2.5 text-left transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-200"
            :class="[
              props.selectedProductId === product.id
                ? 'border-indigo-300 bg-indigo-50/50'
                : 'border-slate-200 hover:border-indigo-200 hover:bg-indigo-50/40',
            ]"
            @click="selectProduct(product.id)"
          >
            <div class="flex items-center justify-between gap-3">
              <div class="flex min-w-0 items-center gap-3">
                <img
                  :src="getProductIcon(product.image_key, product.category, product.name)"
                  :alt="product.name"
                  class="h-10 w-10 rounded-lg border border-slate-200 bg-white p-1 object-contain"
                />
                <div class="min-w-0">
                  <p class="truncate text-sm font-semibold text-slate-900">{{ product.name }}</p>
                  <p class="truncate text-xs text-slate-500">{{ product.unit_label ?? product.unit }}</p>
                </div>
              </div>
              <div class="flex shrink-0 items-center gap-2">
                <span
                  class="inline-flex h-6 items-center rounded-full border px-2.5 text-[11px] font-semibold uppercase tracking-wide"
                  :class="categoryChipClass(product.category)"
                >
                  {{ product.category }}
                </span>
                <span
                  v-if="props.selectedProductId === product.id"
                  class="text-xs font-semibold text-indigo-700"
                >
                  Selected
                </span>
              </div>
            </div>
          </button>
        </li>
      </ul>
    </div>
  </Drawer>
</template>
