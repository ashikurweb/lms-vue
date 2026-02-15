import api from './api';

const API_URL = '/courses';

export const publicCourseService = {
    async index(params = {}) {
        const response = await api.get(API_URL, { params });
        return response.data;
    },

    async featured(limit = 6) {
        const response = await api.get(`${API_URL}/featured`, { params: { limit } });
        return response.data;
    },

    async show(slug) {
        const response = await api.get(`${API_URL}/${slug}`);
        return response.data;
    },

    async categories() {
        const response = await api.get(`${API_URL}/categories`);
        return response.data;
    }
};
