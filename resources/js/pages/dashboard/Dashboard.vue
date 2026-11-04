
<template>
    <div class="container-fluid py-4">

        <h2>Welcome to Retail POS</h2>

        <p class="text-muted">
            POS + E-commerce + Inventory Management
        </p>

        <div class="row g-3 mt-3">

            <div class="col-md-4">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5>Products</h5>
                        <h3>0</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5>Today's Sales</h5>
                        <h3>₹0.00</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5>Available Stock</h5>
                        <h3>0</h3>
                    </div>
                </div>
            </div>

        </div>

    </div>
</template>

<style scoped>
.dashboard-card {
    border: none;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    border-radius: 10px;
}
</style>
