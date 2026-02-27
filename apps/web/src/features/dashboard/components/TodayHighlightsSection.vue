<script setup lang="ts">
import { getMarketLogo } from '@/features/markets/utils/getMarketLogo'
import { getProductIcon } from '@/features/product/utils/getProductIcon'
import type {
  DashboardFuelHighlight,
  DashboardPriceDropInsight,
} from '@/features/dashboard/composables/useDashboardToday'
import type { BasketSummary } from '@/lib/types/api'
import Badge from '@/shared/ui/Badge.vue'
import Button from '@/shared/ui/Button.vue'
import Card from '@/shared/ui/Card.vue'
import EmptyState from '@/shared/ui/EmptyState.vue'
import Skeleton from '@/shared/ui/Skeleton.vue'

const props = withDefaults(
  defineProps<{
    cityName: string
    basket: BasketSummary | null
    biggestDrop: DashboardPriceDropInsight | null
    biggestDropState: 'ok' | 'insufficient' | 'no_drop'
    cheapestFuel: DashboardFuelHighlight | null
    isLoading: boolean
    error: string | null
  }>(),
  {
    error: null,
  },
)

const emit = defineEmits<{
  retry: []
}>()

function formatMoney(value: number): string {
  return value.toLocaleString('en-US', {
    style: 'currency',
    currency: 'EUR',
    minimumFractionDigits: 2,
  })
}

function formatFuel(value: number): string {
  return `${value.toFixed(3)} EUR/L`
}

function fuelTypeLabel(type: string): string {
  if (type === 'petrol95') {
    return 'Petrol95'
  }

  return type.charAt(0).toUpperCase() + type.slice(1)
}
</script>

<template>
  <section class="space-y-5">
    <div class="flex items-center justify-between gap-3">
      <div>
        <h3 class="text-heading">Today in {{ props.cityName }}</h3>
        <p class="text-body">Quick highlights from grocery and fuel markets.</p>
      </div>
      <Button variant="ghost" size="sm" @click="emit('retry')">Refresh highlights</Button>
    </div>

    <div v-if="props.error" class="status-wrap">
      <EmptyState
        title="Highlights unavailable"
        :message="props.error"
        cta-label="Retry"
        @retry="emit('retry')"
      />
    </div>

    <div v-else class="grid gap-4 md:grid-cols-3">
      <Card variant="highlight">
        <div v-if="props.isLoading" class="space-y-3">
          <Skeleton height="4.5rem" rounded="0.75rem" />
          <Skeleton width="65%" />
        </div>
        <div v-else-if="props.basket" class="space-y-3">
          <div class="flex items-center gap-2">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
              <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path d="M3 4.75A.75.75 0 013.75 4h12.5a.75.75 0 010 1.5H15.5V8A4.5 4.5 0 0111 12.5H9A4.5 4.5 0 014.5 8V5.5h-.75A.75.75 0 013 4.75z" />
                <path d="M6 13.5a.75.75 0 011.06 0L10 16.44l2.94-2.94a.75.75 0 111.06 1.06l-3.47 3.47a.75.75 0 01-1.06 0L6 14.56a.75.75 0 010-1.06z" />
              </svg>
            </span>
            <p class="text-sm font-semibold text-slate-900">Cheapest basket</p>
          </div>
          <div class="flex items-center gap-3">
            <img
              :src="getMarketLogo(props.basket.market.name)"
              :alt="`${props.basket.market.name} logo`"
              class="h-10 w-10 rounded-md border border-slate-200 bg-white p-0.5 object-contain"
            />
            <div class="min-w-0">
              <p class="truncate text-sm font-semibold text-slate-900">{{ props.basket.market.name }}</p>
              <p class="text-xs text-slate-500">Updated {{ props.basket.recorded_at ?? 'latest' }}</p>
            </div>
          </div>
          <p class="kpi-value">{{ formatMoney(props.basket.total_price_eur) }}</p>
        </div>
        <div v-else class="status-wrap">
          <EmptyState title="No basket highlight" message="No basket totals are available yet." />
        </div>
      </Card>

      <Card variant="highlight">
        <div v-if="props.isLoading" class="space-y-3">
          <Skeleton height="4.5rem" rounded="0.75rem" />
          <Skeleton width="60%" />
        </div>
        <div v-else-if="props.biggestDrop" class="space-y-3">
          <div class="flex items-center gap-2">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-rose-100 text-rose-700">
              <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path
                  fill-rule="evenodd"
                  d="M10 3.5a.75.75 0 01.75.75v8.19l2.72-2.72a.75.75 0 111.06 1.06l-4 4a.75.75 0 01-1.06 0l-4-4a.75.75 0 011.06-1.06l2.72 2.72V4.25A.75.75 0 0110 3.5z"
                  clip-rule="evenodd"
                />
              </svg>
            </span>
            <p class="text-sm font-semibold text-slate-900">Biggest grocery drop</p>
          </div>
          <div class="flex items-center gap-3">
            <img
              :src="getProductIcon(props.biggestDrop.image_key, props.biggestDrop.product_name)"
              :alt="props.biggestDrop.product_name"
              class="h-10 w-10 rounded-md border border-slate-200 bg-white p-1 object-contain"
            />
            <div class="min-w-0">
              <p class="truncate text-sm font-semibold text-slate-900">{{ props.biggestDrop.product_name }}</p>
              <p class="text-xs text-slate-500">vs {{ props.biggestDrop.comparison_date }}</p>
            </div>
          </div>
          <p class="kpi-value">-{{ formatMoney(props.biggestDrop.drop_eur) }}</p>
          <p class="text-xs text-slate-500">
            Now {{ formatMoney(props.biggestDrop.current_price_eur) }} from {{ formatMoney(props.biggestDrop.previous_price_eur) }}
          </p>
        </div>
        <div v-else class="status-wrap">
          <EmptyState
            v-if="props.biggestDropState === 'insufficient'"
            title="Insufficient history"
            message="Not enough 7-day product history for a confident drop signal."
          />
          <EmptyState
            v-else
            title="No major price drops"
            message="No product posted a meaningful drop against the 7-day snapshot."
          />
        </div>
      </Card>

      <Card variant="highlight">
        <div v-if="props.isLoading" class="space-y-3">
          <Skeleton height="4.5rem" rounded="0.75rem" />
          <Skeleton width="70%" />
        </div>
        <div v-else-if="props.cheapestFuel" class="space-y-3">
          <div class="flex items-center gap-2">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-sky-100 text-sky-700">
              <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path d="M6 2.75A.75.75 0 016.75 2h6.5a.75.75 0 01.75.75V6h.75A2.25 2.25 0 0117 8.25v8A1.75 1.75 0 0115.25 18h-10.5A1.75 1.75 0 013 16.25v-8A2.25 2.25 0 015.25 6H6V2.75zM7.5 6h5V3.5h-5V6z" />
              </svg>
            </span>
            <p class="text-sm font-semibold text-slate-900">Cheapest fuel today</p>
          </div>
          <div class="space-y-1">
            <div class="flex items-center gap-2">
              <Badge variant="warn">{{ fuelTypeLabel(props.cheapestFuel.fuel_type) }}</Badge>
              <span class="text-xs text-slate-500">Updated {{ props.cheapestFuel.recorded_at ?? 'latest' }}</span>
            </div>
            <p class="truncate text-sm font-semibold text-slate-900">{{ props.cheapestFuel.station_name }}</p>
            <p class="truncate text-xs text-slate-500">{{ props.cheapestFuel.station_address ?? 'Address unavailable' }}</p>
          </div>
          <p class="kpi-value">{{ formatFuel(props.cheapestFuel.price_eur_liter) }}</p>
        </div>
        <div v-else class="status-wrap">
          <EmptyState title="No fuel highlight" message="No diesel or petrol95 records are currently available." />
        </div>
      </Card>
    </div>
  </section>
</template>
