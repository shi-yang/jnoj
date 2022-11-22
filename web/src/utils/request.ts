import { Notification } from '@arco-design/web-react';
import axios from 'axios';
import { getAccessToken } from './auth';

const http = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_BASE_URL
})
const err = (error) => {
  if (error.response) {
    const data = error.response.data
    if (error.response.status === 403) {
      Notification.error({
        title: 'Forbidden',
        content: data.message
      })
    }
  }
  return Promise.reject(error)
}

http.interceptors.request.use(config => {
  const token = getAccessToken()
  if (token) {
    config.headers['Authorization'] = 'Bearer ' + token
  }
  return config
}, err)


// http.interceptors.response.use((response) => {
//   return response.data
// }, err)

export default http;
