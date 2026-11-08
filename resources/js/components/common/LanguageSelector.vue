<template>
    <div class="language-selector position-relative" ref="dropdownRef">
        <!-- Trigger Button -->
        <button
            type="button"
            class="btn btn-sm d-flex align-items-center gap-2 lang-selector-btn shadow-sm"
            :class="theme === 'light' ? 'btn-light border text-dark' : 'btn-dark border-secondary text-white'"
            @click="isOpen = !isOpen"
            :title="$t('header.select_language')"
            aria-expanded="isOpen"
        >
            <span class="fs-6 lh-1">🌐</span>
            <span class="fw-semibold">{{ currentLocaleObj.nativeName }}</span>
            <span class="small opacity-75">▾</span>
        </button>

        <!-- Dropdown Menu -->
        <div
            v-if="isOpen"
            class="dropdown-menu show shadow-lg border-0 rounded-3 py-2 animate-fade"
            :class="[
                menuAlign === 'start' ? 'dropdown-menu-start' : 'dropdown-menu-end',
                theme === 'dark' ? 'bg-dark text-white border-secondary border' : 'bg-white'
            ]"
            style="position: absolute; min-width: 175px; z-index: 1060; top: 100%; margin-top: 6px;"
        >
            <div class="px-3 py-1 text-muted extra-small text-uppercase fw-bold border-bottom mb-1" :class="theme === 'dark' ? 'text-secondary border-secondary' : 'text-muted'">
                {{ $t('header.select_language') }}
            </div>

            <button
                v-for="loc in availableLocales"
                :key="loc.code"
                type="button"
                class="dropdown-item d-flex align-items-center justify-content-between px-3 py-2 small"
                :class="{
                    'active fw-bold': loc.code === currentLocaleCode,
                    'text-white-50': theme === 'dark' && loc.code !== currentLocaleCode,
                    'text-white': theme === 'dark' && loc.code === currentLocaleCode
                }"
                @click="onSelect(loc.code)"
            >
                <div class="d-flex align-items-center gap-2">
                    <span>{{ loc.flag }}</span>
                    <span>{{ loc.nativeName }}</span>
                    <span class="extra-small text-muted" v-if="loc.code !== 'en'">({{ loc.name }})</span>
                </div>
                <span v-if="loc.code === currentLocaleCode" class="ms-2">✓</span>
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useI18n } from 'vue-i18n';
import { supportedLocales, enabledLocales, setLanguage, getCurrentLanguage } from '../../i18n';

const props = defineProps({
    theme: {
        type: String,
        default: 'dark', // 'dark' for admin navbar, 'light' for storefront
    },
    menuAlign: {
        type: String,
        default: 'end',
    },
});

const { locale } = useI18n();
const isOpen = ref(false);
const dropdownRef = ref(null);

const availableLocales = computed(() => {
    return enabledLocales.value?.length ? enabledLocales.value : supportedLocales;
});

const currentLocaleCode = computed(() => {
    return locale.value || getCurrentLanguage();
});

const currentLocaleObj = computed(() => {
    return availableLocales.value.find(l => l.code === currentLocaleCode.value)
        || availableLocales.value[0]
        || supportedLocales[0];
});

function onSelect(code) {
    setLanguage(code);
    isOpen.value = false;
}

function handleClickOutside(e) {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
        isOpen.value = false;
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
.extra-small {
    font-size: 0.72rem;
}

.animate-fade {
    animation: fadeIn 0.15s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-4px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
