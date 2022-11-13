import axios from '@/utils/request';

export function ListProblemSolutions(id: number) {
  return axios.get(`/problems/${id}/solutions`)
}

export function createProblemSolution(id: number, data) {
  return axios.post(`/problems/${id}/solutions`, data)
}

export function getProblemSolution(id: number, sid: number) {
  return axios.get(`/problems/${id}/solutions/${sid}`)
}

export function deleteProblemSolution(id: number, sid: number) {
  return axios.delete(`/problems/${id}/solutions/${sid}`)
}

export function updateProblemSolution(id: number, sid: number, data: any) {
  return axios.put(`/problems/${id}/solutions/${sid}`, data)
}

export function runProblemSolution(id: number) {
  return axios.post(`/problem_solutions/${id}/run`, {})
}