<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(defineProps<{
  variant?: 'primary' | 'outline' | 'blue' | 'dark' | 'ghost';
  size?: 'sm' | 'md' | 'lg';
  href?: string;
  as?: 'button' | 'a';
  type?: 'button' | 'submit';
  disabled?: boolean;
}>(), {
  variant: 'primary',
  size: 'md',
  as: 'button',
  type: 'button',
  disabled: false,
});

const baseClasses = 'inline-flex items-center justify-center font-extrabold uppercase tracking-wide cursor-pointer select-none transition-all duration-150 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed';

const variantClasses = computed(() => ({
  primary: 'bg-eco-green text-night-ink shadow-[0px_4px_0px_0px_var(--color-eco-green-dark)] hover:brightness-105 active:translate-y-1 active:shadow-none rounded-2xl',
  outline: 'bg-paper-white text-night-ink border-2 border-b-4 border-border-gray hover:bg-cloud-surface active:translate-y-0.5 active:border-b-2 rounded-xl shadow-xs',
  blue: 'bg-sky-blue text-night-ink shadow-[0px_4px_0px_0px_var(--color-sky-blue-dark)] hover:brightness-105 active:translate-y-1 active:shadow-none rounded-xl',
  dark: 'bg-night-ink text-paper-white shadow-[0px_4px_0px_0px_#00021c] hover:brightness-110 active:translate-y-1 active:shadow-none rounded-2xl',
  ghost: 'text-night-ink hover:text-eco-green active:translate-y-0.5 px-0 font-bold normal-case',
}[props.variant]));

const sizeClasses = computed(() => ({
  sm: 'px-4 py-2 text-xs font-bold',
  md: 'px-5 py-2.5 text-sm font-extrabold',
  lg: 'px-6 py-3.5 text-sm font-extrabold',
}[props.size]));
</script>

<template>
  <component
    :is="href ? 'a' : as"
    :href="href"
    :type="href ? undefined : type"
    :disabled="disabled"
    :class="[baseClasses, variantClasses, sizeClasses]"
  >
    <slot />
  </component>
</template>
