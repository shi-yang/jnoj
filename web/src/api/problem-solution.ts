import axios from '@/utils/request';

export function ListProblemSolutions(id: number) {
  return axios.get(`/problems/${id}/solutions`)
}

export function CreateProblemSolution(id: number, data) {
  return axios.post(`/problems/${id}/solutions`, data)
}

export function getProblemSolution(id: number) {
}

export function deleteProblemSolution(id: number) {
}

export function updateProblemSolution(id: number) {
}
