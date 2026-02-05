import axios from 'axios';

const api = axios.create({
    baseURL: '/api'
});

export const publicBlogService = {
    async getPosts(params = {}) {
        const response = await api.get('/blog/posts', { params });
        return response.data;
    },

    async getFeaturedPosts() {
        const response = await api.get('/blog/posts/featured');
        return response.data;
    },

    async getPost(slug) {
        const response = await api.get(`/blog/posts/${slug}`);
        return response.data;
    },

    async getCategories() {
        const response = await api.get('/blog/categories');
        return response.data;
    }
};
