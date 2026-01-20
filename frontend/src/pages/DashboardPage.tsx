import { useAuth } from '../state/AuthContext'
import { useEffect, useState } from 'react'
import api from '../utils/api'

type MatriculaResumen = {
  id_matricula: number
  curso?: string
  asignaturas: { asignatura?: string; docente?: string }[]
}

type CalificacionResumen = {
  id_calificacion: number
  asignatura?: string
  curso?: string
  nota?: number
  docente?: string
}

export const DashboardPage = () => {
  const { user, isRole } = useAuth()
  const [mats, setMats] = useState<MatriculaResumen[]>([])
  const [cals, setCals] = useState<CalificacionResumen[]>([])
  const isStudent = isRole('estudiante')
  const isParent = isRole('representante')

  useEffect(() => {
    const load = async () => {
      if (!isStudent && !isParent) return
      try {
        const { data: mdata } = await api.get('/api/matriculas')
        const matriculas = Array.isArray(mdata) ? mdata : []
        // fetch asignaturas por matricula
        const fetches = matriculas.map(async (m: any) => {
          const res = await api.get('/api/matricula-asignaturas', { params: { matricula_id: m.id_matricula } })
          return {
            id_matricula: m.id_matricula,
            curso: m.curso,
            asignaturas: Array.isArray(res.data) ? res.data.map((a: any) => ({ asignatura: a.asignatura, docente: a.docente })) : [],
          }
        })
        setMats(await Promise.all(fetches))

        const { data: cdata } = await api.get('/api/calificaciones')
        setCals(Array.isArray(cdata) ? cdata.slice(0, 6) : [])
      } catch (e) {
        // ignorar en resumen
      }
    }
    load()
  }, [isStudent, isParent])
  return (
    <div className="page">
      <div className="card">
        <h2>Panel</h2>
        <p className="muted">Hola, {user?.correo}. Accede rápido a tus secciones clave.</p>
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(180px, 1fr))', gap: '0.75rem', marginTop: '1rem' }}>
          <a className="card" style={{ padding: '1rem' }} href="/calificaciones">
            <div className="muted">Notas</div>
            <div style={{ fontWeight: 700, fontSize: '1.1rem' }}>Calificaciones</div>
          </a>
          <a className="card" style={{ padding: '1rem' }} href="/matriculas">
            <div className="muted">Cursos</div>
            <div style={{ fontWeight: 700, fontSize: '1.1rem' }}>Matrículas</div>
          </a>
          <a className="card" style={{ padding: '1rem' }} href="/chats">
            <div className="muted">Comunicación</div>
            <div style={{ fontWeight: 700, fontSize: '1.1rem' }}>Chats</div>
          </a>
        </div>
      </div>

      {(isStudent || isParent) && (
        <div className="card">
          <h3 style={{ marginTop: 0 }}>Resumen de mis cursos</h3>
          {mats.length === 0 ? (
            <p className="muted">Aún no tienes matrículas visibles.</p>
          ) : (
            <div style={{ display: 'grid', gap: '0.75rem' }}>
              {mats.map((m) => (
                <div key={m.id_matricula} className="table-card" style={{ padding: '0.75rem' }}>
                  <div style={{ fontWeight: 700 }}>{m.curso || 'Curso'}</div>
                  {m.asignaturas.length ? (
                    <div style={{ display: 'flex', gap: '0.5rem', flexWrap: 'wrap', marginTop: '0.35rem' }}>
                      {m.asignaturas.map((a, idx) => (
                        <span key={`${m.id_matricula}-${idx}`} className="pill" style={{ display: 'inline-flex', gap: '0.35rem', alignItems: 'center' }}>
                          <span>{a.asignatura || 'Asignatura'}</span>
                          {a.docente && <span className="muted" style={{ fontSize: '0.8rem' }}>({a.docente})</span>}
                        </span>
                      ))}
                    </div>
                  ) : (
                    <div className="muted" style={{ marginTop: '0.25rem' }}>Sin asignaturas cargadas.</div>
                  )}
                </div>
              ))}
            </div>
          )}
        </div>
      )}

      {(isStudent || isParent) && (
        <div className="card">
          <h3 style={{ marginTop: 0 }}>Últimas calificaciones</h3>
          {cals.length === 0 ? (
            <p className="muted">Aún no hay calificaciones.</p>
          ) : (
            <div className="table-card">
              <table>
                <thead>
                  <tr>
                    <th>Asignatura</th>
                    <th>Curso</th>
                    <th>Docente</th>
                    <th>Nota</th>
                  </tr>
                </thead>
                <tbody>
                  {cals.map((c) => (
                    <tr key={`cal-res-${c.id_calificacion}`}>
                      <td>{c.asignatura || '-'}</td>
                      <td>{c.curso || '-'}</td>
                      <td>{c.docente || '-'}</td>
                      <td><span className="badge">{c.nota ?? '-'}</span></td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
      )}
    </div>
  )
}
