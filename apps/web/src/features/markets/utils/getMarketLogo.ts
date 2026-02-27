import fallbackLogo from '@/shared/assets/markets/fallback.svg'
import interexLogo from '@/shared/assets/markets/interex.svg'
import maxiLogo from '@/shared/assets/markets/maxi.svg'
import meridianLogo from '@/shared/assets/markets/meridian.svg'
import vivaFreshLogo from '@/shared/assets/markets/vivafresh.svg'

export type MarketBrand = 'maxi' | 'vivafresh' | 'meridian' | 'interex' | 'local'

const logoByBrand: Record<MarketBrand, string> = {
  maxi: maxiLogo,
  vivafresh: vivaFreshLogo,
  meridian: meridianLogo,
  interex: interexLogo,
  local: fallbackLogo,
}

function normalizeValue(value: string | null | undefined): string {
  return (value ?? '').trim().toLowerCase()
}

export function detectMarketBrand(brandKeyOrName: string | null | undefined): MarketBrand {
  const normalized = normalizeValue(brandKeyOrName)

  if (
    normalized === 'maxi' ||
    normalized === 'vivafresh' ||
    normalized === 'meridian' ||
    normalized === 'interex' ||
    normalized === 'local'
  ) {
    return normalized
  }

  if (normalized.includes('maxi')) {
    return 'maxi'
  }

  if (normalized.includes('viva')) {
    return 'vivafresh'
  }

  if (normalized.includes('meridian')) {
    return 'meridian'
  }

  if (normalized.includes('interex')) {
    return 'interex'
  }

  return 'local'
}

export function getMarketLogo(brandKeyOrName: string | null | undefined): string {
  return logoByBrand[detectMarketBrand(brandKeyOrName)]
}
