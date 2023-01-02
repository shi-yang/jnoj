import { isSSR } from "./is"

const ACCESS_TOKEN = 'token'

export const isLogged = ():boolean => {
  const token = localStorage.getItem(ACCESS_TOKEN)
  return !isSSR && token !== '' && token !== null
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
