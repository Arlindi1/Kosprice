<script setup lang="ts">
import { computed } from 'vue'

import type { ProductCatalogItem } from '@/lib/types/api'
import Button from '@/shared/ui/Button.vue'
import EmptyState from '@/shared/ui/EmptyState.vue'
import ProductCardLarge from '@/shared/ui/ProductCardLarge.vue'

const props = withDefaults(
  defineProps<{
    sectionId: string
    title: string
    subtitle: string
    products: ProductCatalogItem[]
    expanded?: boolean
    emptyMessage?: string
  }>(),
  {
    expanded: false,
    emptyMessage: 'No products available for this section.',
  },
)

const emit = defineEmits<{
  compare: [productId: number]
  seeAll: []
}>()

const visibleProducts = computed(() =>
  props.expanded ? props.products : props.products.slice(0, 10),
)

const seeAllLabel = computed(() => (props.expanded ? 'Showing all' : 'See all'))
const canExpand = computed(() => props.products.length > 10 && !props.expanded)
</script>

<template>
  <section :id="props.sectionId" class="scroll-mt-36 space-y-3">
    <header class="flex items-start justify-between gap-3">
      <div>
        <h3 class="text-heading">{{ props.title }}</h3>
        <p class="text-sm text-slate-600">{{ props.subtitle }}</p>
      </div>

      <Button
        variant="ghost"
        size="sm"
        :disabled="!canExpand"
        @click="emit('seeAll')"
      >
        {{ seeAllLabel }}
      </Button>
    </header>

    <div v-if="props.products.length === 0" class="status-wrap">
      <EmptyState
        title="No products in this section"
        :message="props.emptyMessage"
      />
    </div>

    <div v-else class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-5">
      <ProductCardLarge
        v-for="product in visibleProducts"
        :key="product.id"
        :product="product"
        layout="vertical"
        @compare="emit('compare', $event)"
      />
    </div>
  </section>
</template>
