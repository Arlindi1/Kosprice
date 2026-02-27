import type {
  ApiItemResponse,
  ApiListResponse,
  BasketSummary,
  BasketTrendMeta,
  BasketTrendPoint,
} from '@/lib/types/api'

import { apiGet, createApiKey, type ApiGetOptions } from '@/lib/api/client'

type RequestOptions = Omit<ApiGetOptions, 'params'>

export async function getBasketCheapest(
  cityId: number,
  options: RequestOptions = {},
): Promise<ApiItemResponse<BasketSummary>> {
  const params = {
    city_id: cityId,
  }

  return apiGet<ApiItemResponse<BasketSummary>>(createApiKey('/basket/cheapest', params), '/basket/cheapest', {
    params,
    ttlMs: 60_000,
    ...options,
  })
}

export async function getBasketTotal(
  cityId: number,
  marketId: number,
  options: RequestOptions = {},
): Promise<ApiItemResponse<BasketSummary>> {
  const params = {
    city_id: cityId,
    market_id: marketId,
  }

  return apiGet<ApiItemResponse<BasketSummary>>(createApiKey('/basket/total', params), '/basket/total', {
    params,
    ttlMs: 60_000,
    ...options,
  })
}

export async function getBasketTrend(
  cityId: number,
  days: number,
  options: RequestOptions = {},
): Promise<ApiListResponse<BasketTrendPoint, BasketTrendMeta>> {
  const params = {
    city_id: cityId,
    days,
  }

  return apiGet<ApiListResponse<BasketTrendPoint, BasketTrendMeta>>(
    createApiKey('/basket/trend', params),
    '/basket/trend',
    {
      params,
      ttlMs: 60_000,
      ...options,
    },
  )
}
