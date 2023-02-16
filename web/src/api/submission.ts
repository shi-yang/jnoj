import axios from '@/utils/request';

export const LanguageMap = {
  0: 'C',
  1: 'C++',
  2: 'Java',
  3: 'Python3'
}

export function listSubmissions(params) {
  return axios.get('/submissions', {params})
}

export function createSubmission(data) {
  return axios.post('/submissions', data)
}

export function getSubmission(id) {
  return axios.get(`/submissions/${id}`)
}

interface getLastSubmissionProps {
  problemId: number,
  entityId?: number,
  entityType?: number,
}
export function getLastSubmission(params:getLastSubmissionProps) {
  return axios.get(`/last_submission`, {params})
}
