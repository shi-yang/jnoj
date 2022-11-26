import axios from '@/utils/request';

export const VerdictMap = {
  1: '等待测评',
  2: '编译错误',
  3: '回答错误',
  4: '回答正确',
  5: '输出格式错误',
  6: '时间超限',
  7: '内存超限',
  8: '运行出错',
  9: '系统错误',
};

export const VerdictColorMap = {
  1: 'secondary',
  2: 'warning',
  3: 'error',
  4: 'success',
  5: 'warning',
  6: 'warning',
  7: 'warning',
  8: 'error',
  9: 'error',
};

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

export function getSubmissionInfo(id) {
  return axios.get(`/submissions/${id}/info`)
}
