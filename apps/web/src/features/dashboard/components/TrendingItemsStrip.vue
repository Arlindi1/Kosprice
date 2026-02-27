<script setup lang="ts">
import { getProductIcon } from '@/features/product/utils/getProductIcon'
import type { DashboardTrendingItem } from '@/features/dashboard/composables/useDashboardToday'
import Badge from '@/shared/ui/Badge.vue'
import Button from '@/shared/ui/Button.vue'
import EmptyState from '@/shared/ui/EmptyState.vue'
import Skeleton from '@/shared/ui/Skeleton.vue'

const props = withDefaults(
  defineProps<{
    items: DashboardTrendingItem[]
    isLoading: boolean
    error: string | null
  }>(),
  {
    error: null,
  },
)

const emit = defineEmits<{
  retry: []
}>()

function formatMoney(value: number | null): string {
  if (value === null) {
    return 'No price'
  }

  return value.toLocaleString('en-US', {
    style: 'currency',
    currency: 'EUR',
    minimumFractionDigits: 2,
  })
}
</script>

<template>
  <section class="space-y-4">
    <div class="flex items-center justify-between gap-3">
      <div>
        <h3 class="text-heading">Trending items</h3>
        <p class="text-body">Products with the most visible movement or strong local popularity.</p>
      </div>
      <Button variant="ghost" size="sm" @click="emit('retry')">Refresh trends</Button>
    </div>

    <div v-if="props.isLoading" class="flex gap-3 overflow-hidden">
      <Skeleton v-for="index in 6" :key="index" width="210px" height="6.25rem" rounded="0.9rem" />
    </div>

    <div v-else-if="props.error" class="status-wrap">
      <EmptyState
        title="Trending items unavailable"
        :message="props.error"
        cta-label="Retry"
        @retry="emit('retry')"
      />
    </div>

    <div v-else-if="props.items.length === 0" class="status-wrap">
      <EmptyState
        title="No trending items"
        message="No product signals were generated for this city yet."
      />
    </div>

    <div v-else class="overflow-x-auto pb-1">
      <ol class="flex min-w-max gap-3">
        <li
          v-for="(item, index) in props.items"
          :key="item.product_id"
          class="w-[230px] rounded-2xl border bg-white p-3.5 shadow-sm"
          :class="[
            index % 4 === 0 && 'border-t-4 border-t-amber-400 border-slate-200',
            index % 4 === 1 && 'border-t-4 border-t-sky-400 border-slate-200',
            index % 4 === 2 && 'border-t-4 border-t-lime-400 border-slate-200',
            index % 4 === 3 && 'border-t-4 border-t-violet-400 border-slate-200',
          ]"
        >
          <div class="flex items-start justify-between gap-3">
            <div class="flex min-w-0 items-center gap-2.5">
              <img
                :src="getProductIcon(item.image_key, item.name)"
                :alt="item.name"
                class="h-10 w-10 rounded-lg border border-slate-200 bg-slate-50 p-1 object-contain"
              />
              <div class="min-w-0">
                <p class="clamp-2 text-sm font-semibold text-slate-900">{{ item.name }}</p>
                <p class="text-xs font-extrabold text-emerald-600">{{ formatMoney(item.current_price_eur) }}</p>
              </div>
            </div>
            <Badge :variant="item.badge_variant">{{ item.badge_label }}</Badge>
          </div>
        </li>
      </ol>
    </div>
  </section>
</template>
