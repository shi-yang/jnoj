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

export function uploadPostImage(data) {
  return axios.post(`/post/upload_image`, data, {
    headers: {
      'Content-Type': 'multipart/form-data;charset=UTF-8'
    }
  });
}
