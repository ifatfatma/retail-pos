<template>
    <div class="container-fluid py-4">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">{{ $t('categories.title') }}</h3>
                <p class="text-muted mb-0">
                    {{ $t('categories.subtitle') }}
                </p>
            </div>

            <button class="btn btn-primary" @click="openModal()">
                + {{ $t('categories.add_category') }}
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
                    @input="loadCategories"
                />
            </div>
        </div>

        <!-- Category Table -->
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>{{ $t('categories.category_name') }}</th>
                            <th>{{ $t('categories.category_name') }} (Parent)</th>
                            <th>{{ $t('categories.category_slug') }}</th>
                            <th>{{ $t('common.status') }}</th>
                            <th width="150">{{ $t('common.actions') }}</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-if="loading">
                            <td colspan="6" class="text-center py-4">
                                {{ $t('common.loading') }}
                            </td>
                        </tr>

                        <tr v-else-if="categories.length === 0">
                            <td colspan="6" class="text-center py-4">
                                {{ $t('common.no_data') }}
                            </td>
                        </tr>

                        <tr v-for="(category, index) in categories"
                            :key="category.id">

                            <td>
                                {{ (currentPage - 1) * 10 + index + 1 }}
                            </td>

                            <td class="fw-semibold">
                                {{ category.name }}
                            </td>

                            <td>
                                {{ category.parent?.name || '-' }}
                            </td>

                            <td>{{ category.slug }}</td>

                            <td>
                                <span
                                    class="badge"
                                    :class="category.status
                                        ? 'bg-success'
                                        : 'bg-secondary'">

                                    {{ category.status
                                        ? $t('common.active')
                                        : $t('common.inactive') }}
                                </span>
                            </td>

                            <td>
                                <button
                                    class="btn btn-sm btn-outline-primary me-2"
                                    @click="openModal(category)">
                                    {{ $t('common.edit') }}
                                </button>

                                <button
                                    class="btn btn-sm btn-outline-danger"
                                    @click="deleteCategory(category.id)">
                                    {{ $t('common.delete') }}
                                </button>
                            </td>

                        </tr>
                    </tbody>

                </table>
            </div>

            <!-- Pagination -->
            <div class="card-body d-flex justify-content-between">

                <span class="text-muted small">
                    {{ $t('common.total') }}: {{ totalCategories }}
                </span>

                <div>
                    <button
                        class="btn btn-sm btn-outline-secondary me-2"
                        :disabled="currentPage <= 1"
                        @click="changePage(currentPage - 1)">
                        {{ $t('common.previous') }}
                    </button>

                    <button
                        class="btn btn-sm btn-outline-secondary"
                        :disabled="currentPage >= lastPage"
                        @click="changePage(currentPage + 1)">
                        {{ $t('common.next') }}
                    </button>
                </div>

            </div>
        </div>

        <!-- Category Modal -->
        <div
            class="modal fade"
            id="categoryModal"
            tabindex="-1">

            <div class="modal-dialog">
                <div class="modal-content">

                    <form @submit.prevent="saveCategory">

                        <div class="modal-header">
                            <h5 class="modal-title">
                                {{ editingId
                                    ? $t('categories.edit_category')
                                    : $t('categories.add_category') }}
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
                                    {{ $t('categories.category_name') }} *
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
                                    {{ $t('categories.category_name') }} (Parent)
                                </label>

                                <select
                                    v-model="form.parent_id"
                                    class="form-select">

                                    <option :value="null">
                                        -- {{ $t('common.all') }} --
                                    </option>

                                    <option
                                        v-for="cat in categories.filter(c =>
                                            c.id !== editingId
                                        )"
                                        :key="cat.id"
                                        :value="cat.id">

                                        {{ cat.name }}
                                    </option>

                                </select>
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

                            <div class="form-check form-switch">
                                <input
                                    v-model="form.status"
                                    class="form-check-input"
                                    type="checkbox"
                                    id="categoryStatus">

                                <label
                                    class="form-check-label"
                                    for="categoryStatus">
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
import { ref, reactive, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { Modal } from 'bootstrap';
import api from '../../services/api';

const { t } = useI18n();

const categories = ref([]);
const search = ref('');
const loading = ref(false);
const saving = ref(false);

const currentPage = ref(1);
const lastPage = ref(1);
const totalCategories = ref(0);

const editingId = ref(null);
const errorMessage = ref('');

let categoryModal = null;

const form = reactive({
    name: '',
    parent_id: null,
    description: '',
    status: true,
});

async function loadCategories(page = 1) {
    loading.value = true;

    try {
        const response = await api.get('/categories', {
            params: {
                search: search.value,
                page: page,
            }
        });

        categories.value = response.data.data;
        currentPage.value = response.data.current_page;
        lastPage.value = response.data.last_page;
        totalCategories.value = response.data.total;

    } catch (error) {
        console.error(error);
        alert(t('common.error') + ': ' + (error.response?.data?.message || 'Unable to load categories.'));
    } finally {
        loading.value = false;
    }
}

function changePage(page) {
    loadCategories(page);
}

function resetForm() {
    editingId.value = null;
    errorMessage.value = '';

    Object.assign(form, {
        name: '',
        parent_id: null,
        description: '',
        status: true,
    });
}

function openModal(category = null) {
    resetForm();

    if (category) {
        editingId.value = category.id;

        Object.assign(form, {
            name: category.name,
            parent_id: category.parent_id,
            description: category.description || '',
            status: Boolean(category.status),
        });
    }

    categoryModal.show();
}

async function saveCategory() {
    saving.value = true;
    errorMessage.value = '';

    try {
        const payload = {
            ...form,
            parent_id: form.parent_id || null,
        };

        if (editingId.value) {
            await api.put(
                `/categories/${editingId.value}`,
                payload
            );
        } else {
            await api.post('/categories', payload);
        }

        categoryModal.hide();

        await loadCategories(currentPage.value);

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

async function deleteCategory(id) {
    if (!confirm(t('categories.delete_confirm'))) {
        return;
    }

    try {
        await api.delete(`/categories/${id}`);

        await loadCategories(currentPage.value);

        alert(t('common.deleted_successfully'));

    } catch (error) {
        alert(
            error.response?.data?.message ||
            t('common.error')
        );
    }
}

onMounted(() => {
    categoryModal = new Modal(
        document.getElementById('categoryModal')
    );

    loadCategories();
});
</script>
