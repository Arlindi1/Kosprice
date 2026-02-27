<script setup lang="ts">
import { computed } from 'vue'

import { getMarketLogo } from '@/features/markets/utils/getMarketLogo'
import { getProductImage } from '@/features/product/utils/getProductImage'
import type { ProductCatalogItem } from '@/lib/types/api'
import Button from '@/shared/ui/Button.vue'

const props = withDefaults(
  defineProps<{
    product: ProductCatalogItem
    isSelected?: boolean
    updatedLabel?: string
  }>(),
  {
    isSelected: false,
    updatedLabel: 'Updated today',
  },
)

const emit = defineEmits<{
  select: [productId: number]
  compare: [productId: number]
}>()

const imageSrc = computed(() => getProductImage(props.product))
const hasCheapestMarket = computed(() => Boolean(props.product.cheapest_market_name))
const marketLogoSrc = computed(() => getMarketLogo(props.product.cheapest_market_name))
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

  return 'Unit unavailable'
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

function selectProduct(): void {
  emit('select', props.product.id)
}

function compareProduct(): void {
  emit('compare', props.product.id)
}
</script>

<template>
  <article
    class="rounded-2xl border border-slate-200 bg-white p-3.5 shadow-sm transition duration-150"
    :class="[
      props.isSelected
        ? 'ring-2 ring-slate-200 shadow-soft'
        : 'hover:border-slate-300 hover:shadow-soft',
    ]"
    @click="selectProduct"
  >
    <div class="space-y-3">
      <div
        class="relative h-40 overflow-hidden rounded-xl border border-slate-200 bg-slate-100 md:h-48"
      >
        <div class="pointer-events-none absolute inset-0 catalog-pattern opacity-20" />
        <img
          :src="imageSrc"
          :alt="props.product.name"
          class="relative z-[1] h-full w-full object-contain p-3 md:p-4"
          loading="lazy"
        />
      </div>

      <div class="space-y-1.5">
        <h4 class="clamp-2 text-[15px] font-semibold leading-5 tracking-[-0.01em] text-slate-900 md:text-base">
          {{ props.product.name }}
        </h4>
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

        <div class="flex flex-wrap items-center gap-2">
          <span
            class="inline-flex h-6 items-center rounded-full border border-slate-300 bg-slate-900 px-2.5 text-[11px] font-semibold uppercase tracking-wide text-white"
          >
            Cheapest today
          </span>
          <span
            v-if="hasCheapestMarket"
            class="inline-flex h-6 items-center gap-1 rounded-full border border-slate-200 bg-white px-2.5 text-[11px] font-semibold text-slate-700"
          >
            <img
              :src="marketLogoSrc"
              :alt="`${props.product.cheapest_market_name ?? 'Market'} logo`"
              class="h-4 w-4 rounded-sm object-contain"
            />
            {{ props.product.cheapest_market_name }}
          </span>
        </div>
      </div>

      <div class="flex items-end justify-between gap-3">
        <div>
          <p class="text-price-display text-[26px] text-slate-900">
            {{ formatMoney(props.product.cheapest_price_today) }}
          </p>
          <p class="text-[11px] text-slate-500">{{ props.updatedLabel }}</p>
        </div>

        <Button size="sm" @click.stop="compareProduct">Compare</Button>
      </div>
    </div>
  </article>
</template>
