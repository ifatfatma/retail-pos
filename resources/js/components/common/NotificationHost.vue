<template>
    <div>
        <!-- Toast Stack Container -->
        <div class="toast-stack-container" aria-live="polite">
            <transition-group name="toast-anim" tag="div" class="d-flex flex-column gap-2">
                <div
                    v-for="t in toasts"
                    :key="t.id"
                    class="custom-toast-card p-3 shadow-lg rounded-4 d-flex align-items-start gap-3 position-relative overflow-hidden"
                    :class="`toast-${t.type}`"
                    role="alert"
                >
                    <!-- Type Icon Avatar -->
                    <div class="toast-icon-circle rounded-circle d-flex align-items-center justify-content-center flex-shrink-0">
                        <span v-if="t.type === 'success'">✓</span>
                        <span v-else-if="t.type === 'error'">✕</span>
                        <span v-else-if="t.type === 'warning'">⚠️</span>
                        <span v-else>ℹ️</span>
                    </div>

                    <!-- Toast Text Body -->
                    <div class="flex-grow-1 min-w-0 pe-2">
                        <div class="toast-title fw-bold small mb-1">{{ t.title }}</div>
                        <div class="toast-message small text-secondary">{{ t.message }}</div>
                    </div>

                    <!-- Close Button -->
                    <button
                        type="button"
                        class="btn-close btn-close-sm flex-shrink-0 p-1 opacity-75"
                        aria-label="Close"
                        @click="removeToast(t.id)"
                    ></button>

                    <!-- Animated Progress Bar -->
                    <div
                        v-if="t.duration > 0"
                        class="toast-progress-bar"
                        :style="{ animationDuration: `${t.duration}ms` }"
                    ></div>
                </div>
            </transition-group>
        </div>

        <!-- Themed Confirmation & Alert Modal Backdrop -->
        <transition name="modal-fade">
            <div
                v-if="confirmState.isOpen"
                class="custom-modal-backdrop d-flex align-items-center justify-content-center"
                @click.self="handleDismiss"
            >
                <div class="custom-modal-dialog p-4 p-sm-5 bg-white rounded-4 shadow-2xl text-center position-relative">
                    <!-- Modal Header Icon Circle -->
                    <div
                        class="modal-icon-badge mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                        :class="`badge-${confirmState.type}`"
                    >
                        <span v-if="confirmState.type === 'danger'" class="fs-2">🗑️</span>
                        <span v-else-if="confirmState.type === 'warning'" class="fs-2">⚠️</span>
                        <span v-else-if="confirmState.type === 'success'" class="fs-2">✓</span>
                        <span v-else class="fs-2">ℹ️</span>
                    </div>

                    <!-- Modal Title -->
                    <h5 class="fw-bold text-dark mb-2">{{ confirmState.title }}</h5>

                    <!-- Modal Message Body -->
                    <p class="text-secondary small mb-4 px-2" style="white-space: pre-line; line-height: 1.6;">
                        {{ confirmState.message }}
                    </p>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-center gap-2">
                        <!-- Cancel Button (if not alert only) -->
                        <button
                            v-if="!confirmState.isAlertOnly"
                            type="button"
                            class="btn btn-light rounded-pill px-4 py-2 fw-medium border text-secondary"
                            @click="handleCancel"
                        >
                            {{ confirmState.cancelText || 'Cancel' }}
                        </button>

                        <!-- Confirm Button -->
                        <button
                            type="button"
                            class="btn rounded-pill px-4 py-2 fw-bold shadow-sm"
                            :class="confirmBtnClass"
                            @click="handleConfirm"
                        >
                            {{ confirmState.confirmText || 'Confirm' }}
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<script setup>
import { computed, onMounted, onBeforeUnmount } from 'vue';
import { useNotification } from '../../composables/useNotification';

const { toasts, confirmState, removeToast, handleConfirmAction } = useNotification();

const confirmBtnClass = computed(() => {
    switch (confirmState.value.type) {
        case 'danger':
            return 'btn-danger text-white';
        case 'warning':
            return 'btn-warning text-dark';
        case 'success':
            return 'btn-success text-white';
        default:
            return 'btn-primary text-white';
    }
});

function handleConfirm() {
    handleConfirmAction(true);
}

function handleCancel() {
    handleConfirmAction(false);
}

function handleDismiss() {
    handleConfirmAction(false);
}

function handleKeydown(e) {
    if (confirmState.value.isOpen) {
        if (e.key === 'Escape') {
            handleCancel();
        } else if (e.key === 'Enter') {
            handleConfirm();
        }
    }
}

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onBeforeUnmount(() => {
    window.removeEventListener('keydown', handleKeydown);
});
</script>

<style scoped>
/* Toasts Fixed Container */
.toast-stack-container {
    position: fixed;
    top: 24px;
    right: 24px;
    z-index: 999999;
    max-width: 380px;
    width: calc(100vw - 32px);
    pointer-events: none;
}

.custom-toast-card {
    pointer-events: auto;
    background: #ffffff;
    border: 1px solid rgba(0, 0, 0, 0.08);
    backdrop-filter: blur(8px);
    transition: all 0.3s ease;
}

.toast-icon-circle {
    width: 36px;
    height: 36px;
    font-weight: bold;
    font-size: 1.1rem;
}

/* Toast Themes */
.toast-success {
    border-left: 5px solid #10b981;
}
.toast-success .toast-icon-circle {
    background: #ecfdf5;
    color: #059669;
}
.toast-success .toast-title {
    color: #065f46;
}
.toast-success .toast-progress-bar {
    background: #10b981;
}

.toast-error {
    border-left: 5px solid #ef4444;
}
.toast-error .toast-icon-circle {
    background: #fef2f2;
    color: #dc2626;
}
.toast-error .toast-title {
    color: #991b1b;
}
.toast-error .toast-progress-bar {
    background: #ef4444;
}

.toast-warning {
    border-left: 5px solid #f59e0b;
}
.toast-warning .toast-icon-circle {
    background: #fffbeb;
    color: #d97706;
}
.toast-warning .toast-title {
    color: #92400e;
}
.toast-warning .toast-progress-bar {
    background: #f59e0b;
}

.toast-info {
    border-left: 5px solid #3b82f6;
}
.toast-info .toast-icon-circle {
    background: #eff6ff;
    color: #2563eb;
}
.toast-info .toast-title {
    color: #1e40af;
}
.toast-info .toast-progress-bar {
    background: #3b82f6;
}

/* Toast Progress Bar */
.toast-progress-bar {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
    animation: shrinkWidth linear forwards;
}

@keyframes shrinkWidth {
    from {
        width: 100%;
    }
    to {
        width: 0%;
    }
}

/* Toast Animations */
.toast-anim-enter-active {
    animation: toastSlideIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.toast-anim-leave-active {
    animation: toastSlideOut 0.25s ease forwards;
}

@keyframes toastSlideIn {
    from {
        opacity: 0;
        transform: translateY(-20px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes toastSlideOut {
    from {
        opacity: 1;
        transform: translateX(0) scale(1);
    }
    to {
        opacity: 0;
        transform: translateX(40px) scale(0.9);
    }
}

/* Custom Modal Backdrop */
.custom-modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(5px);
    z-index: 9999999;
    padding: 20px;
}

.custom-modal-dialog {
    width: 100%;
    max-width: 420px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    animation: modalPopIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.modal-icon-badge {
    width: 64px;
    height: 64px;
}

.badge-danger {
    background: #fee2e2;
    color: #dc2626;
}

.badge-warning {
    background: #fef3c7;
    color: #d97706;
}

.badge-success {
    background: #d1fae5;
    color: #059669;
}

.badge-info {
    background: #dbeafe;
    color: #2563eb;
}

@keyframes modalPopIn {
    from {
        opacity: 0;
        transform: scale(0.92) translateY(10px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.2s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}

@media (max-width: 576px) {
    .toast-stack-container {
        top: 14px;
        right: 14px;
        left: 14px;
        width: auto;
        max-width: 100%;
    }
}
</style>
