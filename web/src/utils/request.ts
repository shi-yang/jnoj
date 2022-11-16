import { Notification } from '@arco-design/web-react';
import axios from 'axios';
import { getAccessToken } from './auth';

const http = axios.create({
  baseURL: 'http://127.0.0.1:8092'
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
