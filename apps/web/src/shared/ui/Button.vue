<script setup lang="ts">
import { computed } from 'vue'

type ButtonVariant = 'primary' | 'secondary' | 'ghost' | 'danger'
type ButtonSize = 'sm' | 'md' | 'lg'
type ButtonType = 'button' | 'submit' | 'reset'

const props = withDefaults(
  defineProps<{
    variant?: ButtonVariant
    size?: ButtonSize
    type?: ButtonType
    disabled?: boolean
    block?: boolean
  }>(),
  {
    variant: 'primary',
    size: 'md',
    type: 'button',
    disabled: false,
    block: false,
  },
)

const className = computed(() => {
  const variantClasses: Record<ButtonVariant, string> = {
    primary:
      'border-transparent bg-slate-900 text-white hover:bg-slate-800 focus-visible:ring-slate-200 disabled:bg-slate-400',
    secondary:
      'border-slate-200 bg-white text-slate-700 hover:border-slate-300 hover:bg-slate-100 focus-visible:ring-blue-200 disabled:bg-slate-100',
    ghost:
      'border-slate-200 bg-white text-slate-700 hover:border-slate-300 hover:bg-slate-100 hover:text-slate-900 focus-visible:ring-blue-200 disabled:bg-white',
    danger:
      'border-transparent bg-rose-500 text-white hover:bg-rose-600 focus-visible:ring-rose-200 disabled:bg-rose-300',
  }

  const sizeClasses: Record<ButtonSize, string> = {
    sm: 'h-8 px-3 text-xs',
    md: 'h-10 px-4 text-sm',
    lg: 'h-11 px-5 text-sm',
  }

  return [
    'inline-flex items-center justify-center gap-2 rounded-xl border font-semibold tracking-tight transition duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-70',
    sizeClasses[props.size],
    variantClasses[props.variant],
    props.block ? 'w-full' : '',
  ]
})
</script>

<template>
  <button :type="props.type" :disabled="props.disabled" :class="className">
    <slot />
  </button>
</template>
