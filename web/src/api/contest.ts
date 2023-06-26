import axios from '@/utils/request';

export function getContest(id) {
  return axios.get(`/contests/${id}`);
}

export function listContests(params) {
  return axios.get(`/contests`, { params });
}

export function createContest(data) {
  return axios.post(`/contests`, data);
}

export function updateContest(id, data) {
  return axios.put(`/contests/${id}`, data);
}

export function getContestStanding(id, params) {
  return axios.get(`/contests/${id}/standing`, { params });
}

export function listContestAllSubmissions(id, params=undefined) {
  return axios.get(`/contests/${id}/all_submissions`, {params});
}

export function listContestProblems(id) {
  return axios.get(`/contests/${id}/problems`);
}

export function createContestProblem(id, data) {
  return axios.post(`/contests/${id}/problems`, data);
}

export function deleteContestProblem(id, problemNumber) {
  return axios.delete(`/contests/${id}/problems/${problemNumber}`);
}

export function listContestProblemLanguages(id, problemNumber) {
  return axios.get(`/contests/${id}/problems/${problemNumber}/languages`);
}

export function getContestProblemLanguage(id, problemNumber, lang) {
  return axios.get(`/contests/${id}/problems/${problemNumber}/languages/${lang}`);
}

export function listContestStatuses(id) {
  return axios.get(`/contests/${id}/statuses`);
}

export function listContestUsers(id, params=undefined) {
  return axios.get(`/contests/${id}/users`, {params});
}

export function createContestUser(id, data = null) {
  return axios.post(`/contests/${id}/users`, data);
}

export function batchCreateContestUsers(id, data) {
  return axios.post(`/contests/${id}/batch_users`, data);
}

export function updateContestUser(id, data) {
  return axios.put(`/contests/${id}/users`, data);
}

export function exitVirtualContest(id) {
  return axios.post(`/contests/${id}/exit_virtual`, {});
}

export function getContestProblem(id, pkey) {
  return axios.get(`/contests/${id}/problems/${pkey}`);
}

export function listContestSubmissions(id, params) {
  return axios.get(`/contests/${id}/submissions`, {params});
}

export function calculateContestRating(id: number) {
  return axios.post(`/contests/${id}/calculate_rating`, {});
}
