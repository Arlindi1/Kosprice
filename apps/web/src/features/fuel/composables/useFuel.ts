import { ref } from 'vue'

import {
  getFuelBrands,
  getFuelHistory,
  getFuelLatestByType,
  getFuelStations,
  isAbortError,
  toErrorMessage,
} from '@/lib/api'
import type {
  FuelBrandSummary,
  FuelBrandSummaryMeta,
  FuelHistoryMeta,
  FuelHistoryPoint,
  FuelPrice,
  FuelStationRankMeta,
  FuelStationRankRow,
  FuelType,
} from '@/lib/types/api'

export function useFuel() {
  const latestRows = ref<FuelPrice[]>([])
  const isLoadingLatestRows = ref(false)
  const latestRowsError = ref<string | null>(null)

  const brandSummary = ref<FuelBrandSummary[]>([])
  const brandSummaryMeta = ref<FuelBrandSummaryMeta | null>(null)
  const isLoadingBrandSummary = ref(false)
  const brandSummaryError = ref<string | null>(null)

  const stationRows = ref<FuelStationRankRow[]>([])
  const stationRowsMeta = ref<FuelStationRankMeta | null>(null)
  const isLoadingStationRows = ref(false)
  const stationRowsError = ref<string | null>(null)

  const fuelHistory = ref<FuelHistoryPoint[]>([])
  const fuelHistoryMeta = ref<FuelHistoryMeta | null>(null)
  const isLoadingFuelHistory = ref(false)
  const fuelHistoryError = ref<string | null>(null)

  let latestRowsController: AbortController | null = null
  let brandSummaryController: AbortController | null = null
  let stationRowsController: AbortController | null = null
  let fuelHistoryController: AbortController | null = null

  async function loadLatestRows(cityId: number | null, type: FuelType, force = false): Promise<void> {
    latestRowsController?.abort()

    if (cityId === null) {
      latestRows.value = []
      latestRowsError.value = null
      isLoadingLatestRows.value = false
      latestRowsController = null
      return
    }

    const controller = new AbortController()
    latestRowsController = controller

    isLoadingLatestRows.value = true
    latestRowsError.value = null

    try {
      const response = await getFuelLatestByType(cityId, type, {
        force,
        signal: controller.signal,
      })

      if (latestRowsController !== controller) {
        return
      }

      latestRows.value = response.data
    } catch (error) {
      if (isAbortError(error)) {
        return
      }

      latestRowsError.value = toErrorMessage(error)
      latestRows.value = []
    } finally {
      if (latestRowsController === controller) {
        latestRowsController = null
        isLoadingLatestRows.value = false
      }
    }
  }

  async function loadBrandSummary(
    cityId: number | null,
    type: FuelType,
    force = false,
  ): Promise<void> {
    brandSummaryController?.abort()

    if (cityId === null) {
      brandSummary.value = []
      brandSummaryMeta.value = null
      brandSummaryError.value = null
      isLoadingBrandSummary.value = false
      brandSummaryController = null
      return
    }

    const controller = new AbortController()
    brandSummaryController = controller

    isLoadingBrandSummary.value = true
    brandSummaryError.value = null

    try {
      const response = await getFuelBrands(cityId, type, {
        force,
        signal: controller.signal,
      })

      if (brandSummaryController !== controller) {
        return
      }

      brandSummary.value = response.data
      brandSummaryMeta.value = response.meta ?? null
    } catch (error) {
      if (isAbortError(error)) {
        return
      }

      brandSummaryError.value = toErrorMessage(error)
      brandSummary.value = []
      brandSummaryMeta.value = null
    } finally {
      if (brandSummaryController === controller) {
        brandSummaryController = null
        isLoadingBrandSummary.value = false
      }
    }
  }

  async function loadStationRows(
    cityId: number | null,
    type: FuelType,
    force = false,
  ): Promise<void> {
    stationRowsController?.abort()

    if (cityId === null) {
      stationRows.value = []
      stationRowsMeta.value = null
      stationRowsError.value = null
      isLoadingStationRows.value = false
      stationRowsController = null
      return
    }

    const controller = new AbortController()
    stationRowsController = controller

    isLoadingStationRows.value = true
    stationRowsError.value = null

    try {
      const response = await getFuelStations(cityId, type, {
        force,
        signal: controller.signal,
      })

      if (stationRowsController !== controller) {
        return
      }

      stationRows.value = response.data
      stationRowsMeta.value = response.meta ?? null
    } catch (error) {
      if (isAbortError(error)) {
        return
      }

      stationRowsError.value = toErrorMessage(error)
      stationRows.value = []
      stationRowsMeta.value = null
    } finally {
      if (stationRowsController === controller) {
        stationRowsController = null
        isLoadingStationRows.value = false
      }
    }
  }

  async function loadFuelHistory(
    cityId: number | null,
    type: FuelType,
    days: number,
    brandKey: string | null = null,
    force = false,
  ): Promise<void> {
    fuelHistoryController?.abort()

    if (cityId === null) {
      fuelHistory.value = []
      fuelHistoryMeta.value = null
      fuelHistoryError.value = null
      isLoadingFuelHistory.value = false
      fuelHistoryController = null
      return
    }

    const controller = new AbortController()
    fuelHistoryController = controller

    isLoadingFuelHistory.value = true
    fuelHistoryError.value = null

    try {
      const response = await getFuelHistory(cityId, type, days, brandKey, {
        force,
        signal: controller.signal,
      })

      if (fuelHistoryController !== controller) {
        return
      }

      fuelHistory.value = response.data
      fuelHistoryMeta.value = response.meta ?? null
    } catch (error) {
      if (isAbortError(error)) {
        return
      }

      fuelHistoryError.value = toErrorMessage(error)
      fuelHistory.value = []
      fuelHistoryMeta.value = null
    } finally {
      if (fuelHistoryController === controller) {
        fuelHistoryController = null
        isLoadingFuelHistory.value = false
      }
    }
  }

  return {
    latestRows,
    isLoadingLatestRows,
    latestRowsError,
    brandSummary,
    brandSummaryMeta,
    isLoadingBrandSummary,
    brandSummaryError,
    stationRows,
    stationRowsMeta,
    isLoadingStationRows,
    stationRowsError,
    fuelHistory,
    fuelHistoryMeta,
    isLoadingFuelHistory,
    fuelHistoryError,
    loadLatestRows,
    loadBrandSummary,
    loadStationRows,
    loadFuelHistory,
  }
}
