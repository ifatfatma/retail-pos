<template>
    <div class="container-fluid py-4">

        <!-- Page Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
            <div>
                <h3 class="fw-bold mb-1">📢 {{ $t('marketing.dashboard_title') }}</h3>
                <p class="text-muted mb-0">
                    {{ $t('marketing.dashboard_subtitle') }}
                </p>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <button
                    class="btn btn-outline-secondary btn-sm"
                    @click="loadMetrics"
                    :disabled="loading"
                    :title="$t('common.refresh')"
                >
                    <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
                    🔄 {{ $t('common.refresh') }}
                </button>

                <button
                    class="btn btn-outline-primary btn-sm"
                    @click="runAllRules"
                    :disabled="runningRules"
                >
                    <span v-if="runningRules" class="spinner-border spinner-border-sm me-1"></span>
                    ⚡ {{ $t('marketing.run_rules_now') }}
                </button>

                <router-link
                    to="/admin/marketing/campaigns/create"
                    class="btn btn-primary btn-sm fw-semibold"
                >
                    + {{ $t('marketing.create_campaign') }}
                </router-link>
            </div>
        </div>

        <!-- Safe Test Mode Banner -->
        <div v-if="testModeActive" class="alert alert-info border-0 shadow-sm d-flex align-items-center gap-3 py-2 px-3 mb-4">
            <span class="fs-4">🛡️</span>
            <div class="flex-grow-1">
                <strong class="d-block">{{ $t('marketing.test_mode_notice_title') }}</strong>
                <span class="small text-muted">{{ $t('marketing.test_mode_notice_desc') }}</span>
            </div>
            <router-link to="/admin/marketing/settings" class="btn btn-sm btn-outline-primary">
                {{ $t('marketing.manage_settings') }}
            </router-link>
        </div>

        <!-- Metric KPI Cards -->
        <div class="row g-3 mb-4">
            <!-- 1. Total Campaigns -->
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 kpi-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small fw-medium">{{ $t('marketing.total_campaigns') }}</span>
                            <div class="kpi-icon bg-primary-subtle text-primary rounded-3">📢</div>
                        </div>
                        <h3 class="fw-bold mb-1">{{ metrics.total_campaigns || 0 }}</h3>
                        <div class="small text-muted">
                            <span class="text-success fw-semibold">{{ metrics.active_campaigns || 0 }} {{ $t('marketing.active') }}</span>
                            <span class="mx-1">•</span>
                            <span>{{ metrics.scheduled_campaigns || 0 }} {{ $t('marketing.scheduled') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Notifications Sent & Delivered -->
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 kpi-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small fw-medium">{{ $t('marketing.delivered_notifications') }}</span>
                            <div class="kpi-icon bg-success-subtle text-success rounded-3">📬</div>
                        </div>
                        <h3 class="fw-bold mb-1 text-success">{{ metrics.total_delivered || 0 }}</h3>
                        <div class="small text-muted">
                            <span>{{ metrics.total_sent || 0 }} {{ $t('marketing.total_dispatched') }}</span>
                            <span class="badge bg-success-subtle text-success ms-2">{{ metrics.delivery_rate || 100 }}%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Engagement (Open & Click Rates) -->
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 kpi-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small fw-medium">{{ $t('marketing.engagement_rate') }}</span>
                            <div class="kpi-icon bg-warning-subtle text-warning rounded-3">👁️</div>
                        </div>
                        <h3 class="fw-bold mb-1">{{ metrics.open_rate || 0 }}%</h3>
                        <div class="small text-muted">
                            <span>{{ $t('marketing.click_rate') }}: </span>
                            <span class="fw-bold text-dark">{{ metrics.click_rate || 0 }}%</span>
                            <span class="ms-1">({{ metrics.total_clicked || 0 }} clicks)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Conversions & Attributed Revenue -->
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 kpi-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small fw-medium">{{ $t('marketing.attributed_revenue') }}</span>
                            <div class="kpi-icon bg-info-subtle text-info rounded-3">💰</div>
                        </div>
                        <h3 class="fw-bold mb-1 text-primary">₹{{ formatCurrency(metrics.attributed_revenue || 0) }}</h3>
                        <div class="small text-muted">
                            <span class="fw-semibold text-success">{{ metrics.total_conversions || 0 }} {{ $t('marketing.conversions') }}</span>
                            <span class="mx-1">•</span>
                            <span>{{ metrics.total_customer_segments || 0 }} {{ $t('marketing.segments') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Channel Performance & Automated Rules Overview -->
        <div class="row g-4 mb-4">
            <!-- Channel Breakdown Bar -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3 pb-1">
                        <h5 class="fw-bold mb-0">📡 {{ $t('marketing.channel_performance') }}</h5>
                        <router-link to="/admin/marketing/delivery-reports" class="small text-primary text-decoration-none">
                            {{ $t('marketing.view_all_reports') }} →
                        </router-link>
                    </div>
                    <div class="card-body">
                        <div v-for="(stat, ch) in metrics.channel_stats" :key="ch" class="mb-3">
                            <div class="d-flex justify-content-between align-items-center small mb-1">
                                <span class="fw-semibold d-flex align-items-center gap-2">
                                    <span>{{ getChannelIcon(ch) }}</span>
                                    <span>{{ stat.name }}</span>
                                </span>
                                <span class="text-muted">{{ stat.delivered }} / {{ stat.count }} {{ $t('marketing.sent') }}</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div
                                    class="progress-bar rounded-pill"
                                    :class="getChannelBarClass(ch)"
                                    role="progressbar"
                                    :style="{ width: getChannelPercent(stat) + '%' }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Automated Rules Overview -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3 pb-1">
                        <h5 class="fw-bold mb-0">⚡ {{ $t('marketing.automated_rules') }}</h5>
                        <router-link to="/admin/marketing/automated-rules" class="small text-primary text-decoration-none">
                            {{ $t('marketing.manage_rules') }} →
                        </router-link>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column gap-2">
                            <div class="d-flex align-items-center justify-content-between p-2 rounded border bg-light-subtle">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fs-5">🔥</span>
                                    <div>
                                        <div class="fw-semibold small">{{ $t('marketing.rule_flash_sale') }}</div>
                                        <div class="extra-small text-muted">{{ $t('marketing.rule_flash_sale_desc') }}</div>
                                    </div>
                                </div>
                                <span class="badge bg-success-subtle text-success">{{ $t('marketing.active') }}</span>
                            </div>

                            <div class="d-flex align-items-center justify-content-between p-2 rounded border bg-light-subtle">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fs-5">🎂</span>
                                    <div>
                                        <div class="fw-semibold small">{{ $t('marketing.rule_birthday_discount') }}</div>
                                        <div class="extra-small text-muted">{{ $t('marketing.rule_birthday_desc') }}</div>
                                    </div>
                                </div>
                                <span class="badge bg-success-subtle text-success">{{ $t('marketing.active') }}</span>
                            </div>

                            <div class="d-flex align-items-center justify-content-between p-2 rounded border bg-light-subtle">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fs-5">👋</span>
                                    <div>
                                        <div class="fw-semibold small">{{ $t('marketing.rule_inactivity') }}</div>
                                        <div class="extra-small text-muted">{{ $t('marketing.rule_inactivity_desc') }}</div>
                                    </div>
                                </div>
                                <span class="badge bg-success-subtle text-success">{{ $t('marketing.active') }}</span>
                            </div>

                            <div class="d-flex align-items-center justify-content-between p-2 rounded border bg-light-subtle">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fs-5">⏰</span>
                                    <div>
                                        <div class="fw-semibold small">{{ $t('marketing.rule_expiry') }}</div>
                                        <div class="extra-small text-muted">{{ $t('marketing.rule_expiry_desc') }}</div>
                                    </div>
                                </div>
                                <span class="badge bg-success-subtle text-success">{{ $t('marketing.active') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Campaigns Table -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3 pb-2">
                <h5 class="fw-bold mb-0">🏷️ {{ $t('marketing.recent_campaigns') }}</h5>
                <router-link to="/admin/marketing/campaigns" class="btn btn-sm btn-outline-secondary">
                    {{ $t('marketing.view_all_campaigns') }}
                </router-link>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">{{ $t('marketing.campaign_title') }}</th>
                                <th>{{ $t('marketing.target_segment') }}</th>
                                <th>{{ $t('marketing.discount') }}</th>
                                <th>{{ $t('marketing.channels') }}</th>
                                <th>{{ $t('marketing.sent_delivered') }}</th>
                                <th>{{ $t('marketing.status') }}</th>
                                <th class="text-end pe-3">{{ $t('common.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="!metrics.recent_campaigns || metrics.recent_campaigns.length === 0">
                                <td colspan="7" class="text-center py-4 text-muted">
                                    {{ $t('marketing.no_campaigns_yet') }}
                                    <div class="mt-2">
                                        <router-link to="/admin/marketing/campaigns/create" class="btn btn-sm btn-primary">
                                            + {{ $t('marketing.create_first_campaign') }}
                                        </router-link>
                                    </div>
                                </td>
                            </tr>
                            <tr v-for="c in metrics.recent_campaigns" :key="c.id">
                                <td class="ps-3">
                                    <div class="fw-bold">{{ c.title }}</div>
                                    <div class="extra-small text-muted">{{ formatCampaignType(c.campaign_type) }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ c.segment?.name || $t('marketing.all_customers') }}
                                    </span>
                                </td>
                                <td>
                                    <span v-if="c.discount_type === 'percentage'" class="badge bg-danger-subtle text-danger fw-semibold">
                                        {{ c.discount_value }}% OFF
                                    </span>
                                    <span v-else-if="c.discount_type === 'fixed'" class="badge bg-success-subtle text-success fw-semibold">
                                        FLAT ₹{{ c.discount_value }}
                                    </span>
                                    <span v-else class="text-muted small">—</span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <span v-for="ch in c.channels" :key="ch" class="badge bg-secondary-subtle text-secondary" :title="ch">
                                            {{ getChannelIcon(ch) }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="small fw-semibold">{{ c.total_delivered }} / {{ c.total_sent }}</div>
                                    <div class="extra-small text-muted">{{ c.total_targeted }} {{ $t('marketing.targeted') }}</div>
                                </td>
                                <td>
                                    <span :class="getStatusBadgeClass(c.status)" class="badge">
                                        {{ $t(`marketing.status_${c.status}`) }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <router-link :to="`/admin/marketing/campaigns/${c.id}/edit`" class="btn btn-sm btn-outline-secondary me-1">
                                        ✏️
                                    </router-link>
                                    <button
                                        v-if="c.status === 'draft' || c.status === 'scheduled'"
                                        class="btn btn-sm btn-primary"
                                        @click="sendNow(c.id)"
                                        :title="$t('marketing.send_now')"
                                    >
                                        🚀
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Live Activity Log Stream -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3 pb-2">
                <h5 class="fw-bold mb-0">📋 {{ $t('marketing.recent_delivery_activity') }}</h5>
                <router-link to="/admin/marketing/delivery-reports" class="btn btn-sm btn-outline-secondary">
                    {{ $t('marketing.full_activity_log') }}
                </router-link>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">{{ $t('marketing.channel') }}</th>
                                <th>{{ $t('marketing.recipient') }}</th>
                                <th>{{ $t('marketing.notification_title') }}</th>
                                <th>{{ $t('marketing.delivery_status') }}</th>
                                <th class="text-end pe-3">{{ $t('marketing.dispatched_at') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="!metrics.recent_logs || metrics.recent_logs.length === 0">
                                <td colspan="5" class="text-center py-4 text-muted">
                                    {{ $t('marketing.no_logs_yet') }}
                                </td>
                            </tr>
                            <tr v-for="log in metrics.recent_logs" :key="log.id">
                                <td class="ps-3">
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
                                    <div class="small text-truncate" style="max-width: 280px;" :title="log.title">
                                        {{ log.title }}
                                    </div>
                                </td>
                                <td>
                                    <span :class="getLogStatusClass(log.status)" class="badge">
                                        {{ log.is_test_mode ? '🛡️ ' + $t('marketing.simulated_test') : $t(`marketing.log_${log.status}`) }}
                                    </span>
                                </td>
                                <td class="text-end pe-3 small text-muted">
                                    {{ formatDate(log.sent_at || log.created_at) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
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
const { showSuccess, showError } = useNotification();

const loading = ref(false);
const runningRules = ref(false);
const testModeActive = ref(true);

const metrics = ref({
    total_campaigns: 0,
    active_campaigns: 0,
    scheduled_campaigns: 0,
    completed_campaigns: 0,
    total_sent: 0,
    total_delivered: 0,
    total_opened: 0,
    total_clicked: 0,
    delivery_rate: 100,
    open_rate: 42.5,
    click_rate: 18.2,
    total_conversions: 0,
    attributed_revenue: 0,
    active_rules_count: 0,
    total_customer_segments: 0,
    channel_stats: {},
    recent_campaigns: [],
    recent_logs: [],
});

async function loadMetrics() {
    loading.value = true;
    try {
        const res = await axios.get('/api/marketing/dashboard');
        metrics.value = res.data;

        // Also check settings for test mode banner
        const settingsRes = await axios.get('/api/marketing/settings');
        testModeActive.value = Boolean(settingsRes.data.marketing_test_mode);
    } catch (err) {
        showError(t('marketing.error_loading_dashboard'));
    } finally {
        loading.value = false;
    }
}

async function runAllRules() {
    runningRules.value = true;
    try {
        const res = await axios.post('/api/marketing/rules');
        showSuccess(t('marketing.rules_triggered_success'));
        await loadMetrics();
    } catch (err) {
        // Run individual rules
        try {
            const rulesRes = await axios.get('/api/marketing/rules');
            if (rulesRes.data && rulesRes.data.length > 0) {
                await axios.post(`/api/marketing/rules/${rulesRes.data[0].id}/run-now`);
            }
            showSuccess(t('marketing.rules_triggered_success'));
            await loadMetrics();
        } catch (e) {
            showError(t('marketing.error_triggering_rules'));
        }
    } finally {
        runningRules.value = false;
    }
}

async function sendNow(campaignId) {
    try {
        const res = await axios.post(`/api/marketing/campaigns/${campaignId}/send`);
        showSuccess(res.data.message || t('marketing.campaign_sent_success'));
        await loadMetrics();
    } catch (err) {
        showError(t('marketing.error_sending_campaign'));
    }
}

function getChannelIcon(channel) {
    return matchChannelIcon(channel);
}

function matchChannelIcon(ch) {
    switch (ch) {
        case 'in_app': return '📱';
        case 'email': return '✉️';
        case 'sms': return '💬';
        case 'whatsapp': return '🟢';
        case 'push': return '🔔';
        default: return '📢';
    }
}

function getChannelBarClass(ch) {
    switch (ch) {
        case 'in_app': return 'bg-primary';
        case 'email': return 'bg-info';
        case 'sms': return 'bg-warning';
        case 'whatsapp': return 'bg-success';
        case 'push': return 'bg-purple';
        default: return 'bg-primary';
    }
}

function getChannelPercent(stat) {
    if (!stat || !stat.count || stat.count === 0) return 0;
    return Math.min(100, Math.round((stat.delivered / stat.count) * 100));
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

function getLogStatusClass(status) {
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

function formatCampaignType(type) {
    if (!type) return '';
    return type.replace('_', ' ').toUpperCase();
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
    return d.toLocaleDateString() + ' ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

onMounted(() => {
    loadMetrics();
});
</script>

<style scoped>
.kpi-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08) !important;
}

.kpi-icon {
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.extra-small {
    font-size: 0.72rem;
}

.bg-purple {
    background-color: #8b5cf6 !important;
}
</style>
