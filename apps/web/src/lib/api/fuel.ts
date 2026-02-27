import type {
  ApiListResponse,
  FuelBrandSummary,
  FuelBrandSummaryMeta,
  FuelHistoryMeta,
  FuelHistoryPoint,
  FuelPrice,
  FuelStationRankMeta,
  FuelStationRankRow,
  FuelType,
} from '@/lib/types/api'

import { apiGet, createApiKey, type ApiGetOptions } from '@/lib/api/client'

type RequestOptions = Omit<ApiGetOptions, 'params'>

export async function getFuelPrices(
  cityId: number,
  type: FuelType,
  options: RequestOptions = {},
): Promise<ApiListResponse<FuelPrice>> {
  const params = {
    city_id: cityId,
    type,
    fuel_type: type,
  }

  return apiGet<ApiListResponse<FuelPrice>>(createApiKey('/fuel-prices', params), '/fuel-prices', {
    params,
    ttlMs: 60_000,
    ...options,
  })
}

export async function getFuelLatest(
  cityId: number,
  options: RequestOptions = {},
): Promise<ApiListResponse<FuelPrice>> {
  const params = {
    city_id: cityId,
  }

  return apiGet<ApiListResponse<FuelPrice>>(createApiKey('/fuel/latest', params), '/fuel/latest', {
    params,
    ttlMs: 60_000,
    ...options,
  })
}

export async function getFuelLatestByType(
  cityId: number,
  type: FuelType,
  options: RequestOptions = {},
): Promise<ApiListResponse<FuelPrice>> {
  const params = {
    city_id: cityId,
    type,
  }

  return apiGet<ApiListResponse<FuelPrice>>(createApiKey('/fuel/latest', params), '/fuel/latest', {
    params,
    ttlMs: 60_000,
    ...options,
  })
}

export async function getFuelBrands(
  cityId: number,
  type: FuelType,
  options: RequestOptions = {},
): Promise<ApiListResponse<FuelBrandSummary, FuelBrandSummaryMeta>> {
  const params = {
    city_id: cityId,
    type,
  }

  return apiGet<ApiListResponse<FuelBrandSummary, FuelBrandSummaryMeta>>(
    createApiKey('/fuel/brands', params),
    '/fuel/brands',
    {
      params,
      ttlMs: 60_000,
      ...options,
    },
  )
}

export async function getFuelStations(
  cityId: number,
  type: FuelType,
  options: RequestOptions = {},
): Promise<ApiListResponse<FuelStationRankRow, FuelStationRankMeta>> {
  const params = {
    city_id: cityId,
    type,
  }

  return apiGet<ApiListResponse<FuelStationRankRow, FuelStationRankMeta>>(
    createApiKey('/fuel/stations', params),
    '/fuel/stations',
    {
      params,
      ttlMs: 60_000,
      ...options,
    },
  )
}

export async function getFuelHistory(
  cityId: number,
  type: FuelType,
  days: number,
  brandKey?: string | null,
  options: RequestOptions = {},
): Promise<ApiListResponse<FuelHistoryPoint, FuelHistoryMeta>> {
  const params = {
    city_id: cityId,
    type,
    brand_key: brandKey ?? undefined,
    days,
  }

  return apiGet<ApiListResponse<FuelHistoryPoint, FuelHistoryMeta>>(
    createApiKey('/fuel/history', params),
    '/fuel/history',
    {
      params,
      ttlMs: 60_000,
      ...options,
    },
  )
}
