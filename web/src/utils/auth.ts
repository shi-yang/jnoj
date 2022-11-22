import { isSSR } from "./is"

const ACCESS_TOKEN = 'token'

export const isLogged = () => {
  return !isSSR && localStorage.getItem(ACCESS_TOKEN)
}

export const setAccessToken = (token) => {
  localStorage.setItem(ACCESS_TOKEN, token)
}

export const getAccessToken = () => {
  return localStorage.getItem(ACCESS_TOKEN)
}

export const removeAccessToken = () => {
  localStorage.removeItem(ACCESS_TOKEN)
}
