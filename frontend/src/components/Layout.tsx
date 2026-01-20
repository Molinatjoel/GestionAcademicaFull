import { NavLink, Outlet, useNavigate } from 'react-router-dom'
import { useAuth } from '../state/AuthContext'
import './Layout.css'

export const Layout = () => {
  const { user, logout, hasAnyRole, isRole } = useAuth()
  const navigate = useNavigate()

  const handleLogout = () => {
    logout()
    navigate('/login', { replace: true })
  }

  const links = [
    { to: '/', label: 'Inicio', roles: [] },
    { to: '/admin', label: 'Admin', roles: ['admin'] },
    { to: '/docente', label: 'Docente', roles: ['docente'] },
    { to: '/calificaciones', label: 'Calificaciones', roles: [] },
    { to: '/matriculas', label: 'Matrículas', roles: ['admin', 'docente', 'estudiante', 'representante'] },
    { to: '/chats', label: 'Chats', roles: [] },
  ].filter((link) => link.roles.length === 0 || hasAnyRole(link.roles))

  const roleBadge = user?.roles?.map((r) => r.toUpperCase()).join(' / ')

  return (
    <div className="layout-shell">
      <header className="topbar">
        <div className="brand">
          <div className="brand__dot" />
          <div>
            <div className="brand__title">Gestión Académica</div>
            <div className="brand__muted">Panel unificado</div>
          </div>
        </div>
        <div className="userbox">
          {roleBadge && <span className="pill">{roleBadge}</span>}
          <div className="userbox__info">
            <strong>{user?.nombres || user?.correo}</strong>
            <span className="muted">{user?.correo}</span>
          </div>
          <button className="btn btn-ghost" onClick={handleLogout}>
            Salir
          </button>
        </div>
      </header>

      <div className="layout-main">
        <aside className="sidebar">
          {links.map((l) => (
            <NavLink
              key={l.to}
              to={l.to}
              className={({ isActive }) => (isActive ? 'navlink navlink--active' : 'navlink')}
            >
              {l.label}
            </NavLink>
          ))}
        </aside>
        <main className="layout__content">
          <Outlet />
        </main>
      </div>
    </div>
  )
}
