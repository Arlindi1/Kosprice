import type { ApiListResponse, City } from '@/lib/types/api'

import { apiGet, createApiKey, type ApiGetOptions } from '@/lib/api/client'

type RequestOptions = Omit<ApiGetOptions, 'params'>

export async function getCities(options: RequestOptions = {}): Promise<ApiListResponse<City>> {
  const key = createApiKey('/cities')
  return apiGet<ApiListResponse<City>>(key, '/cities', {
    ttlMs: 60_000,
    ...options,
  })
}
