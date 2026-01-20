import { Navigate, useLocation } from 'react-router-dom'
import { useAuth } from '../state/AuthContext'

export const ProtectedRoute = ({
  children,
  allowRoles,
}: {
  children: React.ReactElement
  allowRoles?: string[]
}) => {
  const { isAuthenticated, user } = useAuth()
  const location = useLocation()

  if (!isAuthenticated) {
    return <Navigate to="/login" state={{ from: location }} replace />
  }

  if (allowRoles && allowRoles.length > 0) {
    const userRoles = user?.roles || []
    const allowed = allowRoles.some((r) => userRoles.includes(r))
    if (!allowed) {
      return <Navigate to="/login" replace />
    }
  }

  return children
}
