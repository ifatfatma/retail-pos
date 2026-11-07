<template>
    <div class="container-fluid py-4">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">{{ $t('units.title') }}</h3>
                <p class="text-muted mb-0">
                    {{ $t('units.subtitle') }}
                </p>
            </div>

            <button class="btn btn-primary" @click="openModal()">
                + {{ $t('units.add_unit') }}
            </button>
        </div>

        <!-- Search -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <input
                    v-model="search"
                    type="text"
                    class="form-control"
                    :placeholder="$t('common.search') + '...'"
                    @input="loadUnits(1)"
                />
            </div>
        </div>

        <!-- Table -->
        <div class="card border-0 shadow-sm">

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>{{ $t('units.unit_name') }}</th>
                            <th>{{ $t('units.short_name') }}</th>
                            <th>{{ $t('units.allow_decimal') }}</th>
                            <th>{{ $t('common.status') }}</th>
                            <th width="160">{{ $t('common.actions') }}</th>
                        </tr>
                    </thead>

                    <tbody>

                        <tr v-if="loading">
                            <td colspan="6" class="text-center py-4">
                                {{ $t('common.loading') }}
                            </td>
                        </tr>

                        <tr v-else-if="units.length === 0">
                            <td colspan="6" class="text-center py-4">
                                {{ $t('common.no_data') }}
                            </td>
                        </tr>

                        <tr v-for="(unit, index) in units" :key="unit.id">

                            <td>
                                {{ (currentPage - 1) * 10 + index + 1 }}
                            </td>

                            <td class="fw-semibold">
                                {{ unit.name }}
                            </td>

                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ unit.short_name }}
                                </span>
                            </td>

                            <td>
                                <span
                                    :class="unit.allow_decimal
                                        ? 'text-success'
                                        : 'text-muted'">

                                    {{ unit.allow_decimal ? $t('common.yes') : $t('common.no') }}
                                </span>
                            </td>

                            <td>
                                <span
                                    class="badge"
                                    :class="unit.status
                                        ? 'bg-success'
                                        : 'bg-secondary'">

                                    {{ unit.status ? $t('common.active') : $t('common.inactive') }}
                                </span>
                            </td>

                            <td>
                                <button
                                    class="btn btn-sm btn-outline-primary me-2"
                                    @click="openModal(unit)">
                                    {{ $t('common.edit') }}
                                </button>

                                <button
                                    class="btn btn-sm btn-outline-danger"
                                    @click="deleteUnit(unit.id)">
                                    {{ $t('common.delete') }}
                                </button>
                            </td>

                        </tr>

                    </tbody>

                </table>
            </div>

            <!-- Pagination -->
            <div class="card-body d-flex justify-content-between">

                <span class="text-muted">
                    {{ $t('common.total') }}: {{ totalUnits }}
                </span>

                <div>
                    <button
                        class="btn btn-sm btn-outline-secondary me-2"
                        :disabled="currentPage <= 1"
                        @click="loadUnits(currentPage - 1)">
                        {{ $t('common.previous') }}
                    </button>

                    <button
                        class="btn btn-sm btn-outline-secondary"
                        :disabled="currentPage >= lastPage"
                        @click="loadUnits(currentPage + 1)">
                        {{ $t('common.next') }}
                    </button>
                </div>

            </div>
        </div>

        <!-- Add/Edit Modal -->
        <div class="modal fade" id="unitModal" tabindex="-1">

            <div class="modal-dialog">
                <div class="modal-content">

                    <form @submit.prevent="saveUnit">

                        <div class="modal-header">
                            <h5 class="modal-title">
                                {{ editingId ? $t('units.edit_unit') : $t('units.add_unit') }}
                            </h5>

                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="modal">
                            </button>
                        </div>

                        <div class="modal-body">

                            <div class="mb-3">
                                <label class="form-label">
                                    {{ $t('units.unit_name') }} *
                                </label>

                                <input
                                    v-model="form.name"
                                    type="text"
                                    class="form-control"
                                    placeholder="Example: Piece"
                                    required
                                />
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    {{ $t('units.short_name') }} *
                                </label>

                                <input
                                    v-model="form.short_name"
                                    type="text"
                                    class="form-control"
                                    placeholder="Example: PCS"
                                    maxlength="20"
                                    required
                                />
                            </div>

                            <div class="form-check form-switch mb-3">
                                <input
                                    v-model="form.allow_decimal"
                                    class="form-check-input"
                                    type="checkbox"
                                    id="allowDecimal"
                                />

                                <label
                                    class="form-check-label"
                                    for="allowDecimal">
                                    {{ $t('units.allow_decimal') }}
                                </label>
                            </div>

                            <div class="form-check form-switch">
                                <input
                                    v-model="form.status"
                                    class="form-check-input"
                                    type="checkbox"
                                    id="unitStatus"
                                />

                                <label
                                    class="form-check-label"
                                    for="unitStatus">
                                    {{ $t('common.active') }}
                                </label>
                            </div>

                            <div
                                v-if="errorMessage"
                                class="alert alert-danger mt-3">
                                {{ errorMessage }}
                            </div>

                        </div>

                        <div class="modal-footer">

                            <button
                                type="button"
                                class="btn btn-light"
                                data-bs-dismiss="modal">
                                {{ $t('common.cancel') }}
                            </button>

                            <button
                                type="submit"
                                class="btn btn-primary"
                                :disabled="saving">

                                {{ saving ? $t('common.processing') : $t('common.save') }}

                            </button>

                        </div>

                    </form>

                </div>
            </div>

        </div>

    </div>
</template>

<script setup>
import { ref, reactive, onMounted, onBeforeUnmount } from 'vue';
import { useI18n } from 'vue-i18n';
import { Modal } from 'bootstrap';
import api from '../../services/api';

const { t } = useI18n();

const units = ref([]);
const search = ref('');

const loading = ref(false);
const saving = ref(false);

const currentPage = ref(1);
const lastPage = ref(1);
const totalUnits = ref(0);

const editingId = ref(null);
const errorMessage = ref('');

let unitModal = null;

const form = reactive({
    name: '',
    short_name: '',
    allow_decimal: false,
    status: true,
});

async function loadUnits(page = 1) {
    loading.value = true;

    try {
        const response = await api.get('/units', {
            params: {
                search: search.value,
                page: page,
            }
        });

        units.value = response.data.data;
        currentPage.value = response.data.current_page;
        lastPage.value = response.data.last_page;
        totalUnits.value = response.data.total;

    } catch (error) {
        console.error(error);
        alert(t('common.error') + ': ' + (error.response?.data?.message || 'Unable to load units.'));
    } finally {
        loading.value = false;
    }
}

function resetForm() {
    editingId.value = null;
    errorMessage.value = '';

    Object.assign(form, {
        name: '',
        short_name: '',
        allow_decimal: false,
        status: true,
    });
}

function openModal(unit = null) {
    resetForm();

    if (unit) {
        editingId.value = unit.id;

        Object.assign(form, {
            name: unit.name,
            short_name: unit.short_name,
            allow_decimal: Boolean(unit.allow_decimal),
            status: Boolean(unit.status),
        });
    }

    unitModal.show();
}

async function saveUnit() {
    saving.value = true;
    errorMessage.value = '';

    try {
        const payload = {
            name: form.name,
            short_name: form.short_name.trim(),
            allow_decimal: form.allow_decimal,
            status: form.status,
        };

        if (editingId.value) {
            await api.put(`/units/${editingId.value}`, payload);
        } else {
            await api.post('/units', payload);
        }

        unitModal.hide();

        await loadUnits(currentPage.value);

        alert(t('common.saved_successfully'));

    } catch (error) {
        const errors = error.response?.data?.errors;

        errorMessage.value = errors
            ? Object.values(errors).flat().join(', ')
            : error.response?.data?.message || t('common.error');

    } finally {
        saving.value = false;
    }
}

async function deleteUnit(id) {
    if (!confirm(t('units.delete_confirm'))) {
        return;
    }

    try {
        await api.delete(`/units/${id}`);

        await loadUnits(currentPage.value);

        alert(t('common.deleted_successfully'));

    } catch (error) {
        alert(
            error.response?.data?.message ||
            t('common.error')
        );
    }
}

onMounted(() => {
    unitModal = new Modal(
        document.getElementById('unitModal')
    );

    loadUnits();
});

onBeforeUnmount(() => {
    unitModal?.dispose();
});
</script>
