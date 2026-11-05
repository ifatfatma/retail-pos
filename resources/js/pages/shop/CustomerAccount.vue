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
                            🛍️ Shop Catalog
                        </router-link>
                        <button class="btn btn-outline-danger btn-sm rounded-pill px-3" @click="handleSignOut">
                            🚪 Sign Out
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
                                <span>Dashboard</span>
                            </button>
                            <button
                                class="nav-link text-start rounded-pill py-2 px-3 fw-semibold d-flex align-items-center gap-2"
                                :class="{ active: activeTab === 'orders' }"
                                @click="switchToOrders"
                            >
                                <span>📦</span>
                                <span>My Orders</span>
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
                                <span>My Wishlist</span>
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
                                <span>Profile & Address</span>
                            </button>
                            <button
                                class="nav-link text-start rounded-pill py-2 px-3 fw-semibold d-flex align-items-center gap-2"
                                :class="{ active: activeTab === 'security' }"
                                @click="activeTab = 'security'"
                            >
                                <span>🔒</span>
                                <span>Change Password</span>
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
                                    <div class="text-muted extra-small text-uppercase fw-semibold mb-1">Total Purchases</div>
                                    <h3 class="fw-bold text-primary mb-0">{{ customer?.total_orders || 0 }}</h3>
                                    <div class="extra-small text-muted mt-1">Orders placed with us</div>
                                </div>
                            </div>

                            <div class="col-sm-6 col-md-4">
                                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                                    <div class="text-muted extra-small text-uppercase fw-semibold mb-1">Total Spent</div>
                                    <h3 class="fw-bold text-success mb-0">₹{{ formatMoney(customer?.total_spent) }}</h3>
                                    <div class="extra-small text-muted mt-1">Lifetime spending</div>
                                </div>
                            </div>

                            <div class="col-sm-12 col-md-4">
                                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                                    <div class="text-muted extra-small text-uppercase fw-semibold mb-1">Default Address</div>
                                    <div class="small text-truncate-2 mb-0">
                                        {{ customer?.address ? `${customer.address}, ${customer.city || ''} ${customer.pincode || ''}` : 'No address saved yet.' }}
                                    </div>
                                    <button class="btn btn-link p-0 extra-small text-primary text-decoration-none mt-1" @click="activeTab = 'profile'">
                                        Edit Address →
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Orders Preview -->
                        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold mb-0">Recent Order Activity</h5>
                                <button class="btn btn-link btn-sm text-decoration-none text-primary" @click="switchToOrders">
                                    View All Orders →
                                </button>
                            </div>

                            <div v-if="recentOrdersLoading" class="text-center py-4">
                                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                            </div>

                            <div v-else-if="orders.length === 0" class="text-center py-4 text-muted">
                                <div class="fs-2 mb-1">🛍️</div>
                                <div class="small">You haven't placed any orders yet.</div>
                                <router-link to="/shop" class="btn btn-primary btn-sm rounded-pill mt-2">
                                    Start Shopping
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
                                            Track
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
                                    <h5 class="fw-bold mb-0">Saved in Wishlist</h5>
                                    <span class="badge bg-danger-subtle text-danger rounded-pill">{{ wishlistCount }}</span>
                                </div>
                                <button class="btn btn-link btn-sm text-decoration-none text-primary" @click="switchToWishlist">
                                    View Wishlist Tab →
                                </button>
                            </div>

                            <div v-if="wishlistCount === 0" class="text-center py-4 text-muted">
                                <div class="fs-2 mb-1">🤍</div>
                                <div class="small">No products saved to wishlist yet.</div>
                                <router-link to="/shop" class="btn btn-outline-primary btn-sm rounded-pill mt-2">
                                    Browse Stationery Items
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
                                                <span v-if="item.in_stock" class="badge bg-success-subtle text-success extra-small">In Stock</span>
                                                <span v-else class="badge bg-danger-subtle text-danger extra-small">Out of Stock</span>
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
                            <h5 class="fw-bold mb-4">📦 All My Orders</h5>

                            <div v-if="ordersLoading" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="text-muted small mt-2">Loading orders...</p>
                            </div>

                            <div v-else-if="orders.length === 0" class="text-center py-5 text-muted">
                                <div class="fs-1 mb-2">📦</div>
                                <h6>No Orders Found</h6>
                                <p class="small">Browse our catalog and place your first order!</p>
                                <router-link to="/shop" class="btn btn-primary rounded-pill px-4">
                                    Explore Products
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
                                            <span v-if="order.delivery_partner">Courier: <strong>{{ order.delivery_partner }}</strong> | </span>
                                            <span v-if="order.tracking_number">Tracking #: <strong>{{ order.tracking_number }}</strong> | </span>
                                            <span>Payment: <strong class="text-uppercase">{{ order.payment_method }} ({{ order.payment_status }})</strong></span>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <router-link
                                                :to="{ path: '/track-order', query: { order_number: order.order_number } }"
                                                class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1"
                                            >
                                                📍 Live Track
                                            </router-link>
                                        </div>
                                    </div>
                                </div>

                                <!-- Orders Pagination -->
                                <div v-if="ordersPagination.last_page > 1" class="d-flex justify-content-center mt-4">
                                    <ul class="pagination pagination-sm mb-0">
                                        <li class="page-item" :class="{ disabled: ordersPagination.current_page <= 1 }">
                                            <button class="page-link" @click="changeOrdersPage(ordersPagination.current_page - 1)">« Prev</button>
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
                                            <button class="page-link" @click="changeOrdersPage(ordersPagination.current_page + 1)">Next »</button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Wishlist Overview Preview -->
                        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fs-5">❤️</span>
                                    <h5 class="fw-bold mb-0">Saved in Wishlist</h5>
                                    <span class="badge bg-danger-subtle text-danger rounded-pill">{{ wishlistCount }}</span>
                                </div>
                                <button class="btn btn-link btn-sm text-decoration-none text-primary" @click="switchToWishlist">
                                    View Wishlist Tab →
                                </button>
                            </div>

                            <div v-if="wishlistCount === 0" class="text-center py-4 text-muted">
                                <div class="fs-2 mb-1">🤍</div>
                                <div class="small">No products saved to wishlist yet.</div>
                                <router-link to="/shop" class="btn btn-outline-primary btn-sm rounded-pill mt-2">
                                    Browse Stationery Items
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
                                                <span v-if="item.in_stock" class="badge bg-success-subtle text-success extra-small">In Stock</span>
                                                <span v-else class="badge bg-danger-subtle text-danger extra-small">Out of Stock</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 3: Profile & Address -->
                    <div v-show="activeTab === 'profile'">
                        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                            <h5 class="fw-bold mb-4">👤 Profile & Shipping Address</h5>

                            <form @submit.prevent="saveProfile">
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted text-uppercase">Full Name <span class="text-danger">*</span></label>
                                        <input
                                            v-model="profileForm.name"
                                            type="text"
                                            class="form-control"
                                            required
                                        />
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted text-uppercase">Email Address <span class="text-danger">*</span></label>
                                        <input
                                            v-model="profileForm.email"
                                            type="email"
                                            class="form-control"
                                            required
                                        />
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted text-uppercase">Phone Number <span class="text-danger">*</span></label>
                                        <input
                                            v-model="profileForm.phone"
                                            type="tel"
                                            class="form-control"
                                            required
                                        />
                                    </div>

                                    <!-- Address -->
                                    <div class="col-12 pt-3 border-top">
                                        <div class="fw-semibold small text-muted text-uppercase mb-2">Default Shipping Address</div>
                                        <label class="form-label small fw-semibold text-muted text-uppercase">Street Address</label>
                                        <textarea
                                            v-model="profileForm.address"
                                            class="form-control"
                                            rows="2"
                                            placeholder="House / Flat No., Street, Landmark"
                                        ></textarea>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold text-muted text-uppercase">City</label>
                                        <input
                                            v-model="profileForm.city"
                                            type="text"
                                            class="form-control"
                                            placeholder="City"
                                        />
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold text-muted text-uppercase">State</label>
                                        <input
                                            v-model="profileForm.state"
                                            type="text"
                                            class="form-control"
                                            placeholder="State"
                                        />
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold text-muted text-uppercase">Pincode</label>
                                        <input
                                            v-model="profileForm.pincode"
                                            type="text"
                                            class="form-control"
                                            placeholder="Pincode"
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
                                        Save Profile Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tab 4: Security -->
                    <div v-show="activeTab === 'security'">
                        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                            <h5 class="fw-bold mb-4">🔒 Change Password</h5>

                            <form @submit.prevent="savePassword">
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted text-uppercase">Current Password</label>
                                    <input
                                        v-model="passwordForm.current_password"
                                        type="password"
                                        class="form-control"
                                        placeholder="Enter current password"
                                        required
                                    />
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted text-uppercase">New Password</label>
                                        <input
                                            v-model="passwordForm.password"
                                            type="password"
                                            class="form-control"
                                            placeholder="Min. 6 characters"
                                            required
                                            minlength="6"
                                        />
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted text-uppercase">Confirm New Password</label>
                                        <input
                                            v-model="passwordForm.password_confirmation"
                                            type="password"
                                            class="form-control"
                                            placeholder="Repeat new password"
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
                                        Update Password
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
                                    <h5 class="fw-bold mb-1">❤️ My Wishlist & Saved Products</h5>
                                    <div class="text-muted small">Items you've bookmarked for later purchase</div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button
                                        v-if="wishlistCount > 0"
                                        class="btn btn-outline-primary btn-sm rounded-pill px-3"
                                        @click="moveAllToCart"
                                        :disabled="wishlistMeta.in_stock_count === 0"
                                    >
                                        🛒 Add Available to Cart ({{ wishlistMeta.in_stock_count }})
                                    </button>
                                    <router-link to="/wishlist" class="btn btn-primary btn-sm rounded-pill px-3">
                                        Open Full Wishlist →
                                    </router-link>
                                </div>
                            </div>

                            <!-- Dashboard KPI strip -->
                            <div class="row g-3 mb-4">
                                <div class="col-6 col-md-3">
                                    <div class="p-3 rounded-4 bg-light text-center h-100">
                                        <div class="text-muted extra-small text-uppercase fw-semibold mb-1">Total Saved</div>
                                        <div class="fs-4 fw-bold text-dark">{{ wishlistCount }}</div>
                                        <div class="extra-small text-muted">Bookmarked items</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="p-3 rounded-4 bg-success-subtle text-center h-100">
                                        <div class="text-success extra-small text-uppercase fw-semibold mb-1">Available Now</div>
                                        <div class="fs-4 fw-bold text-success">{{ wishlistMeta.in_stock_count }}</div>
                                        <div class="extra-small text-muted">Ready to purchase</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="p-3 rounded-4 bg-danger-subtle text-center h-100">
                                        <div class="text-danger extra-small text-uppercase fw-semibold mb-1">Out of Stock</div>
                                        <div class="fs-4 fw-bold text-danger">{{ wishlistMeta.out_of_stock_count }}</div>
                                        <div class="extra-small text-muted">Awaiting restock</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="p-3 rounded-4 bg-warning-subtle text-center h-100">
                                        <div class="text-warning-emphasis extra-small text-uppercase fw-semibold mb-1">Price Drops</div>
                                        <div class="fs-4 fw-bold text-warning-emphasis">{{ wishlistMeta.price_drop_count || 0 }}</div>
                                        <div class="extra-small text-muted">Discounted since saved</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Loading State -->
                            <div v-if="wishlistLoading" class="text-center py-5">
                                <div class="spinner-border text-danger" role="status"></div>
                                <p class="text-muted small mt-2">Loading saved items...</p>
                            </div>

                            <!-- Empty State -->
                            <div v-else-if="wishlistCount === 0" class="text-center py-5 text-muted">
                                <div class="fs-1 mb-2">🤍</div>
                                <h6 class="fw-bold">Your Wishlist is Empty</h6>
                                <p class="small mx-auto mb-3" style="max-width: 400px;">
                                    Browse our stationery collections and click the heart icon on any product to save it here.
                                </p>
                                <router-link to="/shop" class="btn btn-primary btn-sm rounded-pill px-4">
                                    Browse Catalog
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
                                            title="Remove"
                                            @click="removeFromWishlist(item.product_id)"
                                        >
                                            ✕
                                        </button>

                                        <!-- Stock status / Price drop badge -->
                                        <div class="mb-2">
                                            <span v-if="!item.in_stock" class="badge bg-danger text-white rounded-pill extra-small">
                                                Out of Stock
                                            </span>
                                            <span v-else-if="item.price_dropped" class="badge bg-warning text-dark rounded-pill extra-small fw-bold">
                                                📉 Saved ₹{{ formatMoney(item.price_diff) }}
                                            </span>
                                            <span v-else class="badge bg-success-subtle text-success rounded-pill extra-small">
                                                ● In Stock ({{ item.stock }})
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
                                                🛒 Move to Cart
                                            </button>
                                            <button
                                                v-else
                                                class="btn btn-secondary btn-sm rounded-pill w-100 disabled"
                                                disabled
                                            >
                                                Unavailable
                                            </button>
                                        </div>
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

