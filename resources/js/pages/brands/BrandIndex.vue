<template>
    <div class="container-fluid py-4">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">{{ $t('brands.title') }}</h3>
                <p class="text-muted mb-0">
                    {{ $t('brands.subtitle') }}
                </p>
            </div>

            <button class="btn btn-primary" @click="openModal()">
                + {{ $t('brands.add_brand') }}
            </button>
        </div>

        <!-- Search -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <input
                    v-model="search"
                    class="form-control"
                    :placeholder="$t('common.search') + '...'"
                    @input="loadBrands(1)"
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
                            <th>{{ $t('common.image') }}</th>
                            <th>{{ $t('brands.brand_name') }}</th>
                            <th>{{ $t('common.description') }}</th>
                            <th>{{ $t('categories.category_slug') }}</th>
                            <th>{{ $t('common.status') }}</th>
                            <th>{{ $t('common.actions') }}</th>
                        </tr>
                    </thead>

                    <tbody>

                        <tr v-if="loading">
                            <td colspan="7" class="text-center py-4">
                                {{ $t('common.loading') }}
                            </td>
                        </tr>

                        <tr v-else-if="brands.length === 0">
                            <td colspan="7" class="text-center py-4">
                                {{ $t('common.no_data') }}
                            </td>
                        </tr>

                        <tr v-for="(brand, index) in brands" :key="brand.id">

                            <td>
                                {{ (currentPage - 1) * 10 + index + 1 }}
                            </td>

                            <td>
                                <img
                                    v-if="brand.logo"
                                    :src="'/storage/' + brand.logo"
                                    class="brand-logo"
                                    alt="Brand logo"
                                />

                                <span v-else class="text-muted">
                                    -
                                </span>
                            </td>

                            <td class="fw-semibold">
                                {{ brand.name }}
                            </td>

                            <td>{{ brand.description || '-' }}</td>

                            <td>{{ brand.slug }}</td>

                            <td>
                                <span
                                    class="badge"
                                    :class="brand.status
                                        ? 'bg-success'
                                        : 'bg-secondary'">

                                    {{ brand.status ? $t('common.active') : $t('common.inactive') }}
                                </span>
                            </td>

                            <td>
                                <button
                                    class="btn btn-sm btn-outline-primary me-2"
                                    @click="openModal(brand)">
                                    {{ $t('common.edit') }}
                                </button>

                                <button
                                    class="btn btn-sm btn-outline-danger"
                                    @click="deleteBrand(brand.id)">
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
                    {{ $t('common.total') }}: {{ totalBrands }}
                </span>

                <div>
                    <button
                        class="btn btn-sm btn-outline-secondary me-2"
                        :disabled="currentPage <= 1"
                        @click="loadBrands(currentPage - 1)">
                        {{ $t('common.previous') }}
                    </button>

                    <button
                        class="btn btn-sm btn-outline-secondary"
                        :disabled="currentPage >= lastPage"
                        @click="loadBrands(currentPage + 1)">
                        {{ $t('common.next') }}
                    </button>
                </div>

            </div>

        </div>

        <!-- Modal -->
        <div class="modal fade" id="brandModal" tabindex="-1">

            <div class="modal-dialog">
                <div class="modal-content">

                    <form @submit.prevent="saveBrand">

                        <div class="modal-header">
                            <h5 class="modal-title">
                                {{ editingId ? $t('brands.edit_brand') : $t('brands.add_brand') }}
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
                                    {{ $t('brands.brand_name') }} *
                                </label>

                                <input
                                    v-model="form.name"
                                    type="text"
                                    class="form-control"
                                    required
                                />
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    {{ $t('common.description') }}
                                </label>

                                <textarea
                                    v-model="form.description"
                                    class="form-control"
                                    rows="3">
                                </textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    {{ $t('common.image') }} / Logo
                                </label>

                                <input
                                    type="file"
                                    class="form-control"
                                    accept="image/jpeg,image/png,image/webp"
                                    @change="handleLogo"
                                />
                            </div>

                            <div v-if="logoPreview" class="mb-3">
                                <img
                                    :src="logoPreview"
                                    class="brand-preview"
                                    alt="Logo preview"
                                />
                            </div>

                            <div class="form-check form-switch">
                                <input
                                    v-model="form.status"
                                    class="form-check-input"
                                    type="checkbox"
                                    id="brandStatus"
                                />

                                <label
                                    class="form-check-label"
                                    for="brandStatus">
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

const brands = ref([]);
const search = ref('');

const loading = ref(false);
const saving = ref(false);

const currentPage = ref(1);
const lastPage = ref(1);
const totalBrands = ref(0);

const editingId = ref(null);
const errorMessage = ref('');

const logoFile = ref(null);
const logoPreview = ref(null);

let brandModal = null;

const form = reactive({
    name: '',
    description: '',
    status: true,
});

async function loadBrands(page = 1) {
    loading.value = true;

    try {
        const response = await api.get('/brands', {
            params: {
                search: search.value,
                page: page,
            }
        });

        brands.value = response.data.data;
        currentPage.value = response.data.current_page;
        lastPage.value = response.data.last_page;
        totalBrands.value = response.data.total;

    } catch (error) {
        console.error(error);
        alert(t('common.error') + ': ' + (error.response?.data?.message || 'Unable to load brands.'));
    } finally {
        loading.value = false;
    }
}

function resetForm() {
    editingId.value = null;
    errorMessage.value = '';

    form.name = '';
    form.description = '';
    form.status = true;

    logoFile.value = null;
    logoPreview.value = null;
}

function openModal(brand = null) {
    resetForm();

    if (brand) {
        editingId.value = brand.id;

        form.name = brand.name;
        form.description = brand.description || '';
        form.status = Boolean(brand.status);

        logoPreview.value = brand.logo
            ? `/storage/${brand.logo}`
            : null;
    }

    brandModal.show();
}

function handleLogo(event) {
    const file = event.target.files[0];

    if (!file) return;

    if (logoPreview.value?.startsWith('blob:')) {
        URL.revokeObjectURL(logoPreview.value);
    }

    logoFile.value = file;
    logoPreview.value = URL.createObjectURL(file);
}

async function saveBrand() {
    saving.value = true;
    errorMessage.value = '';

    try {
        const payload = new FormData();

        payload.append('name', form.name);
        payload.append('description', form.description);
        payload.append('status', form.status ? '1' : '0');

        if (logoFile.value) {
            payload.append('logo', logoFile.value);
        }

        if (editingId.value) {
            payload.append('_method', 'PUT');

            await api.post(
                `/brands/${editingId.value}`,
                payload
            );
        } else {
            await api.post('/brands', payload);
        }

        brandModal.hide();

        await loadBrands(currentPage.value);

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

async function deleteBrand(id) {
    if (!confirm(t('brands.delete_confirm'))) {
        return;
    }

    try {
        await api.delete(`/brands/${id}`);

        await loadBrands(currentPage.value);

        alert(t('common.deleted_successfully'));

    } catch (error) {
        alert(
            error.response?.data?.message ||
            t('common.error')
        );
    }
}

onMounted(() => {
    brandModal = new Modal(
        document.getElementById('brandModal')
    );

    loadBrands();
});

onBeforeUnmount(() => {
    brandModal?.dispose();

    if (logoPreview.value?.startsWith('blob:')) {
        URL.revokeObjectURL(logoPreview.value);
    }
});
</script>

<style scoped>
.brand-logo {
    width: 45px;
    height: 45px;
    object-fit: contain;
    border: 1px solid #eee;
    border-radius: 6px;
    padding: 4px;
}

.brand-preview {
    width: 100px;
    height: 100px;
    object-fit: contain;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 5px;
}
</style>
