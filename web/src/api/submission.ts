import axios from '@/utils/request';

export function listSubmissions(params) {
  return axios.get('/submissions', params)
}
