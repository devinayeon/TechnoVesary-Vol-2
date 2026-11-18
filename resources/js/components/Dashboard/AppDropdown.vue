<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount, type Component } from 'vue';
import { ChevronDown, Check } from 'lucide-vue-next';

export interface DropdownOption {
    value: string | number;
    label: string;
    icon?: Component;
    tone?: 'Green' | 'Blue' | 'Amber' | 'Coral' | 'Neutral';
    description?: string;
}

const props = withDefaults(
    defineProps<{
        modelValue?: string | number | null;
        options: Array<DropdownOption | string>;
        placeholder?: string;
        icon?: Component;
        disabled?: boolean;
        menuWidth?: string;
        align?: 'left' | 'right';
        size?: 'sm' | 'md' | 'lg';
    }>(),
    {
        modelValue: '',
        placeholder: 'Pilih...',
        disabled: false,
        align: 'left',
        size: 'md',
    },
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: string | number): void;
    (e: 'change', value: string | number): void;
}>();

const isOpen = ref(false);
const dropdownRef = ref<HTMLElement | null>(null);
const computedAlign = ref<'left' | 'right'>(props.align);

// Normalize options to DropdownOption format
const normalizedOptions = computed<DropdownOption[]>(() => {
    return props.options.map((opt) => {
        if (typeof opt === 'string') {
            return {
                value: opt,
                label: opt,
            };
        }
        return opt;
    });
});

// Currently selected option object
const selectedOption = computed(() => {
    return normalizedOptions.value.find(
        (opt) => opt.value === props.modelValue,
    );
});

// Display label for the trigger button
const displayLabel = computed(() => {
    if (selectedOption.value) {
        return selectedOption.value.label;
    }
    return props.placeholder;
});

const isPlaceholder = computed(() => {
    return (
        !selectedOption.value ||
        props.modelValue === '' ||
        props.modelValue === null
    );
});

const toggleDropdown = () => {
    if (props.disabled) return;
    if (!isOpen.value && dropdownRef.value) {
        const rect = dropdownRef.value.getBoundingClientRect();
        // If explicitly set to right, or if dropdown would overflow the right viewport boundary
        if (props.align === 'right' || rect.right + 140 > window.innerWidth) {
            computedAlign.value = 'right';
        } else {
            computedAlign.value = 'left';
        }
    }
    isOpen.value = !isOpen.value;
};

const selectOption = (opt: DropdownOption) => {
    if (props.disabled) return;
    emit('update:modelValue', opt.value);
    emit('change', opt.value);
    isOpen.value = false;
};

// Close when clicking outside
const handleClickOutside = (event: MouseEvent) => {
    if (
        dropdownRef.value &&
        !dropdownRef.value.contains(event.target as Node)
    ) {
        isOpen.value = false;
    }
};

// Close on Escape key
const handleKeyDown = (event: KeyboardEvent) => {
    if (event.key === 'Escape' && isOpen.value) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    document.addEventListener('keydown', handleKeyDown);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
    document.removeEventListener('keydown', handleKeyDown);
});

// Sizing styles for trigger button
const sizeClasses = computed(() => {
    switch (props.size) {
        case 'sm':
            return 'h-[36px] px-3 text-[12px] rounded-lg';
        case 'lg':
            return 'h-[48px] px-4 text-[15px] rounded-2xl';
        case 'md':
        default:
            return 'h-[44px] px-3.5 text-[14px] rounded-xl';
    }
});
</script>

<template>
    <div
        ref="dropdownRef"
        class="relative inline-block text-left"
        :class="{ 'pointer-events-none opacity-60': disabled }"
    >
        <!-- Trigger Button (Figma 481:745 / 3D Duolingo Tactile) -->
        <button
            type="button"
            @click="toggleDropdown"
            :disabled="disabled"
            :class="[
                'flex w-full cursor-pointer items-center justify-between gap-2.5 border-2 bg-paper-white font-bold shadow-2xs transition-all select-none',
                sizeClasses,
                isOpen
                    ? 'border-eco-green text-night-ink ring-4 ring-eco-green/15'
                    : 'border-border-gray text-night-ink hover:border-charcoal/40 active:translate-y-0.5',
            ]"
        >
            <div class="flex min-w-0 items-center gap-2 truncate">
                <!-- Optional leading icon -->
                <component
                    v-if="icon || selectedOption?.icon"
                    :is="selectedOption?.icon || icon"
                    class="size-4 shrink-0 text-charcoal/70"
                />

                <span
                    class="block truncate"
                    :class="[
                        isPlaceholder
                            ? 'font-semibold text-charcoal/70'
                            : 'font-bold text-night-ink',
                    ]"
                >
                    {{ displayLabel }}
                </span>
            </div>

            <!-- Chevron Icon -->
            <ChevronDown
                class="size-4 shrink-0 text-charcoal transition-transform duration-200"
                :class="{ 'rotate-180 text-night-ink': isOpen }"
            />
        </button>

        <!-- Dropdown Menu / Popover Panel -->
        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="transform scale-95 opacity-0 -translate-y-1"
            enter-to-class="transform scale-100 opacity-100 translate-y-0"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="transform scale-100 opacity-100 translate-y-0"
            leave-to-class="transform scale-95 opacity-0 -translate-y-1"
        >
            <div
                v-if="isOpen"
                :class="[
                    'absolute z-50 mt-1.5 max-h-64 max-w-[calc(100vw-32px)] overflow-y-auto rounded-2xl border-2 border-border-gray bg-paper-white p-1.5 shadow-lg focus:outline-none',
                    menuWidth ? menuWidth : 'w-full min-w-[180px]',
                    computedAlign === 'right'
                        ? 'right-0 origin-top-right'
                        : 'left-0 origin-top-left',
                ]"
            >
                <div class="space-y-1">
                    <button
                        v-for="opt in normalizedOptions"
                        :key="opt.value"
                        type="button"
                        @click="selectOption(opt)"
                        :class="[
                            'group flex w-full cursor-pointer items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-left text-[13px] transition-all select-none',
                            opt.value === modelValue
                                ? 'bg-sprout-tint font-extrabold text-night-ink shadow-2xs'
                                : 'font-bold text-charcoal hover:bg-cloud-surface hover:text-night-ink',
                        ]"
                    >
                        <div class="flex min-w-0 items-center gap-2.5 truncate">
                            <component
                                v-if="opt.icon"
                                :is="opt.icon"
                                class="size-4 shrink-0"
                                :class="
                                    opt.value === modelValue
                                        ? 'text-eco-green-dark'
                                        : 'text-charcoal/70 group-hover:text-night-ink'
                                "
                            />
                            <div class="truncate">
                                <span class="block truncate">{{
                                    opt.label
                                }}</span>
                                <span
                                    v-if="opt.description"
                                    class="mt-0.5 block truncate text-[11px] font-normal text-charcoal/70"
                                >
                                    {{ opt.description }}
                                </span>
                            </div>
                        </div>

                        <!-- Checkmark when selected -->
                        <Check
                            v-if="opt.value === modelValue"
                            class="size-4 shrink-0 font-extrabold text-eco-green-dark"
                        />
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>
