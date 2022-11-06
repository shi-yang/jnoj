import axios from '@/utils/request';

export function listProblemCheckers(id: number) {
  return axios.get(`/problems/${id}/checkers`)
}

export function createProblemChecker(id: number, data) {
  return axios.post(`/problems/${id}/checkers`, data)
}

export function updateProblemChecker(id: number, data) {
  return axios.put(`/problems/${id}/checkers`, data)
}
