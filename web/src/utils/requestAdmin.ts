import axios from 'axios';
import Router from 'next/router';
import { getAccessToken } from './auth';

const http = axios.create({
  baseURL: process.env.NEXT_PUBLIC_ADMIN_BASE_URL
});

http.interceptors.request.use(config => {
  const token = getAccessToken();
  if (token) {
    config.headers['Authorization'] = 'Bearer ' + token;
  }
  return config;
});

const err = (error) => {
  if (error.response) {
    if (error.response.status === 401 && error.config.url !== '/user/info') {
      Router.push('/user/login');
    } else if (error.response.status === 403) {
      Router.push('/403');
    }
  }
  return Promise.reject(error);
};

http.interceptors.response.use(response => {
  return response;
}, err);

export default http;
