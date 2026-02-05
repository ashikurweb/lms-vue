import { createRouter, createWebHistory } from 'vue-router';

const routes = [
    {
        path: '/',
        component: () => import('../layouts/frontend/FrontendLayout.vue'),
        children: [
            {
                path: '',
                name: 'home',
                component: () => import('../views/frontend/home/Index.vue')
            },
            {
                path: 'courses',
                name: 'frontend.courses',
                component: () => import('../views/frontend/courses/Index.vue')
            },
            {
                path: 'instructors',
                name: 'frontend.instructors',
                component: () => import('../views/frontend/instructors/Index.vue')
            },
            {
                path: 'blog',
                name: 'frontend.blog',
                component: () => import('../views/frontend/blog/Index.vue')
            },
            {
                path: 'blog/:slug',
                name: 'frontend.blog.show',
                component: () => import('../views/frontend/blog/Show.vue')
            },
            {
                path: 'login',
                name: 'login',
                component: () => import('../views/frontend/auth/Login.vue'),
                meta: { guest: true }
            },
            {
                path: 'register',
                name: 'register',
                component: () => import('../views/frontend/auth/Register.vue'),
                meta: { guest: true }
            },
            {
                path: 'forgot-password',
                name: 'forgot-password',
                component: () => import('../views/frontend/auth/ForgotPassword.vue'),
                meta: { guest: true }
            },
            {
                path: 'reset-password',
                name: 'reset-password',
                component: () => import('../views/frontend/auth/ResetPassword.vue'),
                meta: { guest: true }
            },
            {
                path: 'verify-email',
                name: 'verify-email',
                component: () => import('../views/frontend/auth/VerifyEmail.vue'),
                meta: { auth: true }
            },
            {
                path: 'my-account',
                name: 'profile',
                component: () => import('../views/frontend/user/Profile.vue'),
                meta: { auth: true } // Requires login, but is a frontend route
            }
        ]
    },
    {
        path: '/admin',
        component: () => import('../layouts/DashboardLayout.vue'),
        meta: { auth: true, admin: true }, // Add admin meta requirement
        children: [
            { path: 'dashboard', name: 'dashboard', component: () => import('../views/admin/dashboard/Index.vue') },
            // Learning
            { path: 'courses', name: 'courses', component: () => import('../views/admin/courses/Index.vue') },
            { path: 'categories', name: 'categories', component: () => import('../views/admin/categories/Index.vue') },
            { path: 'bundles', name: 'bundles', component: () => import('../views/admin/bundles/Index.vue') },
            { path: 'assignments', name: 'assignments', component: () => import('../views/admin/assignments/Index.vue') },
            { path: 'quizzes', name: 'quizzes', component: () => import('../views/admin/quizzes/Index.vue') },
            // Users
            { path: 'users', name: 'users', component: () => import('../views/admin/users/Index.vue') },
            { path: 'instructors', name: 'instructors', component: () => import('../views/admin/instructors/Index.vue') },
            { path: 'discussions', name: 'discussions', component: () => import('../views/admin/discussions/Index.vue') },
            { path: 'live-classes', name: 'live-classes', component: () => import('../views/admin/live-classes/Index.vue') },
            // Finance
            { path: 'orders', name: 'orders', component: () => import('../views/admin/orders/Index.vue') },
            { path: 'payouts', name: 'payouts', component: () => import('../views/admin/payouts/Index.vue') },
            { path: 'affiliates', name: 'affiliates', component: () => import('../views/admin/affiliates/Index.vue') },
            // Settings
            { path: 'settings', name: 'settings', component: () => import('../views/admin/settings/Index.vue') }
        ]
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Navigation Guard
router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('auth_token');
    const user = JSON.parse(localStorage.getItem('auth_user') || 'null');

    // Helper to check for admin role
    const isAdmin = () => {
        if (!user || !user.roles) return false;

        return user.roles.some((role) => {
            // If backend sends roles as strings: ["user", "super-admin"]
            if (typeof role === 'string') {
                return ['admin', 'super-admin'].includes(role);
            }

            // Fallback if roles are objects: [{ name: "super-admin" }]
            return ['admin', 'super-admin'].includes(role.name);
        });
    };

    // 1. Route Requires Authentication
    if (to.matched.some(record => record.meta.auth)) {
        if (!token) {
            next({ name: 'login' });
        }
        // 2. Route Requires Admin Privileges (e.g., /admin/*)
        else if (to.matched.some(record => record.meta.admin)) {
            if (isAdmin()) {
                next();
            } else {
                // User is logged in but NOT admin -> Send to frontend User Profile
                next({ name: 'profile' });
            }
        }
        else {
            next();
        }
    }
    // 3. Guest Routes (Login/Register) - Redirect logged-in users
    else if (to.matched.some(record => record.meta.guest)) {
        if (token) {
            // Redirect based on role
            if (isAdmin()) {
                next({ name: 'dashboard' });
            } else {
                next({ name: 'profile' });
            }
        } else {
            next();
        }
    }
    else {
        next();
    }
});

export default router;