
<template>
    <div class="container-fluid p-4">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">
                    {{ isEdit ? 'Edit Product' : 'Add Product' }}
                </h2>
                <p class="text-muted mb-0">
                    Manage product information, pricing and images
                </p>
            </div>

            <button
                type="button"
                class="btn btn-outline-secondary"
                @click="goBack"
            >
                Back to Products
            </button>
        </div>

        <!-- Error Message -->
        <div v-if="errorMessage" class="alert alert-danger">
            {{ errorMessage }}
        </div>

        <form @submit.prevent="saveProduct">
            <div class="row">

                <!-- LEFT SIDE -->
                <div class="col-lg-8">

                    <!-- Basic Information -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white fw-bold py-3">
                            Basic Information
                        </div>

                        <div class="card-body">

                            <div class="mb-3">
                                <label class="form-label">
                                    Product Name
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    v-model.trim="form.name"
                                    type="text"
                                    class="form-control"
                                    placeholder="Enter product name"
                                    required
                                >
                            </div>

                            <div class="row">

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">
                                        Category
                                        <span class="text-danger">*</span>
                                    </label>

                                    <select
                                        v-model="form.category_id"
                                        class="form-select"
                                        required
                                    >
                                        <option value="">Select Category</option>

                                        <option
                                            v-for="item in categories"
                                            :key="item.id"
                                            :value="String(item.id)"
                                        >
                                            {{ item.name }}
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Brand</label>

                                    <select
                                        v-model="form.brand_id"
                                        class="form-select"
                                    >
                                        <option value="">Select Brand</option>

                                        <option
                                            v-for="item in brands"
                                            :key="item.id"
                                            :value="String(item.id)"
                                        >
                                            {{ item.name }}
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">
                                        Unit
                                        <span class="text-danger">*</span>
                                    </label>

                                    <select
                                        v-model="form.unit_id"
                                        class="form-select"
                                        required
                                    >
                                        <option value="">Select Unit</option>

                                        <option
                                            v-for="item in units"
                                            :key="item.id"
                                            :value="String(item.id)"
                                        >
                                            {{ item.name }}
                                        </option>
                                    </select>
                                </div>

                            </div>

                            <div class="mb-0">
                                <label class="form-label">Description</label>

                                <textarea
                                    v-model="form.description"
                                    class="form-control"
                                    rows="3"
                                    placeholder="Enter product description"
                                ></textarea>
                            </div>

                        </div>
                    </div>

                    <!-- Pricing -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white fw-bold py-3">
                            Pricing Information
                        </div>

                        <div class="card-body">
                            <div class="row">

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">
                                        Purchase Price (₹)
                                        <span class="text-danger">*</span>
                                    </label>

                                    <input
                                        v-model.number="form.purchase_price"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        class="form-control"
                                        required
                                    >
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">
                                        Selling Price (₹)
                                        <span class="text-danger">*</span>
                                    </label>

                                    <input
                                        v-model.number="form.selling_price"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        class="form-control"
                                        required
                                    >
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">
                                        MRP (₹)
                                        <span class="text-danger">*</span>
                                    </label>

                                    <input
                                        v-model.number="form.mrp"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        class="form-control"
                                        required
                                    >
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Tax Rate (%)</label>

                                    <input
                                        v-model.number="form.tax_rate"
                                        type="number"
                                        min="0"
                                        max="100"
                                        step="0.01"
                                        class="form-control"
                                    >
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Inventory -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white fw-bold py-3">
                            Inventory Settings
                        </div>

                        <div class="card-body">

                            <div class="form-check form-switch mb-3">
                                <input
                                    v-model="form.track_inventory"
                                    class="form-check-input"
                                    type="checkbox"
                                    id="trackInventory"
                                >

                                <label
                                    class="form-check-label"
                                    for="trackInventory"
                                >
                                    Track Inventory
                                </label>
                            </div>

                            <div
                                v-if="form.track_inventory"
                                class="col-md-6"
                            >
                                <label class="form-label">
                                    Low Stock Alert Quantity
                                </label>

                                <input
                                    v-model.number="form.low_stock_threshold"
                                    type="number"
                                    min="0"
                                    step="0.001"
                                    class="form-control"
                                >
                            </div>

                        </div>
                    </div>

                </div>

                <!-- RIGHT SIDE -->
                <div class="col-lg-4">

                    <!-- SKU & Barcode -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white fw-bold py-3">
                            Product Identification
                        </div>

                        <div class="card-body">

                            <label class="form-label">SKU</label>

                            <input
                                :value="form.sku || (
                                    isEdit
                                        ? 'Not generated'
                                        : 'Auto-generated on save'
                                )"
                                type="text"
                                class="form-control mb-3"
                                readonly
                            >

                            <label class="form-label">Barcode</label>

                            <input
                                v-model.trim="form.barcode"
                                type="text"
                                class="form-control"
                                placeholder="Enter or scan barcode"
                            >

                            <small class="text-muted">
                                You can enter a barcode manually or use a
                                USB barcode scanner.
                            </small>

                        </div>
                    </div>

                    <!-- Product Images -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white fw-bold py-3">
                            Product Images
                        </div>

                        <div class="card-body">

                            <input
                                type="file"
                                class="form-control"
                                accept="image/jpeg,image/png,image/webp"
                                multiple
                                @change="handleImageSelection"
                            >

                            <small class="text-muted d-block mt-2">
                                JPG, PNG or WEBP. Maximum 2 MB per image.
                            </small>

                            <!-- Existing Images -->
                            <div
                                v-if="existingImages.length"
                                class="mt-3"
                            >
                                <label class="form-label fw-semibold">
                                    Uploaded Images
                                </label>

                                <div class="row g-2">
                                    <div
                                        v-for="image in existingImages"
                                        :key="'existing-' + image.id"
                                        class="col-6"
                                    >
                                        <img
                                            :src="imageUrl(image.image_path)"
                                            class="img-fluid rounded border"
                                            style="
                                                height: 110px;
                                                width: 100%;
                                                object-fit: cover;
                                            "
                                            alt="Product image"
                                        >

                                        <small
                                            v-if="image.is_primary"
                                            class="text-success d-block mt-1"
                                        >
                                            Primary Image
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- New Image Previews -->
                            <div
                                v-if="selectedImagePreviews.length"
                                class="mt-3"
                            >
                                <label class="form-label fw-semibold">
                                    Selected Images
                                </label>

                                <div class="row g-2">
                                    <div
                                        v-for="(preview, index) in selectedImagePreviews"
                                        :key="'preview-' + index"
                                        class="col-6"
                                    >
                                        <img
                                            :src="preview"
                                            class="img-fluid rounded border"
                                            style="
                                                height: 110px;
                                                width: 100%;
                                                object-fit: cover;
                                            "
                                            alt="New product preview"
                                        >
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Status -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white fw-bold py-3">
                            Product Status
                        </div>

                        <div class="card-body">
                            <div class="form-check form-switch">

                                <input
                                    v-model="form.status"
                                    class="form-check-input"
                                    type="checkbox"
                                    id="productStatus"
                                >

                                <label
                                    class="form-check-label"
                                    for="productStatus"
                                >
                                    {{ form.status ? 'Active' : 'Inactive' }}
                                </label>

                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">

                            <button
                                type="submit"
                                class="btn btn-primary w-100 mb-2"
                                :disabled="saving || loading"
                            >
                                {{
                                    saving
                                        ? 'Saving...'
                                        : (
                                            isEdit
                                                ? 'Update Product'
                                                : 'Save Product'
                                        )
                                }}
                            </button>

                            <button
                                type="button"
                                class="btn btn-outline-secondary w-100"
                                :disabled="saving"
                                @click="goBack"
                            >
                                Cancel
                            </button>

                        </div>
                    </div>

                </div>

            </div>
        </form>

    </div>
</template>

<script setup>
import {
    ref,
    reactive,
    computed,
    onMounted,
    onBeforeUnmount
} from 'vue';

import { useRoute, useRouter } from 'vue-router';
import api from '../../services/api';

const route = useRoute();
const router = useRouter();

// Dropdown data
const categories = ref([]);
const brands = ref([]);
const units = ref([]);

// Loading states
const loading = ref(false);
const saving = ref(false);
const errorMessage = ref('');

// Image states
const selectedImages = ref([]);
const selectedImagePreviews = ref([]);
const existingImages = ref([]);

// Edit mode
const isEdit = computed(() => !!route.params.id);

// Product form
const form = reactive({
    name: '',
    category_id: '',
    brand_id: '',
    unit_id: '',
    description: '',
    sku: '',
    barcode: '',
    purchase_price: 0,
    selling_price: 0,
    mrp: 0,
    tax_rate: 0,
    track_inventory: true,
    low_stock_threshold: 0,
    status: true,
});

// Get dropdown rows
const getRows = (response) => {
    const result = response.data;

    return Array.isArray(result)
        ? result
        : (result.data || []);
};

// Load categories, brands and units
const loadDropdowns = async () => {
    try {
        const [
            categoryRes,
            brandRes,
            unitRes
        ] = await Promise.all([
            api.get('/categories'),
            api.get('/brands'),
            api.get('/units'),
        ]);

        categories.value = getRows(categoryRes);
        brands.value = getRows(brandRes);
        units.value = getRows(unitRes);

    } catch (error) {
        errorMessage.value =
            'Unable to load categories, brands or units.';

        console.error('Dropdown loading error:', error);
    }
};

// Image URL
const imageUrl = (path) => {
    if (!path) return '';

    if (/^https?:\/\//i.test(path)) {
        return path;
    }

    return `/storage/${path.replace(/^\/+/, '')}`;
};

// Load product details for editing
const loadProduct = async () => {
    if (!isEdit.value) return;

    loading.value = true;

    try {
        const response = await api.get(
            `/products/${route.params.id}`
        );

        const product = response.data.data;

        const variant =
            product.variants?.find(item => item.is_default)
            || product.variants?.[0]
            || {};

        Object.assign(form, {
            name: product.name || '',
            category_id: String(product.category_id || ''),
            brand_id: product.brand_id
                ? String(product.brand_id)
                : '',
            unit_id: String(product.unit_id || ''),
            description: product.description || '',
            sku: variant.sku || '',
            barcode: variant.barcode || '',
            purchase_price: Number(variant.purchase_price || 0),
            selling_price: Number(variant.selling_price || 0),
            mrp: Number(variant.mrp || 0),
            tax_rate: Number(variant.tax_rate || 0),
            track_inventory: Boolean(product.track_inventory),
            low_stock_threshold: Number(
                product.low_stock_threshold || 0
            ),
            status: Boolean(product.status),
        });

        existingImages.value = product.images || [];

    } catch (error) {
        errorMessage.value =
            error.response?.data?.message
            || 'Unable to load product details.';

        console.error('Product loading error:', error);

    } finally {
        loading.value = false;
    }
};

// Handle image selection
const handleImageSelection = (event) => {
    const files = Array.from(event.target.files || []);

    // Clear previous previews
    selectedImagePreviews.value.forEach(url => {
        URL.revokeObjectURL(url);
    });

    selectedImages.value = [];
    selectedImagePreviews.value = [];

    const invalidFiles = files.filter(file => {
        const validType = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ].includes(file.type);

        const validSize = file.size <= 2 * 1024 * 1024;

        return !validType || !validSize;
    });

    if (invalidFiles.length > 0) {
        errorMessage.value =
            'Only JPG, PNG and WEBP images up to 2 MB are allowed.';

        event.target.value = '';
        return;
    }

    errorMessage.value = '';

    selectedImages.value = files;

    selectedImagePreviews.value = files.map(file => {
        return URL.createObjectURL(file);
    });
};

// Upload product images
const uploadProductImages = async (productId) => {
    if (!selectedImages.value.length) return;

    const imageData = new FormData();

    selectedImages.value.forEach(image => {
        imageData.append('images[]', image);
    });

    await api.post(
        `/products/${productId}/images`,
        imageData
    );
};

// Save or update product
const saveProduct = async () => {
    saving.value = true;
    errorMessage.value = '';

    try {
        const payload = {
            ...form,
            brand_id: form.brand_id || null,
            product_type: 'simple',
        };

        let productId;

        if (isEdit.value) {

            // Update existing product
            productId = route.params.id;

            await api.put(
                `/products/${productId}`,
                payload
            );

        } else {

            // Create new product
            const response = await api.post(
                '/products',
                payload
            );

            productId = response.data.data.id;
        }

        // Upload images after product save
        await uploadProductImages(productId);

        // Release preview URLs
        selectedImagePreviews.value.forEach(url => {
            URL.revokeObjectURL(url);
        });

        router.push('/admin/products');

    } catch (error) {
        const errors = error.response?.data?.errors;

        if (errors) {
            errorMessage.value = Object.values(errors)
                .flat()
                .join(' ');
        } else {
            errorMessage.value =
                error.response?.data?.message
                || 'Unable to save product.';
        }

        console.error('Product save error:', error);

    } finally {
        saving.value = false;
    }
};

// Back to product listing
const goBack = () => {
    router.push('/admin/products');
};

// Release preview URLs when leaving component
onBeforeUnmount(() => {
    selectedImagePreviews.value.forEach(url => {
        URL.revokeObjectURL(url);
    });
});

// Initial loading
onMounted(async () => {
    await loadDropdowns();
    await loadProduct();
});
</script>
