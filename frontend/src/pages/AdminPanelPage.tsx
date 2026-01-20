import { ReactNode, useEffect, useMemo, useState } from 'react'
import api from '../utils/api'
import { useAuth } from '../state/AuthContext'

interface UserItem {
  id: number
  correo: string
  nombres?: string
  apellidos?: string
  telefono?: string
  direccion?: string
  estado?: boolean
  roles?: Array<{ id_rol?: number; nombre_rol?: string }>
}

interface RolItem {
  id_rol?: number
  nombre_rol?: string
  descripcion?: string
}

interface CursoItem {
  id_curso: number
  nombre_curso: string
  nivel?: string
  estado?: boolean
}

interface AsignaturaItem {
  id_asignatura: number
  nombre_asignatura: string
  descripcion?: string
}

interface MatriculaItem {
  id_matricula: number
  estado: boolean
  fecha_matricula?: string
  estudiante?: string
  curso?: string
  periodo?: string
  id_estudiante?: number
  id_curso?: number
  id_periodo?: number
}

interface PeriodoItem {
  id_periodo: number
  descripcion: string
  fecha_inicio?: string
  fecha_fin?: string
  estado?: boolean
}

interface DatosFamiliaresItem {
  id_datos_familiares: number
  id_estudiante?: number
  estudiante?: string
  nombre_padre?: string
  telefono_padre?: string
  nombre_madre?: string
  telefono_madre?: string
  direccion_familiar?: string
  parentesco_representante?: string
  nombre_representante?: string
  ocupacion_representante?: string
  telefono_representante?: string
  id_representante_user?: number | null
  representante_correo?: string | null
}

interface UserRoleItem {
  id_user_rol?: number
  id_user?: number
  id_rol?: number
  correo?: string
  nombre?: string
  rol?: string
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

const formatDate = (iso?: string) => (iso ? new Date(iso).toLocaleDateString() : '-')

type TabKey = 'resumen' | 'usuarios' | 'roles' | 'cursos' | 'asignaturas' | 'familiares' | 'matriculas' | 'periodos' | 'asignaciones'

type ModalType = 'usuario' | 'rol' | 'curso' | 'asignatura' | 'familiares' | 'userRol' | 'matricula' | 'periodo' | 'cursoAsignatura' | 'cursoAsignaturas'
type ModalMode = 'create' | 'edit'

interface ModalProps {
  title: string
  subtitle?: string
  actions?: ReactNode
  onClose: () => void
  children: ReactNode
}

const Modal = ({ title, subtitle, actions, onClose, children }: ModalProps) => (
  <div className="modal-backdrop" role="dialog" aria-modal="true">
    <div className="modal-card">
      <div className="modal__header">
        <div>
          {subtitle && <p className="eyebrow">{subtitle}</p>}
          <h3>{title}</h3>
        </div>
        <button className="btn btn-ghost" onClick={onClose}>Cerrar</button>
      </div>
      <div className="modal__body">{children}</div>
      {actions && <div className="modal__footer">{actions}</div>}
    </div>
  </div>
)

const newUserForm = () => ({
  id: null as number | null,
  correo: '',
  nombres: '',
  apellidos: '',
  telefono: '',
  direccion: '',
  contrasena: '',
  estado: true,
  roleIds: [] as number[],
})

const newRolForm = () => ({ id: null as number | null, nombre_rol: '', descripcion: '' })

const newCursoForm = () => ({ id: null as number | null, nombre_curso: '', nivel: '', estado: true })

const newAsigForm = () => ({ id: null as number | null, nombre_asignatura: '', descripcion: '' })

const newDfForm = () => ({
  id: null as number | null,
  id_estudiante: null as number | null,
  id_representante_user: null as number | null,
  nombre_padre: '',
  telefono_padre: '',
  nombre_madre: '',
  telefono_madre: '',
  direccion_familiar: '',
  parentesco_representante: '',
  nombre_representante: '',
  ocupacion_representante: '',
  telefono_representante: '',
})

const newUserRoleForm = () => ({ id: null as number | null, id_user: null as number | null, id_rol: null as number | null })

const todayStr = () => new Date().toISOString().slice(0, 10)
const newMatriForm = () => ({
  id: null as number | null,
  id_estudiante: null as number | null,
  id_curso: null as number | null,
  id_periodo: null as number | null,
  fecha_matricula: todayStr(),
  estado: true,
})

const newCursoAsigForm = () => ({
  id: null as number | null,
  id_curso: null as number | null,
  id_asignatura: null as number | null,
  id_docente: null as number | null,
})

const newPeriodoForm = () => ({
  id: null as number | null,
  descripcion: '',
  fecha_inicio: todayStr(),
  fecha_fin: todayStr(),
  estado: true,
})

export const AdminPanelPage = () => {
  const { user } = useAuth()
  const [active, setActive] = useState<TabKey>('resumen')
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [usuarios, setUsuarios] = useState<UserItem[]>([])
  const [roles, setRoles] = useState<RolItem[]>([])
  const [cursos, setCursos] = useState<CursoItem[]>([])
  const [asignaturas, setAsignaturas] = useState<AsignaturaItem[]>([])
  const [matriculas, setMatriculas] = useState<MatriculaItem[]>([])
  const [periodos, setPeriodos] = useState<PeriodoItem[]>([])
  const [familiares, setFamiliares] = useState<DatosFamiliaresItem[]>([])
  const [userRoles, setUserRoles] = useState<UserRoleItem[]>([])
  const [cursoAsignaturas, setCursoAsignaturas] = useState<CursoAsignaturaItem[]>([])

  const [modal, setModal] = useState<{ type: ModalType; mode: ModalMode } | null>(null)

  // Forms
  const [userForm, setUserForm] = useState(newUserForm)
  const [rolForm, setRolForm] = useState(newRolForm)
  const [cursoForm, setCursoForm] = useState(newCursoForm)
  const [asigForm, setAsigForm] = useState(newAsigForm)
  const [dfForm, setDfForm] = useState(newDfForm)
  const [userRoleForm, setUserRoleForm] = useState(newUserRoleForm)
  const [matriForm, setMatriForm] = useState(newMatriForm)
  const [matriAsignaturaIds, setMatriAsignaturaIds] = useState<number[]>([])
  const [caForm, setCaForm] = useState(newCursoAsigForm)
  const [periodoForm, setPeriodoForm] = useState(newPeriodoForm)
  const [cursoAsignaturasForm, setCursoAsignaturasForm] = useState<{ id_curso: number | null; asignaturaIds: number[] }>({ id_curso: null, asignaturaIds: [] })
  // Reset selección de asignaturas cuando cambia el curso en la matrícula
  useEffect(() => {
    setMatriAsignaturaIds([])
  }, [matriForm.id_curso])

  // Filtros para asignaciones
  const [asignacionSubtab, setAsignacionSubtab] = useState<'docentes' | 'estudiantes'>('docentes')
  const [filtroAsignacionCurso, setFiltroAsignacionCurso] = useState<number | null>(null)
  const [filtroAsignacionAsignatura, setFiltroAsignacionAsignatura] = useState<number | null>(null)

  const fetchAll = async () => {
    setLoading(true)
    setError(null)
    try {
      const [u, r, c, a, m, p, df, ur, ca] = await Promise.all([
        api.get<UserItem[]>('/api/usuarios'),
        api.get<RolItem[]>('/api/roles'),
        api.get<CursoItem[]>('/api/cursos'),
        api.get<AsignaturaItem[]>('/api/asignaturas'),
        api.get<MatriculaItem[]>('/api/matriculas'),
        api.get<PeriodoItem[]>('/api/periodos-lectivos'),
        api.get<DatosFamiliaresItem[]>('/api/datos-familiares'),
        api.get<UserRoleItem[]>('/api/user-roles'),
        api.get<CursoAsignaturaItem[]>('/api/curso-asignaturas'),
      ])
      setUsuarios(Array.isArray(u.data) ? u.data : [])
      setRoles(Array.isArray(r.data) ? r.data : [])
      setCursos(Array.isArray(c.data) ? c.data : [])
      setAsignaturas(Array.isArray(a.data) ? a.data : [])
      setMatriculas(Array.isArray(m.data) ? m.data : [])
      setPeriodos(Array.isArray(p.data) ? p.data : [])
      setFamiliares(Array.isArray(df.data) ? df.data : [])
      setUserRoles(Array.isArray(ur.data) ? ur.data : [])
      setCursoAsignaturas(Array.isArray(ca.data) ? ca.data : [])
    } catch (e: any) {
      setError(e?.response?.data?.error || 'No se pudo cargar el panel admin')
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    fetchAll()
  }, [])

  const closeModal = (type?: ModalType) => {
    const current = type ?? modal?.type
    setModal(null)
    if (current === 'usuario') setUserForm(newUserForm())
    if (current === 'rol') setRolForm(newRolForm())
    if (current === 'curso') setCursoForm(newCursoForm())
    if (current === 'asignatura') setAsigForm(newAsigForm())
    if (current === 'familiares') setDfForm(newDfForm())
    if (current === 'userRol') setUserRoleForm(newUserRoleForm())
    if (current === 'matricula') setMatriForm(newMatriForm())
    if (current === 'matricula') setMatriAsignaturaIds([])
    if (current === 'periodo') setPeriodoForm(newPeriodoForm())
    if (current === 'cursoAsignatura') setCaForm(newCursoAsigForm())
    if (current === 'cursoAsignaturas') setCursoAsignaturasForm({ id_curso: null, asignaturaIds: [] })
  }

  const openUserModal = (mode: ModalMode, data?: UserItem) => {
    setUserForm(
      data
        ? {
            id: data.id,
            correo: data.correo,
            nombres: data.nombres || '',
            apellidos: data.apellidos || '',
            telefono: data.telefono || '',
            direccion: data.direccion || '',
            contrasena: '',
            estado: data.estado ?? true,
            roleIds: data.roles?.map((r) => {
              const id = r.id_rol || r.id
              return id
            }).filter((id): id is number => id != null) || [],
          }
        : newUserForm(),
    )
    setModal({ type: 'usuario', mode })
  }

  const openRolModal = (mode: ModalMode, data?: RolItem) => {
    setRolForm(data ? { id: data.id_rol ?? null, nombre_rol: data.nombre_rol || '', descripcion: data.descripcion || '' } : newRolForm())
    setModal({ type: 'rol', mode })
  }

  const openCursoModal = (mode: ModalMode, data?: CursoItem) => {
    setCursoForm(
      data
        ? { id: data.id_curso, nombre_curso: data.nombre_curso, nivel: data.nivel || '', estado: data.estado ?? true }
        : newCursoForm(),
    )
    setModal({ type: 'curso', mode })
  }

  const openCursoAsignaturasModal = (curso: CursoItem) => {
    // Preseleccionar asignaturas ya vinculadas al curso
    const actuales = cursoAsignaturas
      .filter((ca) => ca.id_curso === curso.id_curso)
      .map((ca) => ca.id_asignatura)
    setCursoAsignaturasForm({ id_curso: curso.id_curso, asignaturaIds: actuales })
    setModal({ type: 'cursoAsignaturas', mode: 'edit' })
  }

  const openAsigModal = (mode: ModalMode, data?: AsignaturaItem) => {
    setAsigForm(data ? { id: data.id_asignatura, nombre_asignatura: data.nombre_asignatura, descripcion: data.descripcion || '' } : newAsigForm())
    setModal({ type: 'asignatura', mode })
  }

  const openDfModal = (mode: ModalMode, data?: DatosFamiliaresItem) => {
    setDfForm(
      data
        ? {
            id: data.id_datos_familiares,
            id_estudiante: data.id_estudiante ?? null,
            id_representante_user: data.id_representante_user ?? null,
            nombre_padre: data.nombre_padre || '',
            telefono_padre: data.telefono_padre || '',
            nombre_madre: data.nombre_madre || '',
            telefono_madre: data.telefono_madre || '',
            direccion_familiar: data.direccion_familiar || '',
            parentesco_representante: data.parentesco_representante || '',
            nombre_representante: data.nombre_representante || '',
            ocupacion_representante: data.ocupacion_representante || '',
            telefono_representante: data.telefono_representante || '',
          }
        : newDfForm(),
    )
    setModal({ type: 'familiares', mode })
  }

  const openUserRoleModal = (mode: ModalMode, data?: UserRoleItem) => {
    setUserRoleForm(data ? { id: data.id_user_rol ?? null, id_user: data.id_user ?? null, id_rol: data.id_rol ?? null } : newUserRoleForm())
    setModal({ type: 'userRol', mode })
  }

  const openMatriModal = (mode: ModalMode, data?: MatriculaItem) => {
    setMatriForm(
      data
        ? {
            id: data.id_matricula,
            id_estudiante: data.id_estudiante ?? null,
            id_curso: data.id_curso ?? null,
            id_periodo: data.id_periodo ?? null,
            fecha_matricula: data.fecha_matricula || todayStr(),
            estado: data.estado ?? true,
          }
        : newMatriForm(),
    )
    // Preload asignaturas seleccionadas si estamos editando
    if (mode === 'edit' && data?.id_matricula) {
      api.get(`/api/matricula-asignaturas`, { params: { matricula_id: data.id_matricula } })
        .then((resp) => {
          const ids = Array.isArray(resp.data)
            ? resp.data.map((x: any) => x.id_curso_asignatura).filter((v: any) => typeof v === 'number')
            : []
          setMatriAsignaturaIds(ids)
        })
        .catch(() => setMatriAsignaturaIds([]))
    } else {
      setMatriAsignaturaIds([])
    }
    setModal({ type: 'matricula', mode })
  }

  const openCaModal = (mode: ModalMode, data?: CursoAsignaturaItem) => {
    setCaForm(
      data
        ? {
            id: data.id_curso_asignatura,
            id_curso: data.id_curso,
            id_asignatura: data.id_asignatura,
            id_docente: data.id_docente ?? null,
          }
        : newCursoAsigForm(),
    )
    setModal({ type: 'cursoAsignatura', mode })
  }

  const resume = useMemo(
    () => [
      { label: 'Usuarios', value: usuarios.length },
      { label: 'Roles', value: roles.length },
      { label: 'Cursos', value: cursos.length },
      { label: 'Asignaturas', value: asignaturas.length },
      { label: 'Matrículas', value: matriculas.length },
    ],
    [usuarios.length, roles.length, cursos.length, asignaturas.length, matriculas.length],
  )

  // Listas filtradas para asignaciones
  const docentesCurso = useMemo(() => {
    if (!filtroAsignacionCurso) return []
    const casDelCurso = cursoAsignaturas.filter((ca) => ca.id_curso === filtroAsignacionCurso)
    const idsDocentes = new Set(casDelCurso.filter((ca) => ca.id_docente).map((ca) => ca.id_docente))
    return usuarios.filter((u) => u.roles?.some((r) => r.nombre_rol?.toLowerCase() === 'docente') && idsDocentes.has(u.id))
  }, [cursoAsignaturas, filtroAsignacionCurso, usuarios])

  const docentesSinAsignarCurso = useMemo(() => {
    const todosDocentes = usuarios.filter((u) => u.roles?.some((r) => r.nombre_rol?.toLowerCase() === 'docente'))
    const idsAsignados = new Set(docentesCurso.map((d) => d.id))
    return todosDocentes.filter((d) => !idsAsignados.has(d.id))
  }, [docentesCurso, usuarios])

  const asignaturasDelCurso = useMemo(() => {
    if (!filtroAsignacionCurso) return []
    return cursoAsignaturas
      .filter((ca) => ca.id_curso === filtroAsignacionCurso)
      .map((ca) => ({ id: ca.id_asignatura, nombre: ca.asignatura }))
      .filter((v, i, a) => a.findIndex((x) => x.id === v.id) === i)
  }, [cursoAsignaturas, filtroAsignacionCurso])

  const estudiantesCurso = useMemo(() => {
    if (!filtroAsignacionCurso) return []
    return matriculas.filter((m) => m.id_curso === filtroAsignacionCurso && m.estado)
  }, [matriculas, filtroAsignacionCurso])

  const estudiantesAsignatura = useMemo(() => {
    if (!filtroAsignacionCurso || !filtroAsignacionAsignatura) return []
    const caId = cursoAsignaturas.find((ca) => ca.id_curso === filtroAsignacionCurso && ca.id_asignatura === filtroAsignacionAsignatura)?.id_curso_asignatura
    if (!caId) return []
    return estudiantesCurso
  }, [filtroAsignacionCurso, filtroAsignacionAsignatura, cursoAsignaturas, estudiantesCurso])

  // Filtros para usuarios por rol
  const usuariosEstudiantes = useMemo(() => {
    return usuarios.filter((u) => u.roles?.some((r) => r.nombre_rol?.toLowerCase() === 'estudiante'))
  }, [usuarios])

  const usuariosPadres = useMemo(() => {
    return usuarios.filter((u) => u.roles?.some((r) => r.nombre_rol?.toLowerCase() === 'padre'))
  }, [usuarios])

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

  const saveUser = async () => {
    const payload: any = {
      correo: userForm.correo,
      nombres: userForm.nombres,
      apellidos: userForm.apellidos,
      telefono: userForm.telefono,
      direccion: userForm.direccion,
      estado: userForm.estado,
    }
    // Solo incluir contraseña si se proporciona
    if (userForm.contrasena) {
      payload.contrasena = userForm.contrasena
    }
    let userId: number
    if (userForm.id) {
      await api.put(`/api/usuarios/${userForm.id}`, payload)
      userId = userForm.id
    } else {
      const resp = await api.post<{ id: number }>('/api/usuarios', payload)
      userId = resp.data.id
    }

    // Sync roles: get current, delete removed, create new
    const currentRoles = (await api.get<UserRoleItem[]>('/api/user-roles')).data.filter((ur) => ur.id_user === userId)
    const currentRoleIds = currentRoles.map((ur) => ur.id_rol).filter((id): id is number => id != null)
    const toDelete = currentRoles.filter((ur) => !userForm.roleIds.includes(ur.id_rol!))
    const toAdd = userForm.roleIds.filter((rid) => !currentRoleIds.includes(rid))

    await Promise.all(toDelete.map((ur) => api.delete(`/api/user-roles/${ur.id_user_rol}`)))
    await Promise.all(toAdd.map((rid) => api.post('/api/user-roles', { id_user: userId, id_rol: rid })))

    closeModal('usuario')
    fetchAll()
  }

  const deleteUser = async (id: number) => {
    await api.delete(`/api/usuarios/${id}`)
    fetchAll()
  }

  const saveRol = async () => {
    const payload = { nombre_rol: rolForm.nombre_rol, descripcion: rolForm.descripcion }
    if (rolForm.id) {
      await api.put(`/api/roles/${rolForm.id}`, payload)
    } else {
      await api.post('/api/roles', payload)
    }
    closeModal('rol')
    fetchAll()
  }

  const deleteRol = async (id?: number) => {
    if (!id) return
    await api.delete(`/api/roles/${id}`)
    fetchAll()
  }

  const saveCurso = async () => {
    const payload = { nombre_curso: cursoForm.nombre_curso, nivel: cursoForm.nivel, estado: cursoForm.estado }
    if (cursoForm.id) {
      await api.put(`/api/cursos/${cursoForm.id}`, payload)
    } else {
      await api.post('/api/cursos', payload)
    }
    closeModal('curso')
    fetchAll()
  }

  const deleteCurso = async (id: number) => {
    await api.delete(`/api/cursos/${id}`)
    fetchAll()
  }

  const saveCursoAsignaturasBulk = async () => {
    if (!cursoAsignaturasForm.id_curso) return
    const cursoId = cursoAsignaturasForm.id_curso
    const asignaturaIds = cursoAsignaturasForm.asignaturaIds
    await api.post(`/api/cursos/${cursoId}/asignaturas-bulk`, { asignatura_ids: asignaturaIds })
    closeModal('cursoAsignaturas')
    fetchAll()
  }

  const saveAsignatura = async () => {
    const payload = { nombre_asignatura: asigForm.nombre_asignatura, descripcion: asigForm.descripcion }
    if (asigForm.id) {
      await api.put(`/api/asignaturas/${asigForm.id}`, payload)
    } else {
      await api.post('/api/asignaturas', payload)
    }
    closeModal('asignatura')
    fetchAll()
  }

  const deleteAsignatura = async (id: number) => {
    await api.delete(`/api/asignaturas/${id}`)
    fetchAll()
  }

  const saveDatosFamiliares = async () => {
    const payload = {
      id_estudiante: dfForm.id_estudiante,
      id_representante_user: dfForm.id_representante_user || null,
      nombre_padre: dfForm.nombre_padre,
      telefono_padre: dfForm.telefono_padre || null,
      nombre_madre: dfForm.nombre_madre,
      telefono_madre: dfForm.telefono_madre || null,
      direccion_familiar: dfForm.direccion_familiar || null,
      parentesco_representante: dfForm.parentesco_representante || null,
      nombre_representante: dfForm.nombre_representante || null,
      ocupacion_representante: dfForm.ocupacion_representante || null,
      telefono_representante: dfForm.telefono_representante || null,
    }
    if (dfForm.id) {
      await api.put(`/api/datos-familiares/${dfForm.id}`, payload)
    } else {
      await api.post('/api/datos-familiares', payload)
    }
    closeModal('familiares')
    fetchAll()
  }

  const deleteDatosFamiliares = async (id: number) => {
    await api.delete(`/api/datos-familiares/${id}`)
    fetchAll()
  }

  const saveUserRole = async () => {
    const payload = { id_user: userRoleForm.id_user, id_rol: userRoleForm.id_rol }
    if (userRoleForm.id) {
      await api.put(`/api/user-roles/${userRoleForm.id}`, payload)
    } else {
      await api.post('/api/user-roles', payload)
    }
    closeModal('userRol')
    fetchAll()
  }

  const deleteUserRole = async (id?: number) => {
    if (!id) return
    await api.delete(`/api/user-roles/${id}`)
    fetchAll()
  }

  const savePeriodo = async () => {
    const payload = {
      descripcion: periodoForm.descripcion,
      fecha_inicio: periodoForm.fecha_inicio,
      fecha_fin: periodoForm.fecha_fin,
      estado: periodoForm.estado,
    }
    if (periodoForm.id) {
      await api.put(`/api/periodos-lectivos/${periodoForm.id}`, payload)
    } else {
      await api.post('/api/periodos-lectivos', payload)
    }
    closeModal('periodo')
    fetchAll()
  }

  const deletePeriodo = async (id: number) => {
    await api.delete(`/api/periodos-lectivos/${id}`)
    fetchAll()
  }

  const saveMatricula = async () => {
    const payload = {
      id_estudiante: matriForm.id_estudiante,
      id_curso: matriForm.id_curso,
      id_periodo: matriForm.id_periodo,
      fecha_matricula: matriForm.fecha_matricula,
      estado: matriForm.estado,
    }
    let matriculaId = matriForm.id || null
    if (matriForm.id) {
      const resp = await api.put(`/api/matriculas/${matriForm.id}`, payload)
      matriculaId = resp.data?.id_matricula ?? matriForm.id
    } else {
      const resp = await api.post('/api/matriculas', payload)
      matriculaId = resp.data?.id_matricula ?? null
    }

    // Persistir asignaturas seleccionadas (solo las del curso elegido)
    if (matriculaId && matriForm.id_curso) {
      const caIdsDelCurso = new Set(
        cursoAsignaturas.filter((ca) => ca.id_curso === matriForm.id_curso).map((ca) => ca.id_curso_asignatura),
      )
      const seleccionValidada = matriAsignaturaIds.filter((id) => caIdsDelCurso.has(id))
      await api.post('/api/matricula-asignaturas/bulk', {
        matricula_id: matriculaId,
        curso_asignatura_ids: seleccionValidada,
      })
    }
    closeModal('matricula')
    fetchAll()
  }

  const deleteMatricula = async (id: number) => {
    await api.delete(`/api/matriculas/${id}`)
    fetchAll()
  }

  const saveCursoAsignatura = async () => {
    const payload = {
      id_curso: caForm.id_curso,
      id_asignatura: caForm.id_asignatura,
      id_docente: caForm.id_docente,
    }
    if (caForm.id) {
      await api.put(`/api/curso-asignaturas/${caForm.id}`, payload)
    } else {
      await api.post('/api/curso-asignaturas', payload)
    }
    closeModal('cursoAsignatura')
    fetchAll()
  }

  const deleteCursoAsignatura = async (id: number) => {
    await api.delete(`/api/curso-asignaturas/${id}`)
    fetchAll()
  }

  const renderModal = () => {
    if (!modal) return null
    const isEdit = modal.mode === 'edit'

    if (modal.type === 'usuario') {
      return (
        <Modal
          title={isEdit ? 'Editar usuario' : 'Nuevo usuario'}
          subtitle="Credenciales y datos básicos"
          onClose={() => closeModal()}
          actions={(
            <>
              <button className="btn btn-ghost" onClick={() => closeModal()}>Cancelar</button>
              <button className="btn btn-primary" onClick={saveUser}>{isEdit ? 'Guardar cambios' : 'Crear usuario'}</button>
            </>
          )}
        >
          <div className="form-grid">
            <label className="field">
              <span>Correo</span>
              <input placeholder="correo@colegio.com" value={userForm.correo} onChange={(e) => setUserForm((f) => ({ ...f, correo: e.target.value }))} />
            </label>
            <label className="field">
              <span>Nombres</span>
              <input placeholder="Nombres" value={userForm.nombres} onChange={(e) => setUserForm((f) => ({ ...f, nombres: e.target.value }))} />
            </label>
            <label className="field">
              <span>Apellidos</span>
              <input placeholder="Apellidos" value={userForm.apellidos} onChange={(e) => setUserForm((f) => ({ ...f, apellidos: e.target.value }))} />
            </label>
            <label className="field">
              <span>Teléfono</span>
              <input placeholder="Teléfono" value={userForm.telefono} onChange={(e) => setUserForm((f) => ({ ...f, telefono: e.target.value }))} />
            </label>
            <label className="field">
              <span>Dirección</span>
              <input placeholder="Dirección" value={userForm.direccion} onChange={(e) => setUserForm((f) => ({ ...f, direccion: e.target.value }))} />
            </label>
            <label className="field">
              <span>Contraseña</span>
              <input placeholder="Contraseña" type="password" value={userForm.contrasena} onChange={(e) => setUserForm((f) => ({ ...f, contrasena: e.target.value }))} />
              {isEdit && <small>Déjala vacía si no deseas cambiarla.</small>}
            </label>
            <label className="field" style={{ gridColumn: '1 / -1' }}>
              <span>Roles del usuario</span>
              <div style={{ display: 'flex', gap: '0.5rem', flexWrap: 'wrap', marginTop: '0.4rem' }}>
                {roles && roles.filter(r => r.id_rol).map((r) => {
                  const rolId = r.id_rol!
                  return (
                    <label key={rolId} className="checkbox-line" style={{ display: 'inline-flex', padding: '0.5rem 0.75rem' }}>
                      <input
                        type="checkbox"
                        checked={userForm.roleIds.includes(rolId)}
                        onChange={(e) => {
                          if (e.target.checked) {
                            setUserForm((f) => ({ ...f, roleIds: [...f.roleIds, rolId] }))
                          } else {
                            setUserForm((f) => ({ ...f, roleIds: f.roleIds.filter((id) => id !== rolId) }))
                          }
                        }}
                      />
                      {r.nombre_rol}
                    </label>
                  )
                })}
              </div>
              <small>Selecciona uno o varios roles para este usuario.</small>
            </label>
            <label className="checkbox-line" style={{ gridColumn: '1 / -1' }}>
              <input type="checkbox" checked={userForm.estado} onChange={(e) => setUserForm((f) => ({ ...f, estado: e.target.checked }))} /> Activo
            </label>
          </div>
        </Modal>
      )
    }

    if (modal.type === 'rol') {
      return (
        <Modal
          title={isEdit ? 'Editar rol' : 'Nuevo rol'}
          subtitle="Permisos y alcance"
          onClose={() => closeModal()}
          actions={(
            <>
              <button className="btn btn-ghost" onClick={() => closeModal()}>Cancelar</button>
              <button className="btn btn-primary" onClick={saveRol}>{isEdit ? 'Guardar cambios' : 'Crear rol'}</button>
            </>
          )}
        >
          <div className="form-grid">
            <label className="field">
              <span>Nombre de rol</span>
              <input placeholder="Admin, Docente..." value={rolForm.nombre_rol} onChange={(e) => setRolForm((f) => ({ ...f, nombre_rol: e.target.value }))} />
            </label>
            <label className="field">
              <span>Descripción</span>
              <input placeholder="Accesos y límites" value={rolForm.descripcion} onChange={(e) => setRolForm((f) => ({ ...f, descripcion: e.target.value }))} />
            </label>
          </div>
        </Modal>
      )
    }

    if (modal.type === 'curso') {
      return (
        <Modal
          title={isEdit ? 'Editar curso' : 'Nuevo curso'}
          subtitle="Oferta académica"
          onClose={() => closeModal()}
          actions={(
            <>
              <button className="btn btn-ghost" onClick={() => closeModal()}>Cancelar</button>
              <button className="btn btn-primary" onClick={saveCurso}>{isEdit ? 'Guardar cambios' : 'Crear curso'}</button>
            </>
          )}
        >
          <div className="form-grid">
            <label className="field">
              <span>Nombre del curso</span>
              <input placeholder="Ej. 3ro de Básica" value={cursoForm.nombre_curso} onChange={(e) => setCursoForm((f) => ({ ...f, nombre_curso: e.target.value }))} />
            </label>
            <label className="field">
              <span>Nivel</span>
              <input placeholder="Inicial, Medio..." value={cursoForm.nivel} onChange={(e) => setCursoForm((f) => ({ ...f, nivel: e.target.value }))} />
            </label>
            <label className="checkbox-line" style={{ gridColumn: '1 / -1' }}>
              <input type="checkbox" checked={cursoForm.estado} onChange={(e) => setCursoForm((f) => ({ ...f, estado: e.target.checked }))} /> Activo
            </label>
          </div>
        </Modal>
      )
    }

    if (modal.type === 'cursoAsignaturas') {
      const cursoSel = cursos.find((c) => c.id_curso === cursoAsignaturasForm.id_curso)
      return (
        <Modal
          title={`Asignaturas del curso${cursoSel ? ` · ${cursoSel.nombre_curso}` : ''}`}
          subtitle="Selecciona las asignaturas que pertenecerán a este curso"
          onClose={() => closeModal()}
          actions={(
            <>
              <button className="btn btn-ghost" onClick={() => closeModal()}>Cancelar</button>
              <button className="btn btn-primary" onClick={saveCursoAsignaturasBulk}>Guardar</button>
            </>
          )}
        >
          <div className="form-grid">
            <div className="field" style={{ gridColumn: '1 / -1' }}>
              <span>Asignaturas disponibles</span>
              <div style={{ display: 'flex', gap: '0.5rem', flexWrap: 'wrap', marginTop: '0.4rem' }}>
                {asignaturas.length === 0 ? (
                  <span className="muted">No hay asignaturas creadas</span>
                ) : (
                  asignaturas.map((a) => (
                    <label key={`cbulk-a-${a.id_asignatura}`} className="checkbox-line" style={{ display: 'inline-flex', padding: '0.5rem 0.75rem' }}>
                      <input
                        type="checkbox"
                        checked={cursoAsignaturasForm.asignaturaIds.includes(a.id_asignatura)}
                        onChange={(e) => {
                          if (e.target.checked) {
                            setCursoAsignaturasForm((f) => ({ ...f, asignaturaIds: [...f.asignaturaIds, a.id_asignatura] }))
                          } else {
                            setCursoAsignaturasForm((f) => ({ ...f, asignaturaIds: f.asignaturaIds.filter((id) => id !== a.id_asignatura) }))
                          }
                        }}
                      />
                      {a.nombre_asignatura}
                    </label>
                  ))
                )}
              </div>
              <small>Estas asignaturas se usarán para asignación automática en las matrículas del curso.</small>
            </div>
          </div>
        </Modal>
      )
    }

    if (modal.type === 'asignatura') {
      return (
        <Modal
          title={isEdit ? 'Editar asignatura' : 'Nueva asignatura'}
          subtitle="Catálogo académico"
          onClose={() => closeModal()}
          actions={(
            <>
              <button className="btn btn-ghost" onClick={() => closeModal()}>Cancelar</button>
              <button className="btn btn-primary" onClick={saveAsignatura}>{isEdit ? 'Guardar cambios' : 'Crear asignatura'}</button>
            </>
          )}
        >
          <div className="form-grid">
            <label className="field">
              <span>Nombre</span>
              <input placeholder="Matemática, Historia..." value={asigForm.nombre_asignatura} onChange={(e) => setAsigForm((f) => ({ ...f, nombre_asignatura: e.target.value }))} />
            </label>
            <label className="field">
              <span>Descripción</span>
              <input placeholder="Detalle breve" value={asigForm.descripcion} onChange={(e) => setAsigForm((f) => ({ ...f, descripcion: e.target.value }))} />
            </label>
          </div>
        </Modal>
      )
    }

    if (modal.type === 'familiares') {
      return (
        <Modal
          title={isEdit ? 'Editar datos familiares' : 'Nuevo vínculo familiar'}
          subtitle="Contactos y representante"
          onClose={() => closeModal()}
          actions={(
            <>
              <button className="btn btn-ghost" onClick={() => closeModal()}>Cancelar</button>
              <button className="btn btn-primary" onClick={saveDatosFamiliares}>{isEdit ? 'Guardar cambios' : 'Crear registro'}</button>
            </>
          )}
        >
          <div className="form-grid">
            <label className="field">
              <span>Estudiante</span>
              <select value={dfForm.id_estudiante ?? ''} onChange={(e) => setDfForm((f) => ({ ...f, id_estudiante: Number(e.target.value) || null }))}>
                <option value="">Selecciona estudiante</option>
                {usuariosEstudiantes.map((u) => (
                  <option key={u.id} value={u.id}>{`${u.nombres || ''} ${u.apellidos || ''}`.trim() || u.correo}</option>
                ))}
              </select>
            </label>
            <label className="field">
              <span>Usuario representante (opcional)</span>
              <select value={dfForm.id_representante_user ?? ''} onChange={(e) => setDfForm((f) => ({ ...f, id_representante_user: e.target.value ? Number(e.target.value) : null }))}>
                <option value="">Sin vincular</option>
                {usuariosPadres.map((u) => (
                  <option key={`rep-${u.id}`} value={u.id}>{u.correo} · {`${u.nombres || ''} ${u.apellidos || ''}`.trim()}</option>
                ))}
              </select>
            </label>
            <label className="field">
              <span>Nombre del padre</span>
              <input value={dfForm.nombre_padre} onChange={(e) => setDfForm((f) => ({ ...f, nombre_padre: e.target.value }))} />
            </label>
            <label className="field">
              <span>Teléfono del padre</span>
              <input value={dfForm.telefono_padre} onChange={(e) => setDfForm((f) => ({ ...f, telefono_padre: e.target.value }))} />
            </label>
            <label className="field">
              <span>Nombre de la madre</span>
              <input value={dfForm.nombre_madre} onChange={(e) => setDfForm((f) => ({ ...f, nombre_madre: e.target.value }))} />
            </label>
            <label className="field">
              <span>Teléfono de la madre</span>
              <input value={dfForm.telefono_madre} onChange={(e) => setDfForm((f) => ({ ...f, telefono_madre: e.target.value }))} />
            </label>
            <label className="field">
              <span>Dirección familiar</span>
              <input value={dfForm.direccion_familiar} onChange={(e) => setDfForm((f) => ({ ...f, direccion_familiar: e.target.value }))} />
            </label>
            <label className="field">
              <span>Parentesco del representante</span>
              <input value={dfForm.parentesco_representante} onChange={(e) => setDfForm((f) => ({ ...f, parentesco_representante: e.target.value }))} />
            </label>
            <label className="field">
              <span>Nombre del representante</span>
              <input value={dfForm.nombre_representante} onChange={(e) => setDfForm((f) => ({ ...f, nombre_representante: e.target.value }))} />
            </label>
            <label className="field">
              <span>Ocupación del representante</span>
              <input value={dfForm.ocupacion_representante} onChange={(e) => setDfForm((f) => ({ ...f, ocupacion_representante: e.target.value }))} />
            </label>
            <label className="field">
              <span>Teléfono del representante</span>
              <input value={dfForm.telefono_representante} onChange={(e) => setDfForm((f) => ({ ...f, telefono_representante: e.target.value }))} />
            </label>
          </div>
        </Modal>
      )
    }

    if (modal.type === 'userRol') {
      return (
        <Modal
          title={isEdit ? 'Editar rol de usuario' : 'Asignar rol a usuario'}
          subtitle="Control de acceso"
          onClose={() => closeModal()}
          actions={(
            <>
              <button className="btn btn-ghost" onClick={() => closeModal()}>Cancelar</button>
              <button className="btn btn-primary" onClick={saveUserRole}>{isEdit ? 'Guardar cambios' : 'Asignar rol'}</button>
            </>
          )}
        >
          <div className="form-grid">
            <label className="field">
              <span>Usuario</span>
              <select value={userRoleForm.id_user ?? ''} onChange={(e) => setUserRoleForm((f) => ({ ...f, id_user: Number(e.target.value) || null }))}>
                <option value="">Selecciona usuario</option>
                {usuarios.map((u) => (
                  <option key={`ur-u-${u.id}`} value={u.id}>{`${u.nombres || ''} ${u.apellidos || ''}`.trim() || u.correo}</option>
                ))}
              </select>
            </label>
            <label className="field">
              <span>Rol</span>
              <select value={userRoleForm.id_rol ?? ''} onChange={(e) => setUserRoleForm((f) => ({ ...f, id_rol: Number(e.target.value) || null }))}>
                <option value="">Selecciona rol</option>
                {roles && roles.length > 0 && roles.map((r, idx) => (
                  <option key={`${idx}-${r.id_rol}`} value={r.id_rol}>{r.nombre_rol}</option>
                ))}
              </select>
            </label>
          </div>
        </Modal>
      )
    }

    if (modal.type === 'matricula') {
      const handlePeriodoChange = (e: any) => {
        const val = e.target.value
        const numVal = val === '' ? null : parseInt(val, 10)
        setMatriForm((f) => ({ ...f, id_periodo: numVal }))
      }
      const opcionesAsignaturasCurso = matriForm.id_curso
        ? cursoAsignaturas.filter((ca) => ca.id_curso === matriForm.id_curso)
        : []
      return (
        <Modal
          title={isEdit ? 'Editar matrícula' : 'Nueva matrícula'}
          subtitle="Asignación de curso y periodo"
          onClose={() => closeModal()}
          actions={(
            <>
              <button className="btn btn-ghost" onClick={() => closeModal()}>Cancelar</button>
              <button className="btn btn-primary" onClick={saveMatricula}>{isEdit ? 'Guardar cambios' : 'Crear matrícula'}</button>
            </>
          )}
        >
          <div className="form-grid">
            <label className="field">
              <span>Estudiante</span>
              <select value={matriForm.id_estudiante ?? ''} onChange={(e) => setMatriForm((f) => ({ ...f, id_estudiante: Number(e.target.value) || null }))}>
                <option value="">Selecciona estudiante</option>
                {usuariosEstudiantes.map((u) => (
                  <option key={`mat-u-${u.id}`} value={u.id}>{`${u.nombres || ''} ${u.apellidos || ''}`.trim() || u.correo}</option>
                ))}
              </select>
            </label>
            <label className="field">
              <span>Curso</span>
              <select value={matriForm.id_curso ?? ''} onChange={(e) => setMatriForm((f) => ({ ...f, id_curso: Number(e.target.value) || null }))}>
                <option value="">Selecciona curso</option>
                {cursos.map((c) => (
                  <option key={`mat-c-${c.id_curso}`} value={c.id_curso}>{c.nombre_curso}</option>
                ))}
              </select>
            </label>
            <label className="field">
              <span>Periodo lectivo</span>
              <select value={matriForm.id_periodo ?? ''} onChange={handlePeriodoChange}>
                <option value="">Selecciona periodo</option>
                {periodos && periodos.length > 0 ? periodos.map((p) => (
                  <option key={`mat-p-${p.id_periodo}`} value={p.id_periodo}>{p.descripcion}</option>
                )) : <option disabled>Cargando períodos...</option>}
              </select>
            </label>
            <label className="field">
              <span>Fecha de matrícula</span>
              <input type="date" value={matriForm.fecha_matricula} onChange={(e) => setMatriForm((f) => ({ ...f, fecha_matricula: e.target.value }))} />
            </label>
            <label className="checkbox-line" style={{ gridColumn: '1 / -1' }}>
              <input type="checkbox" checked={matriForm.estado} onChange={(e) => setMatriForm((f) => ({ ...f, estado: e.target.checked }))} /> Activa
            </label>

            {matriForm.id_curso && (
              <div className="field" style={{ gridColumn: '1 / -1' }}>
                <span>Asignaturas del curso</span>
                <div style={{ display: 'flex', gap: '0.5rem', flexWrap: 'wrap', marginTop: '0.4rem' }}>
                  {opcionesAsignaturasCurso.length === 0 ? (
                    <span className="muted">Este curso no tiene asignaturas configuradas</span>
                  ) : (
                    opcionesAsignaturasCurso.map((ca) => (
                      <label key={`mat-ca-${ca.id_curso_asignatura}`} className="checkbox-line" style={{ display: 'inline-flex', padding: '0.5rem 0.75rem' }}>
                        <input
                          type="checkbox"
                          checked={matriAsignaturaIds.includes(ca.id_curso_asignatura)}
                          onChange={(e) => {
                            if (e.target.checked) {
                              setMatriAsignaturaIds((ids) => [...ids, ca.id_curso_asignatura])
                            } else {
                              setMatriAsignaturaIds((ids) => ids.filter((id) => id !== ca.id_curso_asignatura))
                            }
                          }}
                        />
                        {ca.asignatura}{ca.docente ? ` · ${ca.docente}` : ''}
                      </label>
                    ))
                  )}
                </div>
                <small>Selecciona las asignaturas que cursará este estudiante.</small>
              </div>
            )}
          </div>
        </Modal>
      )
    }

    if (modal.type === 'cursoAsignatura') {
      const docentes = usuarios.filter((u) => u.roles?.some((r) => r.nombre_rol?.toLowerCase() === 'docente'))
      return (
        <Modal
          title={isEdit ? 'Editar asignación' : 'Nueva asignación'}
          subtitle="Asignar docente, curso y asignatura"
          onClose={() => closeModal()}
          actions={(
            <>
              <button className="btn btn-ghost" onClick={() => closeModal()}>Cancelar</button>
              <button className="btn btn-primary" onClick={saveCursoAsignatura}>{isEdit ? 'Guardar cambios' : 'Crear asignación'}</button>
            </>
          )}
        >
          <div className="form-grid">
            <label className="field">
              <span>Curso</span>
              <select value={caForm.id_curso ?? ''} onChange={(e) => setCaForm((f) => ({ ...f, id_curso: Number(e.target.value) || null }))}>
                <option value="">Selecciona curso</option>
                {cursos.map((c) => (
                  <option key={`ca-c-${c.id_curso}`} value={c.id_curso}>{c.nombre_curso}</option>
                ))}
              </select>
            </label>
            <label className="field">
              <span>Asignatura</span>
              <select value={caForm.id_asignatura ?? ''} onChange={(e) => setCaForm((f) => ({ ...f, id_asignatura: Number(e.target.value) || null }))}>
                <option value="">Selecciona asignatura</option>
                {asignaturas.map((a) => (
                  <option key={`ca-a-${a.id_asignatura}`} value={a.id_asignatura}>{a.nombre_asignatura}</option>
                ))}
              </select>
            </label>
            <label className="field" style={{ gridColumn: '1 / -1' }}>
              <span>Docente</span>
              <select value={caForm.id_docente ?? ''} onChange={(e) => setCaForm((f) => ({ ...f, id_docente: Number(e.target.value) || null }))}>
                <option value="">Selecciona docente</option>
                {docentes.map((d) => (
                  <option key={`ca-d-${d.id}`} value={d.id}>{`${d.nombres || ''} ${d.apellidos || ''}`.trim() || d.correo}</option>
                ))}
              </select>
            </label>
          </div>
        </Modal>
      )
    }

    if (modal.type === 'periodo') {
      const isEdit = modal.mode === 'edit'
      return (
        <Modal
          title={isEdit ? 'Editar período lectivo' : 'Nuevo período lectivo'}
          subtitle="Gestión de períodos académicos"
          onClose={() => closeModal()}
          actions={(
            <>
              <button className="btn btn-ghost" onClick={() => closeModal()}>Cancelar</button>
              <button className="btn btn-primary" onClick={savePeriodo}>{isEdit ? 'Guardar cambios' : 'Crear período'}</button>
            </>
          )}
        >
          <div className="form-grid">
            <label className="field">
              <span>Descripción</span>
              <input value={periodoForm.descripcion} onChange={(e) => setPeriodoForm((f) => ({ ...f, descripcion: e.target.value }))} placeholder="Ej: 2024-2025" />
            </label>
            <label className="field">
              <span>Fecha inicio</span>
              <input type="date" value={periodoForm.fecha_inicio} onChange={(e) => setPeriodoForm((f) => ({ ...f, fecha_inicio: e.target.value }))} />
            </label>
            <label className="field">
              <span>Fecha fin</span>
              <input type="date" value={periodoForm.fecha_fin} onChange={(e) => setPeriodoForm((f) => ({ ...f, fecha_fin: e.target.value }))} />
            </label>
            <label className="checkbox-line" style={{ gridColumn: '1 / -1' }}>
              <input type="checkbox" checked={periodoForm.estado} onChange={(e) => setPeriodoForm((f) => ({ ...f, estado: e.target.checked }))} /> Activo
            </label>
          </div>
        </Modal>
      )
    }

    return null
  }

  return (
    <div className="page" style={{ gap: '1.25rem' }}>
      <div className="card" style={{ display: 'flex', justifyContent: 'space-between', gap: '1rem', flexWrap: 'wrap' }}>
        <div>
          <h2 style={{ margin: 0 }}>Panel de administración</h2>
          <p className="muted" style={{ margin: 0 }}>
            Hola {user?.nombres || user?.correo}, gestiona usuarios, roles y catálogo académico.
          </p>
        </div>
        <div style={{ display: 'flex', gap: '0.6rem', flexWrap: 'wrap' }}>
          {resume.map((r) => (
            <div key={r.label} className="pill">
              <strong>{r.value}</strong>
              <span className="muted" style={{ fontWeight: 500 }}>{r.label}</span>
            </div>
          ))}
        </div>
      </div>

      <div className="card" style={{ display: 'flex', gap: '0.5rem', flexWrap: 'wrap' }}>
        {tabButton('resumen', 'Resumen')}
        {tabButton('usuarios', 'Usuarios')}
        {tabButton('roles', 'Roles')}
        {tabButton('cursos', 'Cursos')}
        {tabButton('asignaturas', 'Asignaturas')}
        {tabButton('asignaciones', 'Asignaciones')}
        {tabButton('familiares', 'Familiares')}
        {tabButton('matriculas', 'Matrículas')}
        {tabButton('periodos', 'Períodos Lectivos')}
      </div>

      {loading && <div className="card">Cargando...</div>}
      {error && <div className="card" style={{ color: 'var(--danger)' }}>{error}</div>}

      {!loading && !error && (
        <>
          {active === 'resumen' && (
            <>
              <div className="card">
                <h3 style={{ marginTop: 0 }}>Usuarios</h3>
                <div className="table-card" style={{ marginTop: '0.5rem' }}>
                  <table>
                    <thead>
                      <tr>
                        <th>Correo</th>
                        <th>Nombre</th>
                        <th>Estado</th>
                      </tr>
                    </thead>
                    <tbody>
                      {usuarios.length === 0 ? (
                        <tr>
                          <td colSpan={3} className="empty">Sin usuarios</td>
                        </tr>
                      ) : (
                        usuarios.slice(0, 6).map((u) => (
                          <tr key={u.id}>
                            <td>{u.correo}</td>
                            <td>{`${u.nombres || ''} ${u.apellidos || ''}`.trim() || '-'}</td>
                            <td>
                              <span className={u.estado ? 'badge' : 'badge badge-neutral'}>
                                {u.estado ? 'Activo' : 'Inactivo'}
                              </span>
                            </td>
                          </tr>
                        ))
                      )}
                    </tbody>
                  </table>
                </div>
              </div>

              <div className="card">
                <h3 style={{ marginTop: 0 }}>Matrículas recientes</h3>
                <div className="table-card" style={{ marginTop: '0.5rem' }}>
                  <table>
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Estudiante</th>
                        <th>Curso</th>
                        <th>Periodo</th>
                      </tr>
                    </thead>
                    <tbody>
                      {matriculas.length === 0 ? (
                        <tr>
                          <td colSpan={6} className="empty">Sin matrículas</td>
                        </tr>
                      ) : (
                        matriculas.slice(0, 6).map((m) => (
                          <tr key={m.id_matricula}>
                            <td>{m.id_matricula}</td>
                            <td>
                              <span className={m.estado ? 'badge' : 'badge badge-neutral'}>
                                {m.estado ? 'Activa' : 'Inactiva'}
                              </span>
                            </td>
                            <td>{formatDate(m.fecha_matricula)}</td>
                            <td>{m.estudiante || '-'}</td>
                            <td>{m.curso || '-'}</td>
                            <td>{m.periodo || '-'}</td>
                          </tr>
                        ))
                      )}
                    </tbody>
                  </table>
                </div>
              </div>
            </>
          )}

          {active === 'usuarios' && (
            <>
              <div className="card" style={{ display: 'grid', gap: '1rem' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', gap: '1rem', alignItems: 'center', flexWrap: 'wrap' }}>
                  <div>
                    <p className="eyebrow">Personas</p>
                    <h3 style={{ margin: '0.1rem 0' }}>Usuarios</h3>
                    <p className="muted" style={{ margin: 0 }}>Crea, edita y desactiva cuentas rápidamente.</p>
                  </div>
                  <div style={{ display: 'flex', gap: '0.5rem', flexWrap: 'wrap' }}>
                    <button className="btn btn-primary" onClick={() => openUserModal('create')}>Nuevo usuario</button>
                  </div>
                </div>

                <div className="table-card">
                  <table>
                    <thead>
                      <tr>
                        <th>Correo</th>
                        <th>Nombre</th>
                        <th>Roles</th>
                        <th>Estado</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      {usuarios.length === 0 ? (
                        <tr>
                          <td colSpan={5} className="empty">Sin usuarios</td>
                        </tr>
                      ) : (
                        usuarios.map((u) => (
                          <tr key={u.id}>
                            <td>{u.correo}</td>
                            <td>{`${u.nombres || ''} ${u.apellidos || ''}`.trim() || '-'}</td>
                            <td>
                              {u.roles && u.roles.length > 0 ? (
                                <div style={{ display: 'flex', gap: '0.3rem', flexWrap: 'wrap' }}>
                                  {u.roles.map((r) => (
                                    <span key={`rol-${r.id_rol || r.nombre_rol}`} className="badge" style={{ fontSize: '0.8rem' }}>
                                      {r.nombre_rol}
                                    </span>
                                  ))}
                                </div>
                              ) : (
                                <span className="muted">Sin roles</span>
                              )}
                            </td>
                            <td>
                              <span className={u.estado ? 'badge' : 'badge badge-neutral'}>{u.estado ? 'Activo' : 'Inactivo'}</span>
                            </td>
                            <td style={{ display: 'flex', gap: '0.4rem' }}>
                              <button className="btn btn-ghost" onClick={() => openUserModal('edit', u)}>
                                Editar
                              </button>
                              <button className="btn btn-danger" onClick={() => deleteUser(u.id)}>Eliminar</button>
                            </td>
                          </tr>
                        ))
                      )}
                    </tbody>
                  </table>
                </div>
              </div>

              <div className="card" style={{ display: 'grid', gap: '1rem' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: '1rem', flexWrap: 'wrap' }}>
                  <div>
                    <p className="eyebrow">Permisos rápidos</p>
                    <h3 style={{ margin: '0.1rem 0' }}>Roles por usuario</h3>
                    <p className="muted" style={{ margin: 0 }}>Asigna o retira roles para acceso inmediato.</p>
                  </div>
                  <button className="btn btn-primary" onClick={() => openUserRoleModal('create')}>Asignar rol</button>
                </div>

                <div className="table-card">
                  <table>
                    <thead>
                      <tr>
                        <th>Usuario</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      {userRoles.length === 0 ? (
                        <tr>
                          <td colSpan={4} className="empty">Sin asignaciones</td>
                        </tr>
                      ) : (
                        userRoles.map((ur) => (
                          <tr key={ur.id_user_rol}>
                            <td>{ur.nombre || '-'}</td>
                            <td>{ur.correo || '-'}</td>
                            <td>{ur.rol || '-'}</td>
                            <td style={{ display: 'flex', gap: '0.4rem' }}>
                              <button className="btn btn-ghost" onClick={() => openUserRoleModal('edit', ur)}>Editar</button>
                              {ur.id_user_rol && <button className="btn btn-danger" onClick={() => deleteUserRole(ur.id_user_rol)}>Eliminar</button>}
                            </td>
                          </tr>
                        ))
                      )}
                    </tbody>
                  </table>
                </div>
              </div>
            </>
          )}

          {active === 'roles' && (
            <div className="card" style={{ display: 'grid', gap: '1rem' }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', gap: '1rem', alignItems: 'center', flexWrap: 'wrap' }}>
                <div>
                  <p className="eyebrow">Permisos</p>
                  <h3 style={{ margin: '0.1rem 0' }}>Roles</h3>
                  <p className="muted" style={{ margin: 0 }}>Define perfiles y descripciones claras.</p>
                </div>
                <button className="btn btn-primary" onClick={() => openRolModal('create')}>Nuevo rol</button>
              </div>

              <div className="table-card">
                <table>
                  <thead>
                    <tr>
                      <th>Rol</th>
                      <th>Descripción</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    {roles.length === 0 ? (
                      <tr>
                        <td colSpan={3} className="empty">Sin roles</td>
                      </tr>
                    ) : (
                      roles.map((r) => (
                        <tr key={r.id_rol || `rol-${r.nombre_rol}`}>
                          <td>{r.nombre_rol}</td>
                          <td>{r.descripcion || '-'}</td>
                          <td style={{ display: 'flex', gap: '0.4rem' }}>
                            <button className="btn btn-ghost" onClick={() => openRolModal('edit', r)}>Editar</button>
                            {r.id_rol && <button className="btn btn-danger" onClick={() => deleteRol(r.id_rol)}>Eliminar</button>}
                          </td>
                        </tr>
                      ))
                    )}
                  </tbody>
                </table>
              </div>
            </div>
          )}

          {active === 'cursos' && (
            <div className="card" style={{ display: 'grid', gap: '1rem' }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', gap: '1rem', alignItems: 'center', flexWrap: 'wrap' }}>
                <div>
                  <p className="eyebrow">Oferta</p>
                  <h3 style={{ margin: '0.1rem 0' }}>Cursos</h3>
                  <p className="muted" style={{ margin: 0 }}>Organiza niveles y estados de los cursos.</p>
                </div>
                <button className="btn btn-primary" onClick={() => openCursoModal('create')}>Nuevo curso</button>
              </div>

              <div className="table-card">
                <table>
                  <thead>
                    <tr>
                      <th>Nombre</th>
                      <th>Nivel</th>
                      <th>Estado</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    {cursos.length === 0 ? (
                      <tr>
                        <td colSpan={4} className="empty">Sin cursos</td>
                      </tr>
                    ) : (
                      cursos.map((c) => (
                        <tr key={c.id_curso}>
                          <td>{c.nombre_curso}</td>
                          <td>{c.nivel || '-'}</td>
                          <td>
                            <span className={c.estado ? 'badge' : 'badge badge-neutral'}>{c.estado ? 'Activo' : 'Inactivo'}</span>
                          </td>
                          <td style={{ display: 'flex', gap: '0.4rem' }}>
                            <button className="btn btn-ghost" onClick={() => openCursoModal('edit', c)}>Editar</button>
                            <button className="btn btn-ghost" onClick={() => openCursoAsignaturasModal(c)}>Asignaturas</button>
                            <button className="btn btn-danger" onClick={() => deleteCurso(c.id_curso)}>Eliminar</button>
                          </td>
                        </tr>
                      ))
                    )}
                  </tbody>
                </table>
              </div>
            </div>
          )}

          {active === 'asignaturas' && (
            <div className="card" style={{ display: 'grid', gap: '1rem' }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', gap: '1rem', alignItems: 'center', flexWrap: 'wrap' }}>
                <div>
                  <p className="eyebrow">Catálogo</p>
                  <h3 style={{ margin: '0.1rem 0' }}>Asignaturas</h3>
                  <p className="muted" style={{ margin: 0 }}>Gestiona nombres y descripciones.</p>
                </div>
                <button className="btn btn-primary" onClick={() => openAsigModal('create')}>Nueva asignatura</button>
              </div>

              <div className="table-card">
                <table>
                  <thead>
                    <tr>
                      <th>Nombre</th>
                      <th>Descripción</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    {asignaturas.length === 0 ? (
                      <tr>
                        <td colSpan={3} className="empty">Sin asignaturas</td>
                      </tr>
                    ) : (
                      asignaturas.map((a) => (
                        <tr key={a.id_asignatura}>
                          <td>{a.nombre_asignatura}</td>
                          <td>{a.descripcion || '-'}</td>
                          <td style={{ display: 'flex', gap: '0.4rem' }}>
                            <button className="btn btn-ghost" onClick={() => openAsigModal('edit', a)}>Editar</button>
                            <button className="btn btn-danger" onClick={() => deleteAsignatura(a.id_asignatura)}>Eliminar</button>
                          </td>
                        </tr>
                      ))
                    )}
                  </tbody>
                </table>
              </div>
            </div>
          )}

          {active === 'familiares' && (
            <div className="card" style={{ display: 'grid', gap: '1rem' }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: '1rem', flexWrap: 'wrap' }}>
                <div>
                  <p className="eyebrow">Vínculos</p>
                  <h3 style={{ margin: '0.1rem 0' }}>Datos familiares</h3>
                  <p className="muted" style={{ margin: 0 }}>Asocia estudiantes con sus responsables y contactos.</p>
                </div>
                <button className="btn btn-primary" onClick={() => openDfModal('create')}>Nuevo registro</button>
              </div>

              <div className="table-card">
                <table>
                  <thead>
                    <tr>
                      <th>Estudiante</th>
                      <th>Representante</th>
                      <th>Padre</th>
                      <th>Madre</th>
                      <th>Parentesco</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    {familiares.length === 0 ? (
                      <tr>
                        <td colSpan={6} className="empty">Sin vínculos registrados</td>
                      </tr>
                    ) : (
                      familiares.map((f) => (
                        <tr key={f.id_datos_familiares}>
                          <td>{f.estudiante || '-'}</td>
                          <td>{f.representante_correo || f.nombre_representante || '-'}</td>
                          <td>{f.nombre_padre || '-'}</td>
                          <td>{f.nombre_madre || '-'}</td>
                          <td>{f.parentesco_representante || '-'}</td>
                          <td style={{ display: 'flex', gap: '0.4rem' }}>
                            <button className="btn btn-ghost" onClick={() => openDfModal('edit', f)}>Editar</button>
                            <button className="btn btn-danger" onClick={() => deleteDatosFamiliares(f.id_datos_familiares)}>Eliminar</button>
                          </td>
                        </tr>
                      ))
                    )}
                  </tbody>
                </table>
              </div>
            </div>
          )}

          {active === 'asignaciones' && (
            <div style={{ display: 'grid', gap: '1rem' }}>
              {/* Subtabs */}
              <div className="card" style={{ display: 'flex', gap: '0.5rem' }}>
                <button
                  className={asignacionSubtab === 'docentes' ? 'btn btn-primary' : 'btn btn-ghost'}
                  onClick={() => setAsignacionSubtab('docentes')}
                >
                  Docentes
                </button>
                <button
                  className={asignacionSubtab === 'estudiantes' ? 'btn btn-primary' : 'btn btn-ghost'}
                  onClick={() => setAsignacionSubtab('estudiantes')}
                >
                  Estudiantes
                </button>
              </div>

              {/* DOCENTES */}
              {asignacionSubtab === 'docentes' && (
                <div className="card">
                  <div style={{ display: 'flex', justifyContent: 'space-between', gap: '1rem', alignItems: 'center', flexWrap: 'wrap' }}>
                    <div>
                      <p className="eyebrow" style={{ marginBottom: 4 }}>Asignación docente</p>
                      <h3 style={{ margin: 0 }}>Cursos y asignaturas</h3>
                      <p className="muted" style={{ margin: '0.25rem 0 0' }}>Asigna docentes a cursos y materias específicas</p>
                    </div>
                    <button className="btn btn-primary" onClick={() => openCaModal('create')}>Nueva asignación</button>
                  </div>

                  {/* Vista de lista de asignaciones */}
                  <div className="table-card" style={{ marginTop: '0.5rem' }}>
                    <table>
                      <thead>
                        <tr>
                          <th>Curso</th>
                          <th>Asignatura</th>
                          <th>Docente</th>
                          <th></th>
                        </tr>
                      </thead>
                      <tbody>
                        {cursoAsignaturas.length === 0 ? (
                          <tr>
                            <td colSpan={4} className="empty">Sin asignaciones</td>
                          </tr>
                        ) : (
                          cursoAsignaturas.map((ca) => (
                            <tr key={ca.id_curso_asignatura}>
                              <td>{ca.curso}</td>
                              <td>{ca.asignatura}</td>
                              <td>{ca.docente || <span className="muted">Sin asignar</span>}</td>
                              <td style={{ display: 'flex', gap: '0.4rem' }}>
                                <button className="btn btn-ghost" onClick={() => openCaModal('edit', ca)}>Editar</button>
                                <button className="btn btn-danger" onClick={() => deleteCursoAsignatura(ca.id_curso_asignatura)}>Eliminar</button>
                              </td>
                            </tr>
                          ))
                        )}
                      </tbody>
                    </table>
                  </div>

                  {/* Filtros por curso */}
                  <div style={{ marginTop: '1.5rem' }}>
                    <h4 style={{ margin: '0 0 0.75rem' }}>Filtrar por curso</h4>
                    <div style={{ display: 'grid', gap: '0.75rem', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))' }}>
                      <label className="field">
                        <span>Selecciona curso</span>
                        <select value={filtroAsignacionCurso ?? ''} onChange={(e) => setFiltroAsignacionCurso(Number(e.target.value) || null)}>
                          <option value="">-- Todos los cursos --</option>
                          {cursos.map((c) => (
                            <option key={`doc-c-${c.id_curso}`} value={c.id_curso}>{c.nombre_curso}</option>
                          ))}
                        </select>
                      </label>
                    </div>
                  </div>

                  {filtroAsignacionCurso && (
                    <>
                      {/* Docentes asignados */}
                      <div style={{ marginTop: '1.5rem' }}>
                        <h4 style={{ margin: '0 0 0.75rem' }}>Docentes asignados</h4>
                        <div className="table-card">
                          <table>
                            <thead>
                              <tr>
                                <th>Docente</th>
                                <th>Asignaturas</th>
                              </tr>
                            </thead>
                            <tbody>
                              {docentesCurso.length === 0 ? (
                                <tr>
                                  <td colSpan={2} className="empty">Sin docentes asignados</td>
                                </tr>
                              ) : (
                                docentesCurso.map((d) => {
                                  const asigs = cursoAsignaturas
                                    .filter((ca) => ca.id_curso === filtroAsignacionCurso && ca.id_docente === d.id)
                                    .map((ca) => ca.asignatura)
                                  return (
                                    <tr key={d.id}>
                                      <td>{`${d.nombres || ''} ${d.apellidos || ''}`.trim() || d.correo}</td>
                                      <td>
                                        <div style={{ display: 'flex', gap: '0.3rem', flexWrap: 'wrap' }}>
                                          {asigs.map((a, i) => (
                                            <span key={i} className="badge" style={{ fontSize: '0.8rem' }}>
                                              {a}
                                            </span>
                                          ))}
                                        </div>
                                      </td>
                                    </tr>
                                  )
                                })
                              )}
                            </tbody>
                          </table>
                        </div>
                      </div>

                      {/* Docentes sin asignar */}
                      <div style={{ marginTop: '1.5rem' }}>
                        <h4 style={{ margin: '0 0 0.75rem' }}>Docentes disponibles</h4>
                        <div className="table-card">
                          <table>
                            <thead>
                              <tr>
                                <th>Docente</th>
                                <th>Correo</th>
                                <th></th>
                              </tr>
                            </thead>
                            <tbody>
                              {docentesSinAsignarCurso.length === 0 ? (
                                <tr>
                                  <td colSpan={3} className="empty">Todos los docentes están asignados</td>
                                </tr>
                              ) : (
                                docentesSinAsignarCurso.map((d) => (
                                  <tr key={d.id}>
                                    <td>{`${d.nombres || ''} ${d.apellidos || ''}`.trim() || d.correo}</td>
                                    <td>{d.correo}</td>
                                    <td>
                                      <button className="btn btn-ghost" onClick={() => openCaModal('create')}>
                                        Asignar
                                      </button>
                                    </td>
                                  </tr>
                                ))
                              )}
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </>
                  )}
                </div>
              )}

              {/* ESTUDIANTES */}
              {asignacionSubtab === 'estudiantes' && (
                <div className="card">
                  <div>
                    <p className="eyebrow" style={{ marginBottom: 4 }}>Asignación estudiantes</p>
                    <h3 style={{ margin: 0 }}>Matrículas por curso y asignatura</h3>
                    <p className="muted" style={{ margin: '0.25rem 0 0' }}>Visualiza estudiantes asignados a cursos y materias</p>
                  </div>

                  {/* Filtros */}
                  <div style={{ marginTop: '1rem' }}>
                    <div style={{ display: 'grid', gap: '0.75rem', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))' }}>
                      <label className="field">
                        <span>Selecciona curso</span>
                        <select value={filtroAsignacionCurso ?? ''} onChange={(e) => {
                          setFiltroAsignacionCurso(Number(e.target.value) || null)
                          setFiltroAsignacionAsignatura(null)
                        }}>
                          <option value="">-- Selecciona un curso --</option>
                          {cursos.map((c) => (
                            <option key={`est-c-${c.id_curso}`} value={c.id_curso}>{c.nombre_curso}</option>
                          ))}
                        </select>
                      </label>
                      {filtroAsignacionCurso && (
                        <label className="field">
                          <span>Selecciona asignatura (opcional)</span>
                          <select value={filtroAsignacionAsignatura ?? ''} onChange={(e) => setFiltroAsignacionAsignatura(Number(e.target.value) || null)}>
                            <option value="">-- Todas las asignaturas --</option>
                            {asignaturasDelCurso.map((a) => (
                              <option key={`est-a-${a.id}`} value={a.id}>{a.nombre}</option>
                            ))}
                          </select>
                        </label>
                      )}
                    </div>
                  </div>

                  {filtroAsignacionCurso && (
                    <>
                      {/* Estudiantes del curso */}
                      <div style={{ marginTop: '1.5rem' }}>
                        <h4 style={{ margin: '0 0 0.75rem' }}>
                          Estudiantes en {cursos.find((c) => c.id_curso === filtroAsignacionCurso)?.nombre_curso}
                          {filtroAsignacionAsignatura && ` - ${asignaturasDelCurso.find((a) => a.id === filtroAsignacionAsignatura)?.nombre}`}
                        </h4>
                        <div className="table-card">
                          <table>
                            <thead>
                              <tr>
                                <th>Estudiante</th>
                                <th>Correo</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                              </tr>
                            </thead>
                            <tbody>
                              {(filtroAsignacionAsignatura ? estudiantesAsignatura : estudiantesCurso).length === 0 ? (
                                <tr>
                                  <td colSpan={4} className="empty">Sin estudiantes</td>
                                </tr>
                              ) : (
                                (filtroAsignacionAsignatura ? estudiantesAsignatura : estudiantesCurso).map((m) => (
                                  <tr key={m.id_matricula}>
                                    <td>{m.estudiante || '-'}</td>
                                    <td>{usuarios.find((u) => u.id === m.id_estudiante)?.correo || '-'}</td>
                                    <td>
                                      <span className={m.estado ? 'badge' : 'badge badge-neutral'}>{m.estado ? 'Activo' : 'Inactivo'}</span>
                                    </td>
                                    <td>{formatDate(m.fecha_matricula)}</td>
                                  </tr>
                                ))
                              )}
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </>
                  )}
                </div>
              )}
            </div>
          )}

          {active === 'matriculas' && (
            <div className="card">
              <div style={{ display: 'flex', justifyContent: 'space-between', gap: '1rem', alignItems: 'center', flexWrap: 'wrap' }}>
                <div>
                  <p className="eyebrow" style={{ marginBottom: 4 }}>Flujo</p>
                  <h3 style={{ margin: 0 }}>Matrículas</h3>
                </div>
                <button className="btn btn-primary" onClick={() => openMatriModal('create')}>Nueva matrícula</button>
              </div>
              <div className="table-card" style={{ marginTop: '0.5rem' }}>
                <table>
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Estado</th>
                      <th>Fecha</th>
                      <th>Estudiante</th>
                      <th>Curso</th>
                      <th>Periodo</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    {matriculas.length === 0 ? (
                      <tr>
                        <td colSpan={7} className="empty">Sin matrículas</td>
                      </tr>
                    ) : (
                      matriculas.map((m) => (
                        <tr key={m.id_matricula}>
                          <td>{m.id_matricula}</td>
                          <td>
                            <span className={m.estado ? 'badge' : 'badge badge-neutral'}>{m.estado ? 'Activa' : 'Inactiva'}</span>
                          </td>
                          <td>{formatDate(m.fecha_matricula)}</td>
                          <td>{m.estudiante || '-'}</td>
                          <td>{m.curso || '-'}</td>
                          <td>{m.periodo || '-'}</td>
                          <td style={{ display: 'flex', gap: '0.4rem' }}>
                            <button className="btn btn-ghost" onClick={() => openMatriModal('edit', m)}>Editar</button>
                            <button className="btn btn-danger" onClick={() => deleteMatricula(m.id_matricula)}>Eliminar</button>
                          </td>
                        </tr>
                      ))
                    )}
                  </tbody>
                </table>
              </div>
            </div>
          )}

          {active === 'periodos' && (
            <div className="card">
              <div style={{ display: 'flex', justifyContent: 'space-between', gap: '1rem', alignItems: 'center', flexWrap: 'wrap' }}>
                <div>
                  <p className="eyebrow" style={{ marginBottom: 4 }}>ADMINISTRACIÓN</p>
                  <h3 style={{ margin: 0 }}>Períodos Lectivos</h3>
                </div>
                <button className="btn btn-primary" onClick={() => { setPeriodoForm(newPeriodoForm()); setModal({ type: 'periodo', mode: 'create' }) }}>Nuevo período</button>
              </div>
              <div className="table-card" style={{ marginTop: '0.5rem' }}>
                <table>
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Descripción</th>
                      <th>Inicio</th>
                      <th>Fin</th>
                      <th>Estado</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    {periodos.length === 0 ? (
                      <tr>
                        <td colSpan={6} className="empty">Sin períodos</td>
                      </tr>
                    ) : (
                      periodos.map((p) => (
                        <tr key={p.id_periodo}>
                          <td>{p.id_periodo}</td>
                          <td>{p.descripcion}</td>
                          <td>{formatDate(p.fecha_inicio)}</td>
                          <td>{formatDate(p.fecha_fin)}</td>
                          <td>
                            <span className={p.estado ? 'badge' : 'badge badge-neutral'}>{p.estado ? 'Activo' : 'Inactivo'}</span>
                          </td>
                          <td style={{ display: 'flex', gap: '0.4rem' }}>
                            <button className="btn btn-ghost" onClick={() => { setPeriodoForm(p); setModal({ type: 'periodo', mode: 'edit' }) }}>Editar</button>
                            <button className="btn btn-danger" onClick={() => deletePeriodo(p.id_periodo)}>Eliminar</button>
                          </td>
                        </tr>
                      ))
                    )}
                  </tbody>
                </table>
              </div>
            </div>
          )}
        </>
      )}
      {renderModal()}
    </div>  )
}