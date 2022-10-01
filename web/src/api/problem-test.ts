import axios from '@/utils/request';

export function CreateProblemTest(pid: number, file: any) {
  return axios.post(`/problems/${pid}/tests`, file)
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
}
export function listProblemTests(id: number) {
  return axios.get<ListProblemTetstsResponse>(`/problems/${id}/tests`)
}

export function deleteProblemTests(pid: number, testId: number) {
  return axios.delete(`/problem_tests/${pid}/tests/${testId}`)
}
