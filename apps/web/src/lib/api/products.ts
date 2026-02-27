import type {
  ApiItemResponse,
  ApiListResponse,
  Product,
  ProductCatalogItem,
  ProductCatalogMeta,
  ProductCheapestResult,
  ProductMeta,
} from '@/lib/types/api'

import { apiGet, createApiKey, type ApiGetOptions } from '@/lib/api/client'

type RequestOptions = Omit<ApiGetOptions, 'params'>

export async function getProducts(
  category?: string,
  options: RequestOptions = {},
): Promise<ApiListResponse<Product, ProductMeta>> {
  const params = {
    category,
  }

  return apiGet<ApiListResponse<Product, ProductMeta>>(createApiKey('/products', params), '/products', {
    params,
    ttlMs: 60_000,
    ...options,
  })
}

export async function getProductsCatalog(
  cityId: number,
  options: RequestOptions = {},
): Promise<ApiListResponse<ProductCatalogItem, ProductCatalogMeta>> {
  const params = {
    city_id: cityId,
  }

  return apiGet<ApiListResponse<ProductCatalogItem, ProductCatalogMeta>>(
    createApiKey('/products/catalog', params),
    '/products/catalog',
    {
      params,
      ttlMs: 60_000,
      ...options,
    },
  )
}

export async function getProductCheapest(
  productId: number,
  cityId: number,
  options: RequestOptions = {},
): Promise<ApiItemResponse<ProductCheapestResult>> {
  const params = {
    city_id: cityId,
  }

  return apiGet<ApiItemResponse<ProductCheapestResult>>(
    createApiKey(`/products/${productId}/cheapest`, params),
    `/products/${productId}/cheapest`,
    {
      params,
      ttlMs: 60_000,
      ...options,
    },
  )
}

export async function getProductPricesByCity(
  productId: number,
  cityId: number,
  options: RequestOptions = {},
): Promise<unknown> {
  const params = {
    city_id: cityId,
  }

  return apiGet<unknown>(createApiKey(`/products/${productId}/prices`, params), `/products/${productId}/prices`, {
    params,
    ttlMs: 60_000,
    ...options,
  })
}
