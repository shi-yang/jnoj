import axios from '@/utils/requestAdmin';

export function listUsers(params) {
  return axios.get(`/users`, {params});
}

export function getUser(id) {
  return axios.get(`/users/${id}`, id);
}

export function createUser(data) {
  return axios.post('/users', data);
}

export function updateUser(id, data) {
  return axios.put(`/users/${id}`, data);
}
