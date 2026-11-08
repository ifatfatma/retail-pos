<template>
    <div class="container-fluid py-4">

        <!-- Page Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
            <div>
                <h3 class="fw-bold mb-1">
                    {{ isEdit ? '✏️ ' + $t('marketing.edit_campaign') : '✨ ' + $t('marketing.create_campaign') }}
                </h3>
                <p class="text-muted mb-0">
                    {{ $t('marketing.campaign_form_subtitle') }}
                </p>
            </div>

            <div class="d-flex gap-2">
                <router-link to="/admin/marketing/campaigns" class="btn btn-outline-secondary btn-sm">
                    ← {{ $t('marketing.back_to_campaigns') }}
                </router-link>

                <button
                    class="btn btn-primary btn-sm fw-semibold"
                    @click="handleSubmit"
                    :disabled="saving"
                >
                    <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                    💾 {{ isEdit ? $t('common.update') : $t('marketing.save_campaign') }}
                </button>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left: Campaign Configuration Form -->
            <div class="col-lg-7">
                <form @submit.prevent="handleSubmit">

                    <!-- Section 1: Basic Information -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-transparent border-0 pt-3 pb-0">
                            <h5 class="fw-bold mb-0">📝 {{ $t('marketing.campaign_details') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">{{ $t('marketing.campaign_title') }} *</label>
                                    <input
                                        v-model="form.title"
                                        type="text"
                                        class="form-control"
                                        :placeholder="$t('marketing.title_placeholder')"
                                        required
                                    />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">
                                        {{ $t('marketing.campaign_title_ar') }}
                                        <span class="badge bg-light text-muted ms-1">عربي</span>
                                    </label>
                                    <input
                                        v-model="form.title_ar"
                                        type="text"
                                        dir="rtl"
                                        class="form-control"
                                        :placeholder="$t('marketing.title_ar_placeholder')"
                                    />
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold small">{{ $t('marketing.campaign_type') }} *</label>
                                    <select v-model="form.campaign_type" class="form-select" required>
                                        <option value="promotional">{{ $t('marketing.type_promotional') }}</option>
                                        <option value="flash_sale">{{ $t('marketing.type_flash_sale') }} (Amazon Lightning Deals)</option>
                                        <option value="festival">{{ $t('marketing.type_festival') }} (Mega Festival Sale)</option>
                                        <option value="seasonal">{{ $t('marketing.type_seasonal') }}</option>
                                        <option value="clearance">{{ $t('marketing.type_clearance') }}</option>
                                        <option value="retargeting">{{ $t('marketing.type_retargeting') }}</option>
                                    </select>
                                </div>

                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold small">{{ $t('marketing.initial_status') }} *</label>
                                    <select v-model="form.status" class="form-select" required>
                                        <option value="draft">{{ $t('marketing.status_draft') }}</option>
                                        <option value="scheduled">{{ $t('marketing.status_scheduled') }}</option>
                                        <option value="active">{{ $t('marketing.status_active') }} ({{ $t('marketing.send_immediately') }})</option>
                                        <option value="paused">{{ $t('marketing.status_paused') }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">{{ $t('marketing.campaign_description') }}</label>
                                    <textarea
                                        v-model="form.description"
                                        class="form-control"
                                        rows="3"
                                        :placeholder="$t('marketing.desc_placeholder')"
                                    ></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">
                                        {{ $t('marketing.campaign_description_ar') }}
                                        <span class="badge bg-light text-muted ms-1">عربي</span>
                                    </label>
                                    <textarea
                                        v-model="form.description_ar"
                                        dir="rtl"
                                        class="form-control"
                                        rows="3"
                                        :placeholder="$t('marketing.desc_ar_placeholder')"
                                    ></textarea>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold small">{{ $t('marketing.banner_url') }}</label>
                                    <input
                                        v-model="form.banner_url"
                                        type="url"
                                        class="form-control form-control-sm"
                                        placeholder="https://example.com/banner.jpg"
                                    />
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold small">{{ $t('marketing.product_image_url') }}</label>
                                    <input
                                        v-model="form.image_url"
                                        type="url"
                                        class="form-control form-control-sm"
                                        placeholder="https://example.com/product.jpg"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Target Audience & Segmentation -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-transparent border-0 pt-3 pb-0">
                            <h5 class="fw-bold mb-0">🎯 {{ $t('marketing.target_audience') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">{{ $t('marketing.target_type') }}</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input
                                            class="form-check-input"
                                            type="radio"
                                            v-model="form.target_type"
                                            value="all"
                                            id="targetAll"
                                        />
                                        <label class="form-check-label" for="targetAll">
                                            {{ $t('marketing.all_customers') }}
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input
                                            class="form-check-input"
                                            type="radio"
                                            v-model="form.target_type"
                                            value="segment"
                                            id="targetSegment"
                                        />
                                        <label class="form-check-label" for="targetSegment">
                                            {{ $t('marketing.specific_segment') }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div v-if="form.target_type === 'segment'" class="mb-3">
                                <label class="form-label fw-semibold small">{{ $t('marketing.select_segment') }} *</label>
                                <select v-model="form.segment_id" class="form-select">
                                    <option :value="null">{{ $t('marketing.choose_segment') }}</option>
                                    <option v-for="seg in segments" :key="seg.id" :value="seg.id">
                                        {{ seg.name }} ({{ seg.customer_count }} {{ $t('marketing.customers') }})
                                    </option>
                                </select>
                            </div>

                            <!-- Target Categories & Products (Optional Filter) -->
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold small">{{ $t('marketing.select_categories') }}</label>
                                    <select v-model="form.target_category_ids" multiple class="form-select form-select-sm" style="height: 90px;">
                                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                            {{ cat.name }}
                                        </option>
                                    </select>
                                    <div class="extra-small text-muted mt-1">{{ $t('marketing.multi_select_hint') }}</div>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold small">{{ $t('marketing.select_products') }}</label>
                                    <select v-model="form.target_product_ids" multiple class="form-select form-select-sm" style="height: 90px;">
                                        <option v-for="prod in products" :key="prod.id" :value="prod.id">
                                            {{ prod.name }} (₹{{ prod.selling_price }})
                                        </option>
                                    </select>
                                    <div class="extra-small text-muted mt-1">{{ $t('marketing.multi_select_hint') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Offers, Discounts & Coupons -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-transparent border-0 pt-3 pb-0">
                            <h5 class="fw-bold mb-0">🏷️ {{ $t('marketing.offers_and_discounts') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3 mb-3">
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold small">{{ $t('marketing.discount_type') }}</label>
                                    <select v-model="form.discount_type" class="form-select">
                                        <option value="none">{{ $t('marketing.discount_none') }}</option>
                                        <option value="percentage">{{ $t('marketing.discount_percentage') }} (%)</option>
                                        <option value="fixed">{{ $t('marketing.discount_fixed') }} (₹)</option>
                                    </select>
                                </div>

                                <div class="col-sm-6" v-if="form.discount_type !== 'none'">
                                    <label class="form-label fw-semibold small">{{ $t('marketing.discount_value') }} *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">{{ form.discount_type === 'percentage' ? '%' : '₹' }}</span>
                                        <input
                                            v-model.number="form.discount_value"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="form-control"
                                            placeholder="20"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">{{ $t('marketing.attach_coupon') }}</label>
                                <select v-model="form.coupon_id" class="form-select">
                                    <option :value="null">{{ $t('marketing.no_coupon_attached') }}</option>
                                    <option v-for="cpn in coupons" :key="cpn.id" :value="cpn.id">
                                        🎟️ {{ cpn.code }} - {{ cpn.name || cpn.code }} ({{ cpn.type === 'percent' ? cpn.value + '%' : '₹' + cpn.value }})
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Section 4: Channels & Scheduling -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-transparent border-0 pt-3 pb-0">
                            <h5 class="fw-bold mb-0">🚀 {{ $t('marketing.channels_and_scheduling') }}</h5>
                        </div>
                        <div class="card-body">
                            <!-- Channel Checkboxes -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold small d-block mb-2">{{ $t('marketing.delivery_channels') }} *</label>
                                <div class="row g-2">
                                    <div class="col-sm-6 col-md-4">
                                        <label class="border p-2 rounded d-flex align-items-center gap-2 cursor-pointer w-100">
                                            <input type="checkbox" v-model="form.channels" value="in_app" class="form-check-input mt-0" />
                                            <span>📱 {{ $t('marketing.channel_in_app') }}</span>
                                        </label>
                                    </div>
                                    <div class="col-sm-6 col-md-4">
                                        <label class="border p-2 rounded d-flex align-items-center gap-2 cursor-pointer w-100">
                                            <input type="checkbox" v-model="form.channels" value="email" class="form-check-input mt-0" />
                                            <span>✉️ {{ $t('marketing.channel_email') }}</span>
                                        </label>
                                    </div>
                                    <div class="col-sm-6 col-md-4">
                                        <label class="border p-2 rounded d-flex align-items-center gap-2 cursor-pointer w-100">
                                            <input type="checkbox" v-model="form.channels" value="push" class="form-check-input mt-0" />
                                            <span>🔔 {{ $t('marketing.channel_push') }}</span>
                                        </label>
                                    </div>
                                    <div class="col-sm-6 col-md-4">
                                        <label class="border p-2 rounded d-flex align-items-center gap-2 cursor-pointer w-100">
                                            <input type="checkbox" v-model="form.channels" value="whatsapp" class="form-check-input mt-0" />
                                            <span>🟢 {{ $t('marketing.channel_whatsapp') }}</span>
                                        </label>
                                    </div>
                                    <div class="col-sm-6 col-md-4">
                                        <label class="border p-2 rounded d-flex align-items-center gap-2 cursor-pointer w-100">
                                            <input type="checkbox" v-model="form.channels" value="sms" class="form-check-input mt-0" />
                                            <span>💬 {{ $t('marketing.channel_sms') }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Start & End Date -->
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold small">{{ $t('marketing.start_date') }}</label>
                                    <input
                                        v-model="form.start_date"
                                        type="datetime-local"
                                        class="form-control form-control-sm"
                                    />
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold small">{{ $t('marketing.end_date') }}</label>
                                    <input
                                        v-model="form.end_date"
                                        type="datetime-local"
                                        class="form-control form-control-sm"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mb-5">
                        <router-link to="/admin/marketing/campaigns" class="btn btn-outline-secondary">
                            {{ $t('common.cancel') }}
                        </router-link>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold" :disabled="saving">
                            <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                            {{ isEdit ? $t('common.update') : $t('marketing.save_campaign') }}
                        </button>
                    </div>

                </form>
            </div>

            <!-- Right: Interactive Live Promotional Preview (Amazon/Flipkart Style) -->
            <div class="col-lg-5">
                <div class="sticky-top" style="top: 20px;">
                    <div class="card border-0 shadow-sm overflow-hidden">
                        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-2 px-3">
                            <span class="small fw-bold">📱 {{ $t('marketing.live_preview') }}</span>
                            <span class="badge bg-danger">{{ $t('marketing.real_time_simulator') }}</span>
                        </div>

                        <!-- Preview Channel Switcher -->
                        <div class="bg-light p-2 border-bottom d-flex gap-1 overflow-x-auto">
                            <button
                                v-for="ch in previewChannels"
                                :key="ch.id"
                                type="button"
                                class="btn btn-sm"
                                :class="activePreviewChannel === ch.id ? 'btn-primary' : 'btn-outline-secondary'"
                                @click="activePreviewChannel = ch.id"
                            >
                                {{ ch.icon }} {{ ch.name }}
                            </button>
                        </div>

                        <div class="card-body p-3 bg-light-subtle d-flex justify-content-center">

                            <!-- 1. IN-APP PROMOTIONAL CARD PREVIEW (Flipkart/Amazon SuperCoin / Deal Card) -->
                            <div v-if="activePreviewChannel === 'in_app'" class="preview-in-app-card shadow-sm border rounded-4 bg-white overflow-hidden w-100" style="max-width: 360px;">
                                <!-- Top Deal Header -->
                                <div class="bg-gradient-primary text-white p-2 d-flex justify-content-between align-items-center">
                                    <span class="extra-small fw-bold text-uppercase tracking-wider">
                                        ⚡ {{ form.campaign_type.replace('_', ' ') }}
                                    </span>
                                    <span class="badge bg-warning text-dark font-monospace extra-small">
                                        ⏰ {{ $t('marketing.expires_soon') }}
                                    </span>
                                </div>

                                <!-- Banner Image -->
                                <div class="position-relative bg-light text-center" style="height: 140px;">
                                    <img
                                        v-if="form.banner_url || form.image_url"
                                        :src="form.banner_url || form.image_url"
                                        class="w-100 h-100 object-fit-cover"
                                        alt=""
                                    />
                                    <div v-else class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-muted">
                                        <span class="fs-1">🛍️</span>
                                        <span class="extra-small">{{ $t('marketing.preview_banner_placeholder') }}</span>
                                    </div>

                                    <!-- Floating Discount Tag -->
                                    <div v-if="discountBadge" class="position-absolute bottom-0 start-0 m-2 badge bg-danger fs-6 px-2 py-1 shadow">
                                        {{ discountBadge }}
                                    </div>
                                </div>

                                <!-- Content Body -->
                                <div class="p-3">
                                    <h6 class="fw-bold mb-1 text-dark">{{ form.title || $t('marketing.title_placeholder') }}</h6>
                                    <p class="small text-muted mb-2 line-clamp-2">
                                        {{ form.description || $t('marketing.desc_placeholder') }}
                                    </p>

                                    <!-- Coupon Chip -->
                                    <div v-if="selectedCouponCode" class="d-flex align-items-center justify-content-between p-2 rounded bg-light border border-dashed mb-3">
                                        <div class="extra-small text-muted">
                                            {{ $t('coupons.coupon_code') }}:
                                            <strong class="font-monospace text-primary ms-1">{{ selectedCouponCode }}</strong>
                                        </div>
                                        <span class="badge bg-primary-subtle text-primary extra-small">{{ $t('marketing.tap_to_copy') }}</span>
                                    </div>

                                    <!-- Action Button -->
                                    <button class="btn btn-primary btn-sm w-100 rounded-pill fw-semibold py-1">
                                        🛒 {{ $t('marketing.shop_now') }} →
                                    </button>
                                </div>
                            </div>

                            <!-- 2. WEB PUSH NOTIFICATION PREVIEW -->
                            <div v-else-if="activePreviewChannel === 'push'" class="preview-push-card shadow-sm border rounded-3 bg-white p-3 w-100" style="max-width: 360px;">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="rounded-circle bg-primary text-white p-1" style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 11px;">
                                        🛍️
                                    </span>
                                    <span class="extra-small fw-bold text-dark">Retail POS • {{ $t('marketing.now') }}</span>
                                    <span class="ms-auto extra-small text-muted">✕</span>
                                </div>
                                <div class="d-flex gap-2">
                                    <div class="flex-grow-1">
                                        <div class="fw-bold small text-dark mb-1">{{ form.title || 'Flash Deal Alert' }}</div>
                                        <div class="extra-small text-muted line-clamp-2">{{ form.description || 'Special limited time promotional savings.' }}</div>
                                    </div>
                                    <div v-if="form.image_url" class="flex-shrink-0">
                                        <img :src="form.image_url" class="rounded object-fit-cover" style="width: 50px; height: 50px;" alt="" />
                                    </div>
                                </div>
                                <div class="mt-2 pt-2 border-top d-flex justify-content-end gap-2">
                                    <button class="btn btn-outline-secondary btn-sm py-0 extra-small">{{ $t('marketing.dismiss') }}</button>
                                    <button class="btn btn-primary btn-sm py-0 extra-small">{{ $t('marketing.view_deal') }}</button>
                                </div>
                            </div>

                            <!-- 3. WHATSAPP BUSINESS MESSAGE PREVIEW -->
                            <div v-else-if="activePreviewChannel === 'whatsapp'" class="preview-wa-card shadow-sm rounded-4 overflow-hidden w-100" style="max-width: 340px; background-color: #e5ddd5;">
                                <div class="p-2 text-white d-flex align-items-center gap-2" style="background-color: #075e54;">
                                    <span class="fs-6">🟢</span>
                                    <span class="small fw-semibold">Retail Store Deals</span>
                                    <span class="ms-auto badge bg-success text-white extra-small">Verified</span>
                                </div>
                                <div class="p-3">
                                    <div class="bg-white p-3 rounded-3 shadow-sm border position-relative" style="max-width: 280px;">
                                        <img
                                            v-if="form.banner_url || form.image_url"
                                            :src="form.banner_url || form.image_url"
                                            class="rounded mb-2 w-100 object-fit-cover"
                                            style="height: 120px;"
                                            alt=""
                                        />
                                        <div class="fw-bold small text-dark mb-1">🔥 {{ form.title || 'Special Promotion' }}</div>
                                        <div class="extra-small text-muted mb-2">
                                            {{ form.description || 'Exclusive discounts for our shoppers.' }}
                                        </div>
                                        <div v-if="selectedCouponCode" class="p-1 rounded bg-light border small text-center mb-2 font-monospace fw-bold text-success">
                                            CODE: {{ selectedCouponCode }}
                                        </div>
                                        <div class="extra-small text-end text-muted">10:45 AM ✓✓</div>
                                    </div>
                                </div>
                            </div>

                            <!-- 4. SMS GATEWAY PREVIEW -->
                            <div v-else-if="activePreviewChannel === 'sms'" class="preview-sms-card shadow-sm border rounded-4 bg-white p-3 w-100" style="max-width: 340px;">
                                <div class="text-center extra-small text-muted mb-2">SMS • Today 11:00 AM</div>
                                <div class="bg-light p-3 rounded-4 border text-dark small">
                                    <strong>[RETAIL]</strong> {{ form.title || 'Special Offer' }}: {{ form.description || 'Enjoy exclusive savings today.' }}
                                    <span v-if="selectedCouponCode"> Use coupon code: {{ selectedCouponCode }}.</span>
                                    <div class="mt-1 text-primary">retailstore.local/shop</div>
                                </div>
                            </div>

                            <!-- 5. EMAIL NEWSLETTER PREVIEW -->
                            <div v-else class="preview-email-card shadow-sm border rounded-3 bg-white overflow-hidden w-100" style="max-width: 360px;">
                                <div class="p-2 border-bottom bg-light d-flex justify-content-between small text-muted">
                                    <span>From: Retail POS Deals</span>
                                    <span>Subject: {{ form.title || 'Exclusive Promotional Offer' }}</span>
                                </div>
                                <div class="p-3 text-center">
                                    <h6 class="fw-bold text-primary mb-2">🛍️ Smart Retail Store</h6>
                                    <div v-if="form.banner_url" class="mb-2">
                                        <img :src="form.banner_url" class="w-100 rounded object-fit-cover" style="height: 110px;" alt="" />
                                    </div>
                                    <h6 class="fw-bold text-dark">{{ form.title || 'Mega Flash Sale' }}</h6>
                                    <p class="extra-small text-muted mb-2">
                                        {{ form.description || 'Shop quality stationery and daily essentials at discounted prices.' }}
                                    </p>
                                    <div v-if="discountBadge" class="badge bg-danger mb-2">{{ discountBadge }}</div>
                                    <div>
                                        <button class="btn btn-primary btn-sm rounded-pill px-3 py-1 extra-small">
                                            Shop Collection Now
                                        </button>
                                    </div>
                                    <div class="extra-small text-muted mt-3 border-top pt-2">
                                        You are receiving this email as a registered shopper.
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useNotification } from '../../composables/useNotification';
import { useI18n } from 'vue-i18n';

const route = useRoute();
const router = useRouter();
const { t } = useI18n();
const { showSuccess, showError } = useNotification();

const isEdit = computed(() => Boolean(route.params.id));
const saving = ref(false);

const segments = ref([]);
const categories = ref([]);
const products = ref([]);
const coupons = ref([]);

const form = ref({
    title: '',
    title_ar: '',
    description: '',
    description_ar: '',
    banner_url: '',
    image_url: '',
    campaign_type: 'flash_sale',
    target_type: 'all',
    segment_id: null,
    channels: ['in_app', 'email', 'push'],
    discount_type: 'percentage',
    discount_value: 20,
    coupon_id: null,
    target_product_ids: [],
    target_category_ids: [],
    target_brand_ids: [],
    start_date: '',
    end_date: '',
    status: 'draft',
});

const previewChannels = [
    { id: 'in_app', name: 'In-App', icon: '📱' },
    { id: 'push', name: 'Web Push', icon: '🔔' },
    { id: 'whatsapp', name: 'WhatsApp', icon: '🟢' },
    { id: 'sms', name: 'SMS', icon: '💬' },
    { id: 'email', name: 'Email', icon: '✉️' },
];
const activePreviewChannel = ref('in_app');

const discountBadge = computed(() => {
    if (form.value.discount_type === 'percentage' && form.value.discount_value > 0) {
        return `${form.value.discount_value}% OFF`;
    }
    if (form.value.discount_type === 'fixed' && form.value.discount_value > 0) {
        return `FLAT ₹${form.value.discount_value}`;
    }
    return '';
});

const selectedCouponCode = computed(() => {
    if (!form.value.coupon_id) return '';
    const found = coupons.value.find(c => c.id === form.value.coupon_id);
    return found ? found.code : '';
});

async function loadDependencies() {
    try {
        const [segRes, catRes, prodRes, cpnRes] = await Promise.all([
            axios.get('/api/marketing/segments'),
            axios.get('/api/categories'),
            axios.get('/api/products?per_page=100'),
            axios.get('/api/coupons?per_page=100'),
        ]);

        segments.value = segRes.data;
        categories.value = catRes.data.data || catRes.data;
        products.value = prodRes.data.data || prodRes.data;
        coupons.value = cpnRes.data.data || cpnRes.data;
    } catch (err) {
        showError(t('marketing.error_loading_dependencies'));
    }
}

async function loadCampaign() {
    if (!isEdit.value) return;
    try {
        const res = await axios.get(`/api/marketing/campaigns/${route.params.id}`);
        const c = res.data;
        form.value = {
            title: c.title,
            title_ar: c.title_ar || '',
            description: c.description || '',
            description_ar: c.description_ar || '',
            banner_url: c.banner_url || '',
            image_url: c.image_url || '',
            campaign_type: c.campaign_type,
            target_type: c.target_type,
            segment_id: c.segment_id,
            channels: c.channels || ['in_app'],
            discount_type: c.discount_type,
            discount_value: c.discount_value,
            coupon_id: c.coupon_id,
            target_product_ids: c.target_product_ids || [],
            target_category_ids: c.target_category_ids || [],
            target_brand_ids: c.target_brand_ids || [],
            start_date: c.start_date ? c.start_date.substring(0, 16) : '',
            end_date: c.end_date ? c.end_date.substring(0, 16) : '',
            status: c.status,
        };
    } catch (err) {
        showError(t('marketing.error_loading_campaign'));
    }
}

async function handleSubmit() {
    if (!form.value.title.trim()) {
        showError(t('marketing.title_required'));
        return;
    }
    if (!form.value.channels || form.value.channels.length === 0) {
        showError(t('marketing.channel_required'));
        return;
    }

    saving.value = true;
    try {
        if (isEdit.value) {
            await axios.put(`/api/marketing/campaigns/${route.params.id}`, form.value);
            showSuccess(t('marketing.campaign_updated_success'));
        } else {
            await axios.post('/api/marketing/campaigns', form.value);
            showSuccess(t('marketing.campaign_created_success'));
        }
        router.push('/admin/marketing/campaigns');
    } catch (err) {
        const msg = err.response?.data?.message || t('marketing.error_saving_campaign');
        showError(msg);
    } finally {
        saving.value = false;
    }
}

onMounted(async () => {
    await loadDependencies();
    await loadCampaign();
});
</script>

<style scoped>
.extra-small {
    font-size: 0.72rem;
}

.cursor-pointer {
    cursor: pointer;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #0d6efd 0%, #1e40af 100%);
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
