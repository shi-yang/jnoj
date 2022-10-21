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
