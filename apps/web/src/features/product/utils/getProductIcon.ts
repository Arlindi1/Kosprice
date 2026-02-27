import applesIcon from '@/shared/assets/products/apples.svg'
import bananasIcon from '@/shared/assets/products/bananas.svg'
import beansIcon from '@/shared/assets/products/beans.svg'
import breadIcon from '@/shared/assets/products/bread.svg'
import butterIcon from '@/shared/assets/products/butter.svg'
import cheeseIcon from '@/shared/assets/products/cheese.svg'
import chickenIcon from '@/shared/assets/products/chicken.svg'
import coffeeIcon from '@/shared/assets/products/coffee.svg'
import detergentIcon from '@/shared/assets/products/detergent.svg'
import eggsIcon from '@/shared/assets/products/eggs.svg'
import fallbackIcon from '@/shared/assets/products/fallback.svg'
import flourIcon from '@/shared/assets/products/flour.svg'
import juiceIcon from '@/shared/assets/products/juice.svg'
import milkIcon from '@/shared/assets/products/milk.svg'
import oilIcon from '@/shared/assets/products/oil.svg'
import onionsIcon from '@/shared/assets/products/onions.svg'
import pastaIcon from '@/shared/assets/products/pasta.svg'
import potatoesIcon from '@/shared/assets/products/potatoes.svg'
import riceIcon from '@/shared/assets/products/rice.svg'
import saltIcon from '@/shared/assets/products/salt.svg'
import sugarIcon from '@/shared/assets/products/sugar.svg'
import teaIcon from '@/shared/assets/products/tea.svg'
import tomatoesIcon from '@/shared/assets/products/tomatoes.svg'
import waterIcon from '@/shared/assets/products/water.svg'
import yogurtIcon from '@/shared/assets/products/yogurt.svg'

const iconByKey: Record<string, string> = {
  apples: applesIcon,
  bananas: bananasIcon,
  beans: beansIcon,
  bread: breadIcon,
  butter: butterIcon,
  cheese: cheeseIcon,
  chicken: chickenIcon,
  milk: milkIcon,
  eggs: eggsIcon,
  oil: oilIcon,
  sugar: sugarIcon,
  flour: flourIcon,
  rice: riceIcon,
  potatoes: potatoesIcon,
  coffee: coffeeIcon,
  yogurt: yogurtIcon,
  pasta: pastaIcon,
  onions: onionsIcon,
  tomatoes: tomatoesIcon,
  salt: saltIcon,
  water: waterIcon,
  'water-rugova': waterIcon,
  'water-mokna': waterIcon,
  cola: juiceIcon,
  pepsi: juiceIcon,
  detergent: detergentIcon,
  tea: teaIcon,
  juice: juiceIcon,
}

const fallbackByCategory: Record<string, string> = {
  bakery: breadIcon,
  dairy: milkIcon,
  grains: riceIcon,
  meat: chickenIcon,
  oil: oilIcon,
  produce: applesIcon,
  pantry: beansIcon,
  beverages: coffeeIcon,
  household: detergentIcon,
}

const keyHints: Array<{ key: string; terms: string[] }> = [
  { key: 'bread', terms: ['bread'] },
  { key: 'bread', terms: ['buke'] },
  { key: 'milk', terms: ['milk'] },
  { key: 'milk', terms: ['qumesht'] },
  { key: 'eggs', terms: ['egg'] },
  { key: 'eggs', terms: ['veze'] },
  { key: 'oil', terms: ['oil'] },
  { key: 'oil', terms: ['vaj'] },
  { key: 'sugar', terms: ['sugar'] },
  { key: 'sugar', terms: ['sheqer'] },
  { key: 'flour', terms: ['flour'] },
  { key: 'flour', terms: ['miell'] },
  { key: 'rice', terms: ['rice'] },
  { key: 'rice', terms: ['oriz'] },
  { key: 'potatoes', terms: ['potato'] },
  { key: 'potatoes', terms: ['patate'] },
  { key: 'coffee', terms: ['coffee'] },
  { key: 'coffee', terms: ['kafe'] },
  { key: 'chicken', terms: ['chicken'] },
  { key: 'chicken', terms: ['pule'] },
  { key: 'yogurt', terms: ['yogurt'] },
  { key: 'yogurt', terms: ['kos'] },
  { key: 'cheese', terms: ['cheese'] },
  { key: 'cheese', terms: ['djathe'] },
  { key: 'pasta', terms: ['pasta'] },
  { key: 'pasta', terms: ['makarona'] },
  { key: 'beans', terms: ['bean'] },
  { key: 'beans', terms: ['fasule'] },
  { key: 'onions', terms: ['onion'] },
  { key: 'onions', terms: ['qepe'] },
  { key: 'tomatoes', terms: ['tomato'] },
  { key: 'tomatoes', terms: ['domate'] },
  { key: 'apples', terms: ['apple'] },
  { key: 'apples', terms: ['molle'] },
  { key: 'bananas', terms: ['banana'] },
  { key: 'bananas', terms: ['banane'] },
  { key: 'salt', terms: ['salt'] },
  { key: 'salt', terms: ['kripe'] },
  { key: 'butter', terms: ['butter'] },
  { key: 'butter', terms: ['gjalpe'] },
  { key: 'water-rugova', terms: ['rugova'] },
  { key: 'water-mokna', terms: ['mokna'] },
  { key: 'water', terms: ['water', 'uje'] },
  { key: 'cola', terms: ['coca-cola', 'cola', 'fanta'] },
  { key: 'pepsi', terms: ['pepsi'] },
  { key: 'detergent', terms: ['detergent'] },
  { key: 'detergent', terms: ['detergjent'] },
  { key: 'tea', terms: ['tea'] },
  { key: 'tea', terms: ['caj'] },
  { key: 'juice', terms: ['juice'] },
  { key: 'juice', terms: ['leng'] },
]

function normalizeValue(value: string | null | undefined): string {
  return (value ?? '').trim().toLowerCase()
}

export function inferProductKeyFromName(name: string | null | undefined): string | null {
  const normalized = normalizeValue(name)

  if (normalized.length === 0) {
    return null
  }

  for (const hint of keyHints) {
    if (hint.terms.some((term) => normalized.includes(term))) {
      return hint.key
    }
  }

  return null
}

export function getProductIcon(
  imageKeyOrNameOrCategory?: string | null,
  productNameOrCategory?: string | null,
  categoryFallback?: string | null,
): string {
  const candidates = [
    normalizeValue(imageKeyOrNameOrCategory),
    normalizeValue(productNameOrCategory),
    normalizeValue(categoryFallback),
  ].filter((value) => value.length > 0)

  for (const candidate of candidates) {
    if (iconByKey[candidate]) {
      return iconByKey[candidate]
    }
  }

  for (const candidate of candidates) {
    const inferredKey = inferProductKeyFromName(candidate)
    if (inferredKey && iconByKey[inferredKey]) {
      return iconByKey[inferredKey]
    }
  }

  for (const candidate of candidates) {
    if (fallbackByCategory[candidate]) {
      return fallbackByCategory[candidate]
    }
  }

  return fallbackIcon
}
