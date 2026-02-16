import api from './api';

const API_URL = '/courses/progress';

export const courseProgressService = {
    async save(data) {
        const response = await api.post(API_URL, data);
        return response.data;
    },

    async getLessonProgress(lessonId) {
        const response = await api.get(`${API_URL}/${lessonId}`);
        return response.data;
    }
};
