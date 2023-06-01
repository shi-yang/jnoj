import axios from '@/utils/request';

export function CreateProblemTest(pid: number, file: any) {
  return axios.post(`/problems/${pid}/tests`, file);
}

export interface ListProblemTetstsResponse {
  data: Array<{
    id: number;
    example: boolean;
    content: string;
    size: number;
    remark: string;
  }>;
  total: number;
  isSampleFirst: boolean;
}
export function listProblemTests(id: number, params) {
  return axios.get<ListProblemTetstsResponse>(`/problems/${id}/tests`, {params});
}

export function deleteProblemTests(pid: number, testId: number) {
  return axios.delete(`/problems/${pid}/tests/${testId}`);
}

export function uploadProblemTest(id, data) {
  return axios.post(`/problems/${id}/upload_test`, data, {
    headers: {
      'Content-Type': 'multipart/form-data;charset=UTF-8'
    }
  });
}

export function updateProblemTest(pid, testId, data) {
  return axios.put(`/problems/${pid}/tests/${testId}`, data);
}

export function sortProblemTests(pid, data) {
  return axios.post(`/problems/${pid}/test/sort`, data);
}
