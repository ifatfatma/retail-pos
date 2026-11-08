<template>
    <div class="container-fluid py-4">

        <!-- Page Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
            <div>
                <h3 class="fw-bold mb-1">📢 {{ $t('marketing.campaigns_title') }}</h3>
                <p class="text-muted mb-0">
                    {{ $t('marketing.campaigns_subtitle') }}
                </p>
            </div>

            <div class="d-flex gap-2">
                <button
                    class="btn btn-outline-secondary btn-sm"
                    @click="fetchCampaigns"
                    :disabled="loading"
                    :title="$t('common.refresh')"
                >
                    <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
                    🔄 {{ $t('common.refresh') }}
                </button>

                <router-link
                    to="/admin/marketing/campaigns/create"
                    class="btn btn-primary btn-sm fw-semibold"
                >
                    + {{ $t('marketing.create_campaign') }}
                </router-link>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3">
                <div class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-transparent border-end-0">🔍</span>
                            <input
                                v-model="filters.search"
                                type="text"
                                class="form-control border-start-0"
                                :placeholder="$t('marketing.search_campaigns_placeholder')"
                                @input="debouncedSearch"
                            />
                        </div>
                    </div>

                    <div class="col-sm-6 col-md-3">
                        <select v-model="filters.status" class="form-select form-select-sm" @change="fetchCampaigns">
                            <option value="">{{ $t('marketing.all_statuses') }}</option>
                            <option value="active">{{ $t('marketing.status_active') }}</option>
                            <option value="scheduled">{{ $t('marketing.status_scheduled') }}</option>
                            <option value="paused">{{ $t('marketing.status_paused') }}</option>
                            <option value="draft">{{ $t('marketing.status_draft') }}</option>
                            <option value="completed">{{ $t('marketing.status_completed') }}</option>
                        </select>
                    </div>

                    <div class="col-sm-6 col-md-3">
                        <select v-model="filters.type" class="form-select form-select-sm" @change="fetchCampaigns">
                            <option value="">{{ $t('marketing.all_campaign_types') }}</option>
                            <option value="promotional">{{ $t('marketing.type_promotional') }}</option>
                            <option value="flash_sale">{{ $t('marketing.type_flash_sale') }}</option>
                            <option value="festival">{{ $t('marketing.type_festival') }}</option>
                            <option value="seasonal">{{ $t('marketing.type_seasonal') }}</option>
                            <option value="clearance">{{ $t('marketing.type_clearance') }}</option>
                        </select>
                    </div>

                    <div class="col-md-2 text-md-end">
                        <button class="btn btn-sm btn-link text-muted p-0 text-decoration-none" @click="resetFilters">
                            ✕ {{ $t('common.clear_filters') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Campaigns Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">{{ $t('marketing.campaign') }}</th>
                                <th>{{ $t('marketing.type') }}</th>
                                <th>{{ $t('marketing.target_segment') }}</th>
                                <th>{{ $t('marketing.discount') }}</th>
                                <th>{{ $t('marketing.channels') }}</th>
                                <th>{{ $t('marketing.timeline') }}</th>
                                <th>{{ $t('marketing.performance') }}</th>
                                <th>{{ $t('marketing.status') }}</th>
                                <th class="text-end pe-3">{{ $t('common.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="loading">
                                <td colspan="9" class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <div class="small text-muted mt-2">{{ $t('common.loading') }}...</div>
                                </td>
                            </tr>
                            <tr v-else-if="campaigns.length === 0">
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <div class="fs-1 mb-2">📢</div>
                                    <div class="fw-bold">{{ $t('marketing.no_campaigns_found') }}</div>
                                    <div class="small mt-1">{{ $t('marketing.no_campaigns_desc') }}</div>
                                    <div class="mt-3">
                                        <router-link to="/admin/marketing/campaigns/create" class="btn btn-sm btn-primary">
                                            + {{ $t('marketing.create_campaign') }}
                                        </router-link>
                                    </div>
                                </td>
                            </tr>
                            <tr v-for="c in campaigns" :key="c.id">
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <img
                                            v-if="c.image_url || c.banner_url"
                                            :src="c.image_url || c.banner_url"
                                            class="rounded object-fit-cover"
                                            style="width: 44px; height: 44px;"
                                            alt=""
                                        />
                                        <div v-else class="rounded bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 44px; height: 44px;">
                                            📢
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ c.title }}</div>
                                            <div class="extra-small text-muted text-truncate" style="max-width: 200px;">
                                                {{ c.description || '—' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border text-capitalize">
                                        {{ c.campaign_type.replace('_', ' ') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary">
                                        {{ c.segment?.name || $t('marketing.all_customers') }}
                                    </span>
                                </td>
                                <td>
                                    <span v-if="c.discount_type === 'percentage'" class="badge bg-danger-subtle text-danger fw-semibold">
                                        🔥 {{ c.discount_value }}% OFF
                                    </span>
                                    <span v-else-if="c.discount_type === 'fixed'" class="badge bg-success-subtle text-success fw-semibold">
                                        💰 ₹{{ c.discount_value }}
                                    </span>
                                    <span v-else class="text-muted small">—</span>
                                    <div v-if="c.coupon" class="extra-small font-monospace text-primary mt-1">
                                        🎟️ {{ c.coupon.code }}
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex gap-1 flex-wrap">
                                        <span v-for="ch in c.channels" :key="ch" class="badge bg-secondary-subtle text-secondary" :title="ch">
                                            {{ getChannelIcon(ch) }}
                                        </span>
                                    </div>
                                </td>
                                <td class="small">
                                    <div><span class="text-muted">{{ $t('marketing.start') }}:</span> {{ formatDate(c.start_date) || $t('marketing.immediate') }}</div>
                                    <div v-if="c.end_date"><span class="text-muted">{{ $t('marketing.end') }}:</span> {{ formatDate(c.end_date) }}</div>
                                </td>
                                <td>
                                    <div class="small fw-semibold">
                                        {{ c.total_delivered }} / {{ c.total_sent }}
                                    </div>
                                    <div class="extra-small text-muted">
                                        {{ c.total_conversions }} {{ $t('marketing.sales') }} (₹{{ formatCurrency(c.revenue_generated) }})
                                    </div>
                                </td>
                                <td>
                                    <span :class="getStatusBadgeClass(c.status)" class="badge">
                                        {{ $t(`marketing.status_${c.status}`) }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <button
                                            v-if="c.status === 'draft' || c.status === 'scheduled' || c.status === 'paused'"
                                            class="btn btn-outline-success"
                                            @click="sendCampaign(c.id)"
                                            :title="$t('marketing.send_now')"
                                        >
                                            🚀
                                        </button>
                                        <button
                                            v-if="c.status === 'active' || c.status === 'paused'"
                                            class="btn btn-outline-warning"
                                            @click="toggleStatus(c.id)"
                                            :title="c.status === 'active' ? $t('marketing.pause') : $t('marketing.resume')"
                                        >
                                            {{ c.status === 'active' ? '⏸️' : '▶️' }}
                                        </button>
                                        <router-link :to="`/admin/marketing/campaigns/${c.id}/edit`" class="btn btn-outline-secondary" :title="$t('common.edit')">
                                            ✏️
                                        </router-link>
                                        <button class="btn btn-outline-danger" @click="confirmDelete(c)" :title="$t('common.delete')">
                                            🗑️
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination Footer -->
            <div v-if="pagination.total > pagination.per_page" class="card-footer bg-transparent border-0 d-flex justify-content-between align-items-center py-3">
                <span class="small text-muted">
                    {{ $t('common.showing') }} {{ pagination.from }} - {{ pagination.to }} {{ $t('common.of') }} {{ pagination.total }}
                </span>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                            <button class="page-link" @click="changePage(pagination.current_page - 1)">«</button>
                        </li>
                        <li
                            v-for="p in pagination.last_page"
                            :key="p"
                            class="page-item"
                            :class="{ active: pagination.current_page === p }"
                        >
                            <button class="page-link" @click="changePage(p)">{{ p }}</button>
                        </li>
                        <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                            <button class="page-link" @click="changePage(pagination.current_page + 1)">»</button>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useNotification } from '../../composables/useNotification';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const { showSuccess, showError, confirmDialog } = useNotification();

const loading = ref(false);
const campaigns = ref([]);

const filters = ref({
    search: '',
    status: '',
    type: '',
});

const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0,
    from: 0,
    to: 0,
});

let debounceTimer = null;
function debouncedSearch() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        fetchCampaigns(1);
    }, 300);
}

function resetFilters() {
    filters.value = { search: '', status: '', type: '' };
    fetchCampaigns(1);
}

async function fetchCampaigns(page = 1) {
    loading.value = true;
    try {
        const params = {
            page,
            search: filters.value.search,
            status: filters.value.status,
            type: filters.value.type,
        };
        const res = await axios.get('/api/marketing/campaigns', { params });
        campaigns.value = res.data.data;
        pagination.value = {
            current_page: res.data.current_page,
            last_page: res.data.last_page,
            per_page: res.data.per_page,
            total: res.data.total,
            from: res.data.from || 0,
            to: res.data.to || 0,
        };
    } catch (err) {
        showError(t('marketing.error_loading_campaigns'));
    } finally {
        loading.value = false;
    }
}

function changePage(page) {
    if (page >= 1 && page <= pagination.value.last_page) {
        fetchCampaigns(page);
    }
}

async function sendCampaign(id) {
    const ok = await confirmDialog({
        title: t('marketing.send_now'),
        message: t('marketing.confirm_send_campaign'),
        confirmText: t('marketing.send_now'),
        cancelText: t('common.cancel'),
        type: 'primary',
    });
    if (!ok) return;

    try {
        const res = await axios.post(`/api/marketing/campaigns/${id}/send`);
        showSuccess(res.data.message || t('marketing.campaign_sent_success'));
        fetchCampaigns(pagination.value.current_page);
    } catch (err) {
        showError(t('marketing.error_sending_campaign'));
    }
}

async function toggleStatus(id) {
    try {
        const res = await axios.post(`/api/marketing/campaigns/${id}/toggle-status`);
        showSuccess(res.data.message);
        fetchCampaigns(pagination.value.current_page);
    } catch (err) {
        showError(t('marketing.error_toggling_campaign'));
    }
}

async function confirmDelete(c) {
    const ok = await confirmDialog({
        title: t('common.delete'),
        message: t('marketing.confirm_delete_campaign', { title: c.title }),
        confirmText: t('common.delete'),
        cancelText: t('common.cancel'),
        type: 'danger',
    });
    if (!ok) return;

    try {
        await axios.delete(`/api/marketing/campaigns/${c.id}`);
        showSuccess(t('marketing.campaign_deleted_success'));
        fetchCampaigns(pagination.value.current_page);
    } catch (err) {
        showError(t('marketing.error_deleting_campaign'));
    }
}

function getChannelIcon(ch) {
    switch (ch) {
        case 'in_app': return '📱';
        case 'email': return '✉️';
        case 'sms': return '💬';
        case 'whatsapp': return '🟢';
        case 'push': return '🔔';
        default: return '📢';
    }
}

function getStatusBadgeClass(status) {
    switch (status) {
        case 'active': return 'bg-success text-white';
        case 'scheduled': return 'bg-info text-white';
        case 'paused': return 'bg-warning text-dark';
        case 'completed': return 'bg-secondary text-white';
        case 'draft': return 'bg-light text-dark border';
        default: return 'bg-secondary text-white';
    }
}

function formatCurrency(amount) {
    return Number(amount || 0).toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString();
}

onMounted(() => {
    fetchCampaigns();
});
</script>

<style scoped>
.extra-small {
    font-size: 0.72rem;
}
</style>
