import api from './api'

export type ChatParticipant = {
  id: number
  nombre: string
  correo: string
  roles: string[]
}

export type Chat = {
  id_chat: number
  titulo: string
  tipo: string
  id_curso?: number | null
  curso?: string | null
  id_creador?: number | null
  fecha_creacion?: string
  participantes: ChatParticipant[]
}

export type ChatMessage = {
  id_mensaje: number
  contenido: string
  fecha_envio: string
  id_chat?: number | null
  emisor?: { id: number; nombre: string | null }
}

export type SearchUser = {
  id: number
  nombre: string
  correo: string
  roles: string[]
}

export const fetchChats = async (): Promise<Chat[]> => {
  const { data } = await api.get('/api/chats')
  return data
}

export const fetchMessages = async (chatId: number): Promise<ChatMessage[]> => {
  const { data } = await api.get(`/api/chats/${chatId}/mensajes`)
  return data
}

export const searchUsersForChat = async (term: string, cursoId: number | null): Promise<SearchUser[]> => {
  const params: Record<string, string | number> = { q: term }
  if (cursoId) params.id_curso = cursoId
  const { data } = await api.get('/api/chats/buscar-usuarios', { params })
  return data
}

export const startPrivateChat = async (userId: number): Promise<Chat> => {
  const { data } = await api.post('/api/chats', { tipo: 'privado', id_usuario_destino: userId })
  return data
}

export const ensureCursoChat = async (cursoId: number): Promise<Chat> => {
  const { data } = await api.post('/api/chats', { tipo: 'curso', id_curso: cursoId })
  return data
}

export const sendMessage = async (chatId: number, contenido: string): Promise<ChatMessage> => {
  const { data } = await api.post('/api/mensajes', { id_chat: chatId, contenido })
  return data
}
