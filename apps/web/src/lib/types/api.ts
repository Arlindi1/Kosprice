export interface ApiListResponse<TData, TMeta = Record<string, unknown>> {
  data: TData[]
  meta?: TMeta
}

export interface ApiItemResponse<TData, TMeta = Record<string, unknown>> {
  data: TData
  meta?: TMeta
}

export interface City {
  id: number
  name: string
  slug: string
}

export interface Market {
  id: number
  name: string
  address: string | null
  city: City
}

export interface BasketItem {
  product_id: number
  name: string
  unit: string
  price_eur: number
}

export interface BasketItemWithCatalog extends BasketItem {
  category: string
  image_key: string | null
  unit_label: string | null
  is_core_basket: boolean
}

export interface MarketBasket {
  market: Market
  recorded_at: string | null
  total_price_eur: number
  items: BasketItem[]
}

export type FuelType = 'diesel' | 'petrol95' | 'petrol98' | 'lpg'

export interface FuelPrice {
  id: number
  brand_key: string
  brand_name: string
  station_name: string
  address: string | null
  city_id: number
  fuel_type: FuelType
  price_eur_per_l: number
  price_eur_liter: number
  recorded_at: string | null
  station: {
    id: number
    name: string
    address: string | null
    brand_key: string
    brand_name: string
  }
  city: City
}

export interface FuelBrandSummary {
  brand_key: string
  brand_name: string
  best_price: number | null
  best_station_name: string | null
  station_count: number
  updated_at: string | null
}

export interface FuelBrandSummaryMeta {
  city_id: number | null
  type: FuelType | null
  count: number
}

export interface FuelStationRankRow {
  station_id: number
  brand_key: string
  brand_name: string
  station_name: string
  address: string | null
  fuel_type: FuelType
  city_id: number
  city_name: string
  city_slug: string
  price_eur_per_l: number
  recorded_at: string | null
}

export interface FuelStationRankMeta {
  city_id: number | null
  type: FuelType | null
  count: number
  updated_at: string | null
}

export interface FuelHistoryPoint {
  recorded_at: string
  fuel_type: FuelType
  avg_price_eur_liter: number
}

export interface FuelHistoryMeta {
  city_id: number | null
  type: FuelType | null
  brand_key?: string | null
  days: number
  start_date: string | null
  end_date: string | null
  count: number
}

export interface BasketSummary {
  market: {
    id: number
    name: string
  }
  city: City
  recorded_at: string | null
  total_price_eur: number
}

export interface BasketTrendPoint {
  recorded_at: string
  average_total_eur: number
  min_total_eur: number
  max_total_eur: number
}

export interface BasketTrendMeta {
  city_id: number
  days: number
  start_date: string | null
  end_date: string | null
  count: number
}

export interface Product {
  id: number
  name: string
  brand: string | null
  variant: string | null
  category: string
  image_key: string | null
  unit: string
  unit_label: string | null
  brand_hint: string | null
  is_core_basket: boolean
}

export interface ProductMeta {
  count: number
  category: string | null
}

export interface ProductCatalogItem {
  id: number
  name: string
  brand: string | null
  variant: string | null
  category: string
  image_key: string | null
  unit_label: string | null
  is_core_basket: boolean
  cheapest_price_today: number | null
  cheapest_market_name: string | null
}

export interface ProductCatalogMeta {
  count: number
  city_id: number
  recorded_at: string | null
}

export interface ProductMarketPriceRow {
  market_id: number
  market_name: string
  market_address: string | null
  recorded_at: string | null
  price_eur: number | null
}

export interface ProductPriceByCityRow {
  market: {
    id: number
    name: string
    address: string | null
  }
  price_eur: number
  delta_from_cheapest_eur: number
  recorded_at: string | null
}

export interface ProductPricesByCityMeta {
  count: number
  updated_at: string | null
}

export interface ProductTrendPoint {
  recorded_at: string
  price_eur: number | null
}

export interface ProductCheapestMarketEntry {
  market: {
    id: number
    name: string
    address: string | null
  }
  price_eur: number
  delta_from_cheapest_eur: number
}

export interface ProductCheapestResult {
  product: {
    id: number
    name: string
    brand: string | null
    variant: string | null
    category: string
    image_key: string | null
    unit_label: string | null
    is_core_basket: boolean
  }
  city: City
  recorded_at: string | null
  cheapest: ProductCheapestMarketEntry | null
  alternatives: ProductCheapestMarketEntry[]
}
