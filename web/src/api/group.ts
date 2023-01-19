import axios from '@/utils/request';

export function getGroup(id) {
  return axios.get(`/groups/${id}`);
}

export function createGroup(data) {
  return axios.post(`/groups`, data);
}

export function listGroups(params) {
  return axios.get(`/groups`, { params });
}

export function updateGroup(id, data) {
  return axios.put(`/groups/${id}`, data);
}
