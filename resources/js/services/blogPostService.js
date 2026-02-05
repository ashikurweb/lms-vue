import api from './api';

const API_URL = '/admin/blog-posts';

export const blogPostService = {
    async index(params = {}) {
        const response = await api.get(API_URL, { params });
        return response.data;
    },

    async show(slug) {
        const response = await api.get(`${API_URL}/${slug}`);
        return response.data;
    },

    async store(data) {
        const response = await api.post(API_URL, data);
        return response.data;
    },

    async update(slug, data) {
        const response = await api.put(`${API_URL}/${slug}`, data);
        return response.data;
    },

    async destroy(slug) {
        const response = await api.delete(`${API_URL}/${slug}`);
        return response.data;
    }
};
