<template>
    <div
        class="shop-slideshow position-relative overflow-hidden"
        @mouseenter="pauseAutoplay"
        @mouseleave="resumeAutoplay"
        @touchstart="handleTouchStart"
        @touchmove="handleTouchMove"
        @touchend="handleTouchEnd"
    >
        <!-- Slides Container -->
        <div class="slides-track position-relative" style="min-height: 480px;">
            <transition name="slide-fade" mode="out-in">
                <div
                    v-if="currentSlide"
                    :key="currentSlide.id || activeIndex"
                    class="slide-item py-5 text-white position-relative d-flex align-items-center"
                    :class="currentSlide.bg_theme || 'blue-gradient'"
                    :style="slideBackgroundStyle(currentSlide)"
                >
                    <!-- Background Pattern / Noise Overlay -->
                    <div class="slide-overlay-pattern"></div>

                    <div class="container py-3 position-relative" style="z-index: 2;">
                        <div class="row align-items-center g-4">
                            <!-- Left: Slide Content & Actions -->
                            <div class="col-lg-7 text-start">
                                <!-- Badge -->
                                <div class="mb-3">
                                    <span class="badge slide-badge px-3 py-2 rounded-pill fw-bold shadow-sm">
                                        {{ currentSlide.badge || '⚡ FEATURED SPECIAL' }}
                                    </span>
                                </div>

                                <!-- Headline -->
                                <h1 class="display-4 fw-extrabold mb-3 slide-title lh-tight">
                                    {{ currentSlide.title }}
                                </h1>

                                <!-- Subtitle / Description -->
                                <p class="lead text-light-opacity mb-4 slide-description" style="max-width: 620px;">
                                    {{ currentSlide.subtitle }}
                                </p>

                                <!-- Instant Search Input (Integrated into slide) -->
                                <div class="card shadow-lg border-0 p-2 rounded-pill bg-white mb-4" style="max-width: 540px;">
                                    <form @submit.prevent="submitSearch" class="d-flex align-items-center">
                                        <span class="ps-3 pe-2 text-muted fs-5">🔍</span>
                                        <input
                                            v-model="searchQuery"
                                            type="text"
                                            class="form-control border-0 shadow-none ps-1"
                                            :placeholder="$t('storefront.search_placeholder')"
                                        />
                                        <button type="submit" class="btn btn-primary px-4 py-2 rounded-pill fw-bold">
                                            {{ $t('common.search') }}
                                        </button>
                                    </form>
                                </div>

                                <!-- Action Buttons -->
                                <div class="d-flex flex-wrap gap-3 align-items-center mb-3">
                                    <router-link
                                        v-if="currentSlide.primary_btn_text"
                                        :to="currentSlide.primary_btn_link || '/shop'"
                                        class="btn btn-warning btn-lg px-4 py-2 rounded-pill fw-bold text-dark shadow d-inline-flex align-items-center gap-2 hover-scale"
                                    >
                                        <span>{{ currentSlide.primary_btn_text }}</span>
                                    </router-link>

                                    <router-link
                                        v-if="currentSlide.secondary_btn_text"
                                        :to="currentSlide.secondary_btn_link || '/shop'"
                                        class="btn btn-outline-light btn-lg px-4 py-2 rounded-pill fw-semibold d-inline-flex align-items-center gap-2 hover-scale"
                                    >
                                        <span>{{ currentSlide.secondary_btn_text }}</span>
                                    </router-link>
                                </div>

                                <!-- Trust Micro-Pills -->
                                <div class="d-flex flex-wrap gap-3 small text-white-50 pt-2">
                                    <span class="d-flex align-items-center gap-1 text-light">
                                        <span class="text-success fw-bold">✔</span> Real-Time Store Sync
                                    </span>
                                    <span class="d-flex align-items-center gap-1 text-light">
                                        <span class="text-success fw-bold">✔</span> Cash on Delivery & UPI
                                    </span>
                                    <span class="d-flex align-items-center gap-1 text-light">
                                        <span class="text-success fw-bold">✔</span> Express Courier Dispatch
                                    </span>
                                </div>
                            </div>

                            <!-- Right: Slide Showcase Visual / Image -->
                            <div class="col-lg-5 text-center d-none d-lg-block">
                                <!-- If image banner provided -->
                                <div v-if="currentSlide.image_url" class="slide-image-wrapper p-3">
                                    <img
                                        :src="currentSlide.image_url"
                                        :alt="currentSlide.title"
                                        class="img-fluid rounded-4 shadow-2xl slide-hero-image"
                                    />
                                </div>

                                <!-- Decorative Feature Card if no image -->
                                <div v-else class="hero-card-promo p-4 rounded-4 shadow-2xl text-start bg-white text-dark position-relative hover-float">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="badge bg-danger-subtle text-danger px-3 py-1 rounded-pill fw-semibold">
                                            ⭐ Neighborhood Store
                                        </span>
                                        <span class="badge bg-success-subtle text-success px-2 py-1 rounded small">
                                            ● Live Stock Connected
                                        </span>
                                    </div>
                                    <h4 class="fw-bold mb-2 text-dark">{{ settings.site_name || 'Retail POS Store' }}</h4>
                                    <p class="text-muted small mb-3">
                                        {{ settings.hero_description || 'Browse thousands of everyday products with live counter sync. Order online for instant pickup or doorstep delivery.' }}
                                    </p>
                                    <div class="d-flex gap-2">
                                        <router-link to="/shop" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                            Browse Catalog →
                                        </router-link>
                                        <router-link to="/track-order" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                                            Track Order
                                        </router-link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>
        </div>

        <!-- Navigation Arrows (Previous / Next) -->
        <button
            v-if="slides.length > 1"
            type="button"
            class="slideshow-arrow slideshow-arrow-prev"
            aria-label="Previous Slide"
            @click="prevSlide"
        >
            <span>‹</span>
        </button>

        <button
            v-if="slides.length > 1"
            type="button"
            class="slideshow-arrow slideshow-arrow-next"
            aria-label="Next Slide"
            @click="nextSlide"
        >
            <span>›</span>
        </button>

        <!-- Slide Indicators / Dots & Progress Bar -->
        <div v-if="slides.length > 1" class="slideshow-indicators d-flex align-items-center gap-2">
            <button
                v-for="(s, idx) in slides"
                :key="s.id || idx"
                type="button"
                class="indicator-dot"
                :class="{ active: idx === activeIndex }"
                :aria-label="`Go to slide ${idx + 1}`"
                @click="goToSlide(idx)"
            ></button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useShopSettings } from '../../composables/useShopSettings';

const props = defineProps({
    customSlides: {
        type: Array,
        default: () => [],
    },
    interval: {
        type: Number,
        default: 5000,
    },
    autoplay: {
        type: Boolean,
        default: true,
    },
});

const router = useRouter();
const { settings } = useShopSettings();

const searchQuery = ref('');
const activeIndex = ref(0);
let timer = null;

// Touch swipe tracking variables
let touchStartX = 0;
let touchEndX = 0;

// Default built-in slides
const defaultSlides = [
    {
        id: 1,
        badge: '🔥 MEGA FESTIVE SALE • UP TO 40% OFF',
        title: 'Premium Stationery & Office Essentials',
        subtitle: 'High quality writing notebooks, fine pens, art supplies, and desk accessories curated for professionals and students.',
        primary_btn_text: 'Shop Collection →',
        primary_btn_link: '/shop',
        secondary_btn_text: 'Explore All',
        secondary_btn_link: '/shop',
        bg_theme: 'blue-gradient',
        image_url: '',
        active: true,
    },
    {
        id: 2,
        badge: '⚡ NEW ARRIVALS & GADGETS',
        title: 'Latest Smartphones & Daily Tech',
        subtitle: 'Discover genuine devices, accessories, and electronics with same-day dispatch and verified authentic warranty.',
        primary_btn_text: 'Browse Electronics →',
        primary_btn_link: '/shop',
        secondary_btn_text: 'Track Your Order',
        secondary_btn_link: '/track-order',
        bg_theme: 'dark-gradient',
        image_url: '',
        active: true,
    },
    {
        id: 3,
        badge: '🚚 LOCAL NEIGHBORHOOD DELIVERY',
        title: 'Live Counter Stock Synchronized 24/7',
        subtitle: 'Every product you see online is linked directly to our physical warehouse inventory. Order online, pick up or get delivered!',
        primary_btn_text: 'Start Shopping Now',
        primary_btn_link: '/shop',
        secondary_btn_text: 'Customer Sign In',
        secondary_btn_link: '/login',
        bg_theme: 'emerald-gradient',
        image_url: '',
        active: true,
    },
];

// Resolved active slides array
const slides = computed(() => {
    let list = [];
    if (props.customSlides && props.customSlides.length > 0) {
        list = props.customSlides;
    } else if (settings.value?.slideshow_slides_list && settings.value.slideshow_slides_list.length > 0) {
        list = settings.value.slideshow_slides_list;
    } else {
        list = defaultSlides;
    }

    // Filter active only
    const filtered = list.filter(s => s.active !== false);
    return filtered.length > 0 ? filtered : defaultSlides;
});

const currentSlide = computed(() => {
    if (slides.value.length === 0) return null;
    return slides.value[activeIndex.value] || slides.value[0];
});

function nextSlide() {
    if (slides.value.length === 0) return;
    activeIndex.value = (activeIndex.value + 1) % slides.value.length;
}

function prevSlide() {
    if (slides.value.length === 0) return;
    activeIndex.value = (activeIndex.value - 1 + slides.value.length) % slides.value.length;
}

function goToSlide(idx) {
    activeIndex.value = idx;
    restartAutoplay();
}

function startAutoplay() {
    stopAutoplay();
    if (!props.autoplay || slides.value.length <= 1) return;
    const speed = Number(settings.value?.slideshow_speed || props.interval || 5000);
    timer = setInterval(() => {
        nextSlide();
    }, speed);
}

function stopAutoplay() {
    if (timer) {
        clearInterval(timer);
        timer = null;
    }
}

function pauseAutoplay() {
    stopAutoplay();
}

function resumeAutoplay() {
    startAutoplay();
}

function restartAutoplay() {
    stopAutoplay();
    startAutoplay();
}

// Touch swipe support for mobile
function handleTouchStart(e) {
    touchStartX = e.touches[0].clientX;
}

function handleTouchMove(e) {
    touchEndX = e.touches[0].clientX;
}

function handleTouchEnd() {
    const diff = touchStartX - touchEndX;
    if (Math.abs(diff) > 50) {
        if (diff > 0) {
            nextSlide(); // Swiped left -> next
        } else {
            prevSlide(); // Swiped right -> prev
        }
        restartAutoplay();
    }
}

function submitSearch() {
    if (!searchQuery.value.trim()) return;
    router.push({
        path: '/shop',
        query: { search: searchQuery.value.trim() },
    });
}

function slideBackgroundStyle(slide) {
    if (slide.image_url && slide.bg_theme === 'custom-image') {
        return {
            backgroundImage: `linear-gradient(rgba(15, 23, 42, 0.75), rgba(15, 23, 42, 0.85)), url(${slide.image_url})`,
            backgroundSize: 'cover',
            backgroundPosition: 'center',
        };
    }
    return {};
}

watch(
    () => slides.value.length,
    (len) => {
        if (activeIndex.value >= len) {
            activeIndex.value = 0;
        }
    }
);

onMounted(() => {
    startAutoplay();
});

onBeforeUnmount(() => {
    stopAutoplay();
});
</script>

<style scoped>
.shop-slideshow {
    width: 100%;
    min-height: 480px;
}

.slide-item {
    min-height: 480px;
    width: 100%;
}

/* Background Gradients */
.blue-gradient {
    background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 45%, #0284c7 100%);
}

.dark-gradient {
    background: linear-gradient(135deg, #09090b 0%, #18181b 50%, #27272a 100%);
}

.purple-gradient {
    background: linear-gradient(135deg, #3b0764 0%, #6b21a8 50%, #a21caf 100%);
}

.emerald-gradient {
    background: linear-gradient(135deg, #022c22 0%, #065f46 50%, #059669 100%);
}

.coral-gradient {
    background: linear-gradient(135deg, #431407 0%, #9a3412 50%, #ea580c 100%);
}

/* Subtle pattern overlay */
.slide-overlay-pattern {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: radial-gradient(rgba(255, 255, 255, 0.08) 1px, transparent 1px);
    background-size: 24px 24px;
    pointer-events: none;
    z-index: 1;
}

.slide-badge {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(8px);
    color: #fef08a;
    border: 1px solid rgba(255, 255, 255, 0.3);
    letter-spacing: 0.5px;
}

.slide-title {
    font-weight: 800;
    letter-spacing: -0.5px;
}

.text-light-opacity {
    color: rgba(255, 255, 255, 0.88);
}

.slide-hero-image {
    max-height: 340px;
    object-fit: cover;
    border: 3px solid rgba(255, 255, 255, 0.2);
    transition: transform 0.4s ease;
}

.slide-hero-image:hover {
    transform: scale(1.02);
}

.hover-scale {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.hover-scale:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.25) !important;
}

.hover-float {
    transition: transform 0.3s ease;
}

.hover-float:hover {
    transform: translateY(-4px);
}

/* Navigation Arrows */
.slideshow-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: rgba(15, 23, 42, 0.45);
    backdrop-filter: blur(6px);
    border: 1px solid rgba(255, 255, 255, 0.25);
    color: #ffffff;
    font-size: 28px;
    line-height: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 10;
    transition: all 0.25s ease;
}

.slideshow-arrow:hover {
    background: rgba(15, 23, 42, 0.85);
    border-color: rgba(255, 255, 255, 0.6);
    transform: translateY(-50%) scale(1.08);
}

.slideshow-arrow-prev {
    left: 18px;
}

.slideshow-arrow-next {
    right: 18px;
}

/* Slide Indicators */
.slideshow-indicators {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 10;
}

.indicator-dot {
    width: 12px;
    height: 12px;
    border-radius: 6px;
    background: rgba(255, 255, 255, 0.4);
    border: none;
    padding: 0;
    cursor: pointer;
    transition: all 0.3s ease;
}

.indicator-dot.active {
    width: 32px;
    background: #ffffff;
    box-shadow: 0 0 10px rgba(255, 255, 255, 0.8);
}

/* Slide Fade Transition */
.slide-fade-enter-active,
.slide-fade-leave-active {
    transition: opacity 0.5s ease, transform 0.5s ease;
}

.slide-fade-enter-from {
    opacity: 0;
    transform: translateX(20px);
}

.slide-fade-leave-to {
    opacity: 0;
    transform: translateX(-20px);
}

@media (max-width: 768px) {
    .slides-track, .slide-item {
        min-height: 520px;
    }
    .slide-title {
        font-size: 1.85rem;
    }
    .slideshow-arrow {
        width: 38px;
        height: 38px;
        font-size: 22px;
    }
    .slideshow-arrow-prev {
        left: 8px;
    }
    .slideshow-arrow-next {
        right: 8px;
    }
}
</style>

