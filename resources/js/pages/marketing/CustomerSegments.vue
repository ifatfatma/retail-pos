<template>
    <div class="container-fluid py-4">

        <!-- Page Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
            <div>
                <h3 class="fw-bold mb-1">🎯 {{ $t('marketing.segments_title') }}</h3>
                <p class="text-muted mb-0">
                    {{ $t('marketing.segments_subtitle') }}
                </p>
            </div>

            <div class="d-flex gap-2">
                <button
                    class="btn btn-outline-secondary btn-sm"
                    @click="fetchSegments"
                    :disabled="loading"
                    :title="$t('common.refresh')"
                >
                    <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
                    🔄 {{ $t('common.refresh') }}
                </button>

                <button class="btn btn-primary btn-sm fw-semibold" @click="openCreateModal">
                    + {{ $t('marketing.add_segment') }}
                </button>
            </div>
        </div>

        <!-- Segments Grid -->
        <div class="row g-3">
            <div v-if="loading" class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <div class="small text-muted mt-2">{{ $t('common.loading') }}...</div>
            </div>

            <div v-for="seg in segments" :key="seg.id" class="col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100 segment-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h5 class="fw-bold mb-1 text-dark">{{ seg.name }}</h5>
                                <span v-if="seg.is_system" class="badge bg-secondary-subtle text-secondary extra-small">
                                    🔒 {{ $t('marketing.system_segment') }}
                                </span>
                                <span v-else class="badge bg-primary-subtle text-primary extra-small">
                                    ✨ {{ $t('marketing.custom_segment') }}
                                </span>
                            </div>

                            <div class="text-end">
                                <span class="badge bg-success-subtle text-success fs-6 fw-bold px-2 py-1">
                                    {{ seg.customer_count }}
                                </span>
                                <div class="extra-small text-muted">{{ $t('marketing.matched_customers') }}</div>
                            </div>
                        </div>

                        <p class="small text-muted mb-3 line-clamp-2">
                            {{ seg.description || 'Target specific shoppers by purchasing patterns' }}
                        </p>

                        <!-- Criteria Tags -->
                        <div class="bg-light-subtle rounded p-2 border mb-3 small">
                            <div class="fw-semibold extra-small text-muted mb-1">{{ $t('marketing.target_criteria') }}:</div>
                            <div class="d-flex flex-wrap gap-1">
                                <span v-if="seg.criteria?.min_spent" class="badge bg-light text-dark border">
                                    Min Spend: ₹{{ seg.criteria.min_spent }}
                                </span>
                                <span v-if="seg.criteria?.min_orders" class="badge bg-light text-dark border">
                                    Min Orders: {{ seg.criteria.min_orders }}
                                </span>
                                <span v-if="seg.criteria?.max_orders" class="badge bg-light text-dark border">
                                    Max Orders: {{ seg.criteria.max_orders }}
                                </span>
                                <span v-if="seg.criteria?.inactivity_days" class="badge bg-warning-subtle text-warning-emphasis border">
                                    Inactive: > {{ seg.criteria.inactivity_days }}d
                                </span>
                                <span v-if="seg.criteria?.loyalty_tiers" class="badge bg-info-subtle text-info-emphasis border">
                                    Tiers: {{ seg.criteria.loyalty_tiers.join(', ') }}
                                </span>
                                <span v-if="seg.criteria?.birthday_month" class="badge bg-danger-subtle text-danger-emphasis border">
                                    Birthday in Month: {{ seg.criteria.birthday_month }}
                                </span>
                                <span v-if="!seg.criteria || Object.keys(seg.criteria).length === 0" class="badge bg-light text-muted border">
                                    All Active Registered Shoppers
                                </span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary btn-sm flex-grow-1" @click="viewCustomers(seg)">
                                👥 {{ $t('marketing.view_customers') }}
                            </button>
                            <button v-if="!seg.is_system" class="btn btn-outline-secondary btn-sm" @click="openEditModal(seg)">
                                ✏️
                            </button>
                            <button v-if="!seg.is_system" class="btn btn-outline-danger btn-sm" @click="confirmDelete(seg)">
                                🗑️
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Matching Customers Drawer / Modal -->
        <div v-if="showCustomersModal" class="modal d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title fw-bold mb-0">👥 {{ activeSegment?.name }}</h5>
                            <span class="small text-muted">{{ activeSegmentCustomers.length }} {{ $t('marketing.sample_customers_shown') }}</span>
                        </div>
                        <button type="button" class="btn-close" @click="showCustomersModal = false"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">{{ $t('marketing.customer_name') }}</th>
                                        <th>{{ $t('marketing.contact') }}</th>
                                        <th>{{ $t('marketing.total_orders') }}</th>
                                        <th>{{ $t('marketing.total_spent') }}</th>
                                        <th>{{ $t('marketing.loyalty_tier') }}</th>
                                        <th class="pe-3">{{ $t('marketing.city') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="activeSegmentCustomers.length === 0">
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            {{ $t('marketing.no_matching_customers') }}
                                        </td>
                                    </tr>
                                    <tr v-for="cust in activeSegmentCustomers" :key="cust.id">
                                        <td class="ps-3 fw-semibold">{{ cust.name }}</td>
                                        <td class="small">
                                            <div>{{ cust.email || '—' }}</div>
                                            <div class="text-muted">{{ cust.phone || '—' }}</div>
                                        </td>
                                        <td>{{ cust.total_orders }}</td>
                                        <td class="fw-semibold text-primary">₹{{ cust.total_spent }}</td>
                                        <td>
                                            <span class="badge bg-warning-subtle text-dark text-capitalize">
                                                {{ cust.loyalty_tier || 'Bronze' }}
                                            </span>
                                        </td>
                                        <td class="pe-3 text-muted small">{{ cust.city || '—' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" @click="showCustomersModal = false">
                            {{ $t('common.close') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create / Edit Custom Segment Modal -->
        <div v-if="showFormModal" class="modal d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">
                            {{ formIsEdit ? $t('marketing.edit_segment') : $t('marketing.add_segment') }}
                        </h5>
                        <button type="button" class="btn-close" @click="showFormModal = false"></button>
                    </div>
                    <form @submit.prevent="saveSegment">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">{{ $t('marketing.segment_name') }} *</label>
                                <input v-model="form.name" type="text" class="form-control" required placeholder="e.g. Pune Festival Shoppers" />
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">{{ $t('marketing.segment_description') }}</label>
                                <textarea v-model="form.description" class="form-control" rows="2" placeholder="Description of this customer group"></textarea>
                            </div>

                            <h6 class="fw-bold small border-bottom pb-1 mb-3">{{ $t('marketing.segment_rules_criteria') }}</h6>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-semibold extra-small">{{ $t('marketing.min_spent') }} (₹)</label>
                                    <input v-model.number="form.criteria.min_spent" type="number" class="form-control form-control-sm" placeholder="1000" />
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold extra-small">{{ $t('marketing.max_spent') }} (₹)</label>
                                    <input v-model.number="form.criteria.max_spent" type="number" class="form-control form-control-sm" placeholder="50000" />
                                </div>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-semibold extra-small">{{ $t('marketing.min_orders') }}</label>
                                    <input v-model.number="form.criteria.min_orders" type="number" class="form-control form-control-sm" placeholder="2" />
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold extra-small">{{ $t('marketing.inactivity_days') }}</label>
                                    <input v-model.number="form.criteria.inactivity_days" type="number" class="form-control form-control-sm" placeholder="30" />
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold extra-small">{{ $t('marketing.loyalty_tier') }}</label>
                                <div class="d-flex gap-2">
                                    <label v-for="tier in ['bronze', 'silver', 'gold', 'platinum']" :key="tier" class="badge bg-light text-dark border p-2 cursor-pointer">
                                        <input type="checkbox" v-model="form.criteria.loyalty_tiers" :value="tier" class="form-check-input mt-0 me-1" />
                                        <span class="text-capitalize">{{ tier }}</span>
                                    </label>
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" @click="showFormModal = false">
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
const segments = ref([]);

const showCustomersModal = ref(false);
const activeSegment = ref(null);
const activeSegmentCustomers = ref([]);

const showFormModal = ref(false);
const formIsEdit = ref(false);
const editId = ref(null);

const form = ref({
    name: '',
    description: '',
    criteria: {
        min_spent: null,
        max_spent: null,
        min_orders: null,
        inactivity_days: null,
        loyalty_tiers: [],
    },
});

async function fetchSegments() {
    loading.value = true;
    try {
        const res = await axios.get('/api/marketing/segments');
        segments.value = res.data;
    } catch (err) {
        showError(t('marketing.error_loading_segments'));
    } finally {
        loading.value = false;
    }
}

async function viewCustomers(seg) {
    activeSegment.value = seg;
    try {
        const res = await axios.get(`/api/marketing/segments/${seg.id}/customers`);
        activeSegmentCustomers.value = res.data.customers;
        showCustomersModal.value = true;
    } catch (err) {
        showError(t('marketing.error_loading_segment_customers'));
    }
}

function openCreateModal() {
    formIsEdit.value = false;
    editId.value = null;
    form.value = {
        name: '',
        description: '',
        criteria: {
            min_spent: null,
            max_spent: null,
            min_orders: null,
            inactivity_days: null,
            loyalty_tiers: [],
        },
    };
    showFormModal.value = true;
}

function openEditModal(seg) {
    formIsEdit.value = true;
    editId.value = seg.id;
    form.value = {
        name: seg.name,
        description: seg.description || '',
        criteria: {
            min_spent: seg.criteria?.min_spent || null,
            max_spent: seg.criteria?.max_spent || null,
            min_orders: seg.criteria?.min_orders || null,
            inactivity_days: seg.criteria?.inactivity_days || null,
            loyalty_tiers: seg.criteria?.loyalty_tiers || [],
        },
    };
    showFormModal.value = true;
}

async function saveSegment() {
    if (!form.value.name.trim()) return;
    saving.value = true;
    try {
        if (formIsEdit.value) {
            await axios.put(`/api/marketing/segments/${editId.value}`, form.value);
            showSuccess(t('marketing.segment_updated_success'));
        } else {
            await axios.post('/api/marketing/segments', form.value);
            showSuccess(t('marketing.segment_created_success'));
        }
        showFormModal.value = false;
        fetchSegments();
    } catch (err) {
        showError(t('marketing.error_saving_segment'));
    } finally {
        saving.value = false;
    }
}

async function confirmDelete(seg) {
    const ok = await confirmDialog({
        title: t('common.delete'),
        message: t('marketing.confirm_delete_segment', { name: seg.name }),
        confirmText: t('common.delete'),
        cancelText: t('common.cancel'),
        type: 'danger',
    });
    if (!ok) return;

    try {
        await axios.delete(`/api/marketing/segments/${seg.id}`);
        showSuccess(t('marketing.segment_deleted_success'));
        fetchSegments();
    } catch (err) {
        showError(t('marketing.error_deleting_segment'));
    }
}

onMounted(() => {
    fetchSegments();
});
</script>

<style scoped>
.segment-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.segment-card:hover {
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
