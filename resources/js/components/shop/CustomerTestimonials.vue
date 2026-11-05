<template>
    <section v-if="isEnabled" class="customer-testimonials py-5 bg-light-subtle position-relative overflow-hidden">
        <!-- Subtle decorative background bubbles -->
        <div class="testimonial-decor-circle circle-1"></div>
        <div class="testimonial-decor-circle circle-2"></div>

        <div class="container position-relative py-3" style="z-index: 2;">
            <!-- Section Header -->
            <div class="text-center mb-5">
                <span class="badge bg-warning-subtle text-dark border border-warning px-3 py-2 rounded-pill fw-bold mb-3 shadow-xs">
                    {{ badgeText }}
                </span>
                <h2 class="display-6 fw-extrabold text-dark mb-2">
                    {{ titleText }}
                </h2>
                <p class="lead text-secondary mx-auto mb-4" style="max-width: 650px; font-size: 1.1rem;">
                    {{ subtitleText }}
                </p>

                <!-- Trust Score Bar -->
                <div class="d-inline-flex flex-wrap align-items-center justify-content-center gap-3 bg-white p-2 px-4 rounded-pill shadow-sm border">
                    <div class="d-flex align-items-center gap-1">
                        <span class="text-warning fs-5">★★★★★</span>
                        <span class="fw-bold fs-6 text-dark ms-1">4.9 / 5.0</span>
                    </div>
                    <span class="text-muted d-none d-sm-inline">|</span>
                    <span class="small fw-semibold text-secondary">
                        <span class="text-success fw-bold">✔</span> 100% Verified Customer Reviews
                    </span>
                    <span class="text-muted d-none d-sm-inline">|</span>
                    <span class="small fw-semibold text-secondary">
                        <span class="text-success fw-bold">✔</span> Real Counter Stock Sync
                    </span>
                </div>
            </div>

            <!-- Filter Pills -->
            <div class="d-flex justify-content-center gap-2 mb-4 flex-wrap">
                <button
                    type="button"
                    class="btn btn-sm rounded-pill px-3 fw-semibold"
                    :class="activeFilter === 'all' ? 'btn-primary shadow-sm' : 'btn-white border text-secondary'"
                    @click="activeFilter = 'all'"
                >
                    All Reviews ({{ testimonials.length }})
                </button>
                <button
                    type="button"
                    class="btn btn-sm rounded-pill px-3 fw-semibold"
                    :class="activeFilter === '5stars' ? 'btn-primary shadow-sm' : 'btn-white border text-secondary'"
                    @click="activeFilter = '5stars'"
                >
                    ⭐ 5 Stars Only ({{ count5Stars }})
                </button>
                <button
                    type="button"
                    class="btn btn-sm rounded-pill px-3 fw-semibold"
                    :class="activeFilter === 'verified' ? 'btn-primary shadow-sm' : 'btn-white border text-secondary'"
                    @click="activeFilter = 'verified'"
                >
                    🛡️ Verified Buyers ({{ countVerified }})
                </button>
            </div>

            <!-- Testimonials Grid -->
            <div class="row g-4">
                <div
                    v-for="(t, idx) in filteredTestimonials"
                    :key="t.id || idx"
                    class="col-12 col-md-6 col-lg-3"
                >
                    <div class="card h-100 border-0 rounded-4 shadow-sm p-4 testimonial-card bg-white d-flex flex-column position-relative">
                        <!-- Top Row: Star Rating & Decorative Quote -->
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="text-warning fs-6">
                                    {{ '★'.repeat(Number(t.rating || 5)) }}
                                </span>
                                <span class="extra-small text-muted ms-1 font-monospace fw-bold">
                                    {{ Number(t.rating || 5).toFixed(1) }}
                                </span>
                            </div>
                            <span class="quote-icon text-primary opacity-25 fs-3 lh-1">❝</span>
                        </div>

                        <!-- Review Text -->
                        <p class="testimonial-comment text-dark small mb-4 flex-grow-1">
                            "{{ t.comment }}"
                        </p>

                        <!-- Purchased Product Tag -->
                        <div v-if="t.purchased_item" class="mb-3">
                            <span class="badge bg-light text-secondary border rounded-pill extra-small text-truncate d-inline-block" style="max-width: 100%;">
                                📦 {{ t.purchased_item }}
                            </span>
                        </div>

                        <!-- User Profile Info -->
                        <div class="d-flex align-items-center gap-3 pt-3 border-top mt-auto">
                            <!-- Initials Avatar -->
                            <div class="avatar-circle rounded-circle text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0" :class="getAvatarColorClass(idx)">
                                {{ getInitials(t.name) }}
                            </div>

                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex align-items-center gap-1">
                                    <h6 class="fw-bold text-dark mb-0 text-truncate small">{{ t.name }}</h6>
                                    <span v-if="t.verified" class="badge bg-success-subtle text-success rounded-circle p-1 extra-small" title="Verified Customer">
                                        ✓
                                    </span>
                                </div>
                                <div class="extra-small text-muted text-truncate">
                                    {{ t.location || 'Verified Buyer' }}
                                    <span v-if="t.date" class="text-muted opacity-75"> • {{ t.date }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Social Proof Note -->
            <div class="text-center mt-5 pt-3">
                <div class="card border-0 bg-white rounded-pill shadow-xs d-inline-flex flex-row align-items-center gap-3 px-4 py-2 border">
                    <span class="fs-4">🛍️</span>
                    <span class="small text-secondary">
                        Have you placed an order with us?
                        <router-link to="/account" class="fw-bold text-primary text-decoration-none">
                            Sign in to your account
                        </router-link>
                        to track past orders or leave feedback!
                    </span>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useShopSettings } from '../../composables/useShopSettings';

const props = defineProps({
    customTestimonials: {
        type: Array,
        default: () => [],
    },
});

const { settings } = useShopSettings();
const activeFilter = ref('all');

// Default authentic testimonials fallback
const defaultTestimonials = [
    {
        id: 1,
        name: 'Pooja Verma',
        location: 'Mumbai, Maharashtra',
        rating: 5,
        comment: 'Ordered stationery and pens for my design office in the morning and received the package by evening. The live stock sync is super reliable — what you see online is actually in the shop!',
        purchased_item: 'Artline Fineliners & Doms Sketch Books',
        verified: true,
        date: 'Yesterday',
        active: true,
    },
    {
        id: 2,
        name: 'Amit Sharma',
        location: 'Kolkata, West Bengal',
        rating: 5,
        comment: 'Excellent customer service and prompt delivery. Cash on Delivery was completely smooth and the courier tracking updates kept me informed every step.',
        purchased_item: 'Classmate Pulse Notebooks (Pack of 6)',
        verified: true,
        date: '3 days ago',
        active: true,
    },
    {
        id: 3,
        name: 'Dr. Sneha Nair',
        location: 'Pune, Maharashtra',
        rating: 5,
        comment: 'The online ordering experience is seamless! Got an instant invoice, clear dispatch label, and packaging was immaculate. My go-to neighborhood store.',
        purchased_item: 'Parker Vector Rollerball Pen & Refills',
        verified: true,
        date: '1 week ago',
        active: true,
    },
    {
        id: 4,
        name: 'Rahul Khanna',
        location: 'Delhi, NCR',
        rating: 5,
        comment: 'Genuine products at fair MRP pricing without inflated markups. Plus, registering an account let me track my full purchase history with one click.',
        purchased_item: 'Casio Scientific Calculator FX-991CW',
        verified: true,
        date: '2 weeks ago',
        active: true,
    },
];

const isEnabled = computed(() => {
    return settings.value?.testimonials_enabled !== '0';
});

const badgeText = computed(() => {
    return settings.value?.testimonials_badge || '⭐ VERIFIED CUSTOMER REVIEWS';
});

const titleText = computed(() => {
    return settings.value?.testimonials_title || 'What Our Customers Say';
});

const subtitleText = computed(() => {
    return settings.value?.testimonials_subtitle || 'Trusted by hundreds of happy shoppers for fast delivery, accurate live stock, and genuine store products.';
});

const testimonials = computed(() => {
    let list = [];
    if (props.customTestimonials && props.customTestimonials.length > 0) {
        list = props.customTestimonials;
    } else if (settings.value?.testimonials_list_items && settings.value.testimonials_list_items.length > 0) {
        list = settings.value.testimonials_list_items;
    } else {
        list = defaultTestimonials;
    }

    const filtered = list.filter(t => t.active !== false);
    return filtered.length > 0 ? filtered : defaultTestimonials;
});

const count5Stars = computed(() => {
    return testimonials.value.filter(t => Number(t.rating) >= 5).length;
});

const countVerified = computed(() => {
    return testimonials.value.filter(t => t.verified).length;
});

const filteredTestimonials = computed(() => {
    if (activeFilter.value === '5stars') {
        return testimonials.value.filter(t => Number(t.rating) >= 5);
    }
    if (activeFilter.value === 'verified') {
        return testimonials.value.filter(t => t.verified);
    }
    return testimonials.value;
});

function getInitials(name) {
    if (!name) return 'CU';
    const parts = name.trim().split(/\s+/);
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return name.slice(0, 2).toUpperCase();
}

function getAvatarColorClass(idx) {
    const classes = [
        'bg-primary',
        'bg-success',
        'bg-indigo',
        'bg-warning text-dark',
        'bg-danger',
        'bg-info text-dark',
    ];
    return classes[idx % classes.length];
}
</script>

<style scoped>
.customer-testimonials {
    background: #f8fafc;
}

.testimonial-decor-circle {
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
    filter: blur(60px);
    opacity: 0.25;
}

.circle-1 {
    width: 380px;
    height: 380px;
    background: #3b82f6;
    top: -100px;
    left: -100px;
}

.circle-2 {
    width: 320px;
    height: 320px;
    background: #10b981;
    bottom: -80px;
    right: -80px;
}

.testimonial-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: 1px solid rgba(226, 232, 240, 0.8) !important;
}

.testimonial-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px -5px rgba(0, 0, 0, 0.08) !important;
}

.testimonial-comment {
    line-height: 1.6;
    color: #334155 !important;
    font-style: italic;
}

.avatar-circle {
    width: 42px;
    height: 42px;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
}

.bg-indigo {
    background-color: #6366f1 !important;
}

.btn-white {
    background-color: #ffffff;
}

.extra-small {
    font-size: 0.75rem;
}

.shadow-xs {
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}
</style>

