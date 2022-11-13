import axios from '@/utils/request';

export interface LoginRequest {
  phone: string;
  password: string;
  captcha: string;
}

export interface LoginResponse {
  id: number;
  token: string;
}

export function Login(data: LoginRequest) {
  return axios.post<LoginResponse>('/login', data)
}

export interface RegisterRequest {
  username: string;
  captcha: string;
  password: string;
}

export function Register(data: RegisterRequest) {
  return axios.post('/register', data)
}

export function GetUserInfo() {
  return axios.get('/user/info')
}
