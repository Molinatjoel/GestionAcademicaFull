import { FormEvent, useState } from 'react'
import { useNavigate, useLocation, Navigate } from 'react-router-dom'
import { useAuth } from '../state/AuthContext'
import { VortexCanvas } from '../components/VortexCanvas'
import './LoginPage.css'

export const LoginPage = () => {
  const { login, isAuthenticated } = useAuth()
  const navigate = useNavigate()
  const location = useLocation()
  const [correo, setCorreo] = useState('')
  const [password, setPassword] = useState('')
  const [error, setError] = useState<string | null>(null)
  const from = (location.state as any)?.from?.pathname || '/'

  if (isAuthenticated) {
    return <Navigate to={from} replace />
  }

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault()
    setError(null)
    try {
      const logged = await login(correo, password)
      const isAdmin = (logged.roles || []).some((r) => r.toLowerCase() === 'admin')
      const isDocente = (logged.roles || []).some((r) => r.toLowerCase() === 'docente')
      const target = isAdmin ? '/admin' : isDocente ? '/docente' : from
      navigate(target, { replace: true })
    } catch (err: any) {
      setError(err?.response?.data?.error || 'No se pudo iniciar sesión')
    }
  }

  return (
    <div className="login">
      <VortexCanvas />
      <div className="login__card">
        <h1>Iniciar sesión</h1>
        <form onSubmit={handleSubmit}>
          <label>
            Correo
            <input
              type="email"
              value={correo}
              onChange={(e) => setCorreo(e.target.value)}
              required
            />
          </label>
          <label>
            Contraseña
            <input
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
            />
          </label>
          {error && <div className="login__error">{error}</div>}
          <button type="submit">Entrar</button>
        </form>
        <p className="login__hint">Demo: admin@admin.com / admin123 o docente@demo.com / Demo12345</p>
      </div>
    </div>
  )
}
