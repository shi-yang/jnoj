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
  return axios.post<LoginResponse>('/login', data);
}

export interface RegisterRequest {
  username: string;
  captcha: string;
  password: string;
}

export function Register(data: RegisterRequest) {
  return axios.post('/register', data);
}

export function getUserInfo() {
  return axios.get('/user/info');
}

export function getUsers(id) {
  return axios.get(`/users/${id}`);
}

export function getUserProfile(id) {
  return axios.get(`/users/${id}/profile`);
}

interface getCaptchaRequest {
  phone?: string;
  email?: string;
}
export function getCaptcha(params:getCaptchaRequest) {
  return axios.get(`/captcha`, { params });
}

export function getUserProfileCalendar(id, params = undefined) {
  return axios.get(`/users/${id}/profile_calendar`, { params });
}

export function getUserProfileProblemSolved(id, params) {
  return axios.get(`/users/${id}/profile_problemsolved`, { params });
}

export function updateUser(id, data) {
  return axios.put(`/users/${id}`, data);
}

export function updateUserPassword(id, data) {
  return axios.put(`/users/${id}/password`, data);
}

export function getUserProfileCount(id: number) {
  return axios.get(`/users/${id}/profile_count`);
}

export function listUserProfileUserBadges(id: number) {
  return axios.get(`/users/${id}/profile_user_badges`);
}

export function updateUserAvatar(id, data) {
  return axios.post(`/users/${id}/avatar`, data);
}
