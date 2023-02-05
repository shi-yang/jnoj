import axios from '@/utils/request';

export interface runRequest {
  stdin: string
  memoryLimit: string
  timeLimit: string
  language: number
  languageId?: number
  source: string
}

export function runSandbox(data:runRequest) {
  return axios.post(`/sandboxs`, data)
}
