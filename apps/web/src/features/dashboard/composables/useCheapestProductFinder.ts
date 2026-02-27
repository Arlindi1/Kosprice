import { computed, ref } from 'vue'

import { getProductCheapest, getProducts, getProductsCatalog, isAbortError, toErrorMessage } from '@/lib/api'
import type {
  Product,
  ProductCatalogItem,
  ProductCheapestResult,
} from '@/lib/types/api'

export function useCheapestProductFinder() {
  const allProducts = ref<Product[]>([])
  const catalog = ref<ProductCatalogItem[]>([])
  const selectedProductId = ref<number | null>(null)

  const isLoadingPicker = ref(false)
  const pickerError = ref<string | null>(null)

  const result = ref<ProductCheapestResult | null>(null)
  const isLoadingResult = ref(false)
  const resultError = ref<string | null>(null)

  let pickerController: AbortController | null = null
  let resultController: AbortController | null = null

  const selectedProduct = computed(() => {
    if (selectedProductId.value === null) {
      return null
    }

    return (
      allProducts.value.find((item) => item.id === selectedProductId.value) ??
      catalog.value.find((item) => item.id === selectedProductId.value) ??
      null
    )
  })

  const popularProducts = computed(() => {
    const coreItems = catalog.value
      .filter((item) => item.is_core_basket)
      .sort((left, right) => {
        const leftPrice = left.cheapest_price_today ?? Number.POSITIVE_INFINITY
        const rightPrice = right.cheapest_price_today ?? Number.POSITIVE_INFINITY

        if (leftPrice !== rightPrice) {
          return leftPrice - rightPrice
        }

        return left.name.localeCompare(right.name)
      })

    const sourceItems = coreItems.length > 0 ? coreItems : [...catalog.value]

    return sourceItems.slice(0, 8)
  })

  async function loadPickerData(cityId: number | null, force = false): Promise<void> {
    pickerController?.abort()

    if (cityId === null) {
      allProducts.value = []
      catalog.value = []
      pickerError.value = null
      isLoadingPicker.value = false
      pickerController = null
      return
    }

    const controller = new AbortController()
    pickerController = controller

    isLoadingPicker.value = true
    pickerError.value = null

    try {
      const [productsResponse, catalogResponse] = await Promise.all([
        getProducts(undefined, {
          force,
          signal: controller.signal,
        }),
        getProductsCatalog(cityId, {
          force,
          signal: controller.signal,
        }),
      ])

      if (pickerController !== controller) {
        return
      }

      allProducts.value = [...productsResponse.data].sort((left, right) =>
        left.name.localeCompare(right.name),
      )
      catalog.value = catalogResponse.data
    } catch (error) {
      if (isAbortError(error)) {
        return
      }

      pickerError.value = toErrorMessage(error)
      allProducts.value = []
      catalog.value = []
    } finally {
      if (pickerController === controller) {
        pickerController = null
        isLoadingPicker.value = false
      }
    }
  }

  async function selectProduct(
    productId: number,
    cityId: number | null,
    force = false,
  ): Promise<void> {
    selectedProductId.value = productId
    await loadCheapestResult(cityId, force)
  }

  async function loadCheapestResult(cityId: number | null, force = false): Promise<void> {
    resultController?.abort()

    const productId = selectedProductId.value

    if (cityId === null || productId === null) {
      result.value = null
      resultError.value = null
      isLoadingResult.value = false
      resultController = null
      return
    }

    const controller = new AbortController()
    resultController = controller

    isLoadingResult.value = true
    resultError.value = null

    try {
      const response = await getProductCheapest(productId, cityId, {
        force,
        signal: controller.signal,
      })

      if (resultController !== controller) {
        return
      }

      result.value = response.data
    } catch (error) {
      if (isAbortError(error)) {
        return
      }

      resultError.value = toErrorMessage(error)
      result.value = null
    } finally {
      if (resultController === controller) {
        resultController = null
        isLoadingResult.value = false
      }
    }
  }

  function clearSelection(): void {
    selectedProductId.value = null
    result.value = null
    resultError.value = null
  }

  return {
    allProducts,
    catalog,
    selectedProductId,
    selectedProduct,
    popularProducts,
    isLoadingPicker,
    pickerError,
    result,
    isLoadingResult,
    resultError,
    loadPickerData,
    selectProduct,
    loadCheapestResult,
    clearSelection,
  }
}
