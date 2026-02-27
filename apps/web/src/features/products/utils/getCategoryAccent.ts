export type CategoryAccent = 'amber' | 'sky' | 'lime' | 'violet'

function normalizeCategory(value: string | null | undefined): string {
  return (value ?? '').trim().toLowerCase()
}

function fallbackAccent(category: string): CategoryAccent {
  const accents: CategoryAccent[] = ['amber', 'sky', 'lime', 'violet']
  const seed = category.charCodeAt(0) || 0
  return accents[seed % accents.length] ?? 'violet'
}

export function getCategoryAccent(category: string | null | undefined): CategoryAccent {
  const normalized = normalizeCategory(category)

  if (
    normalized.includes('bakery') ||
    normalized.includes('grain') ||
    normalized.includes('pantry') ||
    normalized.includes('oil')
  ) {
    return 'amber'
  }

  if (
    normalized.includes('dairy') ||
    normalized.includes('beverage') ||
    normalized.includes('drink')
  ) {
    return 'sky'
  }

  if (
    normalized.includes('produce') ||
    normalized.includes('fruit') ||
    normalized.includes('vegetable')
  ) {
    return 'lime'
  }

  if (
    normalized.includes('meat') ||
    normalized.includes('household') ||
    normalized.includes('clean')
  ) {
    return 'violet'
  }

  if (normalized.length === 0) {
    return 'violet'
  }

  return fallbackAccent(normalized)
}

export function getCategoryChipClass(category: string | null | undefined): string {
  const accent = getCategoryAccent(category)

  if (accent === 'amber') {
    return 'border-amber-300 bg-amber-100/70 text-amber-800'
  }

  if (accent === 'sky') {
    return 'border-sky-300 bg-sky-100/70 text-sky-800'
  }

  if (accent === 'lime') {
    return 'border-lime-300 bg-lime-100/70 text-lime-800'
  }

  return 'border-violet-300 bg-violet-100/70 text-violet-800'
}

export function getCategoryTopBorderClass(category: string | null | undefined): string {
  const accent = getCategoryAccent(category)

  if (accent === 'amber') {
    return 'border-t-4 border-t-amber-400'
  }

  if (accent === 'sky') {
    return 'border-t-4 border-t-sky-400'
  }

  if (accent === 'lime') {
    return 'border-t-4 border-t-lime-400'
  }

  return 'border-t-4 border-t-violet-400'
}
