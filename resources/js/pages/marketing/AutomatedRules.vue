<template>
    <div class="container-fluid py-4">

        <!-- Page Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
            <div>
                <h3 class="fw-bold mb-1">⚡ {{ $t('marketing.automated_rules_title') }}</h3>
                <p class="text-muted mb-0">
                    {{ $t('marketing.automated_rules_subtitle') }}
                </p>
            </div>

            <div class="d-flex gap-2">
                <button
                    class="btn btn-outline-secondary btn-sm"
                    @click="fetchRules"
                    :disabled="loading"
                    :title="$t('common.refresh')"
                >
                    <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
                    🔄 {{ $t('common.refresh') }}
                </button>

                <button class="btn btn-primary btn-sm fw-semibold" @click="openCreateModal">
                    + {{ $t('marketing.add_rule') }}
                </button>
            </div>
        </div>

        <!-- Rules Grid -->
        <div class="row g-3">
            <div v-if="loading" class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <div class="small text-muted mt-2">{{ $t('common.loading') }}...</div>
            </div>

            <div v-else-if="rules.length === 0" class="col-12 text-center py-5 text-muted">
                <div class="fs-1 mb-2">⚡</div>
                <div class="fw-bold">{{ $t('marketing.no_rules_found') }}</div>
            </div>

            <div v-for="rule in rules" :key="rule.id" class="col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100 rule-card position-relative">
                    <div class="card-body">
                        <!-- Top Header: Event Icon & Status Switch -->
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="fs-3">{{ getEventIcon(rule.event_type) }}</span>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">{{ rule.name }}</h6>
                                    <span class="badge bg-light text-muted border extra-small">
                                        {{ formatEventType(rule.event_type) }}
                                    </span>
                                </div>
                            </div>
                            <div class="form-check form-switch ms-2">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    :checked="rule.is_active"
                                    @change="toggleActive(rule)"
                                    :title="rule.is_active ? $t('common.active') : $t('common.inactive')"
                                />
                            </div>
                        </div>

                        <p class="small text-muted mb-3 line-clamp-2">
                            {{ rule.description || 'Automatic trigger rule' }}
                        </p>

                        <!-- Rule Details Grid -->
                        <div class="bg-light-subtle rounded p-2 border mb-3 small">
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">{{ $t('marketing.target_segment') }}:</span>
                                <span class="fw-semibold text-primary">{{ rule.segment?.name || $t('marketing.all_customers') }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">{{ $t('marketing.template') }}:</span>
                                <span class="fw-semibold text-truncate" style="max-width: 140px;">{{ rule.template?.name || 'Default' }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">{{ $t('marketing.frequency') }}:</span>
                                <span class="badge bg-secondary-subtle text-secondary text-capitalize">{{ rule.frequency }} ({{ rule.run_time || '09:00' }})</span>
                            </div>
                            <div class="d-flex justify-content-between py-1">
                                <span class="text-muted">{{ $t('marketing.channels') }}:</span>
                                <div class="d-flex gap-1">
                                    <span v-for="ch in rule.channels" :key="ch" class="badge bg-white border" :title="ch">
                                        {{ getChannelIcon(ch) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Execution Stats -->
                        <div class="d-flex justify-content-between align-items-center extra-small text-muted mb-3">
                            <span>Runs: <strong>{{ rule.total_runs }}</strong></span>
                            <span>Triggers: <strong class="text-success">{{ rule.total_triggers }}</strong></span>
                            <span>Last: {{ formatDate(rule.last_run_at) || 'Never' }}</span>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex gap-2">
                            <button
                                class="btn btn-outline-primary btn-sm flex-grow-1"
                                @click="runRuleNow(rule)"
                                :disabled="runningRuleId === rule.id"
                            >
                                <span v-if="runningRuleId === rule.id" class="spinner-border spinner-border-sm me-1"></span>
                                ⚡ {{ $t('marketing.run_now') }}
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" @click="openEditModal(rule)">
                                ✏️
                            </button>
                            <button class="btn btn-outline-danger btn-sm" @click="confirmDelete(rule)">
                                🗑️
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Create / Edit Rule Modal -->
        <div v-if="showModal" class="modal d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">
                            {{ modalEdit ? $t('marketing.edit_rule') : $t('marketing.add_rule') }}
                        </h5>
                        <button type="button" class="btn-close" @click="showModal = false"></button>
                    </div>
                    <form @submit.prevent="saveRule">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">{{ $t('marketing.rule_name') }} *</label>
                                <input v-model="form.name" type="text" class="form-control" required />
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">{{ $t('marketing.trigger_event') }} *</label>
                                <select v-model="form.event_type" class="form-select" required>
                                    <option value="flash_sale_started">⚡ Flash Sale Started</option>
                                    <option value="offer_expiring_soon">⏰ Offer Expiring Soon</option>
                                    <option value="new_product_announcement">✨ New Product Announcement</option>
                                    <option value="product_back_in_stock">📦 Product Back in Stock</option>
                                    <option value="customer_birthday">🎂 Customer Birthday</option>
                                    <option value="customer_inactivity">👋 Customer Inactivity Reminder</option>
                                    <option value="loyalty_reward_earned">⭐ Loyalty Reward Milestone</option>
                                    <option value="special_customer_discount">🎁 Special Customer Discount</option>
                                    <option value="seasonal_festival_offer">🎉 Seasonal & Festival Offer</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">{{ $t('marketing.target_segment') }}</label>
                                <select v-model="form.segment_id" class="form-select">
                                    <option :value="null">{{ $t('marketing.all_customers') }}</option>
                                    <option v-for="seg in segments" :key="seg.id" :value="seg.id">
                                        {{ seg.name }} ({{ seg.customer_count }} {{ $t('marketing.customers') }})
                                    </option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">{{ $t('marketing.notification_template') }}</label>
                                <select v-model="form.template_id" class="form-select">
                                    <option :value="null">{{ $t('marketing.default_template') }}</option>
                                    <option v-for="t in templates" :key="t.id" :value="t.id">
                                        {{ t.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small d-block">{{ $t('marketing.delivery_channels') }} *</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <label class="badge bg-light text-dark border p-2 d-flex align-items-center gap-1 cursor-pointer">
                                        <input type="checkbox" v-model="form.channels" value="in_app" class="form-check-input mt-0 me-1" />
                                        📱 In-App
                                    </label>
                                    <label class="badge bg-light text-dark border p-2 d-flex align-items-center gap-1 cursor-pointer">
                                        <input type="checkbox" v-model="form.channels" value="email" class="form-check-input mt-0 me-1" />
                                        ✉️ Email
                                    </label>
                                    <label class="badge bg-light text-dark border p-2 d-flex align-items-center gap-1 cursor-pointer">
                                        <input type="checkbox" v-model="form.channels" value="sms" class="form-check-input mt-0 me-1" />
                                        💬 SMS
                                    </label>
                                    <label class="badge bg-light text-dark border p-2 d-flex align-items-center gap-1 cursor-pointer">
                                        <input type="checkbox" v-model="form.channels" value="whatsapp" class="form-check-input mt-0 me-1" />
                                        🟢 WhatsApp
                                    </label>
                                    <label class="badge bg-light text-dark border p-2 d-flex align-items-center gap-1 cursor-pointer">
                                        <input type="checkbox" v-model="form.channels" value="push" class="form-check-input mt-0 me-1" />
                                        🔔 Push
                                    </label>
                                </div>
                            </div>

                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label fw-semibold small">{{ $t('marketing.frequency') }}</label>
                                    <select v-model="form.frequency" class="form-select form-select-sm">
                                        <option value="immediate">Immediate</option>
                                        <option value="daily">Daily</option>
                                        <option value="weekly">Weekly</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold small">Run Time</label>
                                    <input v-model="form.run_time" type="time" class="form-control form-control-sm" />
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" @click="showModal = false">
                                {{ $t('common.cancel') }}
                            </button>
                            <button type="submit" class="btn btn-primary" :disabled="saving">
                                <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                                {{ $t('common.save') }}
                            </button>
                        </div>
                    </form>
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
const { showSuccess, showError, confirmDialog } = useNotification();

const loading = ref(false);
const saving = ref(false);
const runningRuleId = ref(null);
const rules = ref([]);
const segments = ref([]);
const templates = ref([]);

const showModal = ref(false);
const modalEdit = ref(false);
const editId = ref(null);

const form = ref({
    name: '',
    event_type: 'flash_sale_started',
    description: '',
    segment_id: null,
    template_id: null,
    channels: ['in_app', 'email'],
    frequency: 'daily',
    run_time: '09:00',
    conditions: {},
    is_active: true,
});

async function fetchRules() {
    loading.value = true;
    try {
        const [rRes, sRes, tRes] = await Promise.all([
            axios.get('/api/marketing/rules'),
            axios.get('/api/marketing/segments'),
            axios.get('/api/marketing/templates'),
        ]);
        rules.value = rRes.data;
        segments.value = sRes.data;
        templates.value = tRes.data;
    } catch (err) {
        showError(t('marketing.error_loading_rules'));
    } finally {
        loading.value = false;
    }
}

async function runRuleNow(rule) {
    runningRuleId.value = rule.id;
    try {
        const res = await axios.post(`/api/marketing/rules/${rule.id}/run-now`);
        showSuccess(res.data.message || t('marketing.rule_executed_success'));
        fetchRules();
    } catch (err) {
        showError(t('marketing.error_executing_rule'));
    } finally {
        runningRuleId.value = null;
    }
}

async function toggleActive(rule) {
    try {
        const res = await axios.post(`/api/marketing/rules/${rule.id}/toggle-active`);
        rule.is_active = res.data.rule.is_active;
        showSuccess(res.data.message);
    } catch (err) {
        showError(t('marketing.error_updating_rule'));
    }
}

function openCreateModal() {
    modalEdit.value = false;
    editId.value = null;
    form.value = {
        name: '',
        event_type: 'flash_sale_started',
        description: '',
        segment_id: null,
        template_id: null,
        channels: ['in_app', 'email'],
        frequency: 'daily',
        run_time: '09:00',
        conditions: {},
        is_active: true,
    };
    showModal.value = true;
}

function openEditModal(rule) {
    modalEdit.value = true;
    editId.value = rule.id;
    form.value = {
        name: rule.name,
        event_type: rule.event_type,
        description: rule.description || '',
        segment_id: rule.segment_id,
        template_id: rule.template_id,
        channels: rule.channels || ['in_app'],
        frequency: rule.frequency,
        run_time: rule.run_time || '09:00',
        conditions: rule.conditions || {},
        is_active: rule.is_active,
    };
    showModal.value = true;
}

async function saveRule() {
    if (!form.value.name.trim()) return;
    saving.value = true;
    try {
        if (modalEdit.value) {
            await axios.put(`/api/marketing/rules/${editId.value}`, form.value);
            showSuccess(t('marketing.rule_updated_success'));
        } else {
            await axios.post('/api/marketing/rules', form.value);
            showSuccess(t('marketing.rule_created_success'));
        }
        showModal.value = false;
        fetchRules();
    } catch (err) {
        showError(t('marketing.error_saving_rule'));
    } finally {
        saving.value = false;
    }
}

async function confirmDelete(rule) {
    const ok = await confirmDialog({
        title: t('common.delete'),
        message: t('marketing.confirm_delete_rule', { name: rule.name }),
        confirmText: t('common.delete'),
        cancelText: t('common.cancel'),
        type: 'danger',
    });
    if (!ok) return;

    try {
        await axios.delete(`/api/marketing/rules/${rule.id}`);
        showSuccess(t('marketing.rule_deleted_success'));
        fetchRules();
    } catch (err) {
        showError(t('marketing.error_deleting_rule'));
    }
}

function getEventIcon(type) {
    switch (type) {
        case 'flash_sale_started': return '⚡';
        case 'offer_expiring_soon': return '⏰';
        case 'new_product_announcement': return '✨';
        case 'product_back_in_stock': return '📦';
        case 'customer_birthday': return '🎂';
        case 'customer_inactivity': return '👋';
        case 'loyalty_reward_earned': return '⭐';
        case 'special_customer_discount': return '🎁';
        case 'seasonal_festival_offer': return '🎉';
        default: return '📢';
    }
}

function formatEventType(type) {
    if (!type) return '';
    return type.replace(/_/g, ' ').toUpperCase();
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

function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString();
}

onMounted(() => {
    fetchRules();
});
</script>

<style scoped>
.rule-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.rule-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.08) !important;
}

.extra-small {
    font-size: 0.72rem;
}

.cursor-pointer {
    cursor: pointer;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
