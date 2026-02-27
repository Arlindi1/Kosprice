import { computed, type Ref } from 'vue'

import type { BasketSummary, BasketTrendPoint, FuelPrice } from '@/lib/types/api'

type TopMover = {
  label: string
  delta: number
}

function formatDateLabel(date: string): string {
  const parsed = new Date(`${date}T00:00:00Z`)
  return parsed.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

export function useDashboardInsights(
  basketTrend: Ref<BasketTrendPoint[]>,
  cheapestBasket: Ref<BasketSummary | null>,
  fuelLatest: Ref<FuelPrice[]>,
) {
  const topMovers = computed<TopMover[]>(() => {
    const trend = basketTrend.value

    if (trend.length < 2) {
      return []
    }

    const movers: TopMover[] = []

    for (let index = 1; index < trend.length; index++) {
      const previous = trend[index - 1]
      const current = trend[index]

      if (!previous || !current) {
        continue
      }

      const delta = Number((current.average_total_eur - previous.average_total_eur).toFixed(2))
      movers.push({
        label: `${formatDateLabel(current.recorded_at)} vs ${formatDateLabel(previous.recorded_at)}`,
        delta,
      })
    }

    return movers.sort((left, right) => Math.abs(right.delta) - Math.abs(left.delta)).slice(0, 3)
  })

  const lastUpdatedLabel = computed(() => {
    const latestDates: string[] = []

    if (cheapestBasket.value?.recorded_at) {
      latestDates.push(cheapestBasket.value.recorded_at)
    }

    for (const fuelPoint of fuelLatest.value) {
      if (fuelPoint.recorded_at) {
        latestDates.push(fuelPoint.recorded_at)
      }
    }

    const basketTrendDates = basketTrend.value.map((point) => point.recorded_at)
    latestDates.push(...basketTrendDates)

    if (latestDates.length === 0) {
      return 'No updates yet'
    }

    latestDates.sort()
    const maxDate = latestDates[latestDates.length - 1]

    if (!maxDate) {
      return 'No updates yet'
    }

    return `Updated ${new Date(`${maxDate}T00:00:00Z`).toLocaleDateString('en-US', {
      month: 'long',
      day: 'numeric',
      year: 'numeric',
    })}`
  })

  return {
    topMovers,
    lastUpdatedLabel,
  }
}

