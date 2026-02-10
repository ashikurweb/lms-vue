import api from './api';

const API_URL = '/admin/profile';

export const adminProfileService = {
    async show() {
        const response = await api.get(API_URL);
        return response.data;
    },

    async update(data) {
        const response = await api.put(API_URL, data);
        return response.data;
    }
};
