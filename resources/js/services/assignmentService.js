import api from './api';

const API_URL = '/admin/assignments';

export const assignmentService = {
    async index(params = {}) {
        const response = await api.get(API_URL, { params });
        return response.data;
    },

    async byCourse(courseId) {
        const response = await api.get(`/admin/courses/${courseId}/assignments`);
        return response.data;
    },

    async show(id) {
        const response = await api.get(`${API_URL}/${id}`);
        return response.data;
    },

    async store(data) {
        const response = await api.post(API_URL, data);
        return response.data;
    },

    async update(id, data) {
        const response = await api.put(`${API_URL}/${id}`, data);
        return response.data;
    },

    async destroy(id) {
        const response = await api.delete(`${API_URL}/${id}`);
        return response.data;
    },

    async togglePublished(id) {
        const response = await api.patch(`${API_URL}/${id}/toggle-published`);
        return response.data;
    },

    async toggleRequired(id) {
        const response = await api.patch(`${API_URL}/${id}/toggle-required`);
        return response.data;
    },

    async reorder(assignments) {
        const response = await api.post(`${API_URL}/reorder`, { assignments });
        return response.data;
    },

    async getStatistics(id) {
        const response = await api.get(`${API_URL}/${id}/statistics`);
        return response.data;
    },

    async getSubmissions(id, params = {}) {
        const response = await api.get(`${API_URL}/${id}/submissions`, { params });
        return response.data;
    },

    async gradeSubmission(assignmentId, submissionId, data) {
        const response = await api.post(`${API_URL}/${assignmentId}/submissions/${submissionId}/grade`, data);
        return response.data;
    },

    async requestResubmission(assignmentId, submissionId, data) {
        const response = await api.post(`${API_URL}/${assignmentId}/submissions/${submissionId}/request-resubmission`, data);
        return response.data;
    },

    async bulkGrade(assignmentId, submissions) {
        const response = await api.post(`${API_URL}/${assignmentId}/bulk-grade`, { submissions });
        return response.data;
    },

    async exportSubmissions(id) {
        const response = await api.get(`${API_URL}/${id}/export`);
        return response.data;
    }
};
