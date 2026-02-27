<script setup lang="ts">
import { computed } from 'vue'

import { getProductImage } from '@/features/product/utils/getProductImage'
import type { Product, ProductCatalogItem } from '@/lib/types/api'
import Button from '@/shared/ui/Button.vue'

type ProductCardLargeItem = ProductCatalogItem | Product

const props = withDefaults(
  defineProps<{
    product: ProductCardLargeItem
    updatedLabel?: string
    isSelected?: boolean
    layout?: 'adaptive' | 'vertical' | 'horizontal'
  }>(),
  {
    updatedLabel: 'Updated today',
    isSelected: false,
    layout: 'adaptive',
  },
)

const emit = defineEmits<{
  compare: [productId: number]
}>()

const imageSrc = computed(() => getProductImage(props.product))
const cardLayoutClass = computed(() => {
  if (props.layout === 'vertical') {
    return 'flex flex-col'
  }

  if (props.layout === 'horizontal') {
    return 'flex flex-row items-stretch'
  }

  return 'flex flex-col md:flex-row md:items-stretch'
})

const imageWrapClass = computed(() => {
  if (props.layout === 'vertical') {
    return 'relative aspect-square overflow-hidden rounded-xl border border-slate-200 bg-slate-100'
  }

  return 'relative h-40 overflow-hidden rounded-xl border border-slate-200 bg-slate-100 md:h-44 md:w-52 md:shrink-0'
})

const bodyWrapClass = computed(() => {
  if (props.layout === 'vertical') {
    return 'flex min-h-[150px] flex-1 flex-col pt-3'
  }

  return 'flex min-h-[176px] flex-1 flex-col'
})

const unitText = computed(() => {
  const withUnitLabel = 'unit_label' in props.product ? props.product.unit_label : null
  if (withUnitLabel && withUnitLabel.trim().length > 0) {
    return withUnitLabel
  }

  const withUnit = 'unit' in props.product ? props.product.unit : null
  if (withUnit && withUnit.trim().length > 0) {
    return withUnit
  }

  return null
})

const brandText = computed(() => {
  const value = 'brand' in props.product ? props.product.brand : null
  if (value && value.trim().length > 0) {
    return value
  }

  return null
})

const variantText = computed(() => {
  const value = 'variant' in props.product ? props.product.variant : null
  if (value && value.trim().length > 0) {
    return value
  }

  return null
})

const variantAndUnitText = computed(() => {
  const variant = variantText.value
  const unit = unitText.value

  if (variant && unit) {
    return `${variant} • ${unit}`
  }

  if (variant) {
    return variant
  }

  if (unit) {
    return unit
  }

  return 'Details unavailable'
})

const price = computed<number | null>(() => {
  if ('cheapest_price_today' in props.product) {
    return props.product.cheapest_price_today
  }

  return null
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
  <article
    class="rounded-2xl border bg-white p-3.5 shadow-sm transition duration-150 md:p-4"
    :class="[
      props.isSelected
        ? 'border-slate-300 ring-2 ring-slate-200'
        : 'border-slate-200 hover:border-slate-300 hover:shadow-soft',
    ]"
  >
    <div class="gap-3" :class="cardLayoutClass">
      <div :class="imageWrapClass">
        <div class="pointer-events-none absolute inset-0 catalog-pattern opacity-25" />
        <img
          :src="imageSrc"
          :alt="props.product.name"
          class="relative z-[1] h-full w-full object-contain p-3 md:p-4"
          loading="lazy"
        />
      </div>

      <div :class="bodyWrapClass">
        <div class="space-y-1.5">
          <h3 class="clamp-2 text-[15px] font-semibold leading-5 tracking-[-0.01em] text-slate-900 md:text-base">
            {{ props.product.name }}
          </h3>
          <p class="text-xs leading-5 text-slate-500">{{ variantAndUnitText }}</p>
          <div class="flex flex-wrap items-center gap-2">
            <span
              v-if="brandText"
              class="inline-flex h-6 items-center rounded-full border border-slate-300 bg-slate-100 px-2.5 text-[11px] font-semibold uppercase tracking-wide text-slate-700"
            >
              {{ brandText }}
            </span>
            <span
              class="inline-flex h-6 items-center rounded-full border border-slate-300 bg-slate-100 px-2.5 text-[11px] font-semibold uppercase tracking-wide text-slate-700"
            >
              {{ props.product.category }}
            </span>
          </div>
        </div>

        <div class="mt-auto flex items-end justify-between gap-3 pt-4">
          <div>
            <p class="text-price-display text-[26px] text-slate-900">
              {{ formatMoney(price) }}
            </p>
            <p class="text-[11px] text-slate-500">{{ props.updatedLabel }}</p>
          </div>

          <Button size="sm" @click="emit('compare', props.product.id)">Compare</Button>
        </div>
      </div>
    </div>
  </article>
</template>
