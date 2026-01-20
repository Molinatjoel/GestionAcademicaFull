import { useEffect, useState } from 'react'
import api from '../utils/api'
import { useAuth } from '../state/AuthContext'

type Matricula = {
  id_matricula: number
  estado: boolean
  fecha_matricula: string
  estudiante?: string
  curso?: string
  periodo?: string
}

type MatriculaAsignatura = {
  id_matricula_asignatura: number
  id_curso_asignatura: number
  curso?: string
  asignatura?: string
  docente?: string
}

export const MatriculasPage = () => {
  const { isRole } = useAuth()
  const [items, setItems] = useState<Matricula[]>([])
  const [asignaturasByMatricula, setAsignaturasByMatricula] = useState<Record<number, MatriculaAsignatura[]>>({})
  const canSeeAsignaturas = isRole('estudiante') || isRole('representante')
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    const fetchData = async () => {
      setLoading(true)
      setError(null)
      try {
        const { data } = await api.get<Matricula[]>('/api/matriculas')
        setItems(Array.isArray(data) ? data : [])
        // Si es estudiante o representante, traer las asignaturas de cada matrícula
        if (canSeeAsignaturas && Array.isArray(data)) {
          const fetches = data.map(async (m) => {
            try {
              const res = await api.get<MatriculaAsignatura[]>('/api/matricula-asignaturas', { params: { matricula_id: m.id_matricula } })
              return { mid: m.id_matricula, list: Array.isArray(res.data) ? res.data : [] }
            } catch (e) {
              return { mid: m.id_matricula, list: [] }
            }
          })
          const results = await Promise.all(fetches)
          const map: Record<number, MatriculaAsignatura[]> = {}
          results.forEach(({ mid, list }) => {
            map[mid] = list
          })
          setAsignaturasByMatricula(map)
        } else {
          setAsignaturasByMatricula({})
        }
      } catch (e: any) {
        setError(e?.response?.data?.error || 'No se pudieron cargar las matrículas')
      } finally {
        setLoading(false)
      }
    }
    fetchData()
  }, [])

  const roleCopy = isRole('admin')
    ? 'Admin puede ver y gestionar todas las matrículas.'
    : isRole('docente')
      ? 'Ves las matrículas de tus cursos asignados.'
      : isRole('representante')
        ? 'Ves las matrículas y asignaturas de tu representado.'
        : 'Solo ves las matrículas asociadas a tu usuario.'

  return (
    <div className="page">
      <div className="card">
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: '1rem', flexWrap: 'wrap' }}>
          <div>
            <h2 style={{ margin: 0 }}>Matrículas</h2>
            <p className="muted" style={{ margin: 0 }}>{roleCopy}</p>
          </div>
          {isRole('docente') && <span className="pill">Vista de docente</span>}
          {isRole('admin') && <span className="pill">Administrador</span>}
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
                <th>Estado</th>
                <th>Fecha</th>
                <th>Estudiante</th>
                <th>Curso</th>
                <th>Periodo</th>
                {canSeeAsignaturas && <th>Asignaturas</th>}
              </tr>
            </thead>
            <tbody>
              {items.length === 0 ? (
                <tr>
                  <td colSpan={canSeeAsignaturas ? 7 : 6} className="empty">Sin registros visibles para tu rol.</td>
                </tr>
              ) : (
                items.map((m) => (
                  <tr key={`mat-${m.id_matricula}`}>
                    <td>{m.id_matricula}</td>
                    <td>
                      <span className={m.estado ? 'badge' : 'badge badge-neutral'}>
                        {m.estado ? 'Activa' : 'Inactiva'}
                      </span>
                    </td>
                    <td>{new Date(m.fecha_matricula).toLocaleDateString()}</td>
                    <td>{m.estudiante || '-'}</td>
                    <td>{m.curso || '-'}</td>
                    <td>{m.periodo || '-'}</td>
                    {canSeeAsignaturas && (
                      <td>
                        {asignaturasByMatricula[m.id_matricula]?.length ? (
                          <div style={{ display: 'flex', gap: '0.4rem', flexWrap: 'wrap' }}>
                            {asignaturasByMatricula[m.id_matricula].map((a) => (
                              <span key={a.id_matricula_asignatura} className="pill" style={{ display: 'inline-flex', gap: '0.35rem', alignItems: 'center' }}>
                                <span>{a.asignatura || '-'}</span>
                                {a.docente && <span className="muted" style={{ fontSize: '0.8rem' }}>({a.docente})</span>}
                              </span>
                            ))}
                          </div>
                        ) : (
                          <span className="muted">Sin asignaturas</span>
                        )}
                      </td>
                    )}
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
