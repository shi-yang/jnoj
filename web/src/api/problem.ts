import axios from '@/utils/request';

export interface Problem {
  id: string;
  name: string;
  statements: Array<{
    id: number;
    name: string;
    legend: string;
    input: string;
    output: string;
    timeLimit: number;
    memoryLimit: number;
    notes: string;
  }>
}

export function listProblems(params) {
  return axios.get(`/problems`, params)
}

export function getProblem(id) {
  return axios.get<Problem>(`/problems/${id}`)
}

export function updateProblem(id: number, data) {
  return axios.put(`/problems/${id}`, data)
}

export function createProblem(data) {
  return axios.post(`/problems`, data)
}
