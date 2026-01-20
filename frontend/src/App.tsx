import { Routes, Route, Navigate } from 'react-router-dom'
import { ProtectedRoute } from './components/ProtectedRoute'
import { Layout } from './components/Layout'
import { LoginPage } from './pages/LoginPage'
import { DashboardPage } from './pages/DashboardPage'
import { CalificacionesPage } from './pages/CalificacionesPage'
import { MatriculasPage } from './pages/MatriculasPage'
import { ChatsPage } from './pages/ChatsPage'
import { AdminPanelPage } from './pages/AdminPanelPage'
import { DocentePanelPage } from './pages/DocentePanelPage'
import './App.css'

function App() {
  return (
    <Routes>
      <Route path="/login" element={<LoginPage />} />
      <Route
        path="/"
        element={
          <ProtectedRoute>
            <Layout />
          </ProtectedRoute>
        }
      >
        <Route index element={<DashboardPage />} />
        <Route
          path="admin"
          element={
            <ProtectedRoute allowRoles={['admin']}>
              <AdminPanelPage />
            </ProtectedRoute>
          }
        />
        <Route
          path="docente"
          element={
            <ProtectedRoute allowRoles={['docente']}>
              <DocentePanelPage />
            </ProtectedRoute>
          }
        />
        <Route path="calificaciones" element={<CalificacionesPage />} />
        <Route path="matriculas" element={<MatriculasPage />} />
        <Route path="chats" element={<ChatsPage />} />
      </Route>
      <Route path="*" element={<Navigate to="/" replace />} />
    </Routes>
  )
}

export default App
