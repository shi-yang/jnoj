import axios from '@/utils/request';

export interface createProblemStatementRequest {
  input: string;
  output: string;
  legend: string;
  notes: string;
}

export function createProblemStatement(id: number, data: createProblemStatementRequest) {
  return axios.post(`/problems/${id}/statements`, data)
}

export interface statement {
  name: string;
  language: string;
  input: string;
  output: string;
  legend: string;
  notes: string;
}

export interface listProblemStatementsResponse {
  data: statement[];
  total: number;
}

export function listProblemStatements(id: number) {
  return axios.get<listProblemStatementsResponse>(`/problems/${id}/statements`)
}

export interface updateProblemStatementRequest {
  input: string;
  output: string;
  legend: string;
  notes: string;
}

export function updateProblemStatement(id: number, sid: number, data: updateProblemStatementRequest) {
  return axios.put(`/problems/${id}/statements/${sid}`, data)
}

export function deleteProblemStatement(id: number, sid: number) {
  return axios.delete(`/problems/${id}/statements/${sid}`)
}
