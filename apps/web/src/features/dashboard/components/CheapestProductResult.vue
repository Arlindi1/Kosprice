<script setup lang="ts">
import { computed } from 'vue'
import type { RouteLocationRaw } from 'vue-router'

import { getMarketLogo } from '@/features/markets/utils/getMarketLogo'
import type { ProductCheapestMarketEntry, ProductCheapestResult } from '@/lib/types/api'
import BarChart from '@/shared/charts/BarChart.vue'
import AppLink from '@/shared/ui/AppLink.vue'
import Badge from '@/shared/ui/Badge.vue'
import Button from '@/shared/ui/Button.vue'
import EmptyState from '@/shared/ui/EmptyState.vue'
import Skeleton from '@/shared/ui/Skeleton.vue'

const props = withDefaults(
  defineProps<{
    selectedProductLabel: string | null
    result: ProductCheapestResult | null
    isLoading: boolean
    error: string | null
    compareTo?: RouteLocationRaw
  }>(),
  {
    selectedProductLabel: null,
    result: null,
    error: null,
    compareTo: undefined,
  },
)

const emit = defineEmits<{
  retry: []
}>()

const alternativeRows = computed(() => props.result?.alternatives.slice(0, 4) ?? [])

const chartRows = computed(() => {
  if (!props.result?.cheapest) {
    return []
  }

  return [props.result.cheapest, ...alternativeRows.value]
})

const chartLabels = computed(() =>
  chartRows.value.map((row) => {
    const maxLength = 16
    return row.market.name.length > maxLength
      ? `${row.market.name.slice(0, maxLength - 1)}…`
      : row.market.name
  }),
)

const chartData = computed(() => [
  {
    label: 'Price (EUR)',
    data: chartRows.value.map((row) => row.price_eur),
    backgroundColor: chartRows.value.map((_, index) => (index === 0 ? 'rgba(5, 150, 105, 0.85)' : 'rgba(167, 139, 250, 0.7)')),
    borderColor: chartRows.value.map((_, index) => (index === 0 ? 'rgba(4, 120, 87, 1)' : 'rgba(124, 58, 237, 1)')),
  },
])

function formatMoney(value: number): string {
  return value.toLocaleString('en-US', {
    style: 'currency',
    currency: 'EUR',
    minimumFractionDigits: 2,
  })
}

function formatDelta(value: number): string {
  if (value <= 0) {
    return 'Best'
  }

  return `+${value.toFixed(2)} EUR`
}

function rowKey(row: ProductCheapestMarketEntry): string {
  return `${row.market.id}:${row.market.name}`
}
</script>

<template>
  <section class="space-y-4">
    <div class="flex items-center justify-between gap-3">
      <div>
        <h3 class="text-heading">Cheapest nearby</h3>
        <p class="text-body">
          {{ props.selectedProductLabel ? `Selected: ${props.selectedProductLabel}` : 'Select a product to compare nearby markets.' }}
        </p>
      </div>
      <Button
        v-if="props.selectedProductLabel"
        variant="ghost"
        size="sm"
        @click="emit('retry')"
      >
        Refresh
      </Button>
    </div>

    <div v-if="props.isLoading" class="space-y-3">
      <Skeleton height="7.4rem" rounded="0.8rem" />
      <Skeleton height="8rem" rounded="0.8rem" />
      <Skeleton height="10rem" rounded="0.8rem" />
    </div>

    <div v-else-if="props.error" class="status-wrap">
      <EmptyState
        class="min-h-[360px] md:min-h-[420px]"
        title="Cheapest lookup failed"
        :message="props.error"
        cta-label="Retry"
        @retry="emit('retry')"
      />
    </div>

    <div v-else-if="!props.selectedProductLabel" class="status-wrap">
      <EmptyState
        class="min-h-[360px] md:min-h-[420px]"
        title="Pick a product"
        message="Choose a popular item or search the full catalog to find the best nearby price."
      />
    </div>

    <div v-else-if="!props.result || !props.result.cheapest" class="status-wrap">
      <EmptyState
        class="min-h-[360px] md:min-h-[420px]"
        title="No market matches yet"
        message="No price records were found for this product in the selected city."
      />
    </div>

    <div v-else class="space-y-4">
      <article class="rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50 via-white to-sky-50 p-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div class="flex min-w-0 items-start gap-3">
            <img
              :src="getMarketLogo(props.result.cheapest.market.name)"
              :alt="`${props.result.cheapest.market.name} logo`"
              class="h-10 w-10 rounded-md border border-slate-200 bg-white p-0.5 object-contain"
            />
            <div class="space-y-1">
            <div class="flex flex-wrap items-center gap-2">
                <Badge variant="success">Cheapest</Badge>
              <span class="text-xs text-slate-500">Updated {{ props.result.recorded_at ?? 'latest' }}</span>
            </div>
            <h4 class="text-xl font-semibold tracking-tight text-slate-900">{{ props.result.cheapest.market.name }}</h4>
            <p class="text-sm text-slate-600">{{ props.result.cheapest.market.address ?? 'Address not provided' }}</p>
            <AppLink v-if="props.compareTo" :to="props.compareTo">
              Compare markets
            </AppLink>
            </div>
          </div>
          <p class="text-price">
            {{ formatMoney(props.result.cheapest.price_eur) }}
          </p>
        </div>
      </article>

      <section class="space-y-2">
        <div class="flex items-center justify-between">
          <h4 class="text-sm font-semibold text-slate-900">Top alternatives</h4>
          <span class="text-xs text-slate-500">{{ alternativeRows.length }} options</span>
        </div>

        <div v-if="alternativeRows.length === 0" class="status-wrap">
          <EmptyState
            title="No alternatives"
            message="Only one market currently has a price for this product."
          />
        </div>
        <ol v-else class="divide-y divide-slate-200 rounded-xl border border-slate-200 bg-white">
          <li v-for="row in alternativeRows" :key="rowKey(row)">
            <div class="flex items-center justify-between gap-3 px-4 py-3">
              <div class="flex min-w-0 items-center gap-3">
                <img
                  :src="getMarketLogo(row.market.name)"
                  :alt="`${row.market.name} logo`"
                  class="h-8 w-8 rounded-md border border-slate-200 bg-white p-0.5 object-contain"
                />
                <div class="min-w-0">
                  <p class="truncate text-sm font-semibold text-slate-900">{{ row.market.name }}</p>
                  <p class="truncate text-xs text-slate-500">{{ row.market.address ?? 'Address unavailable' }}</p>
                </div>
              </div>
              <div class="text-right">
                <p class="text-sm font-extrabold text-emerald-600">{{ formatMoney(row.price_eur) }}</p>
                <p class="text-xs text-slate-600">{{ formatDelta(row.delta_from_cheapest_eur) }}</p>
              </div>
            </div>
          </li>
        </ol>
      </section>

      <section class="space-y-2 rounded-xl border border-slate-200 bg-slate-50/70 p-4">
        <h4 class="text-sm font-semibold text-slate-900">Mini price comparison</h4>
        <BarChart
          :labels="chartLabels"
          :datasets="chartData"
          :height="170"
          :show-legend="false"
          :show-axes="false"
        />
      </section>
    </div>
  </section>
</template>
