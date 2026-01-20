import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react'
import { parseJwtPayload, getStoredToken, storeToken, clearStoredToken } from '../utils/auth'
import api from '../utils/api'

type AuthUser = {
  correo: string
  roles: string[]
  uid?: number
}

type RoleCheck = {
  isRole: (role: string) => boolean
  hasAnyRole: (roles: string[]) => boolean
}

type AuthContextShape = {
  user: AuthUser | null
  token: string | null
  isAuthenticated: boolean
  login: (correo: string, password: string) => Promise<AuthUser>
  logout: () => void
  isRole: RoleCheck['isRole']
  hasAnyRole: RoleCheck['hasAnyRole']
}

const AuthContext = createContext<AuthContextShape | undefined>(undefined)

export const AuthProvider = ({ children }: { children: React.ReactNode }) => {
  const [token, setToken] = useState<string | null>(getStoredToken())
  const [user, setUser] = useState<AuthUser | null>(() => {
    const stored = getStoredToken()
    return stored ? parseJwtPayload(stored) : null
  })

  useEffect(() => {
    if (token) {
      storeToken(token)
    } else {
      clearStoredToken()
    }
  }, [token])

  const login = useCallback(async (correo: string, password: string) => {
    const { data } = await api.post('/api/auth/login', { correo, password })
    if (!data?.token) {
      throw new Error('Token no recibido')
    }
    const parsed = parseJwtPayload(data.token)
    if (!parsed) {
      throw new Error('Token inválido')
    }
    setToken(data.token)
    setUser(parsed)
    return parsed
  }, [])

  const logout = useCallback(() => {
    setToken(null)
    setUser(null)
  }, [])

  const isRole = useCallback<RoleCheck['isRole']>(
    (role) => (user?.roles || []).map((r) => r.toLowerCase()).includes(role.toLowerCase()),
    [user?.roles],
  )

  const hasAnyRole = useCallback<RoleCheck['hasAnyRole']>(
    (roles) => roles.some((role) => isRole(role)),
    [isRole],
  )

  // Escuchar 401 globales emitidos por api.ts
  useEffect(() => {
    const handler = () => logout()
    window.addEventListener('auth:logout', handler)
    return () => window.removeEventListener('auth:logout', handler)
  }, [logout])

  const value = useMemo(
    () => ({
      user,
      token,
      isAuthenticated: Boolean(token),
      login,
      logout,
      isRole,
      hasAnyRole,
    }),
    [user, token, login, logout, isRole, hasAnyRole],
  )

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}

export const useAuth = (): AuthContextShape => {
  const ctx = useContext(AuthContext)
  if (!ctx) throw new Error('useAuth debe usarse dentro de AuthProvider')
  return ctx
}
