import type { ApiItemResponse, ApiListResponse, Market, MarketBasket } from '@/lib/types/api'

import { apiGet, createApiKey, type ApiGetOptions } from '@/lib/api/client'

type RequestOptions = Omit<ApiGetOptions, 'params'>

export async function getMarkets(
  cityId: number,
  options: RequestOptions = {},
): Promise<ApiListResponse<Market>> {
  const params = {
    city_id: cityId,
  }

  return apiGet<ApiListResponse<Market>>(createApiKey('/markets', params), '/markets', {
    params,
    ttlMs: 60_000,
    ...options,
  })
}

export async function getMarketBasket(
  marketId: number,
  recordedAt?: string,
  options: RequestOptions = {},
): Promise<ApiItemResponse<MarketBasket>> {
  const params = {
    recorded_at: recordedAt,
  }

  return apiGet<ApiItemResponse<MarketBasket>>(
    createApiKey(`/markets/${marketId}/basket`, params),
    `/markets/${marketId}/basket`,
    {
      params,
      ttlMs: 60_000,
      ...options,
    },
  )
}
