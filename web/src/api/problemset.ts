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

// 添加子题单
export function createProblemsetChild(id, data) {
  return axios.post(`/problemsets/${id}/children`, data);
}

// 删除子题单
export function deleteProblemsetChild(id, childId) {
  return axios.delete(`/problemsets/${id}/children/${childId}`);
}

// 题单列表子题单排序
export function sortProblemsetChild(id, pids) {
  return axios.post(`/problemsets/${id}/child/sort`, pids);
}

// 获取题单的题目
export function listProblemsetProblems(id, params) {
  return axios.get(`/problemsets/${id}/problems`, {params});
}

export function getProblemsetProblem(id, order) {
  return axios.get(`/problemsets/${id}/problems/${order}`);
}

export function updateProblemsetProblem(id, pid, data) {
  return axios.put(`/problemsets/${id}/problems/${pid}`, data);
}

export function getProblemsetLateralProblem(id, order) {
  return axios.get(`/problemsets/${id}/problems/${order}/lateral`);
}

// 添加题目到题单
export function addProblemToProblemset(id, data) {
  return axios.post(`/problemsets/${id}/problems`, data);
}

// 上传Excel表，预览批量添加题目到题单的结果
export function batchAddProblemToProblemsetPreview(id, data) {
  return axios.post(`/problemsets/${id}/batch_problems_preview`, data);
}

// batchAddProblemToProblemset 批量添加题目到题单
export function batchAddProblemToProblemset(id, data) {
  return axios.post(`/problemsets/${id}/batch_problems`, data);
}

// 从题单中删除题目
export function deleteProblemFromProblemset(id, problemId) {
  return axios.delete(`/problemsets/${id}/problems/${problemId}`);
}

// 题单列表题目排序
export function sortProblemsetProblems(id, pids) {
  return axios.post(`/problemsets/${id}/problem/sort`, pids);
}

// 创建题单回答
export function createProblemsetAnswer(id) {
  return axios.post(`/problemsets/${id}/answers`, {});
}

// 获取题单回答列表
export function listProblemsetAnswers(id, params) {
  return axios.get(`/problemsets/${id}/answers`, {params});
}

// 获取某个题单回答
export function getProblemsetAnswer(id, answerId) {
  return axios.get(`/problemsets/${id}/answers/${answerId}`);
}

// 修改题单回答
export function updateProblemsetAnswer(id, answerId, data) {
  return axios.put(`/problemsets/${id}/answers/${answerId}`, data);
}

// 获取题单用户列表
export function listProblemsetUsers(id, params) {
  return axios.get(`/problemsets/${id}/users`, {params});
}

// 创建题单用户
export function createProblemsetUser(id, data) {
  return axios.post(`/problemsets/${id}/users`, data);
}

// 删除题单用户
export function deleteProblemsetUser(id, userId) {
  return axios.delete(`/problemsets/${id}/users/${userId}`);
}
