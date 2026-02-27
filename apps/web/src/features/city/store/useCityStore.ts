import { defineStore } from 'pinia'

const CITY_CHANGE_DEBOUNCE_MS = 300
let pendingCityTimer: ReturnType<typeof setTimeout> | null = null

type CityState = {
  selectedCityId: number | null
  activeCityId: number | null
}

export const useCityStore = defineStore('city', {
  state: (): CityState => ({
    selectedCityId: null,
    activeCityId: null,
  }),
  actions: {
    setSelectedCityId(cityId: number | null): void {
      this.selectedCityId = cityId

      if (pendingCityTimer) {
        clearTimeout(pendingCityTimer)
      }

      pendingCityTimer = setTimeout(() => {
        this.activeCityId = cityId
        pendingCityTimer = null
      }, CITY_CHANGE_DEBOUNCE_MS)
    },
  },
})
