import axios from '@/utils/request';

// 题单列表
export function listProblemsets(params) {
  return axios.get(`/problemsets`, {params});
}

// 获取题单
export function getProblemset(id) {
  return axios.get(`/problemsets/${id}`);
}

// 创建题单
export function createProblemset(data) {
  return axios.post(`/problemsets`, data);
}

// 修改题单
export function updateProblemset(id, data) {
  return axios.put(`/problemsets/${id}`, data);
}

// 删除题单
export function deleteProblemset(id) {
  return axios.delete(`/problemsets/${id}`);
}

// 获取题单的题目
export function listProblemsetProblems(id, params) {
  return axios.get(`/problemsets/${id}/problems`, {params});
}

export function getProblemsetProblem(id, order) {
  return axios.get(`/problemsets/${id}/problems/${order}`);
}

// 添加题目到题单
export function addProblemToProblemset(id, data) {
  return axios.post(`/problemsets/${id}/problems`, data);
}

// 从题单中删除题目
export function deleteProblemFromProblemset(id, problemId) {
  return axios.delete(`/problemsets/${id}/problems/${problemId}`);
}

// 题单列表题目排序
export function sortProblemsetProblems(id, pids) {
  return axios.post(`/problemsets/${id}/problem/sort`, pids);
}