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
                path: ':username/profile',
                name: 'user.profile',
                component: () => import('../views/frontend/user/Profile.vue'),
                meta: { auth: true }, // Requires login, but is a frontend route
                beforeEnter: (to, from, next) => {
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

                    // If user is admin, redirect to admin profile
                    if (isAdmin()) {
                        next({ name: 'admin.profile' });
                    } else {
                        next();
                    }
                }
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
            { 
                path: 'courses',
                children: [
                    { path: '', name: 'admin.courses.index', component: () => import('../views/admin/courses/Index.vue') },
                    { path: 'create', name: 'admin.courses.create', component: () => import('../views/admin/courses/Manage.vue') },
                    { path: ':slug/edit', name: 'admin.courses.edit', component: () => import('../views/admin/courses/Manage.vue') },
                    { 
                        path: ':slug/curriculum', 
                        children: [
                            { path: '', name: 'admin.courses.curriculum', component: () => import('../views/admin/courses/Curriculum.vue') },
                            { path: 'lesson/create', name: 'admin.courses.lesson.create', component: () => import('../views/admin/courses/LessonManage.vue') },
                            { path: 'lesson/:lessonId/edit', name: 'admin.courses.lesson.edit', component: () => import('../views/admin/courses/LessonManage.vue') },
                        ]
                    },
                ]
            },
            { path: 'lessons', name: 'lessons', component: () => import('../views/admin/lessons/Index.vue') },
            { path: 'categories', name: 'categories', component: () => import('../views/admin/categories/Index.vue') },
            { path: 'bundles', name: 'bundles', component: () => import('../views/admin/bundles/Index.vue') },
            { path: 'assignments', name: 'assignments', component: () => import('../views/admin/assignments/Index.vue') },
            { path: 'quizzes', name: 'quizzes', component: () => import('../views/admin/quizzes/Index.vue') },
            // Blog
            { path: 'blog/categories', name: 'blog-categories', component: () => import('../views/admin/blog-categories/Index.vue') },
            { path: 'blog/tags', name: 'blog-tags', component: () => import('../views/admin/blog-tags/Index.vue') },
            { path: 'blog/posts', name: 'blog-posts', component: () => import('../views/admin/blog-posts/Index.vue') },
            { path: 'blog/posts/create', name: 'blog-posts.create', component: () => import('../views/admin/blog-posts/Create.vue') },
            { path: 'blog/posts/:slug/edit', name: 'blog-posts.edit', component: () => import('../views/admin/blog-posts/Edit.vue') },
            // Users
            {
                path: 'student',
                children: [
                    { path: '', name: 'users.index', component: () => import('../views/admin/users/Index.vue') },
                    { path: ':uuid', name: 'users.show', component: () => import('../views/admin/users/Show.vue') },
                ]
            },
            {
                path: 'instructors',
                children: [
                    { path: '', name: 'instructors', component: () => import('../views/admin/instructors/Index.vue') },
                    { path: 'create', name: 'instructors.create', component: () => import('../views/admin/instructors/Create.vue') },
                    { path: ':id', name: 'instructors.show', component: () => import('../views/admin/instructors/Show.vue') },
                    { path: ':id/edit', name: 'instructors.edit', component: () => import('../views/admin/instructors/Edit.vue') },
                ]
            },
            { path: 'discussions', name: 'discussions', component: () => import('../views/admin/discussions/Index.vue') },
            { path: 'discussions/create', name: 'discussions.create', component: () => import('../views/admin/discussions/Create.vue') },
            { path: 'discussions/:uuid/edit', name: 'discussions.edit', component: () => import('../views/admin/discussions/Edit.vue') },
            { path: 'live-classes', name: 'live-classes', component: () => import('../views/admin/live-classes/Index.vue') },
            // Finance
            { path: 'orders', name: 'orders', component: () => import('../views/admin/orders/Index.vue') },
            { path: 'payouts', name: 'payouts', component: () => import('../views/admin/payouts/Index.vue') },
            { path: 'affiliates', name: 'affiliates', component: () => import('../views/admin/affiliates/Index.vue') },
            // Settings
            { path: 'settings', name: 'settings', component: () => import('../views/admin/settings/Index.vue') },
            { path: 'settings/general', name: 'settings.general', component: () => import('../views/admin/settings/General.vue') },
            { path: 'current', name: 'current', component: () => import('../views/admin/current/Index.vue') },
            { path: 'currencies', name: 'currencies', component: () => import('../views/admin/currencies/Index.vue') },
            { path: 'profile', name: 'admin.profile', component: () => import('../views/admin/profile/Index.vue') }
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
                next({ name: 'user.profile', params: { username: user.username } });
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
                next({ name: 'user.profile', params: { username: user.username } });
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