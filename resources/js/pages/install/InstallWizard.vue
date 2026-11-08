<template>
    <div class="installer-wrapper" :dir="isRtlLocale ? 'rtl' : 'ltr'">
        <!-- Top Sticky Header -->
        <header class="installer-header py-3 px-4 shadow-sm border-bottom bg-white">
            <div class="container d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <div class="brand-icon-box bg-primary text-white rounded-3 d-flex align-items-center justify-content-center">
                        <i class="bi bi-shop fs-4"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold text-dark">{{ $t('installer.title') }}</h5>
                        <small class="text-muted d-none d-sm-inline">{{ $t('installer.subtitle') }}</small>
                    </div>
                </div>

                <!-- Language Selector -->
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-dark border d-none d-md-inline-block">v2.5 Enterprise</span>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                            <span>{{ currentLocaleInfo.flag }}</span>
                            <span>{{ currentLocaleInfo.name }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li v-for="loc in supportedLocales" :key="loc.code">
                                <a class="dropdown-item d-flex align-items-center gap-2" href="#" @click.prevent="changeLocale(loc.code)" :class="{ active: currentLocale === loc.code }">
                                    <span>{{ loc.flag }}</span>
                                    <span>{{ loc.nativeName }} ({{ loc.name }})</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Body -->
        <main class="container py-4 my-2">
            <!-- If already installed warning screen -->
            <div v-if="isAlreadyInstalled" class="row justify-content-center">
                <div class="col-lg-7">
                    <div class="card shadow-sm border-0 text-center p-5 rounded-4">
                        <div class="text-warning mb-3">
                            <i class="bi bi-shield-lock-fill display-1"></i>
                        </div>
                        <h3 class="fw-bold text-dark mb-2">{{ $t('installer.already_installed_title') }}</h3>
                        <p class="text-muted mb-4">{{ $t('installer.already_installed_desc') }}</p>
                        <div class="d-flex justify-content-center gap-3">
                            <router-link to="/admin/login" class="btn btn-primary px-4 py-2">
                                <i class="bi bi-box-arrow-in-right me-2"></i> {{ $t('installer.go_to_login') }}
                            </router-link>
                            <router-link to="/" class="btn btn-outline-secondary px-4 py-2">
                                <i class="bi bi-house me-2"></i> {{ $t('installer.go_to_store') }}
                            </router-link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Normal Installer Wizard -->
            <div v-else class="row g-4">
                <!-- Left Stepper Navigation (Sidebar on Desktop) -->
                <div class="col-lg-3">
                    <div class="card border-0 shadow-sm rounded-4 p-3 sticky-top" style="top: 80px;">
                        <div class="d-flex align-items-center justify-content-between mb-3 px-2">
                            <span class="text-uppercase fw-bold text-muted small">Installation Steps</span>
                            <span class="badge bg-primary-subtle text-primary">{{ currentStep }} / 10</span>
                        </div>
                        <ul class="list-unstyled mb-0 stepper-list">
                            <li v-for="s in stepList" :key="s.num" class="stepper-item mb-2" :class="{
                                active: currentStep === s.num,
                                completed: currentStep > s.num,
                                disabled: currentStep < s.num
                            }">
                                <div class="stepper-link d-flex align-items-center gap-2 p-2 rounded-3" @click="handleStepClick(s.num)">
                                    <div class="step-badge rounded-circle d-flex align-items-center justify-content-center">
                                        <i v-if="currentStep > s.num" class="bi bi-check-lg text-white"></i>
                                        <span v-else>{{ s.num }}</span>
                                    </div>
                                    <div class="step-label text-truncate">
                                        <span class="d-block fw-semibold small">{{ $t(s.key) }}</span>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Right Form Content Area -->
                <div class="col-lg-9">
                    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">

                        <!-- STEP 1: WELCOME -->
                        <section v-if="currentStep === 1" class="step-pane">
                            <div class="text-center mb-4">
                                <div class="welcome-icon-circle mx-auto mb-3 bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-stars display-5"></i>
                                </div>
                                <h2 class="fw-bold text-dark">{{ $t('installer.welcome_title') }}</h2>
                                <p class="text-muted col-lg-10 mx-auto">{{ $t('installer.welcome_desc') }}</p>
                            </div>

                            <div class="card bg-light border-0 rounded-3 p-4 mb-4">
                                <h6 class="fw-bold text-dark mb-3">
                                    <i class="bi bi-check2-circle text-primary me-2"></i> {{ $t('installer.welcome_features_title') }}
                                </h6>
                                <ul class="list-unstyled mb-0 d-flex flex-column gap-2 text-secondary">
                                    <li class="d-flex align-items-center gap-2">
                                        <i class="bi bi-receipt text-success"></i> {{ $t('installer.welcome_feature_pos') }}
                                    </li>
                                    <li class="d-flex align-items-center gap-2">
                                        <i class="bi bi-cart3 text-success"></i> {{ $t('installer.welcome_feature_ecommerce') }}
                                    </li>
                                    <li class="d-flex align-items-center gap-2">
                                        <i class="bi bi-box-seam text-success"></i> {{ $t('installer.welcome_feature_inventory') }}
                                    </li>
                                    <li class="d-flex align-items-center gap-2">
                                        <i class="bi bi-megaphone text-success"></i> {{ $t('installer.welcome_feature_marketing') }}
                                    </li>
                                    <li class="d-flex align-items-center gap-2">
                                        <i class="bi bi-globe text-success"></i> {{ $t('installer.welcome_feature_multilingual') }}
                                    </li>
                                </ul>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button class="btn btn-primary btn-lg px-4 d-flex align-items-center gap-2" @click="goToStep(2)">
                                    <span>{{ $t('installer.get_started') }}</span>
                                    <i class="bi bi-arrow-right"></i>
                                </button>
                            </div>
                        </section>

                        <!-- STEP 2: SYSTEM REQUIREMENTS -->
                        <section v-else-if="currentStep === 2" class="step-pane">
                            <div class="mb-4">
                                <h3 class="fw-bold text-dark">{{ $t('installer.requirements_title') }}</h3>
                                <p class="text-muted">{{ $t('installer.requirements_desc') }}</p>
                            </div>

                            <div v-if="loadingRequirements" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="text-muted mt-2 small">{{ $t('common.please_wait') }}</p>
                            </div>

                            <div v-else>
                                <div class="table-responsive mb-4">
                                    <table class="table table-hover align-middle border">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Requirement</th>
                                                <th>Required</th>
                                                <th>Current</th>
                                                <th class="text-end">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="req in requirements" :key="req.name">
                                                <td class="fw-semibold">{{ req.label }}</td>
                                                <td><code>{{ req.required }}</code></td>
                                                <td><code>{{ req.current }}</code></td>
                                                <td class="text-end">
                                                    <span v-if="req.status === 'passed'" class="badge bg-success-subtle text-success border border-success">
                                                        <i class="bi bi-check-circle-fill me-1"></i> {{ $t('installer.passed') }}
                                                    </span>
                                                    <span v-else-if="req.status === 'warning'" class="badge bg-warning-subtle text-warning border border-warning">
                                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $t('installer.warning') }}
                                                    </span>
                                                    <span v-else class="badge bg-danger-subtle text-danger border border-danger">
                                                        <i class="bi bi-x-circle-fill me-1"></i> {{ $t('installer.failed') }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div v-if="requirementsPassed" class="alert alert-success d-flex align-items-center gap-2">
                                    <i class="bi bi-check-circle-fill fs-5"></i>
                                    <div>{{ $t('installer.all_requirements_passed') }}</div>
                                </div>
                                <div v-else class="alert alert-danger d-flex align-items-center gap-2">
                                    <i class="bi bi-exclamation-octagon-fill fs-5"></i>
                                    <div>{{ $t('installer.requirements_failed_warning') }}</div>
                                </div>

                                <div class="d-flex justify-content-between mt-4">
                                    <button class="btn btn-outline-secondary" @click="goToStep(1)">
                                        <i class="bi bi-arrow-left me-1"></i> {{ $t('installer.previous') }}
                                    </button>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-outline-primary" @click="fetchRequirements">
                                            <i class="bi bi-arrow-clockwise me-1"></i> {{ $t('installer.retry') }}
                                        </button>
                                        <button class="btn btn-primary" :disabled="!requirementsPassed" @click="goToStep(3)">
                                            {{ $t('installer.next') }} <i class="bi bi-arrow-right ms-1"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- STEP 3: FOLDER PERMISSIONS -->
                        <section v-else-if="currentStep === 3" class="step-pane">
                            <div class="mb-4">
                                <h3 class="fw-bold text-dark">{{ $t('installer.permissions_title') }}</h3>
                                <p class="text-muted">{{ $t('installer.permissions_desc') }}</p>
                            </div>

                            <div v-if="loadingPermissions" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="text-muted mt-2 small">{{ $t('common.please_wait') }}</p>
                            </div>

                            <div v-else>
                                <div class="table-responsive mb-4">
                                    <table class="table table-hover align-middle border">
                                        <thead class="table-light">
                                            <tr>
                                                <th>{{ $t('installer.folder') }}</th>
                                                <th>Target Path</th>
                                                <th class="text-end">Permission Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="perm in permissions" :key="perm.folder">
                                                <td class="fw-semibold"><code>{{ perm.folder }}</code></td>
                                                <td><small class="text-muted font-monospace">{{ perm.path }}</small></td>
                                                <td class="text-end">
                                                    <span v-if="perm.status === 'passed'" class="badge bg-success-subtle text-success border border-success">
                                                        <i class="bi bi-check-circle-fill me-1"></i> {{ $t('installer.writable') }}
                                                    </span>
                                                    <span v-else class="badge bg-danger-subtle text-danger border border-danger">
                                                        <i class="bi bi-x-circle-fill me-1"></i> {{ $t('installer.not_writable') }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div v-if="permissionsPassed" class="alert alert-success d-flex align-items-center gap-2">
                                    <i class="bi bi-check-circle-fill fs-5"></i>
                                    <div>{{ $t('installer.all_permissions_passed') }}</div>
                                </div>
                                <div v-else class="alert alert-danger d-flex align-items-center gap-2">
                                    <i class="bi bi-exclamation-octagon-fill fs-5"></i>
                                    <div>{{ $t('installer.permissions_failed_warning') }}</div>
                                </div>

                                <div class="d-flex justify-content-between mt-4">
                                    <button class="btn btn-outline-secondary" @click="goToStep(2)">
                                        <i class="bi bi-arrow-left me-1"></i> {{ $t('installer.previous') }}
                                    </button>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-outline-primary" @click="fetchPermissions">
                                            <i class="bi bi-arrow-clockwise me-1"></i> {{ $t('installer.retry') }}
                                        </button>
                                        <button class="btn btn-primary" :disabled="!permissionsPassed" @click="goToStep(4)">
                                            {{ $t('installer.next') }} <i class="bi bi-arrow-right ms-1"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- STEP 4: DATABASE CONFIGURATION -->
                        <section v-else-if="currentStep === 4" class="step-pane">
                            <div class="mb-4">
                                <h3 class="fw-bold text-dark">{{ $t('installer.database_title') }}</h3>
                                <p class="text-muted">{{ $t('installer.database_desc') }}</p>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-8">
                                    <label class="form-label fw-semibold">{{ $t('installer.db_host') }} <span class="text-danger">*</span></label>
                                    <input v-model="form.database.host" type="text" class="form-control" required placeholder="127.0.0.1">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ $t('installer.db_port') }} <span class="text-danger">*</span></label>
                                    <input v-model="form.database.port" type="number" class="form-control" required placeholder="3306">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">{{ $t('installer.db_name') }} <span class="text-danger">*</span></label>
                                    <input v-model="form.database.database" type="text" class="form-control" required placeholder="retail_pos">
                                    <small class="text-muted">Enter target database name. Will be auto-created if it does not exist.</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ $t('installer.db_username') }} <span class="text-danger">*</span></label>
                                    <input v-model="form.database.username" type="text" class="form-control" required placeholder="root">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ $t('installer.db_password') }}</label>
                                    <div class="input-group">
                                        <input v-model="form.database.password" :type="showDbPassword ? 'text' : 'password'" class="form-control" :placeholder="$t('installer.db_password_placeholder')">
                                        <button class="btn btn-outline-secondary" type="button" @click="showDbPassword = !showDbPassword">
                                            <i class="bi" :class="showDbPassword ? 'bi-eye-slash' : 'bi-eye'"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info d-flex align-items-center gap-2">
                                <i class="bi bi-shield-lock-fill fs-5"></i>
                                <small>{{ $t('installer.db_notice') }}</small>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <button class="btn btn-outline-secondary" @click="goToStep(3)">
                                    <i class="bi bi-arrow-left me-1"></i> {{ $t('installer.previous') }}
                                </button>
                                <button class="btn btn-primary" :disabled="!form.database.host || !form.database.database || !form.database.username" @click="goToStep(5)">
                                    {{ $t('installer.next') }} <i class="bi bi-arrow-right ms-1"></i>
                                </button>
                            </div>
                        </section>

                        <!-- STEP 5: DATABASE CONNECTION TEST -->
                        <section v-else-if="currentStep === 5" class="step-pane">
                            <div class="mb-4">
                                <h3 class="fw-bold text-dark">{{ $t('installer.step_db_test') }}</h3>
                                <p class="text-muted">Verify connectivity to your MySQL / MariaDB server before proceeding.</p>
                            </div>

                            <div class="card bg-light border-0 rounded-3 p-4 mb-4">
                                <h6 class="fw-bold text-dark mb-3">Connection Summary</h6>
                                <div class="row g-2">
                                    <div class="col-sm-6"><strong>Host:</strong> <code>{{ form.database.host }}:{{ form.database.port }}</code></div>
                                    <div class="col-sm-6"><strong>Database:</strong> <code>{{ form.database.database }}</code></div>
                                    <div class="col-sm-6"><strong>User:</strong> <code>{{ form.database.username }}</code></div>
                                    <div class="col-sm-6"><strong>Password:</strong> <code>{{ form.database.password ? '••••••••' : '(empty / none)' }}</code></div>
                                </div>
                            </div>

                            <div v-if="dbTestResult" class="mb-4">
                                <div v-if="dbTestResult.success" class="alert alert-success d-flex align-items-center gap-3">
                                    <i class="bi bi-check-circle-fill fs-3"></i>
                                    <div>
                                        <div class="fw-bold">{{ $t('installer.connection_success') }}</div>
                                        <div class="small">{{ dbTestResult.message }}</div>
                                        <div v-if="dbTestResult.server_version" class="small text-muted mt-1">Server: {{ dbTestResult.server_version }}</div>
                                    </div>
                                </div>
                                <div v-else class="alert alert-danger d-flex align-items-center gap-3">
                                    <i class="bi bi-exclamation-triangle-fill fs-3"></i>
                                    <div>
                                        <div class="fw-bold">{{ $t('installer.connection_failed') }}</div>
                                        <div class="small">{{ dbTestResult.message }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <button class="btn btn-outline-secondary" @click="goToStep(4)">
                                    <i class="bi bi-arrow-left me-1"></i> Edit Database Details
                                </button>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-primary d-flex align-items-center gap-2" :disabled="testingDb" @click="testDatabaseConnection">
                                        <span v-if="testingDb" class="spinner-border spinner-border-sm"></span>
                                        <i v-else class="bi bi-hdd-network"></i>
                                        <span>{{ testingDb ? $t('installer.testing_connection') : $t('installer.test_connection_btn') }}</span>
                                    </button>
                                    <button class="btn btn-primary" :disabled="!dbTestResult?.success" @click="goToStep(6)">
                                        {{ $t('installer.next') }} <i class="bi bi-arrow-right ms-1"></i>
                                    </button>
                                </div>
                            </div>
                        </section>

                        <!-- STEP 6: APPLICATION CONFIGURATION -->
                        <section v-else-if="currentStep === 6" class="step-pane">
                            <div class="mb-4">
                                <h3 class="fw-bold text-dark">{{ $t('installer.app_config_title') }}</h3>
                                <p class="text-muted">{{ $t('installer.app_config_desc') }}</p>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ $t('installer.app_name') }} <span class="text-danger">*</span></label>
                                    <input v-model="form.app.app_name" type="text" class="form-control" required placeholder="Smart Stationery POS">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ $t('installer.app_url') }} <span class="text-danger">*</span></label>
                                    <input v-model="form.app.app_url" type="url" class="form-control" required placeholder="http://localhost:8000">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ $t('installer.app_env') }} <span class="text-danger">*</span></label>
                                    <select v-model="form.app.app_env" class="form-select">
                                        <option value="production">{{ $t('installer.env_production') }}</option>
                                        <option value="local">{{ $t('installer.env_local') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ $t('installer.default_language') }} <span class="text-danger">*</span></label>
                                    <select v-model="form.app.default_language" class="form-select">
                                        <option value="en">English (UK/US)</option>
                                        <option value="ar">العربية (Arabic - RTL)</option>
                                        <option value="hi">हिन्दी (Hindi)</option>
                                        <option value="ur">اردو (Urdu - RTL)</option>
                                        <option value="bn">বাংলা (Bengali)</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ $t('installer.currency') }} <span class="text-danger">*</span></label>
                                    <input v-model="form.app.currency" type="text" class="form-control" required placeholder="₹">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ $t('installer.app_timezone') }} <span class="text-danger">*</span></label>
                                    <select v-model="form.app.app_timezone" class="form-select">
                                        <option value="Asia/Kolkata">Asia/Kolkata (IST +5:30)</option>
                                        <option value="Asia/Dubai">Asia/Dubai (GST +4:00)</option>
                                        <option value="Asia/Riyadh">Asia/Riyadh (AST +3:00)</option>
                                        <option value="Asia/Dhaka">Asia/Dhaka (BST +6:00)</option>
                                        <option value="Asia/Karachi">Asia/Karachi (PKT +5:00)</option>
                                        <option value="UTC">UTC (Universal Coordinated Time)</option>
                                        <option value="Europe/London">Europe/London (GMT/BST)</option>
                                        <option value="America/New_York">America/New_York (EST/EDT)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ $t('installer.session_driver') }}</label>
                                    <select v-model="form.app.session_driver" class="form-select">
                                        <option value="database">Database (Recommended for multi-user POS)</option>
                                        <option value="file">File System</option>
                                    </select>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <button class="btn btn-outline-secondary" @click="goToStep(5)">
                                    <i class="bi bi-arrow-left me-1"></i> {{ $t('installer.previous') }}
                                </button>
                                <button class="btn btn-primary" :disabled="!form.app.app_name || !form.app.app_url" @click="goToStep(7)">
                                    {{ $t('installer.next') }} <i class="bi bi-arrow-right ms-1"></i>
                                </button>
                            </div>
                        </section>

                        <!-- STEP 7: ADMINISTRATOR ACCOUNT -->
                        <section v-else-if="currentStep === 7" class="step-pane">
                            <div class="mb-4">
                                <h3 class="fw-bold text-dark">{{ $t('installer.admin_title') }}</h3>
                                <p class="text-muted">{{ $t('installer.admin_desc') }}</p>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ $t('installer.admin_name') }} <span class="text-danger">*</span></label>
                                    <input v-model="form.admin.name" type="text" class="form-control" required placeholder="Store Administrator">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ $t('installer.admin_email') }} <span class="text-danger">*</span></label>
                                    <input v-model="form.admin.email" type="email" class="form-control" required placeholder="admin@retailpos.local">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ $t('installer.admin_password') }} <span class="text-danger">*</span></label>
                                    <input v-model="form.admin.password" type="password" class="form-control" required placeholder="••••••••">
                                    <div class="mt-1 small" :class="passwordStrengthClass">
                                        {{ passwordStrengthText }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ $t('installer.admin_password_confirm') }} <span class="text-danger">*</span></label>
                                    <input v-model="form.admin.password_confirmation" type="password" class="form-control" required placeholder="••••••••">
                                    <div v-if="form.admin.password && form.admin.password_confirmation && form.admin.password !== form.admin.password_confirmation" class="text-danger small mt-1">
                                        {{ $t('installer.passwords_must_match') }}
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <button class="btn btn-outline-secondary" @click="goToStep(6)">
                                    <i class="bi bi-arrow-left me-1"></i> {{ $t('installer.previous') }}
                                </button>
                                <button class="btn btn-primary" :disabled="!isAdminFormValid" @click="goToStep(8)">
                                    {{ $t('installer.next') }} <i class="bi bi-arrow-right ms-1"></i>
                                </button>
                            </div>
                        </section>

                        <!-- STEP 8: BUSINESS INFORMATION -->
                        <section v-else-if="currentStep === 8" class="step-pane">
                            <div class="mb-4">
                                <h3 class="fw-bold text-dark">{{ $t('installer.business_title') }}</h3>
                                <p class="text-muted">{{ $t('installer.business_desc') }}</p>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ $t('installer.business_name') }}</label>
                                    <input v-model="form.business.business_name" type="text" class="form-control" placeholder="Smart Stationery Shop">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ $t('installer.business_tagline') }}</label>
                                    <input v-model="form.business.tagline" type="text" class="form-control" placeholder="Quality retail essentials delivered to your door">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ $t('installer.business_email') }}</label>
                                    <input v-model="form.business.email" type="email" class="form-control" placeholder="contact@smartstationery.local">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ $t('installer.business_phone') }}</label>
                                    <input v-model="form.business.phone" type="text" class="form-control" placeholder="+91 98765 43210">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">{{ $t('installer.business_address') }}</label>
                                    <input v-model="form.business.address" type="text" class="form-control" placeholder="Shop 42, Market Avenue, Commercial Hub">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">{{ $t('installer.business_city') }}</label>
                                    <input v-model="form.business.city" type="text" class="form-control" placeholder="Mumbai">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">{{ $t('installer.business_state') }}</label>
                                    <input v-model="form.business.state" type="text" class="form-control" placeholder="Maharashtra">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">{{ $t('installer.business_country') }}</label>
                                    <input v-model="form.business.country" type="text" class="form-control" placeholder="India">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">{{ $t('installer.business_postal_code') }}</label>
                                    <input v-model="form.business.postal_code" type="text" class="form-control" placeholder="400001">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">{{ $t('installer.tax_number') }}</label>
                                    <input v-model="form.business.tax_number" type="text" class="form-control" placeholder="GSTIN 27AAAAA0000A1Z5 or VAT Reg. No.">
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <button class="btn btn-outline-secondary" @click="goToStep(7)">
                                    <i class="bi bi-arrow-left me-1"></i> {{ $t('installer.previous') }}
                                </button>
                                <button class="btn btn-success btn-lg px-4 d-flex align-items-center gap-2" @click="startInstallation">
                                    <i class="bi bi-box-arrow-down"></i>
                                    <span>{{ $t('installer.review_install_btn') }}</span>
                                </button>
                            </div>
                        </section>

                        <!-- STEP 9: INSTALLATION PROGRESS -->
                        <section v-else-if="currentStep === 9" class="step-pane text-center py-4">
                            <div class="mb-4">
                                <div class="spinner-border text-primary mb-3" style="width: 3.5rem; height: 3.5rem;" role="status"></div>
                                <h3 class="fw-bold text-dark">{{ $t('installer.installing_title') }}</h3>
                                <p class="text-muted col-lg-8 mx-auto">{{ $t('installer.installing_desc') }}</p>
                            </div>

                            <div class="progress mb-4" style="height: 18px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" :style="{ width: `${installProgressPct}%` }">
                                    {{ installProgressPct }}%
                                </div>
                            </div>

                            <div class="card bg-light border-0 rounded-3 p-3 text-start col-lg-8 mx-auto mb-4">
                                <div class="small fw-semibold text-secondary d-flex align-items-center gap-2">
                                    <i class="bi bi-arrow-repeat text-primary spin-icon"></i>
                                    <span>{{ currentStageMessage }}</span>
                                </div>
                            </div>

                            <div v-if="installError" class="alert alert-danger text-start col-lg-8 mx-auto">
                                <h6 class="fw-bold"><i class="bi bi-x-circle-fill me-2"></i>Installation Failed</h6>
                                <p class="mb-3 small">{{ installError }}</p>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-danger" @click="goToStep(8)">Go Back & Review</button>
                                    <button class="btn btn-sm btn-danger" @click="startInstallation">Retry Installation</button>
                                </div>
                            </div>
                        </section>

                        <!-- STEP 10: INSTALLATION COMPLETE -->
                        <section v-else-if="currentStep === 10" class="step-pane text-center py-4">
                            <div class="celebration-badge mb-3 bg-success-subtle text-success rounded-circle d-inline-flex align-items-center justify-content-center">
                                <i class="bi bi-check-circle-fill display-3"></i>
                            </div>
                            <h2 class="fw-bold text-dark mb-2">{{ $t('installer.complete_title') }}</h2>
                            <p class="text-muted col-lg-8 mx-auto mb-4">{{ $t('installer.complete_desc') }}</p>

                            <div class="card bg-light border-0 rounded-4 p-4 text-start col-lg-8 mx-auto mb-4 shadow-sm">
                                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Installation Details</h6>
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <small class="text-muted d-block">{{ $t('installer.summary_app_name') }}</small>
                                        <span class="fw-semibold text-dark">{{ installedResult?.app_name || form.app.app_name }}</span>
                                    </div>
                                    <div class="col-sm-6">
                                        <small class="text-muted d-block">{{ $t('installer.summary_admin_email') }}</small>
                                        <span class="fw-semibold text-dark">{{ installedResult?.admin_email || form.admin.email }}</span>
                                    </div>
                                    <div class="col-sm-6">
                                        <small class="text-muted d-block">{{ $t('installer.summary_installed_at') }}</small>
                                        <span class="fw-semibold text-dark">{{ installedResult?.installed_at ? new Date(installedResult.installed_at).toLocaleString() : new Date().toLocaleString() }}</span>
                                    </div>
                                    <div class="col-sm-6">
                                        <small class="text-muted d-block">{{ $t('installer.summary_security_status') }}</small>
                                        <span class="badge bg-success-subtle text-success border border-success">
                                            <i class="bi bi-lock-fill me-1"></i> {{ $t('installer.security_locked') }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-warning col-lg-8 mx-auto small text-start d-flex align-items-center gap-2 mb-4">
                                <i class="bi bi-shield-exclamation fs-4"></i>
                                <div>{{ $t('installer.security_note') }}</div>
                            </div>

                            <div class="d-flex justify-content-center gap-3">
                                <router-link to="/admin/login" class="btn btn-primary btn-lg px-5 py-3 rounded-pill fw-bold shadow">
                                    <i class="bi bi-box-arrow-in-right me-2"></i> {{ $t('installer.go_to_login') }}
                                </router-link>
                            </div>
                        </section>

                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { useI18n } from 'vue-i18n';
import { supportedLocales, isRtl, setLanguage } from '../../i18n';

const router = useRouter();
const { t, locale } = useI18n();

const currentLocale = ref(locale.value || 'en');
const isRtlLocale = computed(() => isRtl(currentLocale.value));

const currentLocaleInfo = computed(() => {
    return supportedLocales.find(l => l.code === currentLocale.value) || supportedLocales[0];
});

function changeLocale(code) {
    currentLocale.value = code;
    setLanguage(code);
}

// Installation state
const currentStep = ref(1);
const isAlreadyInstalled = ref(false);

const stepList = [
    { num: 1, key: 'installer.step_welcome' },
    { num: 2, key: 'installer.step_requirements' },
    { num: 3, key: 'installer.step_permissions' },
    { num: 4, key: 'installer.step_database' },
    { num: 5, key: 'installer.step_db_test' },
    { num: 6, key: 'installer.step_app_config' },
    { num: 7, key: 'installer.step_admin' },
    { num: 8, key: 'installer.step_business' },
    { num: 9, key: 'installer.step_progress' },
    { num: 10, key: 'installer.step_complete' }
];

// Master form data
const form = ref({
    database: {
        host: '127.0.0.1',
        port: 3306,
        database: 'retail_pos',
        username: 'root',
        password: ''
    },
    app: {
        app_name: 'Smart Stationery POS',
        app_url: window.location.origin,
        app_env: 'production',
        app_timezone: 'Asia/Kolkata',
        default_language: 'en',
        currency: '₹',
        session_driver: 'database'
    },
    admin: {
        name: 'Store Administrator',
        email: 'admin@retailpos.local',
        password: '',
        password_confirmation: ''
    },
    business: {
        business_name: 'Smart Stationery Shop',
        tagline: 'Quality retail essentials delivered to your door',
        email: 'admin@retailpos.local',
        phone: '+91 98765 43210',
        address: 'Shop 42, Market Avenue, Commercial Hub',
        city: 'Mumbai',
        state: 'Maharashtra',
        country: 'India',
        postal_code: '400001',
        tax_number: 'GSTIN 27AAAAA0000A1Z5'
    }
});

const showDbPassword = ref(false);

// Step 2 Requirements State
const loadingRequirements = ref(false);
const requirements = ref([]);
const requirementsPassed = ref(false);

async function fetchRequirements() {
    loadingRequirements.value = true;
    try {
        const res = await axios.get('/api/install/requirements');
        requirements.value = res.data.requirements || [];
        requirementsPassed.value = !!res.data.all_passed;
    } catch (e) {
        console.error('Failed to fetch requirements:', e);
    } finally {
        loadingRequirements.value = false;
    }
}

// Step 3 Permissions State
const loadingPermissions = ref(false);
const permissions = ref([]);
const permissionsPassed = ref(false);

async function fetchPermissions() {
    loadingPermissions.value = true;
    try {
        const res = await axios.get('/api/install/permissions');
        permissions.value = res.data.permissions || [];
        permissionsPassed.value = !!res.data.all_passed;
    } catch (e) {
        console.error('Failed to fetch permissions:', e);
    } finally {
        loadingPermissions.value = false;
    }
}

// Step 5 Database Test State
const testingDb = ref(false);
const dbTestResult = ref(null);

async function testDatabaseConnection() {
    testingDb.value = true;
    dbTestResult.value = null;
    try {
        const res = await axios.post('/api/install/database/test', form.value.database);
        dbTestResult.value = res.data;
    } catch (e) {
        dbTestResult.value = {
            success: false,
            message: e.response?.data?.message || 'Database connection failed. Please check host, port, username or password.'
        };
    } finally {
        testingDb.value = false;
    }
}

// Step 7 Admin Validation
const isAdminFormValid = computed(() => {
    const { name, email, password, password_confirmation } = form.value.admin;
    return name?.length >= 3 &&
           email?.includes('@') &&
           password?.length >= 8 &&
           password === password_confirmation;
});

const passwordStrengthText = computed(() => {
    const p = form.value.admin.password;
    if (!p) return t('installer.password_min_length');
    if (p.length < 8) return 'Too short (min 8 characters)';
    if (p.length >= 12 && /[A-Z]/.test(p) && /[0-9]/.test(p) && /[^A-Za-z0-9]/.test(p)) return 'Strong password';
    if (p.length >= 8 && (/[0-9]/.test(p) || /[A-Z]/.test(p))) return 'Medium password';
    return 'Weak password';
});

const passwordStrengthClass = computed(() => {
    const p = form.value.admin.password;
    if (!p || p.length < 8) return 'text-muted';
    if (p.length >= 12 && /[A-Z]/.test(p) && /[0-9]/.test(p)) return 'text-success fw-semibold';
    return 'text-warning fw-semibold';
});

// Step 9 & 10 Installation Execution
const installProgressPct = ref(10);
const currentStageMessage = ref('Initializing installation...');
const installError = ref(null);
const installedResult = ref(null);

async function startInstallation() {
    currentStep.value = 9;
    installError.value = null;
    installProgressPct.value = 15;
    currentStageMessage.value = t('installer.stage_writing_env');

    const progressInterval = setInterval(() => {
        if (installProgressPct.value < 90) {
            installProgressPct.value += 12;
            if (installProgressPct.value >= 30 && installProgressPct.value < 50) {
                currentStageMessage.value = t('installer.stage_connecting_db');
            } else if (installProgressPct.value >= 50 && installProgressPct.value < 70) {
                currentStageMessage.value = t('installer.stage_running_migrations');
            } else if (installProgressPct.value >= 70 && installProgressPct.value < 85) {
                currentStageMessage.value = t('installer.stage_seeding_roles');
            } else if (installProgressPct.value >= 85) {
                currentStageMessage.value = t('installer.stage_saving_settings');
            }
        }
    }, 450);

    try {
        const res = await axios.post('/api/install/run', form.value);
        clearInterval(progressInterval);
        installProgressPct.value = 100;
        currentStageMessage.value = t('installer.stage_locking_installer');
        installedResult.value = res.data;

        setTimeout(() => {
            currentStep.value = 10;
        }, 800);
    } catch (e) {
        clearInterval(progressInterval);
        installError.value = e.response?.data?.message || 'An unexpected error occurred during installation. Please check server logs.';
    }
}

function goToStep(step) {
    if (step === 2 && requirements.value.length === 0) {
        fetchRequirements();
    }
    if (step === 3 && permissions.value.length === 0) {
        fetchPermissions();
    }
    currentStep.value = step;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function handleStepClick(step) {
    // Only allow clicking to previously completed steps or current
    if (step < currentStep.value) {
        goToStep(step);
    }
}

// Initial status check
onMounted(async () => {
    try {
        const res = await axios.get('/api/install/status');
        if (res.data.installed) {
            isAlreadyInstalled.value = true;
        } else {
            fetchRequirements();
        }
    } catch (e) {
        fetchRequirements();
    }
});
</script>

<style scoped>
.installer-wrapper {
    min-height: 100vh;
    background-color: #f8fafc;
    font-family: inherit;
}

.installer-header {
    background-color: #ffffff;
}

.brand-icon-box {
    width: 44px;
    height: 44px;
}

.stepper-item .stepper-link {
    cursor: default;
    transition: all 0.2s ease;
    color: #64748b;
}

.stepper-item.completed .stepper-link {
    cursor: pointer;
    color: #0f172a;
}

.stepper-item.completed .stepper-link:hover {
    background-color: #f1f5f9;
}

.stepper-item.active .stepper-link {
    background-color: #eff6ff;
    color: #2563eb;
    font-weight: 600;
}

.step-badge {
    width: 28px;
    height: 28px;
    background-color: #e2e8f0;
    color: #64748b;
    font-size: 12px;
    font-weight: 700;
    flex-shrink: 0;
}

.stepper-item.completed .step-badge {
    background-color: #16a34a;
    color: #ffffff;
}

.stepper-item.active .step-badge {
    background-color: #2563eb;
    color: #ffffff;
}

.welcome-icon-circle {
    width: 80px;
    height: 80px;
}

.celebration-badge {
    width: 100px;
    height: 100px;
}

.spin-icon {
    animation: spin 1.2s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
