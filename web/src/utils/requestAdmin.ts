import axios from 'axios';
import Router from 'next/router';
import { getAccessToken } from './auth';
import getConfig from "next/config";
const { publicRuntimeConfig } = getConfig();
const http = axios.create({
  baseURL: publicRuntimeConfig.ADMIN_API_BASE_URL
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
