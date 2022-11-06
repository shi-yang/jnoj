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

export interface listProblemStatementsResponse {
  data: Array<{
    language: string;
    input: string;
    output: string;
    legend: string;
    notes: string;
  }>;
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

export function updateProblemStatement(id: number, data: updateProblemStatementRequest) {
  return axios.put(`/problems/${id}/statements`, data)
}