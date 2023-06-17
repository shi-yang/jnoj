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

export function deleteGroup(id: number) {
  return axios.delete(`/groups/${id}`);
}

export function listGroupUsers(id, params) {
  return axios.get(`/groups/${id}/users`, { params });
}

export function createGroupUser(gid, data) {
  return axios.post(`/groups/${gid}/users`, data);
}

export function getGroupUser(gid, uid) {
  return axios.get(`/groups/${gid}/users/${uid}`);
}

export function updateGroupUser(gid, uid, data) {
  return axios.put(`/groups/${gid}/users/${uid}`, data);
}

export function deleteGroupUser(gid, uid) {
  return axios.delete(`/groups/${gid}/users/${uid}`);
}
