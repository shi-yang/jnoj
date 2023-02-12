import axios from '@/utils/request';

export interface Problem {
  id: number;
  name: string;
  type: string;
  statements: Array<{
    id: number;
    name: string;
    legend: string;
    input: string;
    output: string;
    language: string;
    timeLimit: number;
    memoryLimit: number;
    notes: string;
  }>
  sampleTests: Array<{
    input: string;
    output: string;
  }>
}

export function listProblems(params) {
  return axios.get(`/problems`, {params})
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

export function updateProblemChecker(id, data) {
  return axios.put(`/problems/${id}/checkers`, data)
}

export function verifyProblem(id) {
  return axios.post(`/problems/${id}/verify`, {})
}

export function getProblemVerification(id) {
  return axios.get(`/problems/${id}/verification`)
}

export function packProblem(id) {
  return axios.post(`/problems/${id}/pack`, {})
}

export function downloadProblems(ids:number[]) {
  return axios.post(`/download_problems`, { ids: ids })
}
