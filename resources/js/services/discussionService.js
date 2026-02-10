import api from './api';

const API_URL = '/admin/discussions';

export const discussionService = {
    async index(params = {}) {
        const response = await api.get(API_URL, { params });
        return response.data;
    },

    async getAll() {
        const response = await api.get(`${API_URL}/all`);
        return response.data;
    },

    async show(uuid) {
        const response = await api.get(`${API_URL}/${uuid}`);
        return response.data;
    },

    async store(data) {
        const response = await api.post(API_URL, data);
        return response.data;
    },

    async update(uuid, data) {
        const response = await api.put(`${API_URL}/${uuid}`, data);
        return response.data;
    },

    async destroy(uuid) {
        const response = await api.delete(`${API_URL}/${uuid}`);
        return response.data;
    },

    async toggleFeatured(uuid) {
        const response = await api.patch(`${API_URL}/${uuid}/toggle-featured`);
        return response.data;
    },

    async togglePinned(uuid) {
        const response = await api.patch(`${API_URL}/${uuid}/toggle-pinned`);
        return response.data;
    },

    async toggleStatus(uuid) {
        const response = await api.patch(`${API_URL}/${uuid}/toggle-status`);
        return response.data;
    }
};
