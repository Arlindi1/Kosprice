type CacheEntry<TValue> = {
  value: TValue
  expiresAt: number
}

export class MemoryCache<TValue> {
  private readonly store = new Map<string, CacheEntry<TValue>>()
  private readonly ttlMs: number

  public constructor(ttlMs = 60_000) {
    this.ttlMs = ttlMs
  }

  public get(key: string): TValue | undefined {
    const entry = this.store.get(key)

    if (!entry) {
      return undefined
    }

    if (entry.expiresAt <= Date.now()) {
      this.store.delete(key)
      return undefined
    }

    return entry.value
  }

  public set(key: string, value: TValue): void {
    this.store.set(key, {
      value,
      expiresAt: Date.now() + this.ttlMs,
    })
  }

  public delete(key: string): void {
    this.store.delete(key)
  }

  public clear(): void {
    this.store.clear()
  }

  public async getOrSet(key: string, resolver: () => Promise<TValue>): Promise<TValue> {
    const cachedValue = this.get(key)

    if (cachedValue !== undefined) {
      return cachedValue
    }

    const resolvedValue = await resolver()
    this.set(key, resolvedValue)
    return resolvedValue
  }
}
