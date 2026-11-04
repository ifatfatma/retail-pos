
<template>
    <div class="container-fluid py-4">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Units</h3>
                <p class="text-muted mb-0">
                    Manage product measurement units
                </p>
            </div>

            <button class="btn btn-primary" @click="openModal()">
                + Add Unit
            </button>
        </div>

        <!-- Search -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <input
                    v-model="search"
                    type="text"
                    class="form-control"
                    placeholder="Search unit name or code..."
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
                            <th>Unit Name</th>
                            <th>Short Code</th>
                            <th>Decimal Quantity</th>
                            <th>Status</th>
                            <th width="160">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        <tr v-if="loading">
                            <td colspan="6" class="text-center py-4">
                                Loading...
                            </td>
                        </tr>

                        <tr v-else-if="units.length === 0">
                            <td colspan="6" class="text-center py-4">
                                No units found.
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

                                    {{ unit.allow_decimal ? 'Yes' : 'No' }}
                                </span>
                            </td>

                            <td>
                                <span
                                    class="badge"
                                    :class="unit.status
                                        ? 'bg-success'
                                        : 'bg-secondary'">

                                    {{ unit.status ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            <td>
                                <button
                                    class="btn btn-sm btn-outline-primary me-2"
                                    @click="openModal(unit)">
                                    Edit
                                </button>

                                <button
                                    class="btn btn-sm btn-outline-danger"
                                    @click="deleteUnit(unit.id)">
                                    Delete
                                </button>
                            </td>

                        </tr>

                    </tbody>

                </table>
            </div>

            <!-- Pagination -->
            <div class="card-body d-flex justify-content-between">

                <span class="text-muted">
                    Total: {{ totalUnits }} units
                </span>

                <div>
                    <button
                        class="btn btn-sm btn-outline-secondary me-2"
                        :disabled="currentPage <= 1"
                        @click="loadUnits(currentPage - 1)">
                        Previous
                    </button>

                    <button
                        class="btn btn-sm btn-outline-secondary"
                        :disabled="currentPage >= lastPage"
                        @click="loadUnits(currentPage + 1)">
                        Next
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
                                {{ editingId ? 'Edit Unit' : 'Add Unit' }}
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
                                    Unit Name *
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
                                    Short Code *
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
                                    Allow decimal quantity
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
                                    Active
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
                                Cancel
                            </button>

                            <button
                                type="submit"
                                class="btn btn-primary"
                                :disabled="saving">

                                {{ saving ? 'Saving...' : 'Save Unit' }}

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
import { Modal } from 'bootstrap';
import api from '../../services/api';

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
        alert('Unable to load units.');
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

        alert('Unit saved successfully.');

    } catch (error) {
        const errors = error.response?.data?.errors;

        errorMessage.value = errors
            ? Object.values(errors).flat().join(', ')
            : error.response?.data?.message || 'Something went wrong.';

    } finally {
        saving.value = false;
    }
}

async function deleteUnit(id) {
    if (!confirm('Are you sure you want to delete this unit?')) {
        return;
    }

    try {
        await api.delete(`/units/${id}`);

        await loadUnits(currentPage.value);

        alert('Unit deleted successfully.');

    } catch (error) {
        alert(
            error.response?.data?.message ||
            'Unable to delete unit.'
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
