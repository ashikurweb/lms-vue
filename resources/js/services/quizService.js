import api from './api';

export const quizService = {
  // Admin Endpoints
  index(params) {
    return api.get('/admin/quizzes', { params });
  },

  show(id) {
    return api.get(`/admin/quizzes/${id}`);
  },

  store(data) {
    return api.post('/admin/quizzes', data);
  },

  update(id, data) {
    return api.put(`/admin/quizzes/${id}`, data);
  },

  destroy(id) {
    return api.delete(`/admin/quizzes/${id}`);
  },

  togglePublished(id) {
    return api.patch(`/admin/quizzes/${id}/toggle-published`);
  },

  toggleRequired(id) {
    return api.patch(`/admin/quizzes/${id}/toggle-required`);
  },

  statistics(id) {
    return api.get(`/admin/quizzes/${id}/statistics`);
  },

  attempts(id, params) {
    return api.get(`/admin/quizzes/${id}/attempts`, { params });
  },

  saveQuestion(quizId, questionId, data) {
    const url = questionId 
      ? `/admin/quizzes/${quizId}/questions/${questionId}`
      : `/admin/quizzes/${quizId}/questions`;
    return api.post(url, data);
  },

  deleteQuestion(quizId, questionId) {
    return api.delete(`/admin/quizzes/${quizId}/questions/${questionId}`);
  },

  bulkAction(data) {
    return api.post('/admin/quizzes/bulk-action', data);
  },

  // Student Endpoints
  availableQuizzes(params) {
    return api.get('/student/quizzes', { params });
  },

  myQuizzes(params) {
    return api.get('/student/quizzes/my-quizzes', { params });
  },

  startAttempt(quizId) {
    return api.post(`/student/quizzes/${quizId}/start`);
  },

  getAttempt(attemptId) {
    return api.get(`/student/quizzes/attempts/${attemptId}`);
  },

  saveAnswer(attemptId, questionId, data) {
    return api.post(`/student/quizzes/attempts/${attemptId}/questions/${questionId}/answer`, data);
  },

  submitAttempt(attemptId) {
    return api.post(`/student/quizzes/attempts/${attemptId}/submit`);
  }
};
