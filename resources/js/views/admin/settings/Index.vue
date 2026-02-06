<template>
  <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-black theme-text-main tracking-tight">Settings</h1>
        <p class="text-sm theme-text-muted mt-1 font-medium">Manage your application settings and configurations.</p>
      </div>
      <div class="flex items-center gap-3">
        <div class="relative group">
          <input 
            v-model="search"
            type="text" 
            placeholder="Search settings..." 
            class="pl-10 pr-4 py-3 theme-bg-card border theme-border rounded-2xl text-sm font-bold theme-text-main outline-none focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10 transition-all w-64"
            @input="debounceSearch"
          >
          <svg class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 theme-text-dim group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
        </div>
      </div>
    </div>

    <!-- Settings Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div 
        v-for="setting in settingsCategories" 
        :key="setting.id"
        @click="handleSettingClick(setting)"
        class="group p-8 rounded-[2.5rem] theme-bg-card border theme-border hover:border-indigo-500/50 transition-all duration-500 relative overflow-hidden shadow-sm hover:shadow-xl cursor-pointer"
      >
        <div class="absolute top-0 right-0 w-32 h-32 opacity-10 rounded-full blur-3xl -mr-16 -mt-16 group-hover:opacity-20 transition-opacity" :style="{ backgroundColor: setting.color }"></div>
        <div class="relative z-10 flex flex-col gap-6">
          <div class="w-14 h-14 rounded-2xl flex items-center justify-center shadow-inner transition-transform duration-500 group-hover:rotate-12" :style="{ backgroundColor: setting.color + '20' }">
            <div v-html="setting.icon" class="w-7 h-7" :style="{ color: setting.color }"></div>
          </div>
          <div>
            <h3 class="text-xl font-black theme-text-main group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors leading-tight mb-2">
              {{ setting.title }}
            </h3>
            <p class="text-sm theme-text-muted font-medium leading-relaxed">
              {{ setting.description }}
            </p>
          </div>
          <div class="flex items-center justify-between pt-2">
            <span class="text-[11px] font-bold theme-text-dim uppercase tracking-widest">
              {{ setting.count }} {{ setting.count === 1 ? 'item' : 'items' }}
            </span>
            <div class="w-8 h-8 rounded-xl theme-bg-element border theme-border flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white group-hover:border-indigo-600 transition-all">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Settings Table Modal -->
    <transition name="panel">
      <div v-if="showTableModal" class="fixed inset-0 z-100 overflow-hidden">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="closeTableModal"></div>
        
        <div class="absolute inset-x-4 top-1/2 -translate-y-1/2 max-w-6xl mx-auto theme-bg-card border theme-border shadow-2xl rounded-[2.5rem] max-h-[80vh] flex flex-col">
          <!-- Modal Header -->
          <div class="p-8 border-b theme-border flex items-center justify-between">
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 rounded-2xl flex items-center justify-center" :style="{ backgroundColor: selectedSettings?.color + '20' }">
                <div v-html="selectedSettings?.icon" class="w-6 h-6" :style="{ color: selectedSettings?.color }"></div>
              </div>
              <div>
                <h2 class="text-2xl font-black theme-text-main tracking-tight">{{ selectedSettings?.title }}</h2>
                <p class="text-sm theme-text-dim font-medium">{{ selectedSettings?.description }}</p>
              </div>
            </div>
            <button @click="closeTableModal" class="p-2 theme-text-muted hover:theme-text-main transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>

          <!-- Table Content -->
          <div class="flex-1 overflow-hidden">
            <!-- Loading State -->
            <div v-if="tableLoading" class="flex items-center justify-center h-64">
              <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
            </div>

            <!-- Table -->
            <div v-else class="h-full overflow-y-auto custom-scrollbar">
              <!-- Column Headers -->
              <div class="grid grid-cols-12 gap-4 px-8 py-4 theme-table-header sticky top-0 z-10">
                <div class="col-span-1 text-[10px] font-black theme-table-header-text uppercase tracking-[0.2em]">SL</div>
                <div class="col-span-3 text-[10px] font-black theme-table-header-text uppercase tracking-[0.2em]">Name</div>
                <div class="col-span-4 text-[10px] font-black theme-table-header-text uppercase tracking-[0.2em]">Value</div>
                <div class="col-span-2 text-[10px] font-black theme-table-header-text uppercase tracking-[0.2em]">Type</div>
                <div class="col-span-2 text-[10px] font-black theme-table-header-text uppercase tracking-[0.2em] text-right">Actions</div>
              </div>

              <!-- Table Rows -->
              <div class="px-8 pb-8 space-y-3">
                <div 
                  v-for="(item, index) in tableData" 
                  :key="item.id" 
                  class="grid grid-cols-12 gap-4 items-center py-4 theme-bg-element border theme-border rounded-[1.5rem] transition-colors duration-300 group"
                >
                  <!-- SL Column -->
                  <div class="col-span-1">
                    <div class="w-9 h-9 rounded-xl theme-bg-card border theme-border flex items-center justify-center text-[11px] font-black theme-text-dim transition-all">
                      {{ index + 1 }}
                    </div>
                  </div>

                  <!-- Name Column -->
                  <div class="col-span-3">
                    <div class="flex flex-col">
                      <span class="text-sm font-black theme-text-main tracking-tight transition-colors duration-300">{{ item.name }}</span>
                      <span class="text-[9px] theme-text-dim font-bold uppercase tracking-widest opacity-70">{{ item.key }}</span>
                    </div>
                  </div>

                  <!-- Value Column -->
                  <div class="col-span-4">
                    <div class="flex items-center gap-3">
                      <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium theme-text-main truncate" :title="item.value">
                          {{ truncateText(item.value, 40) }}
                        </p>
                      </div>
                      <button 
                        @click="copyToClipboard(item.value)"
                        class="w-8 h-8 rounded-xl theme-bg-card border theme-border flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all hover:bg-indigo-600 hover:text-white hover:border-indigo-600"
                        title="Copy value"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                      </button>
                    </div>
                  </div>

                  <!-- Type Column -->
                  <div class="col-span-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest" :class="getTypeStyle(item.type)">
                      {{ item.type }}
                    </span>
                  </div>

                  <!-- Actions Column -->
                  <div class="col-span-2 text-right">
                    <div class="flex items-center justify-end gap-2">
                      <button 
                        @click="editSetting(item)"
                        class="w-9 h-9 flex items-center justify-center rounded-xl text-indigo-500 hover:bg-indigo-500/10 transition-all active:scale-90"
                        title="Edit Setting"
                      >
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                      </button>
                      <button 
                        @click="deleteSetting(item)"
                        class="w-9 h-9 flex items-center justify-center rounded-xl text-rose-500 hover:bg-rose-500/10 transition-all active:scale-90"
                        title="Delete Setting"
                      >
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="p-6 border-t theme-border">
            <div class="flex items-center justify-between">
              <button 
                @click="addNewSetting"
                class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-2xl font-bold flex items-center justify-center gap-2 shadow-lg shadow-indigo-600/20 active:scale-95 transition-all"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Add New Setting</span>
              </button>
              <button 
                @click="closeTableModal"
                class="px-6 py-3 theme-bg-card border theme-border rounded-2xl font-bold hover:border-indigo-500/50 transition-all"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from '../../../composables/useToast';
import { truncateText } from '../../../utils/helpers';

const router = useRouter();
const toast = useToast();
const search = ref('');
const showTableModal = ref(false);
const selectedSettings = ref(null);
const tableLoading = ref(false);
const tableData = ref([]);

const settingsCategories = ref([
  {
    id: 1,
    title: 'General Settings',
    description: 'Basic application configuration and preferences',
    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
    color: '#6366f1',
    count: 12
  },
  {
    id: 2,
    title: 'Email Configuration',
    description: 'SMTP settings and email templates',
    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>',
    color: '#10b981',
    count: 8
  },
  {
    id: 7,
    title: 'Currency',
    description: 'Currency settings and exchange rates',
    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
    color: '#22c55e',
    count: 4
  },
  {
    id: 0,
    title: 'Current',
    description: 'Current system status and active sessions',
    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
    color: '#f97316',
    count: 5
  },
  {
    id: 3,
    title: 'Security Settings',
    description: 'Authentication, authorization and security policies',
    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>',
    color: '#f59e0b',
    count: 15
  },
  {
    id: 4,
    title: 'Payment Gateway',
    description: 'Payment processors and billing settings',
    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>',
    color: '#8b5cf6',
    count: 6
  },
  {
    id: 5,
    title: 'API Configuration',
    description: 'External API keys and integration settings',
    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>',
    color: '#ef4444',
    count: 10
  },
  {
    id: 6,
    title: 'Storage Settings',
    description: 'File storage and media configuration',
    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>',
    color: '#06b6d4',
    count: 7
  }
]);

const mockTableData = {
  7: [
    { id: 1, name: 'Default Currency', key: 'default_currency', value: 'USD', type: 'string' },
    { id: 2, name: 'Currency Symbol', key: 'currency_symbol', value: '$', type: 'string' },
    { id: 3, name: 'Exchange Rate Update', key: 'exchange_rate_update', value: 'daily', type: 'string' },
    { id: 4, name: 'Supported Currencies', key: 'supported_currencies', value: 'USD, EUR, GBP, BDT', type: 'string' }
  ],
  0: [
    { id: 1, name: 'System Status', key: 'system_status', value: 'Online', type: 'string' },
    { id: 2, name: 'Active Users', key: 'active_users', value: '127', type: 'integer' },
    { id: 3, name: 'Server Uptime', key: 'server_uptime', value: '15 days 7 hours', type: 'string' },
    { id: 4, name: 'Memory Usage', key: 'memory_usage', value: '68%', type: 'string' },
    { id: 5, name: 'Last Backup', key: 'last_backup', value: '2024-02-06 10:30:00', type: 'string' }
  ],
  1: [
    { id: 1, name: 'Application Name', key: 'app_name', value: 'JWT Authentication System', type: 'string' },
    { id: 2, name: 'Default Language', key: 'default_language', value: 'en', type: 'string' },
    { id: 3, name: 'Timezone', key: 'timezone', value: 'UTC', type: 'string' },
    { id: 4, name: 'Maintenance Mode', key: 'maintenance_mode', value: 'false', type: 'boolean' },
    { id: 5, name: 'Debug Mode', key: 'debug_mode', value: 'true', type: 'boolean' },
    { id: 6, name: 'Max Upload Size', key: 'max_upload_size', value: '10240', type: 'integer' },
    { id: 7, name: 'Session Lifetime', key: 'session_lifetime', value: '120', type: 'integer' },
    { id: 8, name: 'Password Min Length', key: 'password_min_length', value: '8', type: 'integer' },
    { id: 9, name: 'Enable Registration', key: 'enable_registration', value: 'true', type: 'boolean' },
    { id: 10, name: 'Default Role', key: 'default_role', value: 'user', type: 'string' },
    { id: 11, name: 'Email Verification', key: 'email_verification', value: 'true', type: 'boolean' },
    { id: 12, name: 'Two Factor Auth', key: 'two_factor_auth', value: 'false', type: 'boolean' }
  ],
  2: [
    { id: 1, name: 'SMTP Host', key: 'smtp_host', value: 'smtp.gmail.com', type: 'string' },
    { id: 2, name: 'SMTP Port', key: 'smtp_port', value: '587', type: 'integer' },
    { id: 3, name: 'SMTP Username', key: 'smtp_username', value: 'noreply@example.com', type: 'string' },
    { id: 4, name: 'SMTP Password', key: 'smtp_password', value: '********', type: 'password' },
    { id: 5, name: 'Email From Address', key: 'email_from', value: 'noreply@example.com', type: 'string' },
    { id: 6, name: 'Email From Name', key: 'email_from_name', value: 'Application', type: 'string' },
    { id: 7, name: 'Queue Email', key: 'queue_email', value: 'true', type: 'boolean' },
    { id: 8, name: 'Email Encryption', key: 'email_encryption', value: 'tls', type: 'string' }
  ],
  3: [
    { id: 1, name: 'Session Secure', key: 'session_secure', value: 'true', type: 'boolean' },
    { id: 2, name: 'CSRF Protection', key: 'csrf_protection', value: 'true', type: 'boolean' },
    { id: 3, name: 'XSS Protection', key: 'xss_protection', value: 'true', type: 'boolean' },
    { id: 4, name: 'Rate Limiting', key: 'rate_limiting', value: 'true', type: 'boolean' },
    { id: 5, name: 'Max Login Attempts', key: 'max_login_attempts', value: '5', type: 'integer' },
    { id: 6, name: 'Lockout Duration', key: 'lockout_duration', value: '300', type: 'integer' },
    { id: 7, name: 'Password Expiry', key: 'password_expiry', value: '90', type: 'integer' },
    { id: 8, name: 'Force HTTPS', key: 'force_https', value: 'true', type: 'boolean' },
    { id: 9, name: 'Allowed IPs', key: 'allowed_ips', value: '*', type: 'string' },
    { id: 10, name: 'API Rate Limit', key: 'api_rate_limit', value: '1000', type: 'integer' },
    { id: 11, name: 'JWT Secret', key: 'jwt_secret', value: '********', type: 'password' },
    { id: 12, name: 'JWT Expiry', key: 'jwt_expiry', value: '3600', type: 'integer' },
    { id: 13, name: 'Refresh Token Expiry', key: 'refresh_token_expiry', value: '86400', type: 'integer' },
    { id: 14, name: 'Enable CORS', key: 'enable_cors', value: 'true', type: 'boolean' },
    { id: 15, name: 'CORS Origins', key: 'cors_origins', value: '*', type: 'string' }
  ],
  4: [
    { id: 1, name: 'Stripe Public Key', key: 'stripe_public_key', value: 'pk_test_...', type: 'string' },
    { id: 2, name: 'Stripe Secret Key', key: 'stripe_secret_key', value: '********', type: 'password' },
    { id: 3, name: 'Stripe Webhook Secret', key: 'stripe_webhook_secret', value: '********', type: 'password' },
    { id: 4, name: 'Currency', key: 'currency', value: 'USD', type: 'string' },
    { id: 5, name: 'Payment Mode', key: 'payment_mode', value: 'test', type: 'string' },
    { id: 6, name: 'Auto Invoice', key: 'auto_invoice', value: 'true', type: 'boolean' }
  ],
  5: [
    { id: 1, name: 'Google API Key', key: 'google_api_key', value: '********', type: 'password' },
    { id: 2, name: 'Facebook App ID', key: 'facebook_app_id', value: '123456789', type: 'string' },
    { id: 3, name: 'Twitter API Key', key: 'twitter_api_key', value: '********', type: 'password' },
    { id: 4, name: 'GitHub Client ID', key: 'github_client_id', value: '********', type: 'password' },
    { id: 5, name: 'LinkedIn Client ID', key: 'linkedin_client_id', value: '********', type: 'password' },
    { id: 6, name: 'Recaptcha Site Key', key: 'recaptcha_site_key', value: '6Le...', type: 'string' },
    { id: 7, name: 'Recaptcha Secret', key: 'recaptcha_secret', value: '********', type: 'password' },
    { id: 8, name: 'Analytics ID', key: 'analytics_id', value: 'GA-XXXXXXXX', type: 'string' },
    { id: 9, name: 'Sentry DSN', key: 'sentry_dsn', value: '********', type: 'password' },
    { id: 10, name: 'API Version', key: 'api_version', value: 'v1', type: 'string' }
  ],
  6: [
    { id: 1, name: 'Default Disk', key: 'default_disk', value: 'local', type: 'string' },
    { id: 2, name: 'AWS Access Key', key: 'aws_access_key', value: '********', type: 'password' },
    { id: 3, name: 'AWS Secret Key', key: 'aws_secret_key', value: '********', type: 'password' },
    { id: 4, name: 'AWS Region', key: 'aws_region', value: 'us-east-1', type: 'string' },
    { id: 5, name: 'AWS Bucket', key: 'aws_bucket', value: 'my-app-bucket', type: 'string' },
    { id: 6, name: 'Image Quality', key: 'image_quality', value: '85', type: 'integer' },
    { id: 7, name: 'Max Image Size', key: 'max_image_size', value: '2048', type: 'integer' }
  ]
};

let debounceTimer;
const debounceSearch = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    console.log('Searching for:', search.value);
  }, 500);
};

const handleSettingClick = (setting) => {
  if (setting.id === 7) {
    // Navigate to currencies page
    router.push({ name: 'currencies' });
  } else {
    // Open modal for other settings
    openSettingsTable(setting);
  }
};

const openSettingsTable = async (setting) => {
  selectedSettings.value = setting;
  showTableModal.value = true;
  tableLoading.value = true;
  
  setTimeout(() => {
    tableData.value = mockTableData[setting.id] || [];
    tableLoading.value = false;
  }, 500);
};

const closeTableModal = () => {
  showTableModal.value = false;
  selectedSettings.value = null;
  tableData.value = [];
};

const getTypeStyle = (type) => {
  const styles = {
    string: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
    boolean: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
    integer: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
    password: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'
  };
  return styles[type] || styles.string;
};

const copyToClipboard = async (value) => {
  try {
    await navigator.clipboard.writeText(value);
    toast.success('Copied to clipboard');
  } catch (error) {
    toast.error('Failed to copy');
  }
};

const editSetting = (item) => {
  toast.info(`Editing ${item.name}`);
};

const deleteSetting = (item) => {
  toast.info(`Deleting ${item.name}`);
};

const addNewSetting = () => {
  toast.info('Adding new setting');
};

onMounted(() => {
  console.log('Settings page mounted');
});
</script>

<style scoped>
.panel-enter-active, .panel-leave-active {
  transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}
.panel-enter-from, .panel-leave-to {
  opacity: 0;
  transform: translate(-50%, -48%) scale(0.95);
}

.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(99, 102, 241, 0.2);
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(99, 102, 241, 0.4);
}
</style>
