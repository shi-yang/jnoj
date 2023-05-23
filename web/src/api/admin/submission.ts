import axios from '@/utils/requestAdmin';

export function rejudge(data) {
  return axios.post(`/rejudge`, data);
}
