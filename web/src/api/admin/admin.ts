import axios from '@/utils/requestAdmin';

export function listServiceStatuses() {
  return axios.get(`/service_statuses`);
}
