import axios from '@/utils/request';

export function listSubmissions(params) {
  return axios.get('/submissions', params)
}

export function createSubmission(data) {
  return axios.post('/submissions', data)
}

export function getSubmission(id) {
  return axios.get(`/submissions/${id}`)
}

export function getSubmissionInfo(id) {
  return axios.get(`/submissions/${id}/info`)
}
