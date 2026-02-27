<script setup lang="ts">
import { computed } from 'vue'

const props = withDefaults(
  defineProps<{
    modelValue: boolean
    title?: string
    subtitle?: string
    widthClass?: string
  }>(),
  {
    title: '',
    subtitle: '',
    widthClass: 'max-w-2xl',
  },
)

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
}>()

const isOpen = computed(() => props.modelValue)

function closeDrawer(): void {
  emit('update:modelValue', false)
}
</script>

<template>
  <Teleport to="body">
    <transition name="fade">
      <div v-if="isOpen" class="fixed inset-0 z-40 bg-slate-900/45 backdrop-blur-[2px]" @click="closeDrawer" />
    </transition>

    <transition name="slide-left">
      <aside
        v-if="isOpen"
        class="fixed inset-y-0 right-0 z-50 w-full border-l border-slate-200 bg-white shadow-2xl"
        :class="props.widthClass"
      >
        <div class="flex h-full flex-col">
          <header class="border-b border-slate-200 px-5 py-4">
            <div class="flex items-start justify-between gap-3">
              <div>
                <h3 v-if="props.title" class="text-base font-semibold text-slate-900">{{ props.title }}</h3>
                <p v-if="props.subtitle" class="mt-1 text-sm text-slate-500">
                  {{ props.subtitle }}
                </p>
              </div>
              <button
                type="button"
                class="rounded-xl border border-slate-200 p-2 text-slate-500 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-200"
                @click="closeDrawer"
              >
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path
                    fill-rule="evenodd"
                    d="M4.47 4.47a.75.75 0 011.06 0L10 8.94l4.47-4.47a.75.75 0 111.06 1.06L11.06 10l4.47 4.47a.75.75 0 11-1.06 1.06L10 11.06l-4.47 4.47a.75.75 0 11-1.06-1.06L8.94 10 4.47 5.53a.75.75 0 010-1.06z"
                    clip-rule="evenodd"
                  />
                </svg>
              </button>
            </div>
          </header>

          <div class="flex-1 overflow-y-auto px-5 py-4">
            <slot />
          </div>

          <footer v-if="$slots.footer" class="border-t border-slate-200 px-5 py-4">
            <slot name="footer" />
          </footer>
        </div>
      </aside>
    </transition>
  </Teleport>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-left-enter-active,
.slide-left-leave-active {
  transition: transform 0.22s ease;
}

.slide-left-enter-from,
.slide-left-leave-to {
  transform: translateX(100%);
}
</style>
