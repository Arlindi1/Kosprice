import type { Product, ProductCatalogItem } from '@/lib/types/api'
import breadImage from '@/shared/assets/products/images/bread.png'
import eggsImage from '@/shared/assets/products/images/eggs.png'
import flourImage from '@/shared/assets/products/images/flour.png'
import milkImage from '@/shared/assets/products/images/milk.png'
import onionsImage from '@/shared/assets/products/images/onions.png'
import oilImage from '@/shared/assets/products/images/oil.png'
import pastaImage from '@/shared/assets/products/images/pasta.png'
import placeholderImage from '@/shared/assets/products/images/placeholder.png'
import pepsiImage from '@/shared/assets/products/images/pepsi.png'
import potatoesImage from '@/shared/assets/products/images/potatoes.png'
import riceImage from '@/shared/assets/products/images/rice.png'
import saltImage from '@/shared/assets/products/images/salt.png'
import sugarImage from '@/shared/assets/products/images/sugar.png'
import colaImage from '@/shared/assets/products/images/cola.png'
import waterMoknaImage from '@/shared/assets/products/images/water-mokna.png'
import waterRugovaImage from '@/shared/assets/products/images/water-rugova.png'
import waterImage from '@/shared/assets/products/images/water.png'

import { inferProductKeyFromName } from '@/features/product/utils/getProductIcon'

type ProductImageKey =
  | 'bread'
  | 'milk'
  | 'eggs'
  | 'oil'
  | 'sugar'
  | 'flour'
  | 'rice'
  | 'potatoes'
  | 'salt'
  | 'water'
  | 'pasta'
  | 'water-rugova'
  | 'water-mokna'
  | 'cola'
  | 'pepsi'
  | 'onions'

type ProductImageLike = Pick<ProductCatalogItem, 'name' | 'category' | 'image_key'> | Pick<Product, 'name' | 'category' | 'image_key'>

const imageByKey: Record<ProductImageKey, string> = {
  bread: breadImage,
  milk: milkImage,
  eggs: eggsImage,
  oil: oilImage,
  sugar: sugarImage,
  flour: flourImage,
  rice: riceImage,
  potatoes: potatoesImage,
  salt: saltImage,
  water: waterImage,
  pasta: pastaImage,
  'water-rugova': waterRugovaImage,
  'water-mokna': waterMoknaImage,
  cola: colaImage,
  pepsi: pepsiImage,
  onions: onionsImage,
}

function normalizeValue(value: string | null | undefined): string {
  return (value ?? '').trim().toLowerCase()
}

function categoryFallbackKey(category: string): ProductImageKey | null {
  if (category.includes('bakery')) {
    return 'bread'
  }

  if (category.includes('dairy')) {
    return 'milk'
  }

  if (category.includes('beverage') || category.includes('drink')) {
    return 'water'
  }

  if (category.includes('grain') || category.includes('pantry')) {
    return 'rice'
  }

  if (category.includes('produce') || category.includes('vegetable')) {
    return 'potatoes'
  }

  return null
}

function resolveImageKey(product: ProductImageLike): ProductImageKey | null {
  const imageKey = normalizeValue(product.image_key)
  if (imageKey in imageByKey) {
    return imageKey as ProductImageKey
  }

  const inferredFromName = inferProductKeyFromName(product.name)
  if (inferredFromName && inferredFromName in imageByKey) {
    return inferredFromName as ProductImageKey
  }

  const categoryFallback = categoryFallbackKey(normalizeValue(product.category))
  if (categoryFallback) {
    return categoryFallback
  }

  return null
}

export function getProductImage(product: ProductImageLike): string {
  const key = resolveImageKey(product)
  if (key === null) {
    return placeholderImage
  }

  return imageByKey[key]
}
