import { useEffect, useState } from 'react'
import api from '../utils/api'
import { useAuth } from '../state/AuthContext'

type Calificacion = {
  id_calificacion: number
  nota: number
  observacion: string
  fecha_registro: string
  estudiante?: string
  asignatura?: string
  curso?: string
}

export const CalificacionesPage = () => {
  const { user, isRole } = useAuth()
  const [items, setItems] = useState<Calificacion[]>([])
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    const fetchData = async () => {
      setLoading(true)
      setError(null)
      try {
        const { data } = await api.get<Calificacion[]>('/api/calificaciones')
        setItems(Array.isArray(data) ? data : [])
      } catch (e: any) {
        setError(e?.response?.data?.error || 'No se pudieron cargar las calificaciones')
      } finally {
        setLoading(false)
      }
    }
    fetchData()
  }, [])

  const roleCopy = isRole('docente')
    ? 'Puedes registrar y editar notas de tus cursos asignados.'
    : isRole('admin')
      ? 'Visión completa de todas las calificaciones.'
      : 'Solo puedes ver tus calificaciones visibles.'

  return (
    <div className="page">
      <div className="card">
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: '1rem', flexWrap: 'wrap' }}>
          <div>
            <h2 style={{ margin: 0 }}>Calificaciones</h2>
            <p className="muted" style={{ margin: 0 }}>{roleCopy}</p>
          </div>
          {user && <span className="pill">{user.correo}</span>}
        </div>
      </div>

      {loading && <div className="card">Cargando...</div>}
      {error && <div className="card" style={{ color: 'var(--danger)' }}>{error}</div>}

      {!loading && !error && (
        <div className="table-card">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Nota</th>
                <th>Observación</th>
                <th>Fecha</th>
                <th>Estudiante</th>
                <th>Asignatura</th>
                <th>Curso</th>
              </tr>
            </thead>
            <tbody>
              {items.length === 0 ? (
                <tr>
                  <td colSpan={7} className="empty">Sin registros visibles para tu rol.</td>
                </tr>
              ) : (
                items.map((c) => (
                  <tr key={`cal-${c.id_calificacion}`}>
                    <td>{c.id_calificacion}</td>
                    <td><span className="badge">{c.nota}</span></td>
                    <td>{c.observacion}</td>
                    <td>{new Date(c.fecha_registro).toLocaleDateString()}</td>
                    <td>{c.estudiante || '-'}</td>
                    <td>{c.asignatura || '-'}</td>
                    <td>{c.curso || '-'}</td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      )}
    </div>
  )
}
