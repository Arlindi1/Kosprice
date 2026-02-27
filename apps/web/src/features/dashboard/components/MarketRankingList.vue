<script setup lang="ts">
import { getMarketLogo } from '@/features/markets/utils/getMarketLogo'
import EmptyState from '@/shared/ui/EmptyState.vue'
import Skeleton from '@/shared/ui/Skeleton.vue'

export type MarketRankingRow = {
  market_id: number
  market_name: string
  market_address: string | null
  total_price_eur: number
  recorded_at: string | null
}

const props = withDefaults(
  defineProps<{
    rows: MarketRankingRow[]
    isLoading: boolean
    error: string | null
  }>(),
  {
    error: null,
  },
)

const emit = defineEmits<{
  select: [marketId: number]
  retry: []
}>()

function formatMoney(value: number): string {
  return value.toLocaleString('en-US', {
    style: 'currency',
    currency: 'EUR',
    minimumFractionDigits: 2,
  })
}

function formatDelta(value: number): string {
  if (!Number.isFinite(value)) {
    return 'Pending'
  }

  if (value <= 0) {
    return 'Best price'
  }

  return `+${value.toFixed(2)} EUR`
}
</script>

<template>
  <section class="space-y-3">
    <div class="space-y-1">
      <h3 class="text-heading">Markets ranked by basket total in this city</h3>
      <p class="text-body">Top 5 markets ranked by current basket total. Lower is better.</p>
    </div>

    <div v-if="props.isLoading" class="space-y-2">
      <Skeleton v-for="index in 5" :key="index" height="4rem" rounded="0.75rem" />
    </div>

    <div v-else-if="props.error" class="status-wrap">
      <EmptyState
        title="Ranking unavailable"
        :message="props.error"
        cta-label="Retry"
        @retry="emit('retry')"
      />
    </div>

    <div v-else-if="props.rows.length === 0" class="status-wrap">
      <EmptyState
        title="No market totals yet"
        message="Ranking appears after basket totals are available in this city."
      />
    </div>

    <ol v-else class="divide-y divide-slate-200 rounded-xl border border-slate-200 bg-white">
      <li v-for="(row, index) in props.rows" :key="row.market_id">
        <button
          type="button"
          class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-200"
          @click="emit('select', row.market_id)"
        >
          <div class="flex min-w-0 items-center gap-3">
            <span class="w-6 text-sm font-semibold text-slate-400">{{ index + 1 }}</span>
            <img
              :src="getMarketLogo(row.market_name)"
              :alt="`${row.market_name} logo`"
              class="h-9 w-9 rounded-md border border-slate-200 bg-white p-0.5 object-contain"
            />
            <div class="min-w-0">
              <p class="truncate text-sm font-semibold text-slate-900">{{ row.market_name }}</p>
              <p class="truncate text-xs text-slate-500">{{ row.market_address ?? 'Address unavailable' }}</p>
            </div>
          </div>

          <div class="flex items-center gap-3">
            <div class="text-right">
              <p class="text-base font-semibold text-slate-900">{{ formatMoney(row.total_price_eur) }}</p>
              <p class="text-xs text-slate-500">
                {{ index === 0 ? 'Best price' : formatDelta(row.total_price_eur - (props.rows[0]?.total_price_eur ?? row.total_price_eur)) }}
              </p>
            </div>
            <svg class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path
                fill-rule="evenodd"
                d="M7.22 4.22a.75.75 0 011.06 0l5.25 5.25a.75.75 0 010 1.06l-5.25 5.25a.75.75 0 11-1.06-1.06L11.94 10 7.22 5.28a.75.75 0 010-1.06z"
                clip-rule="evenodd"
              />
            </svg>
          </div>
        </button>
      </li>
    </ol>
  </section>
</template>
