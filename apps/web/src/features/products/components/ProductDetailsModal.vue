<script setup lang="ts">
import { computed, ref, watch } from 'vue'

import { useCities } from '@/features/city/composables/useCities'
import { useCityStore } from '@/features/city/store/useCityStore'
import { getMarketLogo } from '@/features/markets/utils/getMarketLogo'
import { getProductIcon } from '@/features/product/utils/getProductIcon'
import { useMarkets } from '@/features/products/composables/useMarkets'
import { useProductCheapest } from '@/features/products/composables/useProductCheapest'
import { useProductDetail } from '@/features/products/composables/useProductDetail'
import { useProductPricesByCity } from '@/features/products/composables/useProductPricesByCity'
import type { ProductPriceByCityRow } from '@/lib/types/api'
import Badge from '@/shared/ui/Badge.vue'
import Button from '@/shared/ui/Button.vue'
import EmptyState from '@/shared/ui/EmptyState.vue'
import Modal from '@/shared/ui/Modal.vue'
import PopoverSelect from '@/shared/ui/PopoverSelect.vue'
import Skeleton from '@/shared/ui/Skeleton.vue'

const props = withDefaults(
  defineProps<{
    modelValue: boolean
    productId: number | null
    cityId: number | null
  }>(),
  {
    productId: null,
    cityId: null,
  },
)

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
}>()

const cityStore = useCityStore()
const { cities, isLoadingCities, citiesError, loadCities } = useCities()

const selectedCityId = computed({
  get: (): number | null => cityStore.selectedCityId,
  set: (cityId: number | null) => {
    cityStore.setSelectedCityId(cityId)
  },
})

const productIdRef = computed(() => props.productId)
const { product, productError, isLoadingProduct, loadProduct } = useProductDetail()

const {
  markets,
  isLoading: isLoadingMarkets,
  error: marketsError,
  refresh: refreshMarkets,
} = useMarkets(selectedCityId)

const {
  result: cheapestResult,
  isLoading: isLoadingCheapest,
  error: cheapestError,
  refresh: refreshCheapest,
} = useProductCheapest(productIdRef, selectedCityId)

const {
  rows: compareRows,
  isLoading: isLoadingCompareRows,
  error: compareRowsError,
  load: loadCompareRows,
} = useProductPricesByCity(productIdRef, selectedCityId)

const isCompareExpanded = ref(false)
const selectedMarketId = ref<number | string | null>(null)
const hasLoadedCompareRows = ref(false)

const panelClass = computed(() =>
  isCompareExpanded.value ? 'max-w-[1200px]' : 'max-w-[920px]',
)

const cityOptions = computed(() =>
  cities.value.map((city) => ({
    label: city.name,
    value: city.id,
  })),
)

const marketOptions = computed(() =>
  markets.value.map((market) => ({
    label: market.name,
    value: market.id,
  })),
)

const productIcon = computed(() =>
  getProductIcon(product.value?.image_key, product.value?.name, product.value?.category),
)

const selectedCityName = computed(() => {
  if (selectedCityId.value === null) {
    return 'selected city'
  }

  const selectedCity = cities.value.find((city) => city.id === selectedCityId.value)
  return selectedCity?.name ?? 'selected city'
})

const selectedMarketKey = computed(() => {
  if (selectedMarketId.value === null) {
    return null
  }

  return String(selectedMarketId.value)
})

const selectedMarketRow = computed<ProductPriceByCityRow | null>(() => {
  if (selectedMarketKey.value === null) {
    return null
  }

  return (
    compareRows.value.find((row) => String(row.market.id) === selectedMarketKey.value) ??
    null
  )
})

const isMarketSelectorDisabled = computed(
  () =>
    selectedCityId.value === null ||
    isLoadingMarkets.value ||
    markets.value.length === 0,
)

const compareCountLabel = computed(() => `${compareRows.value.length} markets`)

const cheapestMarketRow = computed<ProductPriceByCityRow | null>(() => {
  if (!cheapestResult.value?.cheapest) {
    return null
  }

  return {
    market: {
      id: cheapestResult.value.cheapest.market.id,
      name: cheapestResult.value.cheapest.market.name,
      address: cheapestResult.value.cheapest.market.address,
    },
    price_eur: cheapestResult.value.cheapest.price_eur,
    delta_from_cheapest_eur: 0,
    recorded_at: cheapestResult.value.recorded_at,
  }
})

const selectedMatchesCheapest = computed(() => {
  if (selectedMarketKey.value === null || cheapestMarketRow.value === null) {
    return false
  }

  return String(cheapestMarketRow.value.market.id) === selectedMarketKey.value
})

const activeMarketRow = computed<ProductPriceByCityRow | null>(() => {
  return selectedMarketRow.value ?? cheapestMarketRow.value
})

const isSelectedMarketView = computed(() => {
  if (selectedMarketKey.value === null) {
    return false
  }

  return selectedMarketRow.value !== null || selectedMatchesCheapest.value
})

const activeMarketBadge = computed(() =>
  isSelectedMarketView.value ? 'Selected market' : `Cheapest in ${selectedCityName.value}`,
)

const activeMarketUpdatedAt = computed(() => activeMarketRow.value?.recorded_at ?? 'latest')

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

function closeModal(): void {
  emit('update:modelValue', false)
}

async function fetchCompareRows(force = false): Promise<void> {
  await loadCompareRows(force)
  hasLoadedCompareRows.value = true
}

async function refreshCompareRows(): Promise<void> {
  await fetchCompareRows(true)
}

async function toggleCompare(): Promise<void> {
  isCompareExpanded.value = !isCompareExpanded.value

  if (isCompareExpanded.value && !hasLoadedCompareRows.value) {
    await fetchCompareRows()
  }
}

function selectCity(cityId: number | string): void {
  const resolvedCityId = Number(cityId)
  selectedCityId.value = Number.isInteger(resolvedCityId) && resolvedCityId > 0
    ? resolvedCityId
    : null
}

function selectMarket(marketId: number | string): void {
  selectedMarketId.value = marketId

  if (!hasLoadedCompareRows.value) {
    void fetchCompareRows()
  }
}

function clearSelectedMarket(): void {
  selectedMarketId.value = null
}

watch(
  () => [props.modelValue, props.productId] as const,
  ([isOpen, productId]) => {
    if (!isOpen) {
      return
    }

    void loadProduct(productId)
  },
  { immediate: true },
)

watch(
  () => props.modelValue,
  (isOpen) => {
    if (isOpen) {
      if (selectedCityId.value === null && props.cityId !== null) {
        selectedCityId.value = props.cityId
      }

      void loadCities()
      void refreshMarkets()
      void refreshCheapest()
      return
    }

    isCompareExpanded.value = false
    selectedMarketId.value = null
    hasLoadedCompareRows.value = false
  },
  { immediate: true },
)

watch(
  [selectedCityId, productIdRef],
  () => {
    selectedMarketId.value = null
    hasLoadedCompareRows.value = false

    if (props.modelValue) {
      void refreshMarkets(true)
      void refreshCheapest(true)
      void fetchCompareRows(true)
    }
  },
)

watch(compareRows, (rows) => {
  if (selectedMarketKey.value === null) {
    return
  }

  if (!rows.some((row) => String(row.market.id) === selectedMarketKey.value)) {
    selectedMarketId.value = null
  }
})
</script>

<template>
  <Modal
    :model-value="props.modelValue"
    :panel-class="panelClass"
    @update:model-value="(value) => emit('update:modelValue', value)"
  >
    <div class="flex max-h-[88vh] flex-col">
      <header class="border-b border-slate-200 px-6 py-5">
        <div v-if="isLoadingProduct" class="space-y-3">
          <Skeleton height="5.2rem" rounded="0.9rem" />
        </div>
        <div v-else-if="productError" class="status-wrap">
          <EmptyState title="Product unavailable" :message="productError" />
        </div>
        <div v-else-if="!product" class="status-wrap">
          <EmptyState title="Select a product" message="Choose a product card to view price details." />
        </div>
        <div v-else class="space-y-5">
          <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex min-w-0 items-center gap-3">
              <img
                :src="productIcon"
                :alt="product.name"
                class="h-14 w-14 rounded-xl border border-slate-200 bg-slate-50 p-1 object-contain"
              />
              <div class="min-w-0">
                <h3 class="truncate text-2xl font-semibold tracking-tight text-slate-900 md:text-3xl">
                  {{ product.name }}
                </h3>
                <p class="truncate text-sm text-slate-600">{{ product.unit_label ?? product.unit }}</p>
              </div>
            </div>

            <Button variant="ghost" size="sm" @click="closeModal">Close</Button>
          </div>

          <div class="grid gap-3 md:grid-cols-2">
            <PopoverSelect
              label="City"
              :model-value="selectedCityId"
              :options="cityOptions"
              :disabled="isLoadingCities || cityOptions.length === 0"
              placeholder="Select city"
              @update:model-value="selectCity"
            />
            <PopoverSelect
              label="Market"
              :model-value="selectedMarketId"
              :options="marketOptions"
              :disabled="isMarketSelectorDisabled"
              placeholder="Select market"
              @update:model-value="selectMarket"
            />
          </div>

          <p v-if="citiesError" class="text-xs text-slate-500">{{ citiesError }}</p>
          <p v-if="marketsError" class="text-xs text-slate-500">{{ marketsError }}</p>
        </div>
      </header>

      <div class="overflow-y-auto px-6 py-6">
        <div
          class="grid gap-6"
          :class="isCompareExpanded ? 'xl:grid-cols-[minmax(0,1fr)_minmax(360px,420px)]' : 'grid-cols-1'"
        >
          <section class="space-y-5">
            <div v-if="selectedCityId === null" class="status-wrap">
              <EmptyState
                title="Choose a city to continue"
                message="City selection drives market availability, cheapest lookup, and compare ranking."
              />
            </div>

            <div v-else-if="isLoadingCheapest" class="space-y-3">
              <Skeleton height="8rem" rounded="0.9rem" />
            </div>

            <div v-else-if="cheapestError" class="status-wrap">
              <EmptyState
                title="Cheapest lookup unavailable"
                :message="cheapestError"
                cta-label="Retry"
                @retry="refreshCheapest(true)"
              />
            </div>

            <article
              v-else-if="activeMarketRow"
              class="rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white p-5"
            >
              <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex min-w-0 items-start gap-3">
                  <img
                    :src="getMarketLogo(activeMarketRow.market.name)"
                    :alt="`${activeMarketRow.market.name} logo`"
                    class="h-11 w-11 rounded-lg border border-slate-200 bg-white p-1 object-contain"
                  />
                  <div class="space-y-1">
                    <div class="flex flex-wrap items-center gap-2">
                      <Badge variant="success">{{ activeMarketBadge }}</Badge>
                      <span class="text-xs text-slate-500">
                        Updated {{ activeMarketUpdatedAt }}
                      </span>
                    </div>
                    <p class="truncate text-xl font-semibold text-slate-900">
                      {{ activeMarketRow.market.name }}
                    </p>
                    <p class="truncate text-sm text-slate-600">
                      {{ activeMarketRow.market.address ?? 'Address unavailable' }}
                    </p>
                  </div>
                </div>

                <div class="text-right">
                  <p class="text-price">
                    {{ formatMoney(activeMarketRow.price_eur) }}
                  </p>
                  <p v-if="isSelectedMarketView" class="text-xs text-slate-500">
                    {{ formatDelta(activeMarketRow.delta_from_cheapest_eur) }}
                  </p>
                </div>
              </div>

              <div v-if="isSelectedMarketView" class="mt-4">
                <Button variant="ghost" size="sm" @click="clearSelectedMarket">Use cheapest</Button>
              </div>
            </article>

            <div v-else-if="selectedCityId !== null" class="status-wrap">
              <EmptyState
                title="No prices found"
                message="No latest prices are available for this product in the selected city."
                cta-label="Retry"
                @retry="refreshCheapest(true)"
              />
            </div>

            <div class="flex flex-wrap items-center gap-3">
              <Button @click="toggleCompare">
                {{ isCompareExpanded ? 'Hide compare' : 'Compare prices' }}
              </Button>
              <Button variant="secondary" @click="closeModal">Close</Button>
              <Button
                v-if="selectedCityId !== null"
                variant="ghost"
                size="sm"
                @click="refreshMarkets(true)"
              >
                Refresh markets
              </Button>
            </div>
          </section>

          <aside
            v-if="isCompareExpanded"
            class="space-y-3 rounded-2xl border border-slate-200 bg-white p-4"
          >
            <div class="flex items-center justify-between gap-3">
              <div>
                <h4 class="text-base font-semibold text-slate-900">Compare by market</h4>
                <p class="text-xs text-slate-500">{{ compareCountLabel }}</p>
              </div>
              <Button variant="ghost" size="sm" @click="refreshCompareRows">Refresh</Button>
            </div>

            <div v-if="isLoadingCompareRows" class="space-y-2">
              <Skeleton v-for="index in 6" :key="index" height="3.9rem" rounded="0.85rem" />
            </div>

            <div v-else-if="compareRowsError" class="status-wrap">
              <EmptyState
                title="Compare data unavailable"
                :message="compareRowsError"
                cta-label="Retry"
                @retry="refreshCompareRows"
              />
            </div>

            <div v-else-if="compareRows.length === 0" class="status-wrap">
              <EmptyState
                title="No compare rows"
                message="No market prices were returned for this city."
              />
            </div>

            <ol v-else class="space-y-1.5">
              <li v-for="(row, index) in compareRows" :key="row.market.id">
                <button
                  type="button"
                  class="w-full rounded-xl border px-3 py-2.5 text-left transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-200"
                  :class="[
                    selectedMarketKey === String(row.market.id)
                      ? 'border-indigo-200 bg-indigo-50'
                      : 'border-slate-200 hover:border-indigo-200 hover:bg-indigo-50/40',
                  ]"
                  @click="selectMarket(row.market.id)"
                >
                  <div class="flex items-center justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-3">
                      <span class="w-5 text-xs font-semibold text-slate-400">{{ index + 1 }}</span>
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
                      <p class="text-sm font-semibold text-slate-900">{{ formatMoney(row.price_eur) }}</p>
                      <p class="text-xs text-slate-500">{{ formatDelta(row.delta_from_cheapest_eur) }}</p>
                    </div>
                  </div>
                </button>
              </li>
            </ol>
          </aside>
        </div>
      </div>
    </div>
  </Modal>
</template>
