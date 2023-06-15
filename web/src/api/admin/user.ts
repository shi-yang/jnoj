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

export function listUserExpirations(params: any) {
  return axios.get(`/user_expirations`, {params});
}

export function createUserExpiration(data) {
  return axios.post(`/user_expirations`, data);
}

export function deleteUserExpiration(id: number) {
  return axios.delete(`/user_expirations/${id}`);
}
