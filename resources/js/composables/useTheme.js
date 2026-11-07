import { ref, computed } from 'vue';

export const THEME_STORAGE_KEY = 'app_theme';

// Shared reactive theme state
const currentTheme = ref(getInitialTheme());

function getInitialTheme() {
    try {
        if (typeof window !== 'undefined' && window.localStorage) {
            const saved = localStorage.getItem(THEME_STORAGE_KEY);
            if (saved === 'dark' || saved === 'light') {
                return saved;
            }
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                return 'dark';
            }
        }
    } catch (e) {
        console.warn('Failed to read theme preference from localStorage:', e);
    }
    return 'light';
}

function applyThemeToDom(theme) {
    if (typeof document === 'undefined') return;

    const html = document.documentElement;
    html.setAttribute('data-bs-theme', theme);

    if (theme === 'dark') {
        html.classList.add('dark-theme');
        html.classList.remove('light-theme');
        document.body.classList.add('dark-theme');
        document.body.classList.remove('light-theme');
    } else {
        html.classList.add('light-theme');
        html.classList.remove('dark-theme');
        document.body.classList.add('light-theme');
        document.body.classList.remove('dark-theme');
    }

    try {
        localStorage.setItem(THEME_STORAGE_KEY, theme);
    } catch (e) {
        console.warn('Failed to persist theme preference:', e);
    }

    if (typeof window !== 'undefined') {
        window.dispatchEvent(new CustomEvent('app:theme-changed', { detail: { theme, isDark: theme === 'dark' } }));
    }
}

// Immediately apply initial theme on script load
if (typeof window !== 'undefined') {
    applyThemeToDom(currentTheme.value);
}

export function useTheme() {
    const isDark = computed(() => currentTheme.value === 'dark');

    function setTheme(target) {
        if (target !== 'dark' && target !== 'light') return;
        currentTheme.value = target;
        applyThemeToDom(target);
    }

    function toggleTheme() {
        setTheme(isDark.value ? 'light' : 'dark');
    }

    return {
        theme: currentTheme,
        isDark,
        setTheme,
        toggleTheme,
    };
}
