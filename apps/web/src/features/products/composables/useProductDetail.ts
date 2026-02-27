import { ref } from 'vue'

import { getProductCheapest, getProducts, toErrorMessage } from '@/lib/api'
import { MemoryCache } from '@/lib/cache/memoryCache'
import type {
  ApiItemResponse,
  ApiListResponse,
  Product,
  ProductCheapestResult,
  ProductMeta,
} from '@/lib/types/api'

const productsCache = new MemoryCache<ApiListResponse<Product, ProductMeta>>(5 * 60_000)
const cheapestCache = new MemoryCache<ApiItemResponse<ProductCheapestResult>>(90_000)

export function useProductDetail() {
  const product = ref<Product | null>(null)
  const productError = ref<string | null>(null)
  const isLoadingProduct = ref(false)

  const cheapestResult = ref<ProductCheapestResult | null>(null)
  const cheapestError = ref<string | null>(null)
  const isLoadingCheapest = ref(false)

  async function loadProduct(productId: number | null, force = false): Promise<void> {
    if (productId === null) {
      product.value = null
      productError.value = null
      isLoadingProduct.value = false
      return
    }

    if (force) {
      productsCache.delete('products:index')
    }

    isLoadingProduct.value = true
    productError.value = null

    try {
      const response = await productsCache.getOrSet('products:index', () => getProducts())
      product.value = response.data.find((item) => item.id === productId) ?? null

      if (product.value === null) {
        productError.value = `Product #${productId} was not found in catalog index.`
      }
    } catch (error) {
      productError.value = toErrorMessage(error)
      product.value = null
    } finally {
      isLoadingProduct.value = false
    }
  }

  async function loadCheapest(productId: number | null, cityId: number | null, force = false): Promise<void> {
    if (productId === null || cityId === null) {
      cheapestResult.value = null
      cheapestError.value = null
      isLoadingCheapest.value = false
      return
    }

    const cacheKey = `products:cheapest:${productId}:${cityId}`
    if (force) {
      cheapestCache.delete(cacheKey)
    }

    isLoadingCheapest.value = true
    cheapestError.value = null

    try {
      const response = await cheapestCache.getOrSet(cacheKey, () => getProductCheapest(productId, cityId))
      cheapestResult.value = response.data
    } catch (error) {
      cheapestError.value = toErrorMessage(error)
      cheapestResult.value = null
    } finally {
      isLoadingCheapest.value = false
    }
  }

  return {
    product,
    productError,
    isLoadingProduct,
    cheapestResult,
    cheapestError,
    isLoadingCheapest,
    loadProduct,
    loadCheapest,
  }
}
