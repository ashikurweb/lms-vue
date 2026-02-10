import api from './api';

const API_URL = '/admin/courses';

export const courseService = {
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
    },

    async toggleFeatured(slug) {
        const response = await api.patch(`${API_URL}/${slug}/toggle-featured`);
        return response.data;
    },

    async toggleStatus(slug) {
        const response = await api.patch(`${API_URL}/${slug}/toggle-status`);
        return response.data;
    },

    // Curriculum
    async getCurriculum(slug) {
        const response = await api.get(`${API_URL}/${slug}/curriculum`);
        return response.data;
    },

    async storeSection(slug, data) {
        const response = await api.post(`${API_URL}/${slug}/sections`, data);
        return response.data;
    },

    async updateSection(sectionId, data) {
        const response = await api.put(`/admin/sections/${sectionId}`, data);
        return response.data;
    },

    async destroySection(sectionId) {
        const response = await api.delete(`/admin/sections/${sectionId}`);
        return response.data;
    },

    async storeLesson(sectionId, data) {
        const response = await api.post(`/admin/sections/${sectionId}/lessons`, data);
        return response.data;
    },

    async updateLesson(lessonId, data) {
        const response = await api.put(`/admin/lessons/${lessonId}`, data);
        return response.data;
    },

    async destroyLesson(lessonId) {
        const response = await api.delete(`/admin/lessons/${lessonId}`);
        return response.data;
    },

    async getLesson(lessonId) {
        const response = await api.get(`/admin/lessons/${lessonId}`);
        return response.data.data;
    }
};
