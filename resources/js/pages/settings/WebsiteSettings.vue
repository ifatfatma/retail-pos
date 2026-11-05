<template>
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
            <div>
                <h3 class="fw-bold mb-1">⚙️ Website CMS, Logo & SEO Settings</h3>
                <p class="text-muted mb-0">
                    Customize your public storefront branding, logo, homepage hero contents, footer details, and search engine optimization.
                </p>
            </div>

            <div class="d-flex gap-2">
                <a href="/" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3 d-flex align-items-center gap-1">
                    <span>🌐</span>
                    <span>Preview Storefront</span>
                </a>
                <button
                    class="btn btn-primary btn-sm rounded-pill px-4 fw-semibold shadow-sm d-flex align-items-center gap-2"
                    :disabled="saving"
                    @click="saveSettings"
                >
                    <span v-if="saving" class="spinner-border spinner-border-sm" role="status"></span>
                    <span>{{ saving ? 'Saving...' : '💾 Save Changes' }}</span>
                </button>
            </div>
        </div>

        <!-- Success and Error Alerts -->
        <div v-if="successMsg" class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <strong>Success:</strong> {{ successMsg }}
            <button type="button" class="btn-close" @click="successMsg = ''"></button>
        </div>

        <div v-if="errorMsg" class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <strong>Error:</strong> {{ errorMsg }}
            <button type="button" class="btn-close" @click="errorMsg = ''"></button>
        </div>

        <!-- Loading Spinner -->
        <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="text-muted mt-2">Loading settings...</p>
        </div>

        <!-- Main Settings Form Tabs -->
        <div v-else class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-5">
            <!-- Navigation Tabs -->
            <div class="card-header bg-light border-bottom p-0">
                <ul class="nav nav-tabs border-0 px-3 pt-2">
                    <li class="nav-item">
                        <button
                            class="nav-link fw-semibold px-4 py-3"
                            :class="{ active: activeTab === 'branding' }"
                            @click="activeTab = 'branding'"
                        >
                            🏷️ Branding & Logo
                        </button>
                    </li>
                    <li class="nav-item">
                        <button
                            class="nav-link fw-semibold px-4 py-3"
                            :class="{ active: activeTab === 'slideshow' }"
                            @click="activeTab = 'slideshow'"
                        >
                            🎠 Homepage Slideshow
                        </button>
                    </li>
                    <li class="nav-item">
                        <button
                            class="nav-link fw-semibold px-4 py-3"
                            :class="{ active: activeTab === 'testimonials' }"
                            @click="activeTab = 'testimonials'"
                        >
                            ⭐ Customer Reviews
                        </button>
                    </li>
                    <li class="nav-item">
                        <button
                            class="nav-link fw-semibold px-4 py-3"
                            :class="{ active: activeTab === 'hero' }"
                            @click="activeTab = 'hero'"
                        >
                            🚀 Homepage & Hero
                        </button>
                    </li>
                    <li class="nav-item">
                        <button
                            class="nav-link fw-semibold px-4 py-3"
                            :class="{ active: activeTab === 'contact' }"
                            @click="activeTab = 'contact'"
                        >
                            📞 Store Info & Footer
                        </button>
                    </li>
                    <li class="nav-item">
                        <button
                            class="nav-link fw-semibold px-4 py-3"
                            :class="{ active: activeTab === 'seo' }"
                            @click="activeTab = 'seo'"
                        >
                            🔍 SEO & Meta Tags
                        </button>
                    </li>
                    <li class="nav-item">
                        <button
                            class="nav-link fw-semibold px-4 py-3"
                            :class="{ active: activeTab === 'payments' }"
                            @click="activeTab = 'payments'"
                        >
                            📱 UPI & Payments
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4 p-md-5">
                <!-- 1. Branding & Logo Tab -->
                <div v-show="activeTab === 'branding'">
                    <h5 class="fw-bold mb-4 pb-2 border-bottom">Brand Identity & Announcement Bar</h5>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted text-uppercase">Store / Brand Name</label>
                            <input
                                v-model="form.site_name"
                                type="text"
                                class="form-control"
                                placeholder="e.g. Retail Store"
                            />
                            <div class="extra-small text-muted mt-1">Displayed in storefront navbar, footer, and browser title.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted text-uppercase">Store Tagline / Slogan</label>
                            <input
                                v-model="form.site_tagline"
                                type="text"
                                class="form-control"
                                placeholder="e.g. Your neighborhood store with live stock"
                            />
                        </div>

                        <!-- Logo Upload Section -->
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-muted text-uppercase">Store Logo</label>
                            <div class="p-3 border rounded-3 bg-light d-flex flex-wrap align-items-center gap-4">
                                <!-- Logo Preview Box -->
                                <div class="logo-preview-box bg-white border rounded-3 p-2 d-flex align-items-center justify-content-center">
                                    <img
                                        v-if="logoPreviewUrl || form.site_logo_url"
                                        :src="logoPreviewUrl || form.site_logo_url"
                                        alt="Logo Preview"
                                        class="logo-preview-img"
                                    />
                                    <div v-else class="text-center text-muted">
                                        <span class="fs-2">🛍️</span>
                                        <div class="extra-small">No Logo</div>
                                    </div>
                                </div>

                                <div class="flex-grow-1">
                                    <div class="mb-2">
                                        <input
                                            type="file"
                                            class="form-control form-control-sm"
                                            accept="image/*"
                                            @change="onLogoFileSelected"
                                        />
                                    </div>
                                    <div class="extra-small text-muted mb-2">
                                        Recommended format: PNG, SVG, JPG, WebP. Transparent background works best (Max 3MB).
                                    </div>
                                    <button
                                        v-if="form.site_logo_url || logoPreviewUrl"
                                        type="button"
                                        class="btn btn-outline-danger btn-sm py-0 px-2 extra-small"
                                        @click="removeLogo"
                                    >
                                        Remove Logo
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Announcement Bar -->
                        <div class="col-12 pt-3 border-top">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label small fw-semibold text-muted text-uppercase mb-0">Top Announcement Bar</label>
                                <div class="form-check form-switch">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        role="switch"
                                        id="announcementSwitch"
                                        v-model="announcementEnabledBool"
                                    />
                                    <label class="form-check-label small" for="announcementSwitch">
                                        {{ announcementEnabledBool ? 'Visible' : 'Hidden' }}
                                    </label>
                                </div>
                            </div>
                            <input
                                v-model="form.announcement_text"
                                type="text"
                                class="form-control"
                                placeholder="e.g. Free Delivery on orders over ₹500! | COD Available"
                                :disabled="!announcementEnabledBool"
                            />
                            <!-- Live Preview of Announcement Bar -->
                            <div v-if="announcementEnabledBool" class="mt-2 p-2 bg-dark text-white rounded text-center small">
                                <span>Preview: </span>
                                <span>{{ form.announcement_text || 'Free delivery on all orders!' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Homepage & Hero Section Tab -->
                <div v-show="activeTab === 'hero'">
                    <h5 class="fw-bold mb-4 pb-2 border-bottom">Homepage Hero Banner & Promotions</h5>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted text-uppercase">Hero Badge Label</label>
                            <input
                                v-model="form.hero_badge"
                                type="text"
                                class="form-control"
                                placeholder="⚡ LIVE INVENTORY & REAL-TIME DISPATCH"
                            />
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted text-uppercase">Hero Main Title (Line 1)</label>
                            <input
                                v-model="form.hero_title"
                                type="text"
                                class="form-control"
                                placeholder="Quality Retail Essentials"
                            />
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted text-uppercase">Hero Subtitle / Highlight (Line 2)</label>
                            <input
                                v-model="form.hero_subtitle"
                                type="text"
                                class="form-control"
                                placeholder="Delivered To Your Door"
                            />
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted text-uppercase">Side Promo Badge</label>
                            <input
                                v-model="form.hero_promo_badge"
                                type="text"
                                class="form-control"
                                placeholder="⭐ Best Seller Deals"
                            />
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold text-muted text-uppercase">Hero Description Paragraph</label>
                            <textarea
                                v-model="form.hero_description"
                                class="form-control"
                                rows="3"
                                placeholder="Explain your store's proposition..."
                            ></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted text-uppercase">Side Promo Box Title</label>
                            <input
                                v-model="form.hero_promo_title"
                                type="text"
                                class="form-control"
                                placeholder="Neighborhood Convenience"
                            />
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted text-uppercase">Side Promo Box Description</label>
                            <input
                                v-model="form.hero_promo_text"
                                type="text"
                                class="form-control"
                                placeholder="Browse our stock catalog, place orders 24/7..."
                            />
                        </div>

                        <!-- Live Hero Banner Preview Box -->
                        <div class="col-12 pt-3 border-top">
                            <label class="form-label small fw-semibold text-muted text-uppercase mb-2">Live Hero Banner Preview</label>
                            <div class="p-4 rounded-4 text-white shadow-sm" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                                <span class="badge bg-warning text-dark px-2 py-1 rounded-pill small fw-bold mb-2">
                                    {{ form.hero_badge || 'PROMOTION' }}
                                </span>
                                <h3 class="fw-bold mb-1">
                                    {{ form.hero_title }} <br>
                                    <span class="text-warning">{{ form.hero_subtitle }}</span>
                                </h3>
                                <p class="text-white-50 small mb-0" style="max-width: 500px;">
                                    {{ form.hero_description }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slideshow Management Tab -->
                <div v-show="activeTab === 'slideshow'">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom gap-2">
                        <div>
                            <h5 class="fw-bold mb-1">🎠 Homepage Slideshow & Banner Carousel</h5>
                            <p class="text-muted small mb-0">Create, customize, and arrange full-width rotating promotional banners for the front page.</p>
                        </div>
                        <button
                            type="button"
                            class="btn btn-success btn-sm rounded-pill px-3 fw-bold d-flex align-items-center gap-1 shadow-sm"
                            @click="addNewSlide"
                        >
                            <span>➕ Add New Slide</span>
                        </button>
                    </div>

                    <!-- Global Slideshow Controls Card -->
                    <div class="card border-0 bg-light rounded-4 p-3 p-md-4 mb-4">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-4">
                                <div class="form-check form-switch mb-0">
                                    <input
                                        id="enableSlideshowSwitch"
                                        v-model="slideshowEnabledBool"
                                        class="form-check-input"
                                        type="checkbox"
                                    />
                                    <label class="form-check-label fw-bold small text-dark" for="enableSlideshowSwitch">
                                        Enable Slideshow on Homepage
                                    </label>
                                </div>
                                <div class="extra-small text-muted ps-5">Replaces static hero with animated slides</div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-check form-switch mb-0">
                                    <input
                                        id="enableAutoplaySwitch"
                                        v-model="slideshowAutoplayBool"
                                        class="form-check-input"
                                        type="checkbox"
                                    />
                                    <label class="form-check-label fw-bold small text-dark" for="enableAutoplaySwitch">
                                        Autoplay Slides
                                    </label>
                                </div>
                                <div class="extra-small text-muted ps-5">Automatically advances to next slide</div>
                            </div>

                            <div class="col-md-4">
                                <div class="d-flex align-items-center gap-2">
                                    <label class="form-label small fw-semibold text-muted text-nowrap mb-0">Slide Speed:</label>
                                    <select v-model="form.slideshow_speed" class="form-select form-select-sm">
                                        <option value="3000">3 seconds (Fast)</option>
                                        <option value="5000">5 seconds (Standard)</option>
                                        <option value="7000">7 seconds (Relaxed)</option>
                                        <option value="10000">10 seconds (Slow)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty Slides State -->
                    <div v-if="slidesList.length === 0" class="text-center py-5 border rounded-4 bg-light-subtle">
                        <span class="fs-1">🎠</span>
                        <h6 class="fw-bold mt-2">No Slides Configured</h6>
                        <p class="text-muted small">Add your first promotional slide banner to showcase on the front page.</p>
                        <button type="button" class="btn btn-primary btn-sm rounded-pill px-4" @click="addNewSlide">
                            ➕ Create Slide 1
                        </button>
                    </div>

                    <!-- Slides List Cards -->
                    <div v-else class="d-flex flex-column gap-4 mb-4">
                        <div
                            v-for="(slide, sIdx) in slidesList"
                            :key="slide.id || sIdx"
                            class="card border rounded-4 overflow-hidden shadow-sm"
                            :class="{ 'opacity-75 bg-light': !slide.active }"
                        >
                            <!-- Slide Header Toolbar -->
                            <div class="card-header bg-white py-3 px-4 d-flex flex-wrap justify-content-between align-items-center border-bottom gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-dark rounded-pill px-3 py-1">Slide #{{ sIdx + 1 }}</span>
                                    <span class="fw-bold text-dark text-truncate" style="max-width: 300px;">{{ slide.title || 'Untitled Slide' }}</span>
                                    <span v-if="!slide.active" class="badge bg-secondary-subtle text-secondary small">Inactive</span>
                                </div>

                                <div class="d-flex align-items-center gap-2">
                                    <!-- Toggle Active Switch -->
                                    <div class="form-check form-switch mb-0 me-2">
                                        <input
                                            :id="`slideActive_${sIdx}`"
                                            v-model="slide.active"
                                            class="form-check-input"
                                            type="checkbox"
                                        />
                                        <label class="form-check-label extra-small fw-semibold" :for="`slideActive_${sIdx}`">
                                            {{ slide.active ? 'Active' : 'Disabled' }}
                                        </label>
                                    </div>

                                    <!-- Reorder Buttons -->
                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary btn-sm px-2 py-0"
                                        :disabled="sIdx === 0"
                                        title="Move Up"
                                        @click="moveSlide(sIdx, -1)"
                                    >
                                        ▲
                                    </button>
                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary btn-sm px-2 py-0"
                                        :disabled="sIdx === slidesList.length - 1"
                                        title="Move Down"
                                        @click="moveSlide(sIdx, 1)"
                                    >
                                        ▼
                                    </button>

                                    <!-- Delete Slide -->
                                    <button
                                        type="button"
                                        class="btn btn-outline-danger btn-sm px-2 py-0 ms-1"
                                        title="Delete Slide"
                                        @click="deleteSlide(sIdx)"
                                    >
                                        🗑️
                                    </button>
                                </div>
                            </div>

                            <div class="card-body p-4">
                                <div class="row g-3">
                                    <!-- Badge & Gradient Theme -->
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted text-uppercase">Slide Badge Text</label>
                                        <input
                                            v-model="slide.badge"
                                            type="text"
                                            class="form-control"
                                            placeholder="e.g. 🔥 FESTIVE SALE • UP TO 40% OFF"
                                        />
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted text-uppercase">Background Theme Gradient</label>
                                        <select v-model="slide.bg_theme" class="form-select">
                                            <option value="blue-gradient">🔵 Royal Indigo & Blue Gradient</option>
                                            <option value="dark-gradient">⚫ Luxury Dark Titanium Gradient</option>
                                            <option value="purple-gradient">🟣 Vibrant Violet & Magenta Gradient</option>
                                            <option value="emerald-gradient">🟢 Forest Emerald Green Gradient</option>
                                            <option value="coral-gradient">🟠 Sunset Amber & Coral Gradient</option>
                                            <option value="custom-image">🖼️ Custom Banner Image Overlay</option>
                                        </select>
                                    </div>

                                    <!-- Title & Subtitle -->
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold text-muted text-uppercase">Slide Headline Title *</label>
                                        <input
                                            v-model="slide.title"
                                            type="text"
                                            class="form-control fw-bold"
                                            placeholder="e.g. Premium Stationery & Office Essentials"
                                            required
                                        />
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label small fw-semibold text-muted text-uppercase">Slide Description / Subtitle</label>
                                        <textarea
                                            v-model="slide.subtitle"
                                            class="form-control"
                                            rows="2"
                                            placeholder="Enter descriptive promotional text for this slide..."
                                        ></textarea>
                                    </div>

                                    <!-- Primary Button -->
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted text-uppercase">Primary Button Label</label>
                                        <input
                                            v-model="slide.primary_btn_text"
                                            type="text"
                                            class="form-control"
                                            placeholder="e.g. Shop Collection →"
                                        />
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted text-uppercase">Primary Button Link URL</label>
                                        <input
                                            v-model="slide.primary_btn_link"
                                            type="text"
                                            class="form-control"
                                            placeholder="e.g. /shop or /shop?category=electronics"
                                        />
                                    </div>

                                    <!-- Secondary Button -->
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted text-uppercase">Secondary Button Label</label>
                                        <input
                                            v-model="slide.secondary_btn_text"
                                            type="text"
                                            class="form-control"
                                            placeholder="e.g. Explore Categories"
                                        />
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted text-uppercase">Secondary Button Link URL</label>
                                        <input
                                            v-model="slide.secondary_btn_link"
                                            type="text"
                                            class="form-control"
                                            placeholder="e.g. /shop or /track-order"
                                        />
                                    </div>

                                    <!-- Optional Slide Image -->
                                    <div class="col-12 pt-2 border-top">
                                        <label class="form-label small fw-semibold text-muted text-uppercase">Slide Showcase Image / Banner (Optional)</label>
                                        <div class="input-group">
                                            <input
                                                v-model="slide.image_url"
                                                type="text"
                                                class="form-control"
                                                placeholder="Enter image URL or upload image below..."
                                            />
                                            <input
                                                type="file"
                                                class="d-none"
                                                :ref="el => fileInputs[sIdx] = el"
                                                accept="image/*"
                                                @change="onSlideImageUpload($event, sIdx)"
                                            />
                                            <button
                                                type="button"
                                                class="btn btn-outline-secondary"
                                                @click="fileInputs[sIdx]?.click()"
                                            >
                                                📁 Upload File
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Testimonials Management Tab -->
                <div v-show="activeTab === 'testimonials'">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom gap-2">
                        <div>
                            <h5 class="fw-bold mb-1">⭐ Customer Reviews & Testimonials</h5>
                            <p class="text-muted small mb-0">Manage customer ratings, feedback, and social proof displayed on the storefront home page.</p>
                        </div>
                        <button
                            type="button"
                            class="btn btn-success btn-sm rounded-pill px-3 fw-bold d-flex align-items-center gap-1 shadow-sm"
                            @click="addNewTestimonial"
                        >
                            <span>➕ Add Testimonial</span>
                        </button>
                    </div>

                    <!-- Global Testimonials Controls Card -->
                    <div class="card border-0 bg-light rounded-4 p-3 p-md-4 mb-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-check form-switch mb-2">
                                    <input
                                        id="enableTestimonialsSwitch"
                                        v-model="testimonialsEnabledBool"
                                        class="form-check-input"
                                        type="checkbox"
                                    />
                                    <label class="form-check-label fw-bold small text-dark" for="enableTestimonialsSwitch">
                                        Enable Customer Testimonials on Homepage
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted text-uppercase">Section Badge Tag</label>
                                <input
                                    v-model="form.testimonials_badge"
                                    type="text"
                                    class="form-control form-control-sm"
                                    placeholder="⭐ VERIFIED CUSTOMER REVIEWS"
                                />
                            </div>

                            <div class="col-md-8">
                                <label class="form-label small fw-semibold text-muted text-uppercase">Section Heading Title</label>
                                <input
                                    v-model="form.testimonials_title"
                                    type="text"
                                    class="form-control form-control-sm fw-bold"
                                    placeholder="What Our Customers Say"
                                />
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-semibold text-muted text-uppercase">Section Subtitle / Description</label>
                                <input
                                    v-model="form.testimonials_subtitle"
                                    type="text"
                                    class="form-control form-control-sm"
                                    placeholder="Trusted by hundreds of happy shoppers for fast delivery..."
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Empty Testimonials State -->
                    <div v-if="testimonialsList.length === 0" class="text-center py-5 border rounded-4 bg-light-subtle">
                        <span class="fs-1">⭐</span>
                        <h6 class="fw-bold mt-2">No Testimonials Added</h6>
                        <p class="text-muted small">Add your first customer review to display on the storefront home page.</p>
                        <button type="button" class="btn btn-primary btn-sm rounded-pill px-4" @click="addNewTestimonial">
                            ➕ Add Review 1
                        </button>
                    </div>

                    <!-- Testimonials Cards Grid -->
                    <div v-else class="row g-4 mb-4">
                        <div
                            v-for="(t, tIdx) in testimonialsList"
                            :key="t.id || tIdx"
                            class="col-12 col-md-6"
                        >
                            <div class="card h-100 border rounded-4 overflow-hidden shadow-sm" :class="{ 'opacity-75 bg-light': !t.active }">
                                <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center border-bottom">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-warning text-dark rounded-pill">Review #{{ tIdx + 1 }}</span>
                                        <span class="fw-bold text-dark small">{{ t.name || 'Anonymous' }}</span>
                                    </div>

                                    <div class="d-flex align-items-center gap-2">
                                        <div class="form-check form-switch mb-0">
                                            <input
                                                :id="`tActive_${tIdx}`"
                                                v-model="t.active"
                                                class="form-check-input"
                                                type="checkbox"
                                            />
                                            <label class="form-check-label extra-small" :for="`tActive_${tIdx}`">
                                                {{ t.active ? 'Active' : 'Hidden' }}
                                            </label>
                                        </div>

                                        <button
                                            type="button"
                                            class="btn btn-outline-danger btn-sm p-1 px-2"
                                            title="Delete Review"
                                            @click="deleteTestimonial(tIdx)"
                                        >
                                            🗑️
                                        </button>
                                    </div>
                                </div>

                                <div class="card-body p-3">
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <label class="form-label extra-small fw-semibold text-muted text-uppercase mb-1">Customer Name *</label>
                                            <input
                                                v-model="t.name"
                                                type="text"
                                                class="form-control form-control-sm"
                                                placeholder="e.g. Pooja Verma"
                                                required
                                            />
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label extra-small fw-semibold text-muted text-uppercase mb-1">Location / Role</label>
                                            <input
                                                v-model="t.location"
                                                type="text"
                                                class="form-control form-control-sm"
                                                placeholder="e.g. Mumbai, Maharashtra"
                                            />
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label extra-small fw-semibold text-muted text-uppercase mb-1">Star Rating</label>
                                            <select v-model="t.rating" class="form-select form-select-sm">
                                                <option :value="5">⭐⭐⭐⭐⭐ 5 Stars</option>
                                                <option :value="4">⭐⭐⭐⭐ 4 Stars</option>
                                                <option :value="3">⭐⭐⭐ 3 Stars</option>
                                                <option :value="2">⭐⭐ 2 Stars</option>
                                                <option :value="1">⭐ 1 Star</option>
                                            </select>
                                        </div>

                                        <div class="col-md-5">
                                            <label class="form-label extra-small fw-semibold text-muted text-uppercase mb-1">Ordered Item Tag</label>
                                            <input
                                                v-model="t.purchased_item"
                                                type="text"
                                                class="form-control form-control-sm"
                                                placeholder="e.g. Pilot G2 Pens & Notebook"
                                            />
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label extra-small fw-semibold text-muted text-uppercase mb-1">Date Note</label>
                                            <input
                                                v-model="t.date"
                                                type="text"
                                                class="form-control form-control-sm"
                                                placeholder="e.g. 2 days ago"
                                            />
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label extra-small fw-semibold text-muted text-uppercase mb-1">Customer Review Quote *</label>
                                            <textarea
                                                v-model="t.comment"
                                                class="form-control form-control-sm"
                                                rows="2"
                                                placeholder="What did the customer say about the order or store?"
                                                required
                                            ></textarea>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-check mb-0">
                                                <input
                                                    :id="`verified_${tIdx}`"
                                                    v-model="t.verified"
                                                    class="form-check-input"
                                                    type="checkbox"
                                                />
                                                <label class="form-check-label extra-small text-muted" :for="`verified_${tIdx}`">
                                                    Mark as <strong>Verified Customer Purchase</strong>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Contact & Footer Tab -->
                <div v-show="activeTab === 'contact'">
                    <h5 class="fw-bold mb-4 pb-2 border-bottom">Contact Information & Store Footer</h5>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted text-uppercase">Physical Store Address</label>
                            <input
                                v-model="form.contact_address"
                                type="text"
                                class="form-control"
                                placeholder="42 Market Street, Commercial Hub, Mumbai"
                            />
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-muted text-uppercase">Support Phone Number</label>
                            <input
                                v-model="form.contact_phone"
                                type="text"
                                class="form-control"
                                placeholder="+91 98765 43210"
                            />
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-muted text-uppercase">Support Email</label>
                            <input
                                v-model="form.contact_email"
                                type="email"
                                class="form-control"
                                placeholder="support@retailstore.local"
                            />
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted text-uppercase">Store Hours & Timings</label>
                            <input
                                v-model="form.store_hours"
                                type="text"
                                class="form-control"
                                placeholder="Mon – Sat: 9:00 AM – 9:00 PM | Sun: 10:00 AM – 7:00 PM"
                            />
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted text-uppercase">Copyright Text</label>
                            <input
                                v-model="form.copyright_text"
                                type="text"
                                class="form-control"
                                placeholder="Retail POS & E-Commerce. All rights reserved."
                            />
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold text-muted text-uppercase">Footer About Us Summary</label>
                            <textarea
                                v-model="form.footer_about"
                                class="form-control"
                                rows="3"
                                placeholder="Your trusted neighborhood store, now delivering online..."
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- 4. SEO & Meta Tags Tab -->
                <div v-show="activeTab === 'seo'">
                    <h5 class="fw-bold mb-4 pb-2 border-bottom">Search Engine Optimization (SEO) & Social Meta</h5>

                    <div class="row g-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-semibold text-muted text-uppercase mb-0">Meta Title</label>
                                <span class="extra-small" :class="form.seo_meta_title?.length > 60 ? 'text-warning' : 'text-muted'">
                                    {{ form.seo_meta_title?.length || 0 }} / 60 characters
                                </span>
                            </div>
                            <input
                                v-model="form.seo_meta_title"
                                type="text"
                                class="form-control"
                                placeholder="e.g. Retail Store - Everyday Essentials with Fast Delivery"
                                maxlength="80"
                            />
                            <div class="extra-small text-muted mt-1">Appears as the clickable title in search engines (optimal: 50-60 characters).</div>
                        </div>

                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-semibold text-muted text-uppercase mb-0">Meta Description</label>
                                <span class="extra-small" :class="form.seo_meta_description?.length > 160 ? 'text-warning' : 'text-muted'">
                                    {{ form.seo_meta_description?.length || 0 }} / 160 characters
                                </span>
                            </div>
                            <textarea
                                v-model="form.seo_meta_description"
                                class="form-control"
                                rows="3"
                                placeholder="Brief summary of your store shown in search engine results..."
                                maxlength="200"
                            ></textarea>
                            <div class="extra-small text-muted mt-1">Appears below the title in search engine results (optimal: 140-160 characters).</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold text-muted text-uppercase">Meta Keywords</label>
                            <input
                                v-model="form.seo_meta_keywords"
                                type="text"
                                class="form-control"
                                placeholder="retail pos, online store, fast delivery, live inventory, books, electronics"
                            />
                            <div class="extra-small text-muted mt-1">Comma-separated terms to assist search engine indexing.</div>
                        </div>

                        <!-- Google Search Preview Snippet -->
                        <div class="col-12 pt-3 border-top">
                            <label class="form-label small fw-semibold text-muted text-uppercase mb-2">Google Search Snippet Preview</label>
                            <div class="card border p-3 bg-light rounded-3">
                                <div class="google-preview">
                                    <div class="text-truncate extra-small text-secondary mb-1">
                                        https://yourstore.com ›
                                    </div>
                                    <h6 class="text-primary fw-bold mb-1 text-truncate" style="cursor: pointer;">
                                        {{ form.seo_meta_title || 'Retail Online Store - Everyday Essentials' }}
                                    </h6>
                                    <p class="text-muted small mb-0">
                                        {{ form.seo_meta_description || 'Shop everyday essentials, books, electronics, and daily essentials online with real-time stock sync.' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5. UPI & POS Payments Tab -->
                <div v-show="activeTab === 'payments'">
                    <h5 class="fw-bold mb-4 pb-2 border-bottom">UPI / QR Payment Configuration for POS Counter</h5>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted text-uppercase">Store UPI VPA / ID</label>
                            <input
                                v-model="form.upi_id"
                                type="text"
                                class="form-control"
                                placeholder="e.g. smartstationery@okhdfcbank or 9876543210@paytm"
                            />
                            <div class="extra-small text-muted mt-1">
                                Enter the UPI ID linked to your merchant bank account or soundbox (e.g. Google Pay, PhonePe, Paytm, BHIM).
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted text-uppercase">Payee / Merchant Display Name</label>
                            <input
                                v-model="form.upi_merchant_name"
                                type="text"
                                class="form-control"
                                placeholder="e.g. Smart Stationery Store"
                            />
                            <div class="extra-small text-muted mt-1">
                                Name displayed on the customer's payment screen when they scan your QR code.
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="upiEnabledSwitch"
                                    :checked="form.upi_enabled === '1' || form.upi_enabled === true"
                                    @change="(e) => form.upi_enabled = e.target.checked ? '1' : '0'"
                                />
                                <label class="form-check-label fw-semibold" for="upiEnabledSwitch">
                                    Enable Live Dynamic UPI QR Code Generation on POS Terminal
                                </label>
                            </div>
                            <div class="extra-small text-muted ms-4">
                                Automatically embeds the exact payable bill amount into the QR code so customers never have to type amounts manually.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Save Action Bar -->
                <div class="pt-4 mt-4 border-top d-flex justify-content-between align-items-center">
                    <span class="extra-small text-muted">All updates take effect across the storefront instantly.</span>
                    <button
                        type="button"
                        class="btn btn-primary px-4 rounded-pill fw-bold shadow-sm d-flex align-items-center gap-2"
                        :disabled="saving"
                        @click="saveSettings"
                    >
                        <span v-if="saving" class="spinner-border spinner-border-sm" role="status"></span>
                        <span>{{ saving ? 'Saving Changes...' : 'Save All Settings' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { useShopSettings } from '../../composables/useShopSettings';

const { loadSettings: reloadStoreSettings } = useShopSettings();

const activeTab = ref('branding');
const loading = ref(true);
const saving = ref(false);
const successMsg = ref('');
const errorMsg = ref('');

const selectedLogoFile = ref(null);
const logoPreviewUrl = ref(null);
const removeLogoFlag = ref(false);

const form = ref({
    site_name: '',
    site_tagline: '',
    site_logo: null,
    site_logo_url: null,
    announcement_text: '',
    announcement_enabled: '1',

    hero_badge: '',
    hero_title: '',
    hero_subtitle: '',
    hero_description: '',
    hero_promo_badge: '',
    hero_promo_title: '',
    hero_promo_text: '',

    contact_address: '',
    contact_phone: '',
    contact_email: '',
    store_hours: '',
    footer_about: '',
    copyright_text: '',

    seo_meta_title: '',
    seo_meta_description: '',
    seo_meta_keywords: '',

    slideshow_enabled: '1',
    slideshow_autoplay: '1',
    slideshow_speed: '5000',
    slideshow_slides: '',

    testimonials_enabled: '1',
    testimonials_title: '',
    testimonials_subtitle: '',
    testimonials_badge: '',
    testimonials_list: '',

    upi_id: 'smartstationery@upi',
    upi_merchant_name: 'Smart Stationery Store',
    upi_enabled: '1',
});

const slidesList = ref([]);
const fileInputs = ref({});

const testimonialsList = ref([]);

const testimonialsEnabledBool = computed({
    get: () => form.value.testimonials_enabled === '1' || form.value.testimonials_enabled === true,
    set: (val) => {
        form.value.testimonials_enabled = val ? '1' : '0';
    }
});

function addNewTestimonial() {
    testimonialsList.value.push({
        id: Date.now(),
        name: 'New Customer',
        location: 'Mumbai, Maharashtra',
        rating: 5,
        comment: 'Great quality products, reliable fast delivery, and hassle-free ordering experience!',
        purchased_item: '',
        verified: true,
        date: 'Recent',
        active: true,
    });
}

function deleteTestimonial(idx) {
    testimonialsList.value.splice(idx, 1);
}

const slideshowEnabledBool = computed({
    get: () => form.value.slideshow_enabled === '1' || form.value.slideshow_enabled === true,
    set: (val) => {
        form.value.slideshow_enabled = val ? '1' : '0';
    }
});

const slideshowAutoplayBool = computed({
    get: () => form.value.slideshow_autoplay === '1' || form.value.slideshow_autoplay === true,
    set: (val) => {
        form.value.slideshow_autoplay = val ? '1' : '0';
    }
});

function addNewSlide() {
    slidesList.value.push({
        id: Date.now(),
        badge: '⚡ SPECIAL OFFER',
        title: 'New Featured Promotion',
        subtitle: 'Describe your promotional offer, new seasonal arrival, or exclusive deal.',
        primary_btn_text: 'Shop Now →',
        primary_btn_link: '/shop',
        secondary_btn_text: 'Explore Categories',
        secondary_btn_link: '/shop',
        bg_theme: 'blue-gradient',
        image_url: '',
        active: true,
    });
}

function deleteSlide(idx) {
    slidesList.value.splice(idx, 1);
}

function moveSlide(idx, delta) {
    const targetIdx = idx + delta;
    if (targetIdx < 0 || targetIdx >= slidesList.value.length) return;
    const temp = slidesList.value[idx];
    slidesList.value[idx] = slidesList.value[targetIdx];
    slidesList.value[targetIdx] = temp;
}

async function onSlideImageUpload(event, sIdx) {
    const file = event.target.files[0];
    if (!file) return;

    const fd = new FormData();
    fd.append('image', file);

    try {
        const res = await axios.post('/api/settings/slide-image', fd, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        if (res.data?.url) {
            slidesList.value[sIdx].image_url = res.data.url;
            successMsg.value = `Image uploaded for Slide #${sIdx + 1}!`;
        }
    } catch (err) {
        errorMsg.value = err.response?.data?.message || 'Failed to upload slide image.';
    }
}

const announcementEnabledBool = computed({
    get: () => form.value.announcement_enabled === '1' || form.value.announcement_enabled === true,
    set: (val) => {
        form.value.announcement_enabled = val ? '1' : '0';
    }
});

function onLogoFileSelected(e) {
    const file = e.target.files[0];
    if (file) {
        selectedLogoFile.value = file;
        logoPreviewUrl.value = URL.createObjectURL(file);
        removeLogoFlag.value = false;
    }
}

function removeLogo() {
    selectedLogoFile.value = null;
    logoPreviewUrl.value = null;
    form.value.site_logo_url = null;
    removeLogoFlag.value = true;
}

async function fetchSettings() {
    loading.value = true;
    try {
        const res = await axios.get('/api/settings');
        if (res.data?.data) {
            form.value = { ...form.value, ...res.data.data };

            if (res.data.data.slideshow_slides_list && res.data.data.slideshow_slides_list.length > 0) {
                slidesList.value = res.data.data.slideshow_slides_list;
            } else if (res.data.data.slideshow_slides) {
                try {
                    slidesList.value = typeof res.data.data.slideshow_slides === 'string'
                        ? JSON.parse(res.data.data.slideshow_slides)
                        : res.data.data.slideshow_slides;
                } catch (e) {
                    slidesList.value = [];
                }
            }

            if (res.data.data.testimonials_list_items && res.data.data.testimonials_list_items.length > 0) {
                testimonialsList.value = res.data.data.testimonials_list_items;
            } else if (res.data.data.testimonials_list) {
                try {
                    testimonialsList.value = typeof res.data.data.testimonials_list === 'string'
                        ? JSON.parse(res.data.data.testimonials_list)
                        : res.data.data.testimonials_list;
                } catch (e) {
                    testimonialsList.value = [];
                }
            }
        }
    } catch (e) {
        console.error('Failed to fetch settings:', e);
        errorMsg.value = 'Failed to load website settings.';
    } finally {
        loading.value = false;
    }
}

async function saveSettings() {
    saving.value = true;
    successMsg.value = '';
    errorMsg.value = '';

    try {
        const formData = new FormData();

        // Sync slides & testimonials list to JSON
        form.value.slideshow_slides = JSON.stringify(slidesList.value);
        form.value.testimonials_list = JSON.stringify(testimonialsList.value);

        // Append logo file if selected
        if (selectedLogoFile.value) {
            formData.append('site_logo', selectedLogoFile.value);
        }

        if (removeLogoFlag.value) {
            formData.append('remove_logo', '1');
        }

        // Append all text settings
        Object.keys(form.value).forEach((key) => {
            if (key !== 'site_logo' && key !== 'site_logo_url' && key !== 'slideshow_slides_list' && key !== 'testimonials_list_items') {
                formData.append(key, form.value[key] !== null ? form.value[key] : '');
            }
        });

        const res = await axios.post('/api/settings', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        if (res.data?.data) {
            form.value = { ...form.value, ...res.data.data };
            selectedLogoFile.value = null;
            logoPreviewUrl.value = null;
            removeLogoFlag.value = false;
        }

        successMsg.value = res.data.message || 'Website settings saved successfully!';

        // Reload shared settings cache so preview & shop update immediately
        await reloadStoreSettings(true);
    } catch (e) {
        console.error('Failed to save settings:', e);
        errorMsg.value = e.response?.data?.message || 'Failed to save settings. Please review errors.';
    } finally {
        saving.value = false;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

onMounted(() => {
    fetchSettings();
});
</script>

<style scoped>
.extra-small {
    font-size: 0.75rem;
}

.logo-preview-box {
    width: 140px;
    height: 70px;
}

.logo-preview-img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.google-preview {
    max-width: 600px;
    font-family: Arial, sans-serif;
}
</style>

