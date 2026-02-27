import axios from 'axios'

import { queryCache } from '@/shared/lib/queryCache'

const DEFAULT_TIMEOUT_MS = 8_000
const DEFAULT_TTL_MS = 60_000
const apiBaseUrlEnv = (import.meta.env.VITE_API_BASE_URL ?? '').trim()
const apiBaseUrl = (apiBaseUrlEnv === '' ? '/api/v1' : apiBaseUrlEnv).replace(/\/+$/, '')

export const apiClient = axios.create({
  baseURL: apiBaseUrl,
})

type PrimitiveValue = string | number | boolean
type QueryParamValue = PrimitiveValue | null | undefined | PrimitiveValue[]
type QueryParams = Record<string, QueryParamValue>

export type ApiGetOptions = {
  params?: QueryParams
  ttlMs?: number
  signal?: AbortSignal
  timeoutMs?: number
  force?: boolean
}

function stringifyParamValue(value: QueryParamValue): string {
  if (Array.isArray(value)) {
    return value.map((item) => String(item)).join(',')
  }

  return String(value)
}

export function createApiKey(url: string, params?: QueryParams): string {
  if (!params) {
    return url
  }

  const serialized = Object.entries(params)
    .filter(([, value]) => value !== undefined && value !== null && value !== '')
    .sort(([left], [right]) => left.localeCompare(right))
    .map(([name, value]) => `${encodeURIComponent(name)}=${encodeURIComponent(stringifyParamValue(value))}`)

  if (serialized.length === 0) {
    return url
  }

  return `${url}?${serialized.join('&')}`
}

function mergeAbortSignals(cacheSignal: AbortSignal, callerSignal?: AbortSignal): AbortSignal {
  if (!callerSignal) {
    return cacheSignal
  }

  if (cacheSignal.aborted || callerSignal.aborted) {
    const immediate = new AbortController()
    immediate.abort(cacheSignal.aborted ? cacheSignal.reason : callerSignal.reason)
    return immediate.signal
  }

  const merged = new AbortController()

  const abortMerged = (): void => {
    if (!merged.signal.aborted) {
      merged.abort(cacheSignal.aborted ? cacheSignal.reason : callerSignal.reason)
    }
  }

  cacheSignal.addEventListener('abort', abortMerged, { once: true })
  callerSignal.addEventListener('abort', abortMerged, { once: true })

  return merged.signal
}

export function abortApiKey(key: string): void {
  queryCache.abort(key)
}

export function abortApiKeysByPrefix(prefix: string): void {
  queryCache.abortWhere((key) => key.startsWith(prefix))
}

export function abortApiRequestsForCity(cityId: number): void {
  const token = `city_id=${cityId}`
  queryCache.abortWhere((key) => key.includes(token))
}

export function invalidateApiKey(key: string): void {
  queryCache.delete(key)
}

export async function apiGet<TResponse>(
  key: string,
  url: string,
  options: ApiGetOptions = {},
): Promise<TResponse> {
  const ttlMs = options.ttlMs ?? DEFAULT_TTL_MS
  const timeoutMs = options.timeoutMs ?? DEFAULT_TIMEOUT_MS

  if (options.force) {
    invalidateApiKey(key)
    abortApiKey(key)
  }

  return queryCache.getOrFetch<TResponse>(
    key,
    async (cacheSignal) => {
      const signal = mergeAbortSignals(cacheSignal, options.signal)
      const response = await apiClient.get<TResponse>(url, {
        params: options.params,
        signal,
        timeout: timeoutMs,
      })

      return response.data
    },
    ttlMs,
  )
}
