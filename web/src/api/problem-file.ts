import axios from '@/utils/request';

export function ListProblemFiles(id: number, params) {
  return axios.get(`/problems/${id}/files`, {
    params
  })
}

export function createProblemFile(id: number, data) {
  return axios.post(`/problems/${id}/files`, data)
}

export function getProblemFile(id: number, sid: number) {
  return axios.get(`/problems/${id}/files/${sid}`)
}

export function deleteProblemFile(id: number, sid: number) {
  return axios.delete(`/problems/${id}/files/${sid}`)
}

export function updateProblemFile(id: number, sid: number, data: any) {
  return axios.put(`/problems/${id}/files/${sid}`, data)
}

export function runProblemFile(id: number) {
  return axios.post(`/problem_files/${id}/run`, {})
}

export function listProblemStdCheckers(id: number) {
  return axios.get(`/problems/${id}/std_checkers`)
}