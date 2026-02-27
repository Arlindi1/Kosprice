<script setup lang="ts">
import { computed } from 'vue'

import { detectMarketBrand, getMarketLogo } from '@/features/markets/utils/getMarketLogo'
import type { Market } from '@/lib/types/api'
import Badge from '@/shared/ui/Badge.vue'
import Skeleton from '@/shared/ui/Skeleton.vue'

const props = withDefaults(
  defineProps<{
    market: Market
    totalPrice: number | null
    recordedAt?: string | null
    isLoading?: boolean
    error?: string | null
    isCheapest?: boolean
  }>(),
  {
    recordedAt: null,
    isLoading: false,
    error: null,
    isCheapest: false,
  },
)

const emit = defineEmits<{
  select: [marketId: number]
}>()

const brand = computed(() => detectMarketBrand(props.market.name))
const logoSrc = computed(() => getMarketLogo(props.market.name))

function formatMoney(value: number): string {
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
    class="group relative w-full overflow-hidden rounded-xl border border-slate-200 bg-white p-4 text-left transition duration-150 hover:border-indigo-200 hover:shadow-soft focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-200"
    @click="emit('select', props.market.id)"
  >
    <div class="pointer-events-none absolute inset-x-0 top-0 h-20 bg-gradient-to-b from-slate-50 to-transparent" />

    <div class="relative space-y-4">
      <div class="flex items-start justify-between gap-3">
        <div class="flex min-w-0 items-center gap-3">
          <img
            :src="logoSrc"
            :alt="`${props.market.name} logo`"
            class="h-12 w-12 rounded-xl border border-slate-200 bg-white object-contain p-1"
          />
          <div class="min-w-0 space-y-1">
            <h3 class="truncate text-xl font-semibold tracking-tight text-slate-900">{{ props.market.name }}</h3>
            <p class="truncate text-xs text-slate-500">{{ props.market.address ?? 'Address unavailable' }}</p>
          </div>
        </div>
        <Badge :variant="props.isCheapest ? 'accent' : 'neutral'">
          {{ props.isCheapest ? 'Cheapest' : brand }}
        </Badge>
      </div>

      <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3">
        <p class="text-xs uppercase tracking-wide text-slate-500">Basket Total</p>
        <div v-if="props.isLoading" class="mt-2">
          <Skeleton height="1.8rem" width="8.5rem" />
        </div>
        <p v-else-if="props.error" class="mt-1 text-sm text-slate-500">{{ props.error }}</p>
        <p v-else-if="props.totalPrice !== null" class="text-price mt-1">
          {{ formatMoney(props.totalPrice) }}
        </p>
        <p v-else class="mt-1 text-sm text-slate-500">No total available</p>
      </div>

      <p class="text-xs text-slate-500">
        {{ props.recordedAt ? `Updated ${props.recordedAt}` : 'Tap to see market basket details' }}
      </p>
    </div>
  </button>
</template>
