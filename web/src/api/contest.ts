import axios from '@/utils/request';

export function getContest(id) {
  return axios.get(`/contests/${id}`)
}

export function listContestStandings(id) {
  return axios.get(`/contests/${id}/standings`)
}

export function listContestProblems(id) {
  return axios.get(`/contests/${id}/problems`)
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
