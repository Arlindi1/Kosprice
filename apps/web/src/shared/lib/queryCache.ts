type CacheEntry<TData> = {
  data: TData
  expiresAt: number
}

type CacheFetcher<TData> = (signal: AbortSignal) => Promise<TData>

class QueryCache {
  private readonly cache = new Map<string, CacheEntry<unknown>>()
  private readonly inFlight = new Map<string, Promise<unknown>>()
  private readonly controllers = new Map<string, AbortController>()

  public get<TData>(key: string): TData | undefined {
    const entry = this.cache.get(key)

    if (!entry) {
      return undefined
    }

    if (entry.expiresAt <= Date.now()) {
      this.cache.delete(key)
      return undefined
    }

    return entry.data as TData
  }

  public set<TData>(key: string, data: TData, ttlMs: number): void {
    this.cache.set(key, {
      data,
      expiresAt: Date.now() + Math.max(0, ttlMs),
    })
  }

  public delete(key: string): void {
    this.cache.delete(key)
  }

  public clear(): void {
    this.cache.clear()
  }

  public abort(key: string): void {
    const controller = this.controllers.get(key)
    controller?.abort()
    this.controllers.delete(key)
    this.inFlight.delete(key)
  }

  public abortWhere(predicate: (key: string) => boolean): void {
    for (const key of this.controllers.keys()) {
      if (predicate(key)) {
        this.abort(key)
      }
    }
  }

  public getOrFetch<TData>(
    key: string,
    fetcher: CacheFetcher<TData>,
    ttlMs: number,
  ): Promise<TData> {
    const cached = this.get<TData>(key)
    if (cached !== undefined) {
      this.logDev('cache', key)
      return Promise.resolve(cached)
    }

    const pending = this.inFlight.get(key)
    if (pending) {
      this.logDev('dedupe', key)
      return pending as Promise<TData>
    }

    const controller = new AbortController()
    this.controllers.set(key, controller)

    const request = fetcher(controller.signal)
      .then((data) => {
        this.set(key, data, ttlMs)
        return data
      })
      .finally(() => {
        this.inFlight.delete(key)
        this.controllers.delete(key)
      })

    this.inFlight.set(key, request)

    return request
  }

  private logDev(mode: 'cache' | 'dedupe', key: string): void {
    if (import.meta.env.DEV) {
      console.info(`[queryCache] ${mode} ${key}`)
    }
  }
}

export const queryCache = new QueryCache()
