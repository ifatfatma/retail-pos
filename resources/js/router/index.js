import { createRouter, createWebHistory } from 'vue-router';

import Dashboard from '../pages/dashboard/Dashboard.vue';
import CategoryIndex from '../pages/categories/CategoryIndex.vue';
import BrandIndex from '../pages/brands/BrandIndex.vue';
import UnitIndex from '../pages/units/UnitIndex.vue';
import ProductIndex from '../pages/products/ProductIndex.vue';
import ProductForm from '../pages/products/ProductForm.vue';
import InventoryIndex from '../pages/inventory/InventoryIndex.vue';
import PurchaseIndex from '../pages/purchases/PurchaseIndex.vue';
import PurchaseForm from '../pages/purchases/PurchaseForm.vue';
import PosCounter from '../pages/pos/PosCounter.vue';

const routes = [
    {
        path: '/',
        redirect: '/admin/dashboard'
    },
    {
        path: '/admin/dashboard',
        component: Dashboard
    },
    {
        path: '/admin/categories',
        component: CategoryIndex
    },
    {
        path: '/admin/brands',
        component: BrandIndex
    },
    {
        path: '/admin/units',
        component: UnitIndex
    },
    {
        path: '/admin/products',
        component: ProductIndex
    },
    {
        path: '/admin/products/create',
        component: ProductForm
    },
    {
        path: '/admin/products/:id/edit',
        component: ProductForm
    },
    {
        path: '/admin/inventory',
        component: InventoryIndex
    },
    {
        path: '/admin/purchases',
        component: PurchaseIndex
    },
    {
        path: '/admin/purchases/create',
        component: PurchaseForm
    },
    {
        path: '/admin/pos',
        component: PosCounter
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

export default router;
