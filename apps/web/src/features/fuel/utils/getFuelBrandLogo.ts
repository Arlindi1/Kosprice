import alPetrolLogo from '@/shared/assets/fuel/alpetrol.svg'
import fallbackLogo from '@/shared/assets/fuel/fallback.svg'
import hibLogo from '@/shared/assets/fuel/hib.svg'
import ipPetrolLogo from '@/shared/assets/fuel/ippetrol.svg'
import liriaOilLogo from '@/shared/assets/fuel/liriaoil.svg'
import shellLogo from '@/shared/assets/fuel/shell.svg'

type FuelBrandKey = 'shell' | 'hib' | 'alpetrol' | 'ippetrol' | 'liriaoil' | 'local'

const logoByBrand: Record<FuelBrandKey, string> = {
  shell: shellLogo,
  hib: hibLogo,
  alpetrol: alPetrolLogo,
  ippetrol: ipPetrolLogo,
  liriaoil: liriaOilLogo,
  local: fallbackLogo,
}

function normalize(value: string | null | undefined): string {
  return (value ?? '').trim().toLowerCase()
}

export function detectFuelBrand(brandKeyOrName: string | null | undefined): FuelBrandKey {
  const normalized = normalize(brandKeyOrName)

  if (normalized in logoByBrand) {
    return normalized as FuelBrandKey
  }

  if (normalized.includes('shell')) {
    return 'shell'
  }

  if (normalized.includes('hib')) {
    return 'hib'
  }

  if (normalized.includes('al')) {
    return 'alpetrol'
  }

  if (normalized.includes('ip')) {
    return 'ippetrol'
  }

  if (normalized.includes('liria')) {
    return 'liriaoil'
  }

  return 'local'
}

export function getFuelBrandLogo(brandKeyOrName: string | null | undefined): string {
  const brand = detectFuelBrand(brandKeyOrName)
  return logoByBrand[brand]
}

