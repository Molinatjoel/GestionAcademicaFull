const TOKEN_KEY = 'ga_token'

export const getStoredToken = (): string | null => {
  if (typeof window === 'undefined') return null
  return localStorage.getItem(TOKEN_KEY)
}

export const storeToken = (token: string) => {
  if (typeof window === 'undefined') return
  localStorage.setItem(TOKEN_KEY, token)
}

export const clearStoredToken = () => {
  if (typeof window === 'undefined') return
  localStorage.removeItem(TOKEN_KEY)
}

export const parseJwtPayload = (
  token: string,
): { correo: string; roles: string[]; uid?: number } | null => {
  try {
    const base64Url = token.split('.')[1]
    const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/')
    const jsonPayload = decodeURIComponent(
      atob(base64)
        .split('')
        .map((c) => '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2))
        .join(''),
    )
    const payload = JSON.parse(jsonPayload)
    return {
      correo: payload.sub ?? '',
      roles: Array.isArray(payload.roles) ? payload.roles : [],
      uid: payload.uid,
    }
  } catch (e) {
    console.error('No se pudo decodificar el JWT', e)
    return null
  }
}
