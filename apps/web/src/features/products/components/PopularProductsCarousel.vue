<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'

import { useAutoplayCarousel } from '@/features/products/composables/useAutoplayCarousel'
import FeaturedProductSlide from '@/features/products/components/FeaturedProductSlide.vue'
import type { ProductCatalogItem } from '@/lib/types/api'
import Button from '@/shared/ui/Button.vue'
import EmptyState from '@/shared/ui/EmptyState.vue'
import Skeleton from '@/shared/ui/Skeleton.vue'

const props = withDefaults(
  defineProps<{
    products: ProductCatalogItem[]
    selectedProductId: number | null
    isLoading: boolean
    error: string | null
    cityId: number | null
  }>(),
  {
    selectedProductId: null,
    error: null,
    cityId: null,
  },
)

const emit = defineEmits<{
  select: [productId: number]
  openDetails: [productId: number]
  retry: []
  browseAll: []
}>()

const viewportWidth = ref(0)
const currentIndex = ref(0)

const responsiveItemsPerView = computed(() => {
  if (viewportWidth.value >= 768) {
    return 2
  }

  return 1
})

const itemsPerView = computed(() => {
  if (props.products.length === 0) {
    return responsiveItemsPerView.value
  }

  return Math.min(responsiveItemsPerView.value, props.products.length)
})

const maxStartIndex = computed(() => Math.max(0, props.products.length - itemsPerView.value))
const pageCount = computed(() => Math.max(1, maxStartIndex.value + 1))
const canNavigate = computed(() => pageCount.value > 1)
const slideWidth = computed(() => `${100 / itemsPerView.value}%`)

const trackStyle = computed(() => ({
  transform: `translate3d(-${(currentIndex.value * 100) / itemsPerView.value}%, 0, 0)`,
  transition: canNavigate.value
    ? 'transform 560ms cubic-bezier(0.22, 1, 0.36, 1)'
    : 'transform 200ms ease-out',
}))

const autoplayEnabled = computed(
  () =>
    props.cityId !== null &&
    !props.isLoading &&
    !props.error &&
    props.products.length > itemsPerView.value,
)

const skeletonCount = computed(() => responsiveItemsPerView.value * 2)

const { setHovered, markUserInteraction } = useAutoplayCarousel({
  enabled: autoplayEnabled,
  onTick: () => {
    goToNext(false)
  },
  delayMs: 3500,
})

function syncViewportWidth(): void {
  if (typeof window === 'undefined') {
    return
  }

  viewportWidth.value = window.innerWidth
}

function clampIndex(nextIndex: number): number {
  if (maxStartIndex.value === 0) {
    return 0
  }

  if (nextIndex < 0) {
    return maxStartIndex.value
  }

  if (nextIndex > maxStartIndex.value) {
    return 0
  }

  return nextIndex
}

function goTo(nextIndex: number, fromUser = false): void {
  currentIndex.value = clampIndex(nextIndex)

  if (fromUser) {
    markUserInteraction()
  }
}

function goToPrevious(fromUser = true): void {
  if (!canNavigate.value) {
    return
  }

  goTo(currentIndex.value - 1, fromUser)
}

function goToNext(fromUser = true): void {
  if (!canNavigate.value) {
    return
  }

  goTo(currentIndex.value + 1, fromUser)
}

function goToDot(index: number): void {
  goTo(index, true)
}

function onCarouselKeydown(event: KeyboardEvent): void {
  if (event.key === 'ArrowLeft') {
    event.preventDefault()
    goToPrevious(true)
    return
  }

  if (event.key === 'ArrowRight') {
    event.preventDefault()
    goToNext(true)
  }
}

function selectProduct(productId: number): void {
  markUserInteraction()
  emit('select', productId)
}

function compareProduct(productId: number): void {
  markUserInteraction()
  emit('select', productId)
  emit('openDetails', productId)
}

watch(
  [maxStartIndex, () => props.products.length],
  () => {
    currentIndex.value = Math.min(currentIndex.value, maxStartIndex.value)
  },
  { immediate: true },
)

watch(
  () => props.cityId,
  () => {
    currentIndex.value = 0
  },
)

onMounted(() => {
  syncViewportWidth()

  if (typeof window === 'undefined') {
    return
  }

  window.addEventListener('resize', syncViewportWidth, { passive: true })
})

onBeforeUnmount(() => {
  if (typeof window === 'undefined') {
    return
  }

  window.removeEventListener('resize', syncViewportWidth)
})
</script>

<template>
  <section class="space-y-3">
    <header class="rounded-2xl border border-slate-900 bg-slate-900 p-4 md:p-5">
      <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="space-y-1">
          <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-300">City spotlight</p>
          <h3 class="text-heading text-2xl !font-semibold !tracking-[-0.02em] !text-white md:text-3xl">Popular products</h3>
          <p class="text-[13px] leading-5 text-slate-300">
            Top picks from your selected city.
          </p>
        </div>

        <Button variant="secondary" size="sm" class="!border-white/30 !bg-white !text-slate-900" @click="emit('browseAll')">
          Search all products
        </Button>
      </div>
    </header>

    <div v-if="props.isLoading" class="grid gap-4 md:grid-cols-2">
      <Skeleton v-for="index in skeletonCount" :key="index" height="18rem" rounded="1rem" />
    </div>

    <div v-else-if="props.error" class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
      <EmptyState
        title="Popular products unavailable"
        :message="props.error"
        cta-label="Retry"
        @retry="emit('retry')"
      />
    </div>

    <div v-else-if="props.cityId === null" class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
      <EmptyState
        title="Select a city first"
        message="Choose your city to load popular products and cheapest-price spotlights."
      />
    </div>

    <div v-else-if="props.products.length === 0" class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
      <EmptyState
        title="No popular products"
        message="We could not find popular products for this city right now. Try refreshing the dashboard."
        cta-label="Refresh"
        @retry="emit('retry')"
      />
    </div>

    <div v-else class="space-y-3">
      <div
        class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-2.5 md:p-3 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-300"
        tabindex="0"
        role="region"
        aria-label="Popular products carousel"
        @mouseenter="setHovered(true)"
        @mouseleave="setHovered(false)"
        @pointerdown="markUserInteraction"
        @touchstart.passive="markUserInteraction"
        @keydown="onCarouselKeydown"
      >
        <div class="relative overflow-hidden rounded-xl">
          <ol class="flex will-change-transform" :style="trackStyle">
            <li
              v-for="product in props.products"
              :key="product.id"
              class="shrink-0 px-2 md:px-2.5"
              :style="{ flexBasis: slideWidth, maxWidth: slideWidth }"
            >
              <FeaturedProductSlide
                :product="product"
                :is-selected="props.selectedProductId === product.id"
                @select="selectProduct"
                @compare="compareProduct"
              />
            </li>
          </ol>
        </div>

        <div class="pointer-events-none absolute inset-y-0 left-0 right-0 hidden items-center justify-between px-2 md:flex">
          <button
            type="button"
            class="pointer-events-auto inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-900 bg-white text-slate-900 shadow-sm transition hover:bg-slate-900 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-300 disabled:cursor-not-allowed disabled:opacity-40"
            aria-label="Show previous popular products"
            :disabled="!canNavigate"
            @click.stop="goToPrevious(true)"
          >
            <svg viewBox="0 0 20 20" class="h-5 w-5" fill="none" aria-hidden="true">
              <path d="M12.5 15 7.5 10l5-5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </button>
          <button
            type="button"
            class="pointer-events-auto inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-900 bg-white text-slate-900 shadow-sm transition hover:bg-slate-900 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-300 disabled:cursor-not-allowed disabled:opacity-40"
            aria-label="Show next popular products"
            :disabled="!canNavigate"
            @click.stop="goToNext(true)"
          >
            <svg viewBox="0 0 20 20" class="h-5 w-5" fill="none" aria-hidden="true">
              <path d="m7.5 5 5 5-5 5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </button>
        </div>
      </div>

      <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
        <div class="flex items-center gap-2 md:hidden">
          <button
            type="button"
            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-900 bg-white text-slate-900 shadow-sm transition hover:bg-slate-900 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-300 disabled:cursor-not-allowed disabled:opacity-50"
            aria-label="Show previous popular products"
            :disabled="!canNavigate"
            @click="goToPrevious(true)"
          >
            <svg viewBox="0 0 20 20" class="h-4 w-4" fill="none" aria-hidden="true">
              <path d="M12.5 15 7.5 10l5-5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </button>
          <button
            type="button"
            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-900 bg-white text-slate-900 shadow-sm transition hover:bg-slate-900 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-300 disabled:cursor-not-allowed disabled:opacity-50"
            aria-label="Show next popular products"
            :disabled="!canNavigate"
            @click="goToNext(true)"
          >
            <svg viewBox="0 0 20 20" class="h-4 w-4" fill="none" aria-hidden="true">
              <path d="m7.5 5 5 5-5 5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </button>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 md:hidden">
          {{ currentIndex + 1 }} / {{ pageCount }}
        </div>

        <div class="flex flex-1 items-center justify-center gap-2">
          <button
            v-for="dotIndex in pageCount"
            :key="dotIndex"
            type="button"
            class="h-1.5 rounded-full transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-300 focus-visible:ring-offset-2"
            :class="currentIndex === dotIndex - 1 ? 'w-7 bg-slate-900' : 'w-2 bg-slate-300 hover:bg-slate-400'"
            :aria-label="`Go to popular products slide ${dotIndex}`"
            :aria-current="currentIndex === dotIndex - 1 ? 'true' : undefined"
            @click="goToDot(dotIndex - 1)"
          />
        </div>

        <div v-if="autoplayEnabled" class="hidden rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-medium text-slate-600 md:block">
          Auto-play on
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
/* Smooth transitions for carousel */
.will-change-transform {
  will-change: transform;
}

/* Enhanced focus states */
button:focus-visible {
  outline-offset: 2px;
}
</style>
