import axios from '@/utils/request';

export enum ContestRole {
  GUEST = 'GUEST',
  PLAYER = 'PLAYER',
  ADMIN = 'ADMIN',
};

export function getContest(id) {
  return axios.get(`/contests/${id}`)
}

export function listContests(params) {
  return axios.get(`/contests`, { params })
}

export function createContest(data) {
  return axios.post(`/contests`, data)
}

export function updateContest(id, data) {
  return axios.put(`/contests/${id}`, data)
}

export function listContestStandings(id) {
  return axios.get(`/contests/${id}/standings`)
}

export function listContestProblems(id) {
  return axios.get(`/contests/${id}/problems`)
}

export function createContestProblem(id, data) {
  return axios.post(`/contests/${id}/problems`, data)
}

export function deleteContestProblem(id, problemNumber) {
  return axios.delete(`/contests/${id}/problems/${problemNumber}`)
}

export function listContestStatuses(id) {
  return axios.get(`/contests/${id}/statuses`)
}

export function listContestUsers(id) {
  return axios.get(`/contests/${id}/users`)
}

export function getContestProblem(id, pkey) {
  return axios.get(`/contests/${id}/problems/${pkey}`)
}

export function listContestSubmissions(id, params) {
  return axios.get(`/contests/${id}/submissions`, params)
}
