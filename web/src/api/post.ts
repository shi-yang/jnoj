import axios from '@/utils/request';

export function getPost(id) {
  return axios.get(`/posts/${id}`);
}

export function createPost(data) {
  return axios.post(`/posts`, data);
}

export function listPosts(params) {
  return axios.get(`/posts`, { params });
}

export function updatePost(id, data) {
  return axios.put(`/posts/${id}`, data);
}
