<script setup lang="ts">
import { computed } from 'vue'

import { getProductIcon } from '@/features/product/utils/getProductIcon'
import {
  getCategoryChipClass,
  getCategoryTopBorderClass,
} from '@/features/products/utils/getCategoryAccent'
import type { ProductCatalogItem } from '@/lib/types/api'

const props = withDefaults(
  defineProps<{
    product: ProductCatalogItem
    isSelected?: boolean
  }>(),
  {
    isSelected: false,
  },
)

const emit = defineEmits<{
  select: [productId: number]
}>()

const iconSrc = computed(() =>
  getProductIcon(props.product.image_key, props.product.category, props.product.name),
)

const categoryChipClass = computed(() => getCategoryChipClass(props.product.category))
const categoryTopBorderClass = computed(() => getCategoryTopBorderClass(props.product.category))
const brandText = computed(() => {
  if (props.product.brand && props.product.brand.trim().length > 0) {
    return props.product.brand
  }

  return null
})
const variantAndUnitText = computed(() => {
  const variant = props.product.variant?.trim() ?? ''
  const unit = props.product.unit_label?.trim() ?? ''

  if (variant.length > 0 && unit.length > 0) {
    return `${variant} • ${unit}`
  }

  if (variant.length > 0) {
    return variant
  }

  if (unit.length > 0) {
    return unit
  }

  return '-'
})

function formatMoney(value: number | null): string {
  if (value === null) {
    return 'No city price'
  }

  return value.toLocaleString('en-US', {
    style: 'currency',
    currency: 'EUR',
    minimumFractionDigits: 2,
  })
}
</script>

<template>
  <button
    type="button"
    class="group relative w-full overflow-hidden rounded-2xl border bg-white p-4 text-left transition duration-150 hover:shadow-soft focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-200"
    :class="[
      categoryTopBorderClass,
      props.isSelected ? 'border-indigo-300 bg-indigo-50/30 shadow-soft' : 'border-slate-200 hover:border-indigo-200',
    ]"
    @click="emit('select', props.product.id)"
  >
    <div class="pointer-events-none absolute inset-x-0 top-0 h-20 bg-gradient-to-b from-slate-50 to-transparent" />
    <span
      v-if="props.product.is_core_basket"
      class="absolute right-3 top-3 rounded-full bg-rose-500 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-white shadow-sm"
    >
      Featured
    </span>

    <div class="relative space-y-4">
      <div class="flex items-start justify-between gap-3">
        <div class="flex min-w-0 items-center gap-3">
          <img
            :src="iconSrc"
            :alt="props.product.name"
            class="h-12 w-12 rounded-xl border border-slate-200 bg-white p-1 object-contain"
          />
          <div class="min-w-0">
            <h3 class="truncate text-lg font-semibold tracking-tight text-slate-900">{{ props.product.name }}</h3>
            <p class="truncate text-sm text-slate-500">{{ variantAndUnitText }}</p>
          </div>
        </div>
        <div class="flex flex-wrap items-center justify-end gap-1.5">
          <span
            v-if="brandText"
            class="inline-flex h-6 items-center rounded-full border border-indigo-200 bg-indigo-50 px-2.5 text-[11px] font-semibold uppercase tracking-wide text-indigo-700"
          >
            {{ brandText }}
          </span>
          <span
            class="inline-flex h-6 items-center rounded-full border px-2.5 text-[11px] font-semibold uppercase tracking-wide"
            :class="categoryChipClass"
          >
            {{ props.product.category }}
          </span>
        </div>
      </div>

      <div class="rounded-xl border border-emerald-200/70 bg-emerald-50/60 p-3">
        <p class="text-xs uppercase tracking-wide text-slate-500">Cheapest today</p>
        <p class="text-price text-price-hover mt-1">
          {{ formatMoney(props.product.cheapest_price_today) }}
        </p>
        <p class="mt-0.5 truncate text-xs text-slate-500">
          {{ props.product.cheapest_market_name ?? 'No market found in selected city' }}
        </p>
      </div>
    </div>
  </button>
</template>
