<template>
    <div class="container-fluid py-4">

        <!-- Page Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
            <div>
                <h3 class="fw-bold mb-1">📊 {{ $t('marketing.delivery_reports_title') }}</h3>
                <p class="text-muted mb-0">
                    {{ $t('marketing.delivery_reports_subtitle') }}
                </p>
            </div>

            <div class="d-flex gap-2">
                <button
                    class="btn btn-outline-secondary btn-sm"
                    @click="fetchReports"
                    :disabled="loading"
                    :title="$t('common.refresh')"
                >
                    <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
                    🔄 {{ $t('common.refresh') }}
                </button>
            </div>
        </div>

        <!-- Filter Bar -->
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
                                :placeholder="$t('marketing.search_recipient_title')"
                                @input="debouncedSearch"
                            />
                        </div>
                    </div>

                    <div class="col-sm-6 col-md-3">
                        <select v-model="filters.channel" class="form-select form-select-sm" @change="fetchReports">
                            <option value="">{{ $t('marketing.all_channels') }}</option>
                            <option value="in_app">📱 In-App</option>
                            <option value="email">✉️ Email</option>
                            <option value="sms">💬 SMS</option>
                            <option value="whatsapp">🟢 WhatsApp</option>
                            <option value="push">🔔 Web Push</option>
                        </select>
                    </div>

                    <div class="col-sm-6 col-md-3">
                        <select v-model="filters.status" class="form-select form-select-sm" @change="fetchReports">
                            <option value="">{{ $t('marketing.all_statuses') }}</option>
                            <option value="delivered">{{ $t('marketing.log_delivered') }}</option>
                            <option value="simulated">{{ $t('marketing.log_simulated') }}</option>
                            <option value="sent">{{ $t('marketing.log_sent') }}</option>
                            <option value="failed">{{ $t('marketing.log_failed') }}</option>
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

        <!-- Logs Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">{{ $t('marketing.dispatched_at') }}</th>
                                <th>{{ $t('marketing.channel') }}</th>
                                <th>{{ $t('marketing.recipient') }}</th>
                                <th>{{ $t('marketing.campaign_or_rule') }}</th>
                                <th>{{ $t('marketing.message') }}</th>
                                <th>{{ $t('marketing.delivery_status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="loading">
                                <td colspan="6" class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <div class="small text-muted mt-2">{{ $t('common.loading') }}...</div>
                                </td>
                            </tr>
                            <tr v-else-if="logs.length === 0">
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <div class="fs-1 mb-2">📊</div>
                                    <div class="fw-bold">{{ $t('marketing.no_logs_found') }}</div>
                                </td>
                            </tr>
                            <tr v-for="log in logs" :key="log.id">
                                <td class="ps-3 small text-muted text-nowrap">
                                    {{ formatDate(log.sent_at || log.created_at) }}
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1">
                                        <span>{{ getChannelIcon(log.channel) }}</span>
                                        <span class="text-capitalize">{{ log.channel }}</span>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold small">{{ log.recipient }}</div>
                                    <div v-if="log.customer" class="extra-small text-muted">{{ log.customer.name }}</div>
                                </td>
                                <td>
                                    <div v-if="log.campaign" class="small fw-semibold text-primary">
                                        📢 {{ log.campaign.title }}
                                    </div>
                                    <div v-else-if="log.rule" class="small fw-semibold text-secondary">
                                        ⚡ {{ log.rule.name }}
                                    </div>
                                    <div v-else class="extra-small text-muted">Direct / Test</div>
                                </td>
                                <td>
                                    <div class="small fw-semibold text-dark text-truncate" style="max-width: 250px;">
                                        {{ log.title }}
                                    </div>
                                    <div class="extra-small text-muted text-truncate" style="max-width: 250px;">
                                        {{ log.content }}
                                    </div>
                                </td>
                                <td>
                                    <span :class="getStatusBadgeClass(log.status)" class="badge">
                                        {{ log.is_test_mode ? '🛡️ ' + $t('marketing.simulated_test') : $t(`marketing.log_${log.status}`) }}
                                    </span>
                                    <div v-if="log.error_message" class="extra-small text-danger mt-1">
                                        {{ log.error_message }}
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
const { showError } = useNotification();

const loading = ref(false);
const logs = ref([]);

const filters = ref({
    search: '',
    channel: '',
    status: '',
});

const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
    from: 0,
    to: 0,
});

let debounceTimer = null;
function debouncedSearch() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        fetchReports(1);
    }, 300);
}

function resetFilters() {
    filters.value = { search: '', channel: '', status: '' };
    fetchReports(1);
}

async function fetchReports(page = 1) {
    loading.value = true;
    try {
        const params = {
            page,
            search: filters.value.search,
            channel: filters.value.channel,
            status: filters.value.status,
        };
        const res = await axios.get('/api/marketing/delivery-reports', { params });
        logs.value = res.data.data;
        pagination.value = {
            current_page: res.data.current_page,
            last_page: res.data.last_page,
            per_page: res.data.per_page,
            total: res.data.total,
            from: res.data.from || 0,
            to: res.data.to || 0,
        };
    } catch (err) {
        showError(t('marketing.error_loading_reports'));
    } finally {
        loading.value = false;
    }
}

function changePage(page) {
    if (page >= 1 && page <= pagination.value.last_page) {
        fetchReports(page);
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
        case 'delivered':
        case 'opened':
        case 'clicked': return 'bg-success-subtle text-success';
        case 'sent': return 'bg-primary-subtle text-primary';
        case 'simulated': return 'bg-info-subtle text-info';
        case 'failed': return 'bg-danger-subtle text-danger';
        default: return 'bg-secondary-subtle text-secondary';
    }
}

function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString() + ' ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

onMounted(() => {
    fetchReports();
});
</script>

<style scoped>
.extra-small {
    font-size: 0.72rem;
}
</style>
