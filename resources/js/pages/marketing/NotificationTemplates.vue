<template>
    <div class="container-fluid py-4">

        <!-- Page Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
            <div>
                <h3 class="fw-bold mb-1">📄 {{ $t('marketing.templates_title') }}</h3>
                <p class="text-muted mb-0">
                    {{ $t('marketing.templates_subtitle') }}
                </p>
            </div>

            <div class="d-flex gap-2">
                <button
                    class="btn btn-outline-secondary btn-sm"
                    @click="fetchTemplates"
                    :disabled="loading"
                    :title="$t('common.refresh')"
                >
                    <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
                    🔄 {{ $t('common.refresh') }}
                </button>

                <button class="btn btn-primary btn-sm fw-semibold" @click="openCreateModal">
                    + {{ $t('marketing.add_template') }}
                </button>
            </div>
        </div>

        <!-- Available Variables Reference Callout -->
        <div class="card border-0 shadow-sm bg-primary-subtle text-primary-emphasis mb-4">
            <div class="card-body p-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <span class="fw-bold me-2">💡 {{ $t('marketing.available_merge_tags') }}:</span>
                    <span class="badge bg-white text-dark border me-1"><code>&#123;&#123;customer_name&#125;&#125;</code></span>
                    <span class="badge bg-white text-dark border me-1"><code>&#123;&#123;campaign_title&#125;&#125;</code></span>
                    <span class="badge bg-white text-dark border me-1"><code>&#123;&#123;discount_badge&#125;&#125;</code></span>
                    <span class="badge bg-white text-dark border me-1"><code>&#123;&#123;coupon_code&#125;&#125;</code></span>
                    <span class="badge bg-white text-dark border me-1"><code>&#123;&#123;expiry_date&#125;&#125;</code></span>
                    <span class="badge bg-white text-dark border"><code>&#123;&#123;loyalty_tier&#125;&#125;</code></span>
                </div>
                <span class="extra-small text-muted">{{ $t('marketing.merge_tags_auto_replaced') }}</span>
            </div>
        </div>

        <!-- Templates Grid -->
        <div class="row g-3">
            <div v-if="loading" class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <div class="small text-muted mt-2">{{ $t('common.loading') }}...</div>
            </div>

            <div v-for="t in templates" :key="t.id" class="col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100 template-card">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="fw-bold mb-1 text-dark">{{ t.name }}</h6>
                                <span class="badge bg-light text-muted border extra-small font-monospace">{{ t.key }}</span>
                            </div>
                            <span class="badge bg-primary-subtle text-primary text-capitalize extra-small">
                                {{ t.category }}
                            </span>
                        </div>

                        <!-- Title Preview -->
                        <div class="small fw-semibold text-primary mb-1">
                            {{ t.title }}
                        </div>

                        <!-- Body Preview -->
                        <div class="bg-light-subtle rounded p-2 border mb-3 small text-muted flex-grow-1 line-clamp-3">
                            {{ t.body }}
                        </div>

                        <!-- Channel tags & action link -->
                        <div class="d-flex justify-content-between align-items-center mb-3 extra-small">
                            <span class="text-muted">
                                CTA: <strong>{{ t.action_text || 'Shop Now' }}</strong> ({{ t.action_url || '/shop' }})
                            </span>
                            <div class="d-flex gap-1">
                                <span v-for="ch in (t.channels || ['in_app', 'email'])" :key="ch" class="badge bg-light text-dark border">
                                    {{ getChannelIcon(ch) }}
                                </span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex gap-2 pt-2 border-top">
                            <button class="btn btn-outline-primary btn-sm flex-grow-1" @click="previewTemplate(t)">
                                👁️ {{ $t('marketing.preview') }}
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" @click="openEditModal(t)">
                                ✏️
                            </button>
                            <button v-if="!t.is_system" class="btn btn-outline-danger btn-sm" @click="confirmDelete(t)">
                                🗑️
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Template Form Modal -->
        <div v-if="showModal" class="modal d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">
                            {{ isEdit ? $t('marketing.edit_template') : $t('marketing.add_template') }}
                        </h5>
                        <button type="button" class="btn-close" @click="showModal = false"></button>
                    </div>
                    <form @submit.prevent="saveTemplate">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">{{ $t('marketing.template_name') }} *</label>
                                <input v-model="form.name" type="text" class="form-control" required />
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-semibold small">{{ $t('marketing.template_key') }} *</label>
                                    <input v-model="form.key" type="text" class="form-control font-monospace form-control-sm" :disabled="isEdit" required />
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold small">{{ $t('marketing.category') }} *</label>
                                    <select v-model="form.category" class="form-select form-select-sm">
                                        <option value="promotional">Promotional</option>
                                        <option value="retention">Retention</option>
                                        <option value="seasonal">Seasonal</option>
                                        <option value="transactional">Transactional</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">{{ $t('marketing.notification_title') }} *</label>
                                <input v-model="form.title" type="text" class="form-control" required />
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">{{ $t('marketing.email_subject') }}</label>
                                <input v-model="form.subject" type="text" class="form-control" placeholder="Optional email subject line" />
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">{{ $t('marketing.message_body') }} *</label>
                                <textarea v-model="form.body" class="form-control" rows="4" required></textarea>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-semibold small">{{ $t('marketing.action_button_text') }}</label>
                                    <input v-model="form.action_text" type="text" class="form-control form-control-sm" placeholder="Claim Offer" />
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold small">{{ $t('marketing.action_url') }}</label>
                                    <input v-model="form.action_url" type="text" class="form-control form-control-sm" placeholder="/shop" />
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

        <!-- Rendered Preview Modal -->
        <div v-if="showPreviewModal" class="modal d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">👁️ {{ previewData?.title }}</h5>
                        <button type="button" class="btn-close" @click="showPreviewModal = false"></button>
                    </div>
                    <div class="modal-body">
                        <div class="card border rounded-3 p-3 bg-light-subtle">
                            <div class="fw-bold mb-2 text-dark">{{ previewData?.title }}</div>
                            <div class="text-muted small whitespace-pre-line mb-3">{{ previewData?.body }}</div>
                            <div class="text-end">
                                <button class="btn btn-primary btn-sm rounded-pill px-3">
                                    {{ previewData?.action_text || 'Shop Now' }} →
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" @click="showPreviewModal = false">
                            {{ $t('common.close') }}
                        </button>
                    </div>
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
const templates = ref([]);

const showModal = ref(false);
const isEdit = ref(false);
const editId = ref(null);

const showPreviewModal = ref(false);
const previewData = ref(null);

const form = ref({
    name: '',
    key: '',
    category: 'promotional',
    subject: '',
    title: '',
    body: '',
    action_url: '/shop',
    action_text: 'Claim Offer',
    channels: ['in_app', 'email', 'push'],
});

async function fetchTemplates() {
    loading.value = true;
    try {
        const res = await axios.get('/api/marketing/templates');
        templates.value = res.data;
    } catch (err) {
        showError(t('marketing.error_loading_templates'));
    } finally {
        loading.value = false;
    }
}

function openCreateModal() {
    isEdit.value = false;
    editId.value = null;
    form.value = {
        name: '',
        key: 'custom_promo_' + Math.floor(Math.random() * 1000),
        category: 'promotional',
        subject: '',
        title: '',
        body: '',
        action_url: '/shop',
        action_text: 'Claim Offer',
        channels: ['in_app', 'email', 'push'],
    };
    showModal.value = true;
}

function openEditModal(template) {
    isEdit.value = true;
    editId.value = template.id;
    form.value = {
        name: template.name,
        key: template.key,
        category: template.category,
        subject: template.subject || '',
        title: template.title,
        body: template.body,
        action_url: template.action_url || '/shop',
        action_text: template.action_text || 'Claim Offer',
        channels: template.channels || ['in_app', 'email'],
    };
    showModal.value = true;
}

function previewTemplate(template) {
    const renderedTitle = template.title
        .replace(/\{\{customer_name\}\}/g, 'Khushi')
        .replace(/\{\{discount_badge\}\}/g, '30% OFF')
        .replace(/\{\{campaign_title\}\}/g, 'Super Flash Sale')
        .replace(/\{\{coupon_code\}\}/g, 'MEGA30');

    const renderedBody = template.body
        .replace(/\{\{customer_name\}\}/g, 'Khushi')
        .replace(/\{\{discount_badge\}\}/g, '30% OFF')
        .replace(/\{\{campaign_title\}\}/g, 'Super Flash Sale')
        .replace(/\{\{coupon_code\}\}/g, 'MEGA30')
        .replace(/\{\{loyalty_tier\}\}/g, 'Gold');

    previewData.value = {
        title: renderedTitle,
        body: renderedBody,
        action_text: template.action_text,
    };
    showPreviewModal.value = true;
}

async function saveTemplate() {
    if (!form.value.name.trim() || !form.value.title.trim() || !form.value.body.trim()) return;
    saving.value = true;
    try {
        if (isEdit.value) {
            await axios.put(`/api/marketing/templates/${editId.value}`, form.value);
            showSuccess(t('marketing.template_updated_success'));
        } else {
            await axios.post('/api/marketing/templates', form.value);
            showSuccess(t('marketing.template_created_success'));
        }
        showModal.value = false;
        fetchTemplates();
    } catch (err) {
        showError(t('marketing.error_saving_template'));
    } finally {
        saving.value = false;
    }
}

async function confirmDelete(template) {
    const ok = await confirmDialog({
        title: t('common.delete'),
        message: t('marketing.confirm_delete_template', { name: template.name }),
        confirmText: t('common.delete'),
        cancelText: t('common.cancel'),
        type: 'danger',
    });
    if (!ok) return;

    try {
        await axios.delete(`/api/marketing/templates/${template.id}`);
        showSuccess(t('marketing.template_deleted_success'));
        fetchTemplates();
    } catch (err) {
        showError(t('marketing.error_deleting_template'));
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

onMounted(() => {
    fetchTemplates();
});
</script>

<style scoped>
.template-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.template-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.08) !important;
}

.extra-small {
    font-size: 0.72rem;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.whitespace-pre-line {
    white-space: pre-line;
}
</style>
