<script setup lang="ts">
import { computed, ref, watch } from 'vue'

import CitySelector from '@/features/city/components/CitySelector.vue'
import { useCityStore } from '@/features/city/store/useCityStore'
import { useFuel } from '@/features/fuel/composables/useFuel'
import { getFuelBrandLogo } from '@/features/fuel/utils/getFuelBrandLogo'
import { abortApiRequestsForCity } from '@/lib/api/client'
import type { FuelType } from '@/lib/types/api'
import LineChart from '@/shared/charts/LineChart.vue'
import Badge from '@/shared/ui/Badge.vue'
import Button from '@/shared/ui/Button.vue'
import Card from '@/shared/ui/Card.vue'
import EmptyState from '@/shared/ui/EmptyState.vue'
import SegmentedControl from '@/shared/ui/SegmentedControl.vue'
import Skeleton from '@/shared/ui/Skeleton.vue'

type HistoryMode = 'overall' | 'brand'

const cityStore = useCityStore()
const activeCityId = computed(() => cityStore.activeCityId)
const selectedType = ref<FuelType>('diesel')
const selectedDays = ref<14 | 30>(30)
const historyMode = ref<HistoryMode>('overall')
const selectedBrandKey = ref<string | null>(null)
const expandedStationId = ref<number | null>(null)

const {
  latestRows,
  isLoadingLatestRows,
  latestRowsError,
  brandSummary,
  isLoadingBrandSummary,
  brandSummaryError,
  stationRows,
  isLoadingStationRows,
  stationRowsError,
  fuelHistory,
  isLoadingFuelHistory,
  fuelHistoryError,
  loadLatestRows,
  loadBrandSummary,
  loadStationRows,
  loadFuelHistory,
} = useFuel()

const typeOptions = [
  { label: 'Diesel', value: 'diesel' },
  { label: 'Petrol95', value: 'petrol95' },
]

const historyOptions = [
  { label: '14d', value: 14 },
  { label: '30d', value: 30 },
]

const historyModeOptions = [
  { label: 'Overall', value: 'overall' },
  { label: 'By brand', value: 'brand' },
]

const selectedTypeValue = computed({
  get: () => selectedType.value,
  set: (value: string | number) => {
    if (value === 'diesel' || value === 'petrol95') {
      selectedType.value = value
    }
  },
})

const selectedDaysValue = computed({
  get: () => selectedDays.value,
  set: (value: string | number) => {
    if (value === 14 || value === 30) {
      selectedDays.value = value
    }
  },
})

const historyModeValue = computed({
  get: () => historyMode.value,
  set: (value: string | number) => {
    if (value === 'overall' || value === 'brand') {
      historyMode.value = value
    }
  },
})

const activeHistoryBrandKey = computed(() =>
  historyMode.value === 'brand' ? selectedBrandKey.value : null,
)

const selectedBrand = computed(() =>
  brandSummary.value.find((brand) => brand.brand_key === selectedBrandKey.value) ?? null,
)

const selectedBrandName = computed(() => selectedBrand.value?.brand_name ?? 'Selected brand')

const cheapestStation = computed(() => stationRows.value[0] ?? null)

const currentAverage = computed(() => {
  if (latestRows.value.length === 0) {
    return null
  }

  const total = latestRows.value.reduce((sum, row) => sum + row.price_eur_per_l, 0)
  return total / latestRows.value.length
})

const averageSavings = computed(() => {
  if (cheapestStation.value === null || currentAverage.value === null) {
    return null
  }

  return Math.max(0, currentAverage.value - cheapestStation.value.price_eur_per_l)
})

const fuelTypeLabel = computed(() => selectedType.value.toUpperCase())
const stationSnapshotError = computed(() => latestRowsError.value ?? stationRowsError.value)

const historyLabels = computed(() => fuelHistory.value.map((row) => row.recorded_at.slice(5)))
const historyDatasets = computed(() => {
  const isBrandMode = historyMode.value === 'brand'
  const scope = isBrandMode ? selectedBrandName.value : 'Overall city'

  return [
    {
      label: `${scope} ${fuelTypeLabel.value} average`,
      data: fuelHistory.value.map((row) => row.avg_price_eur_liter),
      borderColor: isBrandMode ? '#e11d48' : '#4f46e5',
      backgroundColor: isBrandMode ? 'rgba(225, 29, 72, 0.14)' : 'rgba(79, 70, 229, 0.15)',
      fill: true,
    },
  ]
})

function formatFuel(value: number | null): string {
  if (value === null) {
    return 'N/A'
  }

  return `${value.toFixed(3)} EUR/L`
}

function formatDate(value: string | null): string {
  if (!value) {
    return 'No update'
  }

  return new Date(`${value}T00:00:00Z`).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  })
}

function selectBrandForHistory(brandKey: string): void {
  selectedBrandKey.value = brandKey
  historyMode.value = 'brand'
}

function toggleStation(stationId: number): void {
  expandedStationId.value = expandedStationId.value === stationId ? null : stationId
}

async function refreshFuel(force = false): Promise<void> {
  const cityId = activeCityId.value
  const type = selectedType.value
  const brandKey = historyMode.value === 'brand' ? selectedBrandKey.value : null

  await Promise.allSettled([
    loadLatestRows(cityId, type, force),
    loadBrandSummary(cityId, type, force),
    loadStationRows(cityId, type, force),
    loadFuelHistory(cityId, type, selectedDays.value, brandKey, force),
  ])
}

watch(
  activeCityId,
  (cityId, previousCityId) => {
    if (typeof previousCityId === 'number' && previousCityId !== cityId) {
      abortApiRequestsForCity(previousCityId)
    }

    expandedStationId.value = null

    void Promise.allSettled([
      loadLatestRows(cityId, selectedType.value),
      loadBrandSummary(cityId, selectedType.value),
      loadStationRows(cityId, selectedType.value),
      loadFuelHistory(
        cityId,
        selectedType.value,
        selectedDays.value,
        historyMode.value === 'brand' ? selectedBrandKey.value : null,
      ),
    ])
  },
  { immediate: true },
)

watch(selectedType, (type) => {
    expandedStationId.value = null

    void Promise.allSettled([
      loadLatestRows(activeCityId.value, type),
      loadBrandSummary(activeCityId.value, type),
      loadStationRows(activeCityId.value, type),
      loadFuelHistory(
        activeCityId.value,
        type,
        selectedDays.value,
        historyMode.value === 'brand' ? selectedBrandKey.value : null,
      ),
    ])
  },
)

watch(
  brandSummary,
  (rows) => {
    if (rows.length === 0) {
      selectedBrandKey.value = null
      historyMode.value = 'overall'
      return
    }

    if (
      selectedBrandKey.value === null ||
      !rows.some((row) => row.brand_key === selectedBrandKey.value)
    ) {
      selectedBrandKey.value = rows[0]?.brand_key ?? null
    }
  },
  { immediate: true },
)

watch(
  [selectedDays, historyMode, selectedBrandKey],
  ([days, mode, brandKey]) => {
    if (mode === 'brand' && brandKey === null) {
      return
    }

    void loadFuelHistory(
      activeCityId.value,
      selectedType.value,
      days,
      mode === 'brand' ? brandKey : null,
    )
  },
)
</script>

<template>
  <section class="page-stack">
    <Card compact variant="highlight">
      <div class="flex flex-wrap items-end justify-between gap-4">
        <div class="space-y-1">
          <p class="text-label text-rose-500">Fuel Catalog</p>
          <h2 class="text-display">Real-time fuel brands and station ranking</h2>
          <p class="text-body">
            Compare brands in your city, spot the cheapest station, and track price movement.
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <CitySelector mode="pill" />
          <SegmentedControl v-model="selectedTypeValue" :options="typeOptions" />
          <Button variant="ghost" size="sm" @click="refreshFuel(true)">Refresh</Button>
        </div>
      </div>
    </Card>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(0,1fr)]">
      <Card title="Cheapest today" subtitle="Best station for selected fuel type in your city">
        <div v-if="isLoadingLatestRows || isLoadingStationRows" class="status-wrap">
          <Skeleton height="9rem" rounded="1rem" />
        </div>
        <div v-else-if="stationSnapshotError" class="status-wrap">
          <EmptyState
            title="Fuel snapshot unavailable"
            :message="stationSnapshotError"
            cta-label="Retry"
            @retry="refreshFuel(true)"
          />
        </div>
        <div v-else-if="cheapestStation === null" class="status-wrap">
          <EmptyState
            title="No fuel station data"
            message="Select a city and fuel type to load the latest station prices."
          />
        </div>
        <article
          v-else
          class="space-y-4 rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50 via-white to-amber-50 p-4"
        >
          <div class="flex items-start justify-between gap-3">
            <div class="flex min-w-0 items-start gap-3">
              <img
                :src="getFuelBrandLogo(cheapestStation.brand_key)"
                :alt="`${cheapestStation.brand_name} logo`"
                class="h-12 w-12 rounded-xl border border-slate-200 bg-white p-1.5 object-contain"
              />
              <div class="min-w-0">
                <p class="text-sm font-semibold text-slate-600">{{ cheapestStation.brand_name }}</p>
                <h3 class="clamp-2 text-xl font-extrabold tracking-tight text-slate-900">
                  {{ cheapestStation.station_name }}
                </h3>
                <p class="mt-1 text-sm text-slate-500">{{ cheapestStation.address ?? 'Address unavailable' }}</p>
              </div>
            </div>

            <Badge variant="accent">{{ fuelTypeLabel }}</Badge>
          </div>

          <div class="flex flex-wrap items-end justify-between gap-3 border-t border-emerald-200 pt-3">
            <div>
              <p class="text-xs uppercase tracking-wide text-slate-500">Best price now</p>
              <p class="text-3xl font-extrabold tracking-tight text-emerald-600">
                {{ formatFuel(cheapestStation.price_eur_per_l) }}
              </p>
            </div>
            <div class="text-right">
              <p class="text-xs uppercase tracking-wide text-slate-500">Average city price</p>
              <p class="text-lg font-bold text-slate-900">{{ formatFuel(currentAverage) }}</p>
              <p class="text-xs font-semibold text-emerald-600">
                Save {{ formatFuel(averageSavings) }}
              </p>
            </div>
          </div>
        </article>
      </Card>

      <Card title="Brand grid" subtitle="Best price per brand in selected city">
        <div v-if="isLoadingBrandSummary" class="grid gap-3 sm:grid-cols-2">
          <Skeleton v-for="index in 4" :key="index" height="7.5rem" rounded="1rem" />
        </div>
        <div v-else-if="brandSummaryError" class="status-wrap">
          <EmptyState
            title="Brand summary unavailable"
            :message="brandSummaryError"
            cta-label="Retry"
            @retry="loadBrandSummary(activeCityId, selectedType, true)"
          />
        </div>
        <div v-else-if="brandSummary.length === 0" class="status-wrap">
          <EmptyState
            title="No brands found"
            message="No brand-level stations were found for this city and fuel type."
          />
        </div>
        <div v-else class="grid gap-3 sm:grid-cols-2">
          <button
            v-for="brand in brandSummary"
            :key="brand.brand_key"
            type="button"
            class="rounded-2xl border bg-white p-3 text-left transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-200"
            :class="
              selectedBrandKey === brand.brand_key
                ? 'border-indigo-300 ring-1 ring-indigo-100'
                : 'border-slate-200 hover:border-indigo-200'
            "
            @click="selectBrandForHistory(brand.brand_key)"
          >
            <div class="flex items-start justify-between gap-3">
              <div class="flex min-w-0 items-center gap-2.5">
                <img
                  :src="getFuelBrandLogo(brand.brand_key)"
                  :alt="`${brand.brand_name} logo`"
                  class="h-10 w-10 rounded-lg border border-slate-200 bg-white p-1 object-contain"
                />
                <div class="min-w-0">
                  <p class="truncate text-sm font-bold text-slate-900">{{ brand.brand_name }}</p>
                  <p class="truncate text-xs text-slate-500">{{ brand.station_count }} stations</p>
                </div>
              </div>

              <p class="text-sm font-extrabold text-emerald-600">{{ formatFuel(brand.best_price) }}</p>
            </div>

            <p class="mt-2 truncate text-xs text-slate-500">
              Best at {{ brand.best_station_name ?? 'Unknown station' }}
            </p>
          </button>
        </div>
      </Card>
    </div>

    <Card title="Station ranking" subtitle="Tap a row to expand station details">
      <div v-if="isLoadingStationRows" class="status-wrap">
        <Skeleton v-for="index in 6" :key="index" height="3.8rem" rounded="0.9rem" />
      </div>
      <div v-else-if="stationRowsError" class="status-wrap">
        <EmptyState
          title="Station list unavailable"
          :message="stationRowsError"
          cta-label="Retry"
          @retry="loadStationRows(activeCityId, selectedType, true)"
        />
      </div>
      <div v-else-if="stationRows.length === 0" class="status-wrap">
        <EmptyState
          title="No stations available"
          message="No ranked stations were found for this city and fuel type."
        />
      </div>
      <ol v-else class="space-y-2">
        <li v-for="(row, index) in stationRows" :key="`${row.station_id}:${row.fuel_type}`" class="rounded-xl border border-slate-200 bg-white">
          <button
            type="button"
            class="flex w-full items-center justify-between gap-3 px-3 py-3 text-left transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-200"
            @click="toggleStation(row.station_id)"
          >
            <div class="flex min-w-0 items-center gap-3">
              <div
                class="grid h-7 w-7 shrink-0 place-items-center rounded-full text-xs font-bold"
                :class="index === 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'"
              >
                {{ index + 1 }}
              </div>
              <img
                :src="getFuelBrandLogo(row.brand_key)"
                :alt="`${row.brand_name} logo`"
                class="h-8 w-8 rounded-md border border-slate-200 bg-white p-1 object-contain"
              />
              <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-slate-900">{{ row.station_name }}</p>
                <p class="truncate text-xs text-slate-500">{{ row.brand_name }}</p>
              </div>
            </div>

            <div class="text-right">
              <p class="text-sm font-extrabold text-emerald-600">{{ formatFuel(row.price_eur_per_l) }}</p>
              <p class="text-[11px] text-slate-500">{{ formatDate(row.recorded_at) }}</p>
            </div>
          </button>

          <div
            v-if="expandedStationId === row.station_id"
            class="border-t border-slate-200 bg-slate-50/70 px-3 py-3 text-sm"
          >
            <p class="text-slate-700"><span class="font-semibold">Address:</span> {{ row.address ?? 'Address unavailable' }}</p>
            <p class="mt-1 text-slate-600"><span class="font-semibold">Fuel type:</span> {{ row.fuel_type.toUpperCase() }}</p>
            <p class="mt-1 text-slate-600"><span class="font-semibold">City:</span> {{ row.city_name }}</p>
          </div>
        </li>
      </ol>
    </Card>

    <Card title="Fuel history" subtitle="Overall city average or selected brand trend">
      <template #actions>
        <div class="flex flex-wrap items-center gap-2">
          <SegmentedControl v-model="historyModeValue" :options="historyModeOptions" />
          <SegmentedControl v-model="selectedDaysValue" :options="historyOptions" />
        </div>
      </template>

      <div
        v-if="historyMode === 'brand' && selectedBrand !== null"
        class="mb-4 flex items-center gap-2 rounded-xl border border-indigo-200 bg-indigo-50 px-3 py-2 text-sm text-indigo-700"
      >
        <img
          :src="getFuelBrandLogo(selectedBrand.brand_key)"
          :alt="`${selectedBrand.brand_name} logo`"
          class="h-6 w-6 rounded-md border border-indigo-200 bg-white p-0.5 object-contain"
        />
        <span class="font-semibold">{{ selectedBrand.brand_name }}</span>
        <span>history mode</span>
      </div>

      <div v-if="isLoadingFuelHistory" class="status-wrap">
        <Skeleton height="18rem" />
      </div>
      <div v-else-if="fuelHistoryError" class="status-wrap">
        <EmptyState
          title="History unavailable"
          :message="fuelHistoryError"
          cta-label="Retry"
          @retry="loadFuelHistory(activeCityId, selectedType, selectedDays, activeHistoryBrandKey, true)"
        />
      </div>
      <div v-else-if="fuelHistory.length === 0" class="status-wrap">
        <EmptyState
          title="No history points"
          message="No history points were returned for this city, type, and history mode."
        />
      </div>
      <LineChart v-else :labels="historyLabels" :datasets="historyDatasets" :height="320" />
    </Card>
  </section>
</template>
