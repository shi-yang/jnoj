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

export function batchCreateUser(data) {
  return axios.post('/batch_users', data);
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

export function listUserBadges(params: any) {
  return axios.get(`/user_badges`, {params});
}

export function getUserBadge(id: number) {
  return axios.get(`/user_badges/${id}`);
}

export function createUserBadge(data) {
  return axios.post(`/user_badges`, data, {
    headers: {
      'Content-Type': 'multipart/form-data;charset=UTF-8'
    }
  });
}

export function updateUserBadge(id, data) {
  return axios.put(`/user_badges/${id}`, data, {
    headers: {
      'Content-Type': 'multipart/form-data;charset=UTF-8'
    }
  });
}

export function deleteUserBadge(id: number) {
  return axios.delete(`/user_badges/${id}`);
}
