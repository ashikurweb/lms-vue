import api from './api';

const API_URL = '/admin/upload-image';

export const uploadService = {
    async uploadImage(file, folder = 'uploads/avatars') {
        const formData = new FormData();
        formData.append('image', file);
        formData.append('folder', folder);

        const response = await api.post(API_URL, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });

        return response.data;
    },

    async uploadFromUrl(url, folder = 'uploads/others') {
        const response = await api.post(API_URL, {
            url: url,
            folder: folder
        });

        return response.data;
    }
};
