import { isSSR } from "./is"

const ACCESS_TOKEN = 'token'

export const isLogged = ():boolean => {
  if (isSSR) {
    return false;
  }
  const token = localStorage.getItem(ACCESS_TOKEN);
  return token !== '' && token !== null;
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
