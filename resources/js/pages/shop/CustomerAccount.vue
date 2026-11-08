<template>
    <div class="customer-account py-4">
        <div class="container">
            <!-- Top Customer Header -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="customer-avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fs-3 fw-bold shadow-sm">
                            {{ customerInitials }}
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0 text-dark">{{ customer?.name }}</h4>
                            <div class="text-muted small">
                                <span>📱 {{ customer?.phone }}</span>
                                <span class="mx-2">|</span>
                                <span>✉️ {{ customer?.email }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <router-link to="/shop" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                            🛍️ {{ $t('storefront.catalog') }}
                        </router-link>
                        <button class="btn btn-outline-danger btn-sm rounded-pill px-3" @click="handleSignOut">
                            🚪 {{ $t('nav.logout') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tab Navigation & Content Row -->
            <div class="row g-4">
                <!-- Sidebar Tabs Navigation -->
                <div class="col-lg-3">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                        <div class="nav flex-column nav-pills gap-1">
                            <button
                                class="nav-link text-start rounded-pill py-2 px-3 fw-semibold d-flex align-items-center gap-2"
                                :class="{ active: activeTab === 'overview' }"
                                @click="activeTab = 'overview'"
                            >
                                <span>📊</span>
                                <span>{{ $t('account.tab_overview') }}</span>
                            </button>
                            <button
                                class="nav-link text-start rounded-pill py-2 px-3 fw-semibold d-flex align-items-center gap-2"
                                :class="{ active: activeTab === 'orders' }"
                                @click="switchToOrders"
                            >
                                <span>📦</span>
                                <span>{{ $t('account.tab_orders') }}</span>
                                <span class="badge bg-secondary rounded-pill ms-auto small" v-if="customer?.total_orders">
                                    {{ customer.total_orders }}
                                </span>
                            </button>
                            <button
                                class="nav-link text-start rounded-pill py-2 px-3 fw-semibold d-flex align-items-center gap-2"
                                :class="{ active: activeTab === 'wishlist' }"
                                @click="switchToWishlist"
                            >
                                <span>❤️</span>
                                <span>{{ $t('account.tab_wishlist') }}</span>
                                <span class="badge bg-danger rounded-pill ms-auto small" v-if="wishlistCount">
                                    {{ wishlistCount }}
                                </span>
                            </button>
                            <button
                                class="nav-link text-start rounded-pill py-2 px-3 fw-semibold d-flex align-items-center gap-2"
                                :class="{ active: activeTab === 'profile' }"
                                @click="activeTab = 'profile'"
                            >
                                <span>👤</span>
                                <span>{{ $t('account.tab_profile') }}</span>
                            </button>
                            <button
                                class="nav-link text-start rounded-pill py-2 px-3 fw-semibold d-flex align-items-center gap-2"
                                :class="{ active: activeTab === 'security' }"
                                @click="activeTab = 'security'"
                            >
                                <span>🔒</span>
                                <span>{{ $t('account.tab_security') }}</span>
                            </button>
                            <button
                                class="nav-link text-start rounded-pill py-2 px-3 fw-semibold d-flex align-items-center gap-2"
                                :class="{ active: activeTab === 'notifications' }"
                                @click="switchToNotifications"
                            >
                                <span>🔔</span>
                                <span>{{ $t('account.tab_notifications') }}</span>
                                <span class="badge bg-danger rounded-pill ms-auto small" v-if="unreadNotificationsCount > 0">
                                    {{ unreadNotificationsCount }}
                                </span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="col-lg-9">
                    <!-- Tab 1: Overview -->
                    <div v-show="activeTab === 'overview'">
                        <div class="row g-3 mb-4">
                            <div class="col-sm-6 col-md-4">
                                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                                    <div class="text-muted extra-small text-uppercase fw-semibold mb-1">{{ $t('account.total_purchases') }}</div>
                                    <h3 class="fw-bold text-primary mb-0">{{ customer?.total_orders || 0 }}</h3>
                                    <div class="extra-small text-muted mt-1">{{ $t('account.orders_placed') }}</div>
                                </div>
                            </div>

                            <div class="col-sm-6 col-md-4">
                                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                                    <div class="text-muted extra-small text-uppercase fw-semibold mb-1">{{ $t('account.total_spent') }}</div>
                                    <h3 class="fw-bold text-success mb-0">₹{{ formatMoney(customer?.total_spent) }}</h3>
                                    <div class="extra-small text-muted mt-1">{{ $t('account.lifetime_spending') }}</div>
                                </div>
                            </div>

                            <div class="col-sm-12 col-md-4">
                                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                                    <div class="text-muted extra-small text-uppercase fw-semibold mb-1">{{ $t('account.default_address') }}</div>
                                    <div class="small text-truncate-2 mb-0">
                                        {{ customer?.address ? `${customer.address}, ${customer.city || ''} ${customer.pincode || ''}` : $t('account.no_address_saved') }}
                                    </div>
                                    <button class="btn btn-link p-0 extra-small text-primary text-decoration-none mt-1" @click="activeTab = 'profile'">
                                        {{ $t('account.edit_address') }} →
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Orders Preview -->
                        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold mb-0">{{ $t('account.recent_order_activity') }}</h5>
                                <button class="btn btn-link btn-sm text-decoration-none text-primary" @click="switchToOrders">
                                    {{ $t('account.view_all_orders') }} →
                                </button>
                            </div>

                            <div v-if="recentOrdersLoading" class="text-center py-4">
                                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                            </div>

                            <div v-else-if="orders.length === 0" class="text-center py-4 text-muted">
                                <div class="fs-2 mb-1">🛍️</div>
                                <div class="small">{{ $t('account.no_orders_yet') }}</div>
                                <router-link to="/shop" class="btn btn-primary btn-sm rounded-pill mt-2">
                                    {{ $t('account.start_shopping') }}
                                </router-link>
                            </div>

                            <div v-else class="list-group list-group-flush">
                                <div
                                    v-for="order in orders.slice(0, 3)"
                                    :key="order.id"
                                    class="list-group-item px-0 py-3 d-flex flex-wrap justify-content-between align-items-center gap-2"
                                >
                                    <div>
                                        <div class="fw-bold text-dark">{{ order.order_number }}</div>
                                        <div class="extra-small text-muted">{{ order.sale_date }} | {{ order.items_count }} item(s)</div>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge rounded-pill text-uppercase px-2 py-1 me-2" :class="getStatusBadgeClass(order.status)">
                                            {{ order.status }}
                                        </span>
                                        <span class="fw-bold text-primary">₹{{ formatMoney(order.total_amount) }}</span>
                                    </div>
                                    <div>
                                        <router-link :to="{ path: '/track-order', query: { order_number: order.order_number } }" class="btn btn-outline-primary btn-sm rounded-pill py-0 px-2 extra-small">
                                            {{ $t('common.track') }}
                                        </router-link>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Wishlist Overview Preview -->
                        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fs-5">❤️</span>
                                    <h5 class="fw-bold mb-0">{{ $t('storefront.saved_in_wishlist') }}</h5>
                                    <span class="badge bg-danger-subtle text-danger rounded-pill">{{ wishlistCount }}</span>
                                </div>
                                <button class="btn btn-link btn-sm text-decoration-none text-primary" @click="switchToWishlist">
                                    {{ $t('account.view_wishlist_tab') }} →
                                </button>
                            </div>

                            <div v-if="wishlistCount === 0" class="text-center py-4 text-muted">
                                <div class="fs-2 mb-1">🤍</div>
                                <div class="small">{{ $t('account.no_wishlist_yet') }}</div>
                                <router-link to="/shop" class="btn btn-outline-primary btn-sm rounded-pill mt-2">
                                    {{ $t('account.browse_items') }}
                                </router-link>
                            </div>

                            <div v-else class="row g-3">
                                <div
                                    v-for="item in wishlistItems.slice(0, 4)"
                                    :key="item.id || item.product_id"
                                    class="col-6 col-md-3"
                                >
                                    <div class="p-2 border rounded-3 text-center h-100 d-flex flex-column bg-light-subtle">
                                        <router-link :to="`/product/${item.product_id}`" class="text-decoration-none">
                                            <img
                                                v-if="item.image_url"
                                                :src="item.image_url"
                                                :alt="item.name"
                                                class="img-fluid rounded mb-2"
                                                style="height: 80px; object-fit: contain;"
                                            />
                                            <div v-else class="rounded bg-white mb-2 d-flex align-items-center justify-content-center" style="height: 80px;">
                                                📦
                                            </div>
                                            <div class="extra-small fw-semibold text-dark text-truncate">{{ item.name }}</div>
                                        </router-link>
                                        <div class="mt-auto pt-2">
                                            <div class="small fw-bold text-primary">₹{{ formatMoney(item.selling_price) }}</div>
                                            <div class="d-flex justify-content-center gap-1 mt-1">
                                                <span v-if="item.in_stock" class="badge bg-success-subtle text-success extra-small">{{ $t('common.in_stock') }}</span>
                                                <span v-else class="badge bg-danger-subtle text-danger extra-small">{{ $t('common.out_of_stock') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 2: Orders List -->
                    <div v-show="activeTab === 'orders'">
                        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                            <h5 class="fw-bold mb-4">📦 {{ $t('account.all_my_orders') }}</h5>

                            <div v-if="ordersLoading" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="text-muted small mt-2">{{ $t('account.loading_orders') }}</p>
                            </div>

                            <div v-else-if="orders.length === 0" class="text-center py-5 text-muted">
                                <div class="fs-1 mb-2">📦</div>
                                <h6>{{ $t('account.no_orders_found') }}</h6>
                                <p class="small">{{ $t('account.browse_and_order') }}</p>
                                <router-link to="/shop" class="btn btn-primary rounded-pill px-4">
                                    {{ $t('account.explore_products') }}
                                </router-link>
                            </div>

                            <div v-else>
                                <div
                                    v-for="order in orders"
                                    :key="order.id"
                                    class="card border rounded-3 p-3 mb-3 bg-light-subtle"
                                >
                                    <div class="d-flex flex-wrap justify-content-between align-items-center pb-2 border-bottom mb-2 gap-2">
                                        <div>
                                            <span class="fw-bold text-primary fs-6">{{ order.order_number }}</span>
                                            <span class="text-muted extra-small ms-2">{{ order.sale_date }}</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge rounded-pill text-uppercase px-2 py-1" :class="getStatusBadgeClass(order.status)">
                                                {{ order.status }}
                                            </span>
                                            <span class="fw-bold text-dark fs-6">₹{{ formatMoney(order.total_amount) }}</span>
                                        </div>
                                    </div>

                                    <!-- Items snippet -->
                                    <div class="small mb-2">
                                        <div v-for="(it, idx) in order.items" :key="idx" class="d-flex justify-content-between text-secondary">
                                            <span>• {{ it.name }} (× {{ it.quantity }})</span>
                                            <span class="fw-medium">₹{{ formatMoney(it.total) }}</span>
                                        </div>
                                    </div>

                                    <!-- Tracking and Courier info if present -->
                                    <div class="d-flex flex-wrap justify-content-between align-items-center pt-2 border-top extra-small text-muted gap-2">
                                        <div>
                                            <span v-if="order.delivery_partner">{{ $t('account.courier') }}: <strong>{{ order.delivery_partner }}</strong> | </span>
                                            <span v-if="order.tracking_number">{{ $t('account.tracking_num') }}: <strong>{{ order.tracking_number }}</strong> | </span>
                                            <span>{{ $t('account.payment') }}: <strong class="text-uppercase">{{ order.payment_method }} ({{ order.payment_status }})</strong></span>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <router-link
                                                :to="{ path: '/track-order', query: { order_number: order.order_number } }"
                                                class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1"
                                            >
                                                📍 {{ $t('account.live_track') }}
                                            </router-link>
                                        </div>
                                    </div>
                                </div>

                                <!-- Orders Pagination -->
                                <div v-if="ordersPagination.last_page > 1" class="d-flex justify-content-center mt-4">
                                    <ul class="pagination pagination-sm mb-0">
                                        <li class="page-item" :class="{ disabled: ordersPagination.current_page <= 1 }">
                                            <button class="page-link" @click="changeOrdersPage(ordersPagination.current_page - 1)">« {{ $t('common.previous') }}</button>
                                        </li>
                                        <li
                                            v-for="p in ordersPagination.last_page"
                                            :key="p"
                                            class="page-item"
                                            :class="{ active: ordersPagination.current_page === p }"
                                        >
                                            <button class="page-link" @click="changeOrdersPage(p)">{{ p }}</button>
                                        </li>
                                        <li class="page-item" :class="{ disabled: ordersPagination.current_page >= ordersPagination.last_page }">
                                            <button class="page-link" @click="changeOrdersPage(ordersPagination.current_page + 1)">{{ $t('common.next') }} »</button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 3: Profile & Address -->
                    <div v-show="activeTab === 'profile'">
                        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                            <h5 class="fw-bold mb-4">👤 {{ $t('account.profile_shipping_address') }}</h5>

                            <form @submit.prevent="saveProfile">
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted text-uppercase">{{ $t('checkout.full_name') }} <span class="text-danger">*</span></label>
                                        <input
                                            v-model="profileForm.name"
                                            type="text"
                                            class="form-control"
                                            required
                                        />
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted text-uppercase">{{ $t('checkout.email') }} <span class="text-danger">*</span></label>
                                        <input
                                            v-model="profileForm.email"
                                            type="email"
                                            class="form-control"
                                            required
                                        />
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted text-uppercase">{{ $t('checkout.phone') }} <span class="text-danger">*</span></label>
                                        <input
                                            v-model="profileForm.phone"
                                            type="tel"
                                            class="form-control"
                                            required
                                        />
                                    </div>

                                    <!-- Address -->
                                    <div class="col-12 pt-3 border-top">
                                        <div class="fw-semibold small text-muted text-uppercase mb-2">{{ $t('account.default_shipping_address') }}</div>
                                        <label class="form-label small fw-semibold text-muted text-uppercase">{{ $t('checkout.delivery_address') }}</label>
                                        <textarea
                                            v-model="profileForm.address"
                                            class="form-control"
                                            rows="2"
                                            :placeholder="$t('checkout.delivery_address')"
                                        ></textarea>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold text-muted text-uppercase">{{ $t('checkout.city') }}</label>
                                        <input
                                            v-model="profileForm.city"
                                            type="text"
                                            class="form-control"
                                            :placeholder="$t('checkout.city')"
                                        />
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold text-muted text-uppercase">{{ $t('checkout.state') }}</label>
                                        <input
                                            v-model="profileForm.state"
                                            type="text"
                                            class="form-control"
                                            :placeholder="$t('checkout.state')"
                                        />
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold text-muted text-uppercase">{{ $t('checkout.pincode') }}</label>
                                        <input
                                            v-model="profileForm.pincode"
                                            type="text"
                                            class="form-control"
                                            :placeholder="$t('checkout.pincode')"
                                        />
                                    </div>
                                </div>

                                <div class="text-end">
                                    <button
                                        type="submit"
                                        class="btn btn-primary rounded-pill px-4 fw-semibold"
                                        :disabled="savingProfile"
                                    >
                                        <span v-if="savingProfile" class="spinner-border spinner-border-sm me-1" role="status"></span>
                                        {{ $t('profile.update_profile') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tab 4: Security -->
                    <div v-show="activeTab === 'security'">
                        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                            <h5 class="fw-bold mb-4">🔒 {{ $t('profile.security') }}</h5>

                            <form @submit.prevent="savePassword">
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted text-uppercase">{{ $t('profile.current_password') }}</label>
                                    <input
                                        v-model="passwordForm.current_password"
                                        type="password"
                                        class="form-control"
                                        :placeholder="$t('profile.current_password')"
                                        required
                                    />
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted text-uppercase">{{ $t('profile.new_password') }}</label>
                                        <input
                                            v-model="passwordForm.password"
                                            type="password"
                                            class="form-control"
                                            placeholder="••••••"
                                            required
                                            minlength="6"
                                        />
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted text-uppercase">{{ $t('profile.confirm_password') }}</label>
                                        <input
                                            v-model="passwordForm.password_confirmation"
                                            type="password"
                                            class="form-control"
                                            placeholder="••••••"
                                            required
                                            minlength="6"
                                        />
                                    </div>
                                </div>

                                <div class="text-end">
                                    <button
                                        type="submit"
                                        class="btn btn-warning rounded-pill px-4 fw-bold text-dark"
                                        :disabled="savingPassword"
                                    >
                                        <span v-if="savingPassword" class="spinner-border spinner-border-sm me-1" role="status"></span>
                                        {{ $t('profile.update_password') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tab 5: Wishlist & Save for Later -->
                    <div v-show="activeTab === 'wishlist'">
                        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                                <div>
                                    <h5 class="fw-bold mb-1">❤️ {{ $t('account.wishlist_and_saved') }}</h5>
                                    <div class="text-muted small">{{ $t('account.bookmarked_sub') }}</div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button
                                        v-if="wishlistCount > 0"
                                        class="btn btn-outline-primary btn-sm rounded-pill px-3"
                                        @click="moveAllToCart"
                                        :disabled="wishlistMeta.in_stock_count === 0"
                                    >
                                        🛒 {{ $t('wishlist.add_all_to_cart') }} ({{ wishlistMeta.in_stock_count }})
                                    </button>
                                    <router-link to="/wishlist" class="btn btn-primary btn-sm rounded-pill px-3">
                                        {{ $t('account.open_full_wishlist') }} →
                                    </router-link>
                                </div>
                            </div>

                            <!-- Dashboard KPI strip -->
                            <div class="row g-3 mb-4">
                                <div class="col-6 col-md-3">
                                    <div class="p-3 rounded-4 bg-light text-center h-100">
                                        <div class="text-muted extra-small text-uppercase fw-semibold mb-1">{{ $t('account.total_saved') }}</div>
                                        <div class="fs-4 fw-bold text-dark">{{ wishlistCount }}</div>
                                        <div class="extra-small text-muted">{{ $t('account.bookmarked_items') }}</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="p-3 rounded-4 bg-success-subtle text-center h-100">
                                        <div class="text-success extra-small text-uppercase fw-semibold mb-1">{{ $t('account.available_now') }}</div>
                                        <div class="fs-4 fw-bold text-success">{{ wishlistMeta.in_stock_count }}</div>
                                        <div class="extra-small text-muted">{{ $t('account.ready_to_purchase') }}</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="p-3 rounded-4 bg-danger-subtle text-center h-100">
                                        <div class="text-danger extra-small text-uppercase fw-semibold mb-1">{{ $t('common.out_of_stock') }}</div>
                                        <div class="fs-4 fw-bold text-danger">{{ wishlistMeta.out_of_stock_count }}</div>
                                        <div class="extra-small text-muted">{{ $t('account.awaiting_restock') }}</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="p-3 rounded-4 bg-warning-subtle text-center h-100">
                                        <div class="text-warning-emphasis extra-small text-uppercase fw-semibold mb-1">{{ $t('account.price_drops') }}</div>
                                        <div class="fs-4 fw-bold text-warning-emphasis">{{ wishlistMeta.price_drop_count || 0 }}</div>
                                        <div class="extra-small text-muted">{{ $t('account.discounted_since_saved') }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Loading State -->
                            <div v-if="wishlistLoading" class="text-center py-5">
                                <div class="spinner-border text-danger" role="status"></div>
                                <p class="text-muted small mt-2">{{ $t('account.loading_saved_items') }}</p>
                            </div>

                            <!-- Empty State -->
                            <div v-else-if="wishlistCount === 0" class="text-center py-5 text-muted">
                                <div class="fs-1 mb-2">🤍</div>
                                <h6 class="fw-bold">{{ $t('wishlist.empty_title') }}</h6>
                                <p class="small mx-auto mb-3" style="max-width: 400px;">
                                    {{ $t('wishlist.empty_sub') }}
                                </p>
                                <router-link to="/shop" class="btn btn-primary btn-sm rounded-pill px-4">
                                    {{ $t('account.browse_catalog') }}
                                </router-link>
                            </div>

                            <!-- Wishlist Grid -->
                            <div v-else class="row g-3">
                                <div
                                    v-for="item in wishlistItems"
                                    :key="item.id || item.product_id"
                                    class="col-12 col-sm-6 col-md-4"
                                >
                                    <div class="card h-100 border rounded-3 p-3 bg-white position-relative d-flex flex-column shadow-sm">
                                        <!-- Remove Button -->
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-light position-absolute top-0 end-0 m-2 rounded-circle p-1 d-flex align-items-center justify-content-center shadow-sm text-muted"
                                            style="width: 28px; height: 28px; z-index: 2;"
                                            :title="$t('common.remove')"
                                            @click="removeFromWishlist(item.product_id)"
                                        >
                                            ✕
                                        </button>

                                        <!-- Stock status / Price drop badge -->
                                        <div class="mb-2">
                                            <span v-if="!item.in_stock" class="badge bg-danger text-white rounded-pill extra-small">
                                                {{ $t('common.out_of_stock') }}
                                            </span>
                                            <span v-else-if="item.price_dropped" class="badge bg-warning text-dark rounded-pill extra-small fw-bold">
                                                📉 {{ $t('storefront.save_discount') }} ₹{{ formatMoney(item.price_diff) }}
                                            </span>
                                            <span v-else class="badge bg-success-subtle text-success rounded-pill extra-small">
                                                ● {{ $t('common.in_stock') }} ({{ item.stock }})
                                            </span>
                                        </div>

                                        <router-link :to="`/product/${item.product_id}`" class="text-center mb-2">
                                            <img
                                                v-if="item.image_url"
                                                :src="item.image_url"
                                                :alt="item.name"
                                                class="img-fluid rounded"
                                                style="height: 120px; object-fit: contain;"
                                            />
                                            <div v-else class="rounded bg-light d-flex align-items-center justify-content-center mx-auto" style="height: 120px; width: 100%;">
                                                <span class="fs-2 text-muted">📦</span>
                                            </div>
                                        </router-link>

                                        <div class="extra-small text-muted text-uppercase mb-1">
                                            {{ item.category_name || 'Stationery' }}
                                        </div>
                                        <h6 class="fw-bold text-dark text-truncate mb-2" :title="item.name">
                                            <router-link :to="`/product/${item.product_id}`" class="text-dark text-decoration-none">
                                                {{ item.name }}
                                            </router-link>
                                        </h6>

                                        <div class="mt-auto pt-2 border-top">
                                            <div class="d-flex align-items-baseline gap-2 mb-2">
                                                <span class="fs-5 fw-bold text-primary">₹{{ formatMoney(item.selling_price) }}</span>
                                                <span v-if="item.mrp && item.mrp > item.selling_price" class="extra-small text-muted text-decoration-line-through">
                                                    ₹{{ formatMoney(item.mrp) }}
                                                </span>
                                            </div>
                                            <button
                                                v-if="item.in_stock"
                                                class="btn btn-primary btn-sm rounded-pill w-100 fw-semibold"
                                                @click="moveItemToCart(item)"
                                            >
                                                🛒 {{ $t('cart.move_to_cart') }}
                                            </button>
                                            <button
                                                v-else
                                                class="btn btn-secondary btn-sm rounded-pill w-100 disabled"
                                                disabled
                                            >
                                                {{ $t('account.unavailable') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 6: Notification Preferences & Promotional Inbox -->
                    <div v-show="activeTab === 'notifications'">
                        <!-- Promotional Inbox Card -->
                        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                                <div>
                                    <h5 class="fw-bold mb-1">🔔 {{ $t('account.promotional_notifications') }}</h5>
                                    <div class="text-muted small">{{ $t('account.promotional_notifications_sub') }}</div>
                                </div>
                                <button
                                    v-if="notifications.length > 0 && unreadNotificationsCount > 0"
                                    class="btn btn-outline-secondary btn-sm rounded-pill px-3"
                                    @click="markAllAsRead"
                                >
                                    ✓ {{ $t('account.mark_all_read') }}
                                </button>
                            </div>

                            <div v-if="notificationsLoading" class="text-center py-4">
                                <div class="spinner-border text-primary" role="status"></div>
                            </div>

                            <div v-else-if="notifications.length === 0" class="text-center py-5 text-muted">
                                <div class="fs-1 mb-2">📭</div>
                                <div class="fw-bold">{{ $t('account.no_notifications') }}</div>
                                <div class="small mt-1">{{ $t('account.no_notifications_desc') }}</div>
                            </div>

                            <div v-else class="d-flex flex-column gap-3">
                                <div
                                    v-for="notif in notifications"
                                    :key="notif.id"
                                    class="p-3 rounded-4 border"
                                    :class="notif.is_read ? 'bg-light-subtle' : 'bg-primary-subtle border-primary-subtle'"
                                >
                                    <div class="d-flex gap-3 align-items-start">
                                        <img
                                            v-if="notif.image_url"
                                            :src="notif.image_url"
                                            class="rounded-3 object-fit-cover flex-shrink-0"
                                            style="width: 56px; height: 56px;"
                                            alt=""
                                        />
                                        <div v-else class="rounded-3 bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0 fs-4" style="width: 56px; height: 56px;">
                                            🏷️
                                        </div>

                                        <div class="flex-grow-1">
                                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-1">
                                                <div class="d-flex align-items-center gap-2">
                                                    <h6 class="fw-bold mb-0 text-dark">{{ notif.title }}</h6>
                                                    <span v-if="notif.discount_badge" class="badge bg-danger rounded-pill extra-small">
                                                        {{ notif.discount_badge }}
                                                    </span>
                                                    <span v-if="!notif.is_read" class="badge bg-primary rounded-pill extra-small">
                                                        NEW
                                                    </span>
                                                </div>
                                                <span class="extra-small text-muted">{{ formatDate(notif.created_at) }}</span>
                                            </div>

                                            <p class="small text-muted mb-2">{{ notif.message }}</p>

                                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                                <div v-if="notif.coupon_code" class="d-inline-flex align-items-center gap-2 bg-white px-2 py-1 rounded border">
                                                    <span class="extra-small text-muted">{{ $t('coupons.coupon_code') }}:</span>
                                                    <strong class="font-monospace text-primary small">{{ notif.coupon_code }}</strong>
                                                    <button class="btn btn-sm btn-link p-0 text-decoration-none extra-small" @click="copyCoupon(notif.coupon_code)">
                                                        📋
                                                    </button>
                                                </div>

                                                <div class="d-flex align-items-center gap-2 ms-auto">
                                                    <button
                                                        v-if="!notif.is_read"
                                                        class="btn btn-link text-muted btn-sm p-0 extra-small text-decoration-none"
                                                        @click="markOneAsRead(notif)"
                                                    >
                                                        ✓ {{ $t('account.mark_read') }}
                                                    </button>
                                                    <router-link :to="notif.action_url || '/shop'" class="btn btn-primary btn-sm rounded-pill px-3 py-1 fw-semibold extra-small">
                                                        {{ notif.action_text || $t('marketing.shop_now') }} →
                                                    </router-link>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Marketing Preferences & Consent Card -->
                        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                            <h5 class="fw-bold mb-1">⚙️ {{ $t('account.notification_preferences') }}</h5>
                            <p class="text-muted small mb-4">{{ $t('account.notification_preferences_sub') }}</p>

                            <!-- Master Promotional Toggle -->
                            <div class="p-3 rounded-4 bg-light border mb-4 d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="fw-bold mb-1 text-dark">{{ $t('account.promotional_consent_title') }}</h6>
                                    <div class="small text-muted">{{ $t('account.promotional_consent_desc') }}</div>
                                </div>
                                <div class="form-check form-switch fs-4">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        v-model="preferences.promotional_enabled"
                                        @change="savePreferences"
                                    />
                                </div>
                            </div>

                            <div v-if="preferences.promotional_enabled">
                                <!-- Preferred Channels -->
                                <h6 class="fw-bold small text-uppercase text-muted mb-3">{{ $t('account.preferred_channels') }}</h6>
                                <div class="row g-3 mb-4">
                                    <div class="col-sm-6 col-md-4">
                                        <label class="d-flex align-items-center justify-content-between p-3 border rounded-3 bg-light-subtle cursor-pointer">
                                            <span class="small fw-semibold">📱 {{ $t('marketing.channel_in_app') }}</span>
                                            <input type="checkbox" v-model="preferences.channel_in_app" class="form-check-input mt-0" @change="savePreferences" />
                                        </label>
                                    </div>
                                    <div class="col-sm-6 col-md-4">
                                        <label class="d-flex align-items-center justify-content-between p-3 border rounded-3 bg-light-subtle cursor-pointer">
                                            <span class="small fw-semibold">✉️ {{ $t('marketing.channel_email') }}</span>
                                            <input type="checkbox" v-model="preferences.channel_email" class="form-check-input mt-0" @change="savePreferences" />
                                        </label>
                                    </div>
                                    <div class="col-sm-6 col-md-4">
                                        <label class="d-flex align-items-center justify-content-between p-3 border rounded-3 bg-light-subtle cursor-pointer">
                                            <span class="small fw-semibold">💬 {{ $t('marketing.channel_sms') }}</span>
                                            <input type="checkbox" v-model="preferences.channel_sms" class="form-check-input mt-0" @change="savePreferences" />
                                        </label>
                                    </div>
                                    <div class="col-sm-6 col-md-4">
                                        <label class="d-flex align-items-center justify-content-between p-3 border rounded-3 bg-light-subtle cursor-pointer">
                                            <span class="small fw-semibold">🟢 {{ $t('marketing.channel_whatsapp') }}</span>
                                            <input type="checkbox" v-model="preferences.channel_whatsapp" class="form-check-input mt-0" @change="savePreferences" />
                                        </label>
                                    </div>
                                    <div class="col-sm-6 col-md-4">
                                        <label class="d-flex align-items-center justify-content-between p-3 border rounded-3 bg-light-subtle cursor-pointer">
                                            <span class="small fw-semibold">🔔 {{ $t('marketing.channel_push') }}</span>
                                            <input type="checkbox" v-model="preferences.channel_push" class="form-check-input mt-0" @change="savePreferences" />
                                        </label>
                                    </div>
                                </div>

                                <!-- Notification Categories -->
                                <h6 class="fw-bold small text-uppercase text-muted mb-3">{{ $t('account.alert_topics') }}</h6>
                                <div class="d-flex flex-column gap-2 mb-4">
                                    <label class="d-flex align-items-center justify-content-between p-2 border rounded-3 bg-light-subtle cursor-pointer">
                                        <div>
                                            <div class="small fw-semibold">⚡ {{ $t('marketing.rule_flash_sale') }}</div>
                                            <div class="extra-small text-muted">{{ $t('marketing.rule_flash_sale_desc') }}</div>
                                        </div>
                                        <input type="checkbox" v-model="preferences.category_flash_sales" class="form-check-input mt-0" @change="savePreferences" />
                                    </label>
                                    <label class="d-flex align-items-center justify-content-between p-2 border rounded-3 bg-light-subtle cursor-pointer">
                                        <div>
                                            <div class="small fw-semibold">📦 {{ $t('marketing.rule_back_in_stock') }}</div>
                                            <div class="extra-small text-muted">{{ $t('marketing.rule_back_in_stock_desc') }}</div>
                                        </div>
                                        <input type="checkbox" v-model="preferences.category_back_in_stock" class="form-check-input mt-0" @change="savePreferences" />
                                    </label>
                                    <label class="d-flex align-items-center justify-content-between p-2 border rounded-3 bg-light-subtle cursor-pointer">
                                        <div>
                                            <div class="small fw-semibold">⭐ {{ $t('marketing.rule_loyalty_rewards') }}</div>
                                            <div class="extra-small text-muted">{{ $t('marketing.rule_loyalty_desc') }}</div>
                                        </div>
                                        <input type="checkbox" v-model="preferences.category_rewards" class="form-check-input mt-0" @change="savePreferences" />
                                    </label>
                                    <label class="d-flex align-items-center justify-content-between p-2 border rounded-3 bg-light-subtle cursor-pointer">
                                        <div>
                                            <div class="small fw-semibold">🎉 {{ $t('marketing.rule_festival') }}</div>
                                            <div class="extra-small text-muted">{{ $t('marketing.rule_festival_desc') }}</div>
                                        </div>
                                        <input type="checkbox" v-model="preferences.category_festivals" class="form-check-input mt-0" @change="savePreferences" />
                                    </label>
                                </div>
                            </div>

                            <!-- 1-Click Opt-Out Button -->
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 pt-3 border-top">
                                <div class="extra-small text-muted" style="max-width: 500px;">
                                    🔒 <em>{{ $t('account.transactional_guarantee') }}</em>
                                </div>
                                <button
                                    v-if="preferences.promotional_enabled"
                                    class="btn btn-outline-danger btn-sm rounded-pill px-3"
                                    @click="unsubscribeAllMarketing"
                                >
                                    {{ $t('account.unsubscribe_all_marketing') }}
                                </button>
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
import { useCustomerAuth } from '../../composables/useCustomerAuth';
import { useWishlist } from '../../composables/useWishlist';
import { useNotification } from '../../composables/useNotification';

const route = useRoute();
const router = useRouter();
const { customer, fetchCustomerProfile, updateProfile, changePassword, fetchOrders, logout } = useCustomerAuth();
const {
    wishlistItems,
    wishlistCount,
    wishlistMeta,
    loading: wishlistLoading,
    loadWishlist,
    removeFromWishlist,
    moveItemToCart,
    moveAllToCart,
} = useWishlist();
const { toast, confirmDialog } = useNotification();

const activeTab = ref('overview');

const recentOrdersLoading = ref(false);
const ordersLoading = ref(false);
const orders = ref([]);
const ordersPagination = ref({ current_page: 1, last_page: 1 });

const savingProfile = ref(false);
const savingPassword = ref(false);

const profileForm = ref({
    name: '',
    email: '',
    phone: '',
    address: '',
    city: '',
    state: '',
    pincode: '',
});

const passwordForm = ref({
    current_password: '',
    password: '',
    password_confirmation: '',
});

// Notifications & Marketing Preferences State
const notifications = ref([]);
const unreadNotificationsCount = ref(0);
const notificationsLoading = ref(false);
const preferences = ref({
    promotional_enabled: true,
    channel_in_app: true,
    channel_email: true,
    channel_sms: true,
    channel_whatsapp: true,
    channel_push: true,
    category_flash_sales: true,
    category_back_in_stock: true,
    category_rewards: true,
    category_new_arrivals: true,
    category_festivals: true,
});

async function switchToNotifications() {
    activeTab.value = 'notifications';
    await Promise.all([loadNotifications(), loadPreferences()]);
}

async function loadNotifications() {
    notificationsLoading.value = true;
    try {
        const res = await axios.get('/api/shop/account/notifications');
        notifications.value = res.data.notifications?.data || [];
        unreadNotificationsCount.value = res.data.unread_count || 0;
    } catch (err) {
        // silently ignore if not authed
    } finally {
        notificationsLoading.value = false;
    }
}

async function loadPreferences() {
    try {
        const res = await axios.get('/api/shop/account/notification-preferences');
        if (res.data) {
            preferences.value = { ...preferences.value, ...res.data };
        }
    } catch (err) {
        // ignore
    }
}

async function savePreferences() {
    try {
        await axios.put('/api/shop/account/notification-preferences', preferences.value);
        toast({ title: 'Saved', message: 'Notification preferences updated.', type: 'success' });
    } catch (err) {
        toast({ title: 'Error', message: 'Could not update preferences.', type: 'danger' });
    }
}

async function markOneAsRead(notif) {
    try {
        await axios.patch(`/api/shop/account/notifications/${notif.id}/read`);
        notif.is_read = true;
        if (unreadNotificationsCount.value > 0) unreadNotificationsCount.value--;
    } catch (err) {}
}

async function markAllAsRead() {
    try {
        await axios.patch('/api/shop/account/notifications/read-all');
        notifications.value.forEach(n => n.is_read = true);
        unreadNotificationsCount.value = 0;
        toast({ title: 'Success', message: 'All notifications marked as read.', type: 'success' });
    } catch (err) {}
}

function copyCoupon(code) {
    if (navigator?.clipboard) {
        navigator.clipboard.writeText(code);
        toast({ title: 'Copied!', message: `Coupon code ${code} copied to clipboard!`, type: 'success' });
    }
}

async function unsubscribeAllMarketing() {
    const ok = await confirmDialog({
        title: 'Unsubscribe from Marketing?',
        message: 'Are you sure you want to stop receiving all promotional emails, deal alerts, and vouchers?',
        confirmText: 'Unsubscribe',
        cancelText: 'Keep Subscribed',
        type: 'danger',
    });
    if (!ok) return;

    try {
        await axios.post('/api/shop/account/notification-preferences/unsubscribe-all');
        preferences.value.promotional_enabled = false;
        toast({ title: 'Unsubscribed', message: 'You have been unsubscribed from promotional marketing.', type: 'info' });
    } catch (err) {
        toast({ title: 'Error', message: 'Failed to update preferences.', type: 'danger' });
    }
}

function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString();
}

const customerInitials = computed(() => {
    if (!customer.value?.name) return 'CU';
    return customer.value.name
        .split(' ')
        .map(w => w[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
});

function formatMoney(amount) {
    return Number(amount || 0).toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function getStatusBadgeClass(status) {
    switch (status) {
        case 'delivered':
            return 'bg-success text-white';
        case 'shipped':
            return 'bg-primary text-white';
        case 'processing':
            return 'bg-info text-dark';
        case 'confirmed':
            return 'bg-warning text-dark';
        case 'cancelled':
            return 'bg-danger text-white';
        default:
            return 'bg-secondary text-white';
    }
}

async function loadOrders(page = 1) {
    ordersLoading.value = true;
    const res = await fetchOrders(page);
    if (res?.data) {
        orders.value = res.data;
        ordersPagination.value = {
            current_page: res.current_page || 1,
            last_page: res.last_page || 1,
        };
    }
    ordersLoading.value = false;
}

function switchToOrders() {
    activeTab.value = 'orders';
    loadOrders(1);
}

function switchToWishlist() {
    activeTab.value = 'wishlist';
    loadWishlist();
}

function changeOrdersPage(page) {
    if (page < 1 || page > ordersPagination.value.last_page) return;
    loadOrders(page);
}

async function saveProfile() {
    savingProfile.value = true;
    await updateProfile(profileForm.value);
    savingProfile.value = false;
}

async function savePassword() {
    if (passwordForm.value.password !== passwordForm.value.password_confirmation) {
        toast.error('New password and confirmation do not match.', 'Validation Error');
        return;
    }

    savingPassword.value = true;
    const ok = await changePassword(passwordForm.value);
    if (ok?.success) {
        passwordForm.value.current_password = '';
        passwordForm.value.password = '';
        passwordForm.value.password_confirmation = '';
    }
    savingPassword.value = false;
}

async function handleSignOut() {
    const ok = await confirmDialog({
        title: 'Sign Out?',
        message: 'Are you sure you want to log out of your customer account?',
        confirmText: 'Sign Out',
        cancelText: 'Cancel',
        type: 'danger',
    });
    if (ok) {
        logout();
    }
}

onMounted(async () => {
    await fetchCustomerProfile();
    if (customer.value) {
        profileForm.value = {
            name: customer.value.name || '',
            email: customer.value.email || '',
            phone: customer.value.phone || '',
            address: customer.value.address || '',
            city: customer.value.city || '',
            state: customer.value.state || '',
            pincode: customer.value.pincode || '',
        };
    }

    // Load recent orders snippet for overview tab
    recentOrdersLoading.value = true;
    const res = await fetchOrders(1);
    if (res?.data) {
        orders.value = res.data;
    }
    recentOrdersLoading.value = false;

    // Load wishlist
    loadWishlist();
    if (route.query.tab === 'wishlist') {
        activeTab.value = 'wishlist';
    } else if (route.query.tab === 'notifications') {
        switchToNotifications();
    } else {
        loadNotifications();
    }
});
</script>

<style scoped>
.extra-small {
    font-size: 0.75rem;
}

.customer-avatar {
    width: 64px;
    height: 64px;
}

.text-truncate-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

