import { ref, type Ref, watch } from 'vue'

import { getProductPricesByCity, toErrorMessage } from '@/lib/api'
import { apiClient } from '@/lib/api/client'
import { MemoryCache } from '@/lib/cache/memoryCache'
import type {
  ProductPriceByCityRow,
  ProductPricesByCityMeta,
} from '@/lib/types/api'

const pricesByCityCache = new MemoryCache<unknown>(90_000)

type UnknownRecord = Record<string, unknown>

function asRecord(value: unknown): UnknownRecord | null {
  if (value === null || typeof value !== 'object' || Array.isArray(value)) {
    return null
  }

  return value as UnknownRecord
}

function toNullableString(value: unknown): string | null {
  if (typeof value !== 'string') {
    return null
  }

  return value
}

function toNumber(value: unknown): number | null {
  if (typeof value === 'number' && Number.isFinite(value)) {
    return value
  }

  if (typeof value === 'string') {
    const parsed = Number(value)
    if (Number.isFinite(parsed)) {
      return parsed
    }
  }

  return null
}

function resolvePriceArray(payload: unknown): unknown[] {
  if (Array.isArray(payload)) {
    return payload
  }

  const root = asRecord(payload)
  if (!root) {
    return []
  }

  if (Array.isArray(root.data)) {
    return root.data
  }

  if (Array.isArray(root.prices)) {
    return root.prices
  }

  const dataRecord = asRecord(root.data)
  if (dataRecord && Array.isArray(dataRecord.prices)) {
    return dataRecord.prices
  }

  return []
}

function normalizeRow(value: unknown): ProductPriceByCityRow | null {
  const row = asRecord(value)
  if (!row) {
    return null
  }

  const marketObject = asRecord(row.market)
  const marketId = toNumber(marketObject?.id ?? row.market_id)
  const marketName = toNullableString(marketObject?.name ?? row.market_name)
  const marketAddress = toNullableString(marketObject?.address ?? row.address ?? row.market_address)
  const priceEur = toNumber(row.price_eur)
  const recordedAt = toNullableString(row.recorded_at)
  const deltaFromCheapest = toNumber(row.delta_from_cheapest_eur)

  if (marketId === null || marketName === null || priceEur === null) {
    return null
  }

  return {
    market: {
      id: marketId,
      name: marketName,
      address: marketAddress,
    },
    price_eur: priceEur,
    delta_from_cheapest_eur: deltaFromCheapest ?? 0,
    recorded_at: recordedAt,
  }
}

function roundToTwo(value: number): number {
  return Math.round((value + Number.EPSILON) * 100) / 100
}

function parseRows(payload: unknown): ProductPriceByCityRow[] {
  const normalized = resolvePriceArray(payload)
    .map((row) => normalizeRow(row))
    .filter((row): row is ProductPriceByCityRow => row !== null)
    .sort((left, right) => {
      if (left.price_eur !== right.price_eur) {
        return left.price_eur - right.price_eur
      }

      return left.market.name.localeCompare(right.market.name)
    })

  const cheapestPrice = normalized[0]?.price_eur

  if (cheapestPrice === undefined) {
    return []
  }

  return normalized.map((row) => ({
    ...row,
    delta_from_cheapest_eur: roundToTwo(row.price_eur - cheapestPrice),
  }))
}

function parseMeta(payload: unknown, fallbackCount: number): ProductPricesByCityMeta | null {
  const root = asRecord(payload)
  const metaRecord = asRecord(root?.meta)

  const count = toNumber(metaRecord?.count) ?? fallbackCount
  const updatedAt =
    toNullableString(metaRecord?.updated_at) ??
    toNullableString(metaRecord?.recorded_at) ??
    null

  return {
    count,
    updated_at: updatedAt,
  }
}

export function useProductPricesByCity(
  productId: Ref<number | null>,
  cityId: Ref<number | null>,
) {
  const rows = ref<ProductPriceByCityRow[]>([])
  const meta = ref<ProductPricesByCityMeta | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  async function load(force = false): Promise<void> {
    const resolvedProductId = productId.value
    const resolvedCityId = cityId.value

    if (resolvedProductId === null || resolvedCityId === null) {
      rows.value = []
      meta.value = null
      error.value = null
      isLoading.value = false
      return
    }

    const cacheKey = `products:prices:${resolvedProductId}:${resolvedCityId}`
    if (force) {
      pricesByCityCache.delete(cacheKey)
    }

    isLoading.value = true
    error.value = null

    try {
      const endpointPath = `/products/${resolvedProductId}/prices`
      const endpointUrl = `${apiClient.defaults.baseURL ?? ''}${endpointPath}?city_id=${resolvedCityId}`

      if (import.meta.env.DEV) {
        console.debug('[useProductPricesByCity] request', {
          cityId: resolvedCityId,
          endpoint: endpointUrl,
        })
      }

      const rawResponse = await pricesByCityCache.getOrSet(
        cacheKey,
        () => getProductPricesByCity(resolvedProductId, resolvedCityId),
      )

      if (import.meta.env.DEV) {
        console.debug('[useProductPricesByCity] response', {
          cityId: resolvedCityId,
          endpoint: endpointUrl,
          raw: rawResponse,
        })
      }

      const resolvedRows = parseRows(rawResponse)
      rows.value = resolvedRows
      meta.value = parseMeta(rawResponse, resolvedRows.length)
    } catch (caughtError) {
      error.value = toErrorMessage(caughtError)
      rows.value = []
      meta.value = null
    } finally {
      isLoading.value = false
    }
  }

  function clear(): void {
    rows.value = []
    meta.value = null
    error.value = null
    isLoading.value = false
  }

  watch([productId, cityId], clear)

  return {
    rows,
    meta,
    isLoading,
    error,
    load,
    clear,
  }
}
