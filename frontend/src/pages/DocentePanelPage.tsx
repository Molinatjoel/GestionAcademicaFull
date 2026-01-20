import { useEffect, useMemo, useState } from 'react'
import api from '../utils/api'
import { useAuth } from '../state/AuthContext'

interface CursoItem {
  id_curso: number
  nombre_curso: string
  nivel?: string
  estado?: boolean
  id_docente_titular?: number
}

interface CursoAsignaturaItem {
  id_curso_asignatura: number
  id_curso: number
  curso: string
  id_asignatura: number
  asignatura: string
  id_docente?: number
  docente?: string
}

interface MatriculaItem {
  id_matricula: number
  id_estudiante?: number
  estudiante?: string
  id_curso?: number
  curso?: string
  id_periodo?: number
  periodo?: string
  estado?: boolean
  fecha_matricula?: string
}

interface CalificacionItem {
  id_calificacion?: number
  id_matricula: number
  id_curso_asignatura: number
  estudiante?: string
  curso?: string
  asignatura?: string
  nota?: number
  observacion?: string
  fecha_registro?: string
}

type TabKey = 'resumen' | 'cursos' | 'estudiantes' | 'calificaciones'

export const DocentePanelPage = () => {
  const { user } = useAuth()
  const [active, setActive] = useState<TabKey>('resumen')
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const userId = user?.uid || (user as any)?.id

  const [cursoAsignaturas, setCursoAsignaturas] = useState<CursoAsignaturaItem[]>([])
  const [matriculas, setMatriculas] = useState<MatriculaItem[]>([])
  const [calificaciones, setCalificaciones] = useState<CalificacionItem[]>([])
  const [cursos, setCursos] = useState<CursoItem[]>([])

  const [selectedCurso, setSelectedCurso] = useState<number | null>(null)
  const [selectedAsignatura, setSelectedAsignatura] = useState<number | null>(null)

  const [notaForm, setNotaForm] = useState({ id_matricula: null as number | null, nota: '', observacion: '' })
  const [showNotaModal, setShowNotaModal] = useState(false)

  const fetchAll = async () => {
    setLoading(true)
    setError(null)
    try {
      const [ca, m, c, cu] = await Promise.all([
        api.get<CursoAsignaturaItem[]>('/api/curso-asignaturas'),
        api.get<MatriculaItem[]>('/api/matriculas'),
        api.get<CalificacionItem[]>('/api/calificaciones'),
        api.get<CursoItem[]>('/api/cursos'),
      ])
      const allCursoAsignaturas = Array.isArray(ca.data) ? ca.data : []
      const allCursos = Array.isArray(cu.data) ? cu.data : []
      // Mostrar asignaturas donde: está asignado directamente O es titular del curso sin que otro docente esté asignado
      const misCursoAsignaturas = allCursoAsignaturas.filter((ca) => {
        // Asignación directa: este docente está asignado a esta asignatura
        if (ca.id_docente === userId) return true
        // O: es titular del curso Y (no hay docente asignado O es este docente)
        const curso = allCursos.find((c) => c.id_curso === ca.id_curso)
        if (curso && curso.id_docente_titular === userId) {
          // Si es titular, mostrar si no hay otro docente asignado
          return !ca.id_docente || ca.id_docente === userId
        }
        return false
      })
      setCursoAsignaturas(misCursoAsignaturas)
      setCursos(allCursos)
      setMatriculas(Array.isArray(m.data) ? m.data : [])
      setCalificaciones(Array.isArray(c.data) ? c.data : [])
    } catch (e: any) {
      setError(e?.response?.data?.error || 'No se pudo cargar el panel')
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    fetchAll()
  }, [])

  const misCursos = useMemo(() => {
    const cursosUnicos = new Map<number, string>()
    cursoAsignaturas.forEach((ca) => cursosUnicos.set(ca.id_curso, ca.curso))
    return Array.from(cursosUnicos, ([id, nombre]) => ({ id, nombre }))
  }, [cursoAsignaturas])

  const asignaturasPorCurso = useMemo(() => {
    if (!selectedCurso) return []
    return cursoAsignaturas.filter((ca) => ca.id_curso === selectedCurso)
  }, [cursoAsignaturas, selectedCurso])

  const estudiantesPorCurso = useMemo(() => {
    if (!selectedCurso) return []
    return matriculas.filter((m) => m.id_curso === selectedCurso && m.estado)
  }, [matriculas, selectedCurso])

  const calificacionesPorAsignatura = useMemo(() => {
    if (!selectedAsignatura) return []
    const caId = cursoAsignaturas.find((ca) => ca.id_asignatura === selectedAsignatura && ca.id_curso === selectedCurso)?.id_curso_asignatura
    if (!caId) return []
    return calificaciones.filter((c) => c.id_curso_asignatura === caId)
  }, [calificaciones, selectedAsignatura, selectedCurso, cursoAsignaturas])

  const tabButton = (key: TabKey, label: string) => (
    <button
      key={key}
      className={active === key ? 'btn btn-primary' : 'btn btn-ghost'}
      style={{ padding: '0.55rem 0.9rem' }}
      onClick={() => setActive(key)}
    >
      {label}
    </button>
  )

  const openNotaModal = (idMatricula: number) => {
    const existing = calificacionesPorAsignatura.find((c) => c.id_matricula === idMatricula)
    setNotaForm({
      id_matricula: idMatricula,
      nota: existing?.nota?.toString() || '',
      observacion: existing?.observacion || '',
    })
    setShowNotaModal(true)
  }

  const saveNota = async () => {
    if (!notaForm.id_matricula || !selectedAsignatura || !selectedCurso) return
    const caId = cursoAsignaturas.find((ca) => ca.id_asignatura === selectedAsignatura && ca.id_curso === selectedCurso)?.id_curso_asignatura
    if (!caId) return

    const payload = {
      id_matricula: notaForm.id_matricula,
      id_curso_asignatura: caId,
      nota: parseFloat(notaForm.nota) || 0,
      observacion: notaForm.observacion,
    }

    const existing = calificacionesPorAsignatura.find((c) => c.id_matricula === notaForm.id_matricula)
    if (existing?.id_calificacion) {
      await api.put(`/api/calificaciones/${existing.id_calificacion}`, payload)
    } else {
      await api.post('/api/calificaciones', payload)
    }

    setShowNotaModal(false)
    setNotaForm({ id_matricula: null, nota: '', observacion: '' })
    fetchAll()
  }

  return (
    <div className="page" style={{ gap: '1.25rem' }}>
      <div className="card" style={{ display: 'flex', justifyContent: 'space-between', gap: '1rem', flexWrap: 'wrap' }}>
        <div>
          <h2 style={{ margin: 0 }}>Panel de docente</h2>
          <p className="muted" style={{ margin: 0 }}>
            Hola {user?.nombres || user?.correo}, gestiona tus cursos, estudiantes y calificaciones.
          </p>
        </div>
        <div style={{ display: 'flex', gap: '0.6rem', flexWrap: 'wrap' }}>
          <div className="pill">
            <strong>{misCursos.length}</strong>
            <span className="muted" style={{ fontWeight: 500 }}>Cursos</span>
          </div>
          <div className="pill">
            <strong>{cursoAsignaturas.length}</strong>
            <span className="muted" style={{ fontWeight: 500 }}>Asignaturas</span>
          </div>
        </div>
      </div>

      <div className="card" style={{ display: 'flex', gap: '0.5rem', flexWrap: 'wrap' }}>
        {tabButton('resumen', 'Resumen')}
        {tabButton('cursos', 'Mis cursos')}
        {tabButton('estudiantes', 'Estudiantes')}
        {tabButton('calificaciones', 'Calificaciones')}
      </div>

      {loading && <div className="card">Cargando...</div>}
      {error && <div className="card" style={{ color: 'var(--danger)' }}>{error}</div>}

      {!loading && !error && (
        <>
          {active === 'resumen' && (
            <>
              <div className="card">
                <h3 style={{ marginTop: 0 }}>Mis cursos y asignaturas</h3>
                <div className="table-card" style={{ marginTop: '0.5rem' }}>
                  <table>
                    <thead>
                      <tr>
                        <th>Curso</th>
                        <th>Asignatura</th>
                      </tr>
                    </thead>
                    <tbody>
                      {cursoAsignaturas.length === 0 ? (
                        <tr>
                          <td colSpan={2} className="empty">Sin asignaciones</td>
                        </tr>
                      ) : (
                        cursoAsignaturas.map((ca) => (
                          <tr key={ca.id_curso_asignatura}>
                            <td>{ca.curso}</td>
                            <td>{ca.asignatura}</td>
                          </tr>
                        ))
                      )}
                    </tbody>
                  </table>
                </div>
              </div>
            </>
          )}

          {active === 'cursos' && (
            <div className="card" style={{ display: 'grid', gap: '1rem' }}>
              <div>
                <p className="eyebrow">Mis cursos</p>
                <h3 style={{ margin: '0.1rem 0' }}>Cursos asignados</h3>
                <p className="muted" style={{ margin: 0 }}>Listado de cursos donde impartes clases.</p>
              </div>
              <div className="table-card">
                <table>
                  <thead>
                    <tr>
                      <th>Curso</th>
                      <th>Asignaturas que imparto</th>
                    </tr>
                  </thead>
                  <tbody>
                    {misCursos.length === 0 ? (
                      <tr>
                        <td colSpan={2} className="empty">Sin cursos asignados</td>
                      </tr>
                    ) : (
                      misCursos.map((c) => (
                        <tr key={c.id}>
                          <td>{c.nombre}</td>
                          <td>
                            <div style={{ display: 'flex', gap: '0.3rem', flexWrap: 'wrap' }}>
                              {cursoAsignaturas
                                .filter((ca) => ca.id_curso === c.id)
                                .map((ca) => (
                                  <span key={ca.id_curso_asignatura} className="badge" style={{ fontSize: '0.8rem' }}>
                                    {ca.asignatura}
                                  </span>
                                ))}
                            </div>
                          </td>
                        </tr>
                      ))
                    )}
                  </tbody>
                </table>
              </div>
            </div>
          )}

          {active === 'estudiantes' && (
            <div className="card" style={{ display: 'grid', gap: '1rem' }}>
              <div>
                <p className="eyebrow">Estudiantes</p>
                <h3 style={{ margin: '0.1rem 0' }}>Listado por curso</h3>
                <p className="muted" style={{ margin: 0 }}>Selecciona un curso para ver sus estudiantes.</p>
              </div>
              <div>
                <label className="field">
                  <span>Selecciona curso</span>
                  <select value={selectedCurso ?? ''} onChange={(e) => setSelectedCurso(Number(e.target.value) || null)}>
                    <option value="">-- Selecciona un curso --</option>
                    {misCursos.map((c) => (
                      <option key={c.id} value={c.id}>{c.nombre}</option>
                    ))}
                  </select>
                </label>
              </div>

              {selectedCurso && (
                <div className="table-card">
                  <table>
                    <thead>
                      <tr>
                        <th>Estudiante</th>
                        <th>Periodo</th>
                        <th>Fecha matrícula</th>
                      </tr>
                    </thead>
                    <tbody>
                      {estudiantesPorCurso.length === 0 ? (
                        <tr>
                          <td colSpan={3} className="empty">Sin estudiantes matriculados</td>
                        </tr>
                      ) : (
                        estudiantesPorCurso.map((m) => (
                          <tr key={m.id_matricula}>
                            <td>{m.estudiante || '-'}</td>
                            <td>{m.periodo || '-'}</td>
                            <td>{m.fecha_matricula || '-'}</td>
                          </tr>
                        ))
                      )}
                    </tbody>
                  </table>
                </div>
              )}
            </div>
          )}

          {active === 'calificaciones' && (
            <div className="card" style={{ display: 'grid', gap: '1rem' }}>
              <div>
                <p className="eyebrow">Calificaciones</p>
                <h3 style={{ margin: '0.1rem 0' }}>Gestión de notas</h3>
                <p className="muted" style={{ margin: 0 }}>Asigna y edita calificaciones por asignatura.</p>
              </div>
              <div style={{ display: 'grid', gap: '0.75rem', gridTemplateColumns: 'repeat(auto-fit, minmax(240px, 1fr))' }}>
                <label className="field">
                  <span>Curso</span>
                  <select value={selectedCurso ?? ''} onChange={(e) => { setSelectedCurso(Number(e.target.value) || null); setSelectedAsignatura(null); }}>
                    <option value="">-- Selecciona curso --</option>
                    {misCursos.map((c) => (
                      <option key={c.id} value={c.id}>{c.nombre}</option>
                    ))}
                  </select>
                </label>
                {selectedCurso && (
                  <label className="field">
                    <span>Asignatura</span>
                    <select value={selectedAsignatura ?? ''} onChange={(e) => setSelectedAsignatura(Number(e.target.value) || null)}>
                      <option value="">-- Selecciona asignatura --</option>
                      {asignaturasPorCurso.map((ca) => (
                        <option key={ca.id_asignatura} value={ca.id_asignatura}>{ca.asignatura}</option>
                      ))}
                    </select>
                  </label>
                )}
              </div>

              {selectedCurso && selectedAsignatura && (
                <div className="table-card">
                  <table>
                    <thead>
                      <tr>
                        <th>Estudiante</th>
                        <th>Nota</th>
                        <th>Observación</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      {estudiantesPorCurso.length === 0 ? (
                        <tr>
                          <td colSpan={4} className="empty">Sin estudiantes</td>
                        </tr>
                      ) : (
                        estudiantesPorCurso.map((m) => {
                          const calif = calificacionesPorAsignatura.find((c) => c.id_matricula === m.id_matricula)
                          return (
                            <tr key={m.id_matricula}>
                              <td>{m.estudiante || '-'}</td>
                              <td>{calif?.nota ?? '-'}</td>
                              <td>{calif?.observacion || '-'}</td>
                              <td>
                                <button className="btn btn-ghost" onClick={() => openNotaModal(m.id_matricula)}>
                                  {calif ? 'Editar' : 'Asignar'}
                                </button>
                              </td>
                            </tr>
                          )
                        })
                      )}
                    </tbody>
                  </table>
                </div>
              )}
            </div>
          )}
        </>
      )}

      {showNotaModal && (
        <div className="modal-backdrop" onClick={() => setShowNotaModal(false)}>
          <div className="modal-card" onClick={(e) => e.stopPropagation()}>
            <div className="modal__header">
              <div>
                <p className="eyebrow">Calificación</p>
                <h3>Asignar nota</h3>
              </div>
              <button className="btn btn-ghost" onClick={() => setShowNotaModal(false)}>Cerrar</button>
            </div>
            <div className="modal__body">
              <div className="form-grid">
                <label className="field">
                  <span>Nota</span>
                  <input type="number" step="0.1" min="0" max="10" value={notaForm.nota} onChange={(e) => setNotaForm((f) => ({ ...f, nota: e.target.value }))} />
                </label>
                <label className="field" style={{ gridColumn: '1 / -1' }}>
                  <span>Observación</span>
                  <textarea rows={3} value={notaForm.observacion} onChange={(e) => setNotaForm((f) => ({ ...f, observacion: e.target.value }))} />
                </label>
              </div>
            </div>
            <div className="modal__footer">
              <button className="btn btn-ghost" onClick={() => setShowNotaModal(false)}>Cancelar</button>
              <button className="btn btn-primary" onClick={saveNota}>Guardar nota</button>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}
