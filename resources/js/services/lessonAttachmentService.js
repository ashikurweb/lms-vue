import api from './api';

export const lessonAttachmentService = {
    // ==================== LESSON RESOURCES ====================

    async getResources(lessonId) {
        const response = await api.get(`/admin/lessons/${lessonId}/resources`);
        return response.data;
    },

    async uploadResource(lessonId, formData) {
        const response = await api.post(`/admin/lessons/${lessonId}/resources`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        return response.data;
    },

    async updateResource(resourceId, data) {
        const response = await api.put(`/admin/lesson-resources/${resourceId}`, data);
        return response.data;
    },

    async deleteResource(resourceId) {
        const response = await api.delete(`/admin/lesson-resources/${resourceId}`);
        return response.data;
    },

    // ==================== VIDEO TRACKS ====================

    async getTracks(lessonId) {
        const response = await api.get(`/admin/lessons/${lessonId}/tracks`);
        return response.data;
    },

    async uploadTrack(lessonId, formData) {
        const response = await api.post(`/admin/lessons/${lessonId}/tracks`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        return response.data;
    },

    async updateTrack(trackId, data) {
        const response = await api.put(`/admin/video-tracks/${trackId}`, data);
        return response.data;
    },

    async deleteTrack(trackId) {
        const response = await api.delete(`/admin/video-tracks/${trackId}`);
        return response.data;
    },
};
