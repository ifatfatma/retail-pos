<template>
    <button
        type="button"
        class="theme-toggle-btn d-inline-flex align-items-center justify-content-center border"
        :class="buttonClasses"
        :title="isDark ? $t('common.light_mode') : $t('common.dark_mode')"
        :aria-label="isDark ? $t('common.light_mode') : $t('common.dark_mode')"
        @click="toggleTheme"
    >
        <span class="theme-icon-wrap" :class="{ 'rotate-icon': isDark }">
            <span v-if="isDark" class="theme-sun-icon">☀️</span>
            <span v-else class="theme-moon-icon">🌙</span>
        </span>
        <span v-if="showLabel" class="theme-label-text ms-1 d-none d-sm-inline">
            {{ isDark ? $t('common.light_mode') : $t('common.dark_mode') }}
        </span>
    </button>
</template>

<script setup>
import { computed } from 'vue';
import { useTheme } from '../../composables/useTheme';

const props = defineProps({
    theme: {
        type: String,
        default: 'auto', // 'dark', 'light', or 'auto'
    },
    showLabel: {
        type: Boolean,
        default: false,
    },
    size: {
        type: String,
        default: 'md', // 'sm', 'md'
    },
});

const { isDark, toggleTheme } = useTheme();

const buttonClasses = computed(() => {
    const list = [];

    if (props.size === 'sm') {
        list.push('theme-btn-sm');
    } else {
        list.push('theme-btn-md');
    }

    if (props.theme === 'dark') {
        list.push('btn-dark text-white border-secondary bg-dark');
    } else if (props.theme === 'light') {
        list.push('btn-light text-dark border-secondary-subtle');
    } else {
        // Auto responds to isDark
        if (isDark.value) {
            list.push('btn-dark text-warning border-secondary bg-dark');
        } else {
            list.push('btn-light text-dark border-secondary-subtle');
        }
    }

    return list.join(' ');
});
</script>

<style scoped>
.theme-toggle-btn {
    border-radius: 50rem;
    padding: 0.35rem 0.65rem;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.25s ease;
    user-select: none;
    line-height: 1;
}

.theme-btn-sm {
    width: 36px;
    height: 36px;
    min-width: 36px;
    padding: 0;
    font-size: 0.85rem;
    border-radius: 50%;
}

.theme-btn-md {
    height: 38px;
    min-width: 38px;
}

.theme-toggle-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.theme-icon-wrap {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.rotate-icon {
    transform: rotate(360deg);
}

.theme-sun-icon {
    font-size: 1rem;
    filter: drop-shadow(0 0 4px rgba(251, 191, 36, 0.6));
}

.theme-moon-icon {
    font-size: 0.95rem;
    filter: drop-shadow(0 0 3px rgba(96, 165, 250, 0.4));
}

.theme-label-text {
    font-size: 0.82rem;
    font-weight: 500;
}
</style>
