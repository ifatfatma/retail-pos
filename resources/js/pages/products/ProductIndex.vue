
<template>
    <div class="container-fluid p-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Products</h2>
                <p class="text-muted mb-0">Manage your product inventory</p>
            </div>

            <button class="btn btn-primary" @click="openCreate">
                + Add Product
            </button>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <input
                    v-model="search"
                    @input="getProducts"
                    type="text"
                    class="form-control"
                    placeholder="Search product name or SKU..."
                >
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Category</th>
                            <th>Brand</th>
                            <th>Unit</th>
                            <th>Purchase Price</th>
                            <th>Selling Price</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="(product, index) in products" :key="product.id">
                            <td>{{ index + 1 }}</td>
                            <td class="fw-semibold">{{ product.name }}</td>
                            <td>{{ product.sku }}</td>
                            <td>{{ product.category?.name || '-' }}</td>
                            <td>{{ product.brand?.name || '-' }}</td>
                            <td>{{ product.unit?.name || '-' }}</td>
                            <td>₹{{ product.purchase_price }}</td>
                            <td>₹{{ product.selling_price }}</td>
                            <td>
                                <span class="badge bg-success" v-if="product.status">
                                    Active
                                </span>
                                <span class="badge bg-danger" v-else>
                                    Inactive
                                </span>
                            </td>
                            <td>
                                <button
                                    class="btn btn-sm btn-outline-primary me-1"
                                    @click="editProduct(product)"
                                >
                                    Edit
                                </button>

                                <button
                                    class="btn btn-sm btn-outline-danger"
                                    @click="deleteProduct(product.id)"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>

                        <tr v-if="products.length === 0">
                            <td colspan="10" class="text-center py-4">
                                No products found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-white">
                Total: {{ total }} products
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../services/api';
import { useRouter } from 'vue-router';

const products = ref([]);
const search = ref('');
const total = ref(0);
const router = useRouter();

const getProducts = async () => {
    try {
        const response = await api.get('/products', {
            params: { search: search.value }
        });

        products.value = response.data.data;
        total.value = response.data.total;

    } catch (error) {
        console.error('Product loading failed:', error);
    }
};

const openCreate = () => {
    router.push('/admin/products/create');
};

const editProduct = (product) => {
    router.push(`/admin/products/${product.id}/edit`);
};

const deleteProduct = async (id) => {
    if (!confirm('Are you sure you want to delete this product?')) {
        return;
    }

    try {
        await api.delete(`/products/${id}`);
        getProducts();
    } catch (error) {
        alert(error.response?.data?.message || 'Unable to delete product.');
    }
};

onMounted(getProducts);
</script>
