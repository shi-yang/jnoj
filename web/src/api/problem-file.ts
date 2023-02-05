import axios from '@/utils/request';

export function listProblemFiles(id: number, params=undefined) {
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

export function uploadProblemFile(id:number, data:FormData) {
  return axios.post(`/problems/${id}/upload_file`, data)
}

export function runProblemFile(id: number) {
  return axios.post(`/problem_files/${id}/run`, {})
}

export function listProblemStdCheckers(id: number) {
  return axios.get(`/problems/${id}/std_checkers`)
}

export function createProblemLanguage(id:number, data) {
  return axios.post(`/problems/${id}/languages`, data)
}

export function getProblemLanguage(problemId:number, id:number) {
  return axios.get(`/problems/${problemId}/languages/${id}`)
}

export function listProblemLanguages(problemId:number, params=undefined) {
  return axios.get(`/problems/${problemId}/languages`, {params})
}

export function deleteProblemLanguage(problemId:number, id:number) {
  return axios.delete(`/problems/${problemId}/languages/${id}`)
}

export function updateProblemLanguage(problemId:number, id:number, data) {
  return axios.put(`/problems/${problemId}/languages/${id}`, data)
}
