<template>
    <div class="container-fluid py-4">

        <!-- Page Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
            <div>
                <h3 class="fw-bold mb-1">⚙️ {{ $t('marketing.settings_title') }}</h3>
                <p class="text-muted mb-0">
                    {{ $t('marketing.settings_subtitle') }}
                </p>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary btn-sm fw-semibold" @click="openTestModal">
                    🚀 {{ $t('marketing.send_test_notification') }}
                </button>

                <button
                    class="btn btn-primary btn-sm fw-semibold"
                    @click="saveSettings"
                    :disabled="saving"
                >
                    <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                    💾 {{ $t('common.save_settings') }}
                </button>
            </div>
        </div>

        <!-- Global Test Mode Card -->
        <div class="card border-0 shadow-sm mb-4 border-start border-4 border-primary">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h5 class="fw-bold mb-1 d-flex align-items-center gap-2">
                        <span>🛡️</span>
                        <span>{{ $t('marketing.test_mode_title') }}</span>
                    </h5>
                    <p class="small text-muted mb-0">
                        {{ $t('marketing.test_mode_description') }}
                    </p>
                </div>
                <div class="form-check form-switch fs-4">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        v-model="form.marketing_test_mode"
                    />
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- 1. IN-APP CHANNEL CONFIGURATION -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3">
                        <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                            <span>📱</span>
                            <span>{{ $t('marketing.channel_in_app') }}</span>
                        </h5>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" v-model="form.marketing_in_app_enabled" />
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-3">
                            {{ $t('marketing.in_app_desc') }}
                        </p>
                        <div class="p-3 bg-light-subtle rounded border small">
                            <div class="d-flex justify-content-between mb-2">
                                <span>{{ $t('marketing.status') }}:</span>
                                <span class="badge bg-success-subtle text-success">Native Database Inbox</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>{{ $t('marketing.customer_delivery') }}:</span>
                                <span class="fw-semibold text-primary">Storefront Bell & Account Inbox</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. EMAIL CONFIGURATION -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3">
                        <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                            <span>✉️</span>
                            <span>{{ $t('marketing.channel_email') }}</span>
                        </h5>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" v-model="form.marketing_email_enabled" />
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">{{ $t('marketing.email_provider') }}</label>
                            <select v-model="form.marketing_email_provider" class="form-select form-select-sm">
                                <option value="simulated">Simulated / Local Log (Test Mode)</option>
                                <option value="smtp">Custom SMTP Gateway</option>
                                <option value="mailgun">Mailgun API</option>
                                <option value="ses">Amazon SES</option>
                            </select>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label fw-semibold extra-small">{{ $t('marketing.from_name') }}</label>
                                <input v-model="form.marketing_email_from_name" type="text" class="form-control form-control-sm" />
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold extra-small">{{ $t('marketing.from_email') }}</label>
                                <input v-model="form.marketing_email_from_address" type="email" class="form-control form-control-sm" />
                            </div>
                        </div>
                        <div class="row g-2 mb-2" v-if="form.marketing_email_provider === 'smtp'">
                            <div class="col-8">
                                <label class="form-label fw-semibold extra-small">SMTP Host</label>
                                <input v-model="form.marketing_email_smtp_host" type="text" class="form-control form-control-sm" />
                            </div>
                            <div class="col-4">
                                <label class="form-label fw-semibold extra-small">Port</label>
                                <input v-model="form.marketing_email_smtp_port" type="text" class="form-control form-control-sm" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. WHATSAPP BUSINESS API -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3">
                        <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                            <span>🟢</span>
                            <span>{{ $t('marketing.channel_whatsapp') }}</span>
                        </h5>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" v-model="form.marketing_whatsapp_enabled" />
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">{{ $t('marketing.whatsapp_provider') }}</label>
                            <select v-model="form.marketing_whatsapp_provider" class="form-select form-select-sm">
                                <option value="simulated">Simulated / Safe Test Mode</option>
                                <option value="meta_cloud">Meta Cloud API (WhatsApp Official)</option>
                                <option value="twilio">Twilio WhatsApp API</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-semibold extra-small">Phone Number ID</label>
                            <input v-model="form.marketing_whatsapp_phone_number_id" type="text" class="form-control form-control-sm" placeholder="1029384756" />
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-semibold extra-small">Permanent Access Token</label>
                            <input v-model="form.marketing_whatsapp_token" type="password" class="form-control form-control-sm" placeholder="••••••••" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. SMS GATEWAY -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3">
                        <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                            <span>💬</span>
                            <span>{{ $t('marketing.channel_sms') }}</span>
                        </h5>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" v-model="form.marketing_sms_enabled" />
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">{{ $t('marketing.sms_provider') }}</label>
                            <select v-model="form.marketing_sms_provider" class="form-select form-select-sm">
                                <option value="simulated">Simulated / Safe Test Mode</option>
                                <option value="msg91">MSG91</option>
                                <option value="twilio">Twilio SMS</option>
                                <option value="fast2sms">Fast2SMS</option>
                            </select>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label fw-semibold extra-small">Sender ID</label>
                                <input v-model="form.marketing_sms_sender_id" type="text" class="form-control form-control-sm" placeholder="RETAIL" />
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold extra-small">API Key / Token</label>
                                <input v-model="form.marketing_sms_api_key" type="password" class="form-control form-control-sm" placeholder="••••••••" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5. WEB PUSH NOTIFICATIONS -->
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3">
                        <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                            <span>🔔</span>
                            <span>{{ $t('marketing.channel_push') }}</span>
                        </h5>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" v-model="form.marketing_push_enabled" />
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">{{ $t('marketing.push_provider') }}</label>
                                <select v-model="form.marketing_push_provider" class="form-select form-select-sm">
                                    <option value="simulated">Simulated / Safe Test Mode</option>
                                    <option value="web_push">VAPID Browser Web Push</option>
                                    <option value="firebase">Firebase Cloud Messaging (FCM)</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold small">Public VAPID / FCM Key</label>
                                <input v-model="form.marketing_push_public_key" type="text" class="form-control form-control-sm font-monospace" placeholder="BN_39Xk..." />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live Test Notification Dispatch Modal -->
        <div v-if="showTestModal" class="modal d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">🚀 {{ $t('marketing.send_test_notification') }}</h5>
                        <button type="button" class="btn-close" @click="showTestModal = false"></button>
                    </div>
                    <form @submit.prevent="sendTestNotification">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">{{ $t('marketing.channel') }} *</label>
                                <select v-model="testForm.channel" class="form-select" required>
                                    <option value="in_app">📱 In-App Notification</option>
                                    <option value="email">✉️ Email Campaign</option>
                                    <option value="sms">💬 SMS Gateway</option>
                                    <option value="whatsapp">🟢 WhatsApp Business</option>
                                    <option value="push">🔔 Web Push Notification</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">{{ $t('marketing.test_recipient') }} *</label>
                                <input
                                    v-model="testForm.recipient"
                                    type="text"
                                    class="form-control"
                                    placeholder="e.g. admin@example.com or +919876543210"
                                    required
                                />
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">{{ $t('marketing.notification_title') }} *</label>
                                <input v-model="testForm.title" type="text" class="form-control" required />
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">{{ $t('marketing.message') }} *</label>
                                <textarea v-model="testForm.message" class="form-control" rows="3" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" @click="showTestModal = false">
                                {{ $t('common.cancel') }}
                            </button>
                            <button type="submit" class="btn btn-primary" :disabled="sendingTest">
                                <span v-if="sendingTest" class="spinner-border spinner-border-sm me-1"></span>
                                🚀 {{ $t('marketing.send_now') }}
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
const { showSuccess, showError } = useNotification();

const loading = ref(false);
const saving = ref(false);
const sendingTest = ref(false);
const showTestModal = ref(false);

const form = ref({
    marketing_test_mode: true,
    marketing_email_enabled: true,
    marketing_email_provider: 'simulated',
    marketing_email_from_name: 'Retail POS Deals',
    marketing_email_from_address: 'offers@retailstore.local',
    marketing_email_smtp_host: 'smtp.mailtrap.io',
    marketing_email_smtp_port: '2525',
    marketing_sms_enabled: true,
    marketing_sms_provider: 'simulated',
    marketing_sms_sender_id: 'RETAIL',
    marketing_sms_api_key: '',
    marketing_whatsapp_enabled: true,
    marketing_whatsapp_provider: 'simulated',
    marketing_whatsapp_phone_number_id: '',
    marketing_whatsapp_token: '',
    marketing_push_enabled: true,
    marketing_push_provider: 'simulated',
    marketing_push_public_key: '',
    marketing_in_app_enabled: true,
});

const testForm = ref({
    channel: 'in_app',
    recipient: 'admin@retailstore.local',
    title: '🔥 Exclusive Test Deal: 25% OFF',
    message: 'This is a test marketing notification dispatched from your Retail POS admin dashboard.',
});

async function fetchSettings() {
    loading.value = true;
    try {
        const res = await axios.get('/api/marketing/settings');
        form.value = { ...form.value, ...res.data };
    } catch (err) {
        showError(t('marketing.error_loading_settings'));
    } finally {
        loading.value = false;
    }
}

async function saveSettings() {
    saving.value = true;
    try {
        await axios.post('/api/marketing/settings', form.value);
        showSuccess(t('marketing.settings_saved_success'));
    } catch (err) {
        showError(t('marketing.error_saving_settings'));
    } finally {
        saving.value = false;
    }
}

function openTestModal() {
    showTestModal.value = true;
}

async function sendTestNotification() {
    sendingTest.value = true;
    try {
        const res = await axios.post('/api/marketing/test-message', testForm.value);
        showSuccess(res.data.message || t('marketing.test_notification_sent'));
        showTestModal.value = false;
    } catch (err) {
        showError(t('marketing.error_sending_test'));
    } finally {
        sendingTest.value = false;
    }
}

onMounted(() => {
    fetchSettings();
});
</script>

<style scoped>
.extra-small {
    font-size: 0.72rem;
}
</style>
