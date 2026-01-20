import { useEffect, useMemo, useRef, useState } from 'react'
import { useAuth } from '../state/AuthContext'
import {
  fetchChats,
  fetchMessages,
  searchUsersForChat,
  startPrivateChat,
  ensureCursoChat,
  sendMessage,
} from '../utils/chat'
import type { Chat, ChatMessage, SearchUser } from '../utils/chat'
import './ChatsPage.css'

export const ChatsPage = () => {
  const { user } = useAuth()
  const [chats, setChats] = useState<Chat[]>([])
  const [selectedChatId, setSelectedChatId] = useState<number | null>(null)
  const [messages, setMessages] = useState<ChatMessage[]>([])
  const [loadingChats, setLoadingChats] = useState(false)
  const [loadingMessages, setLoadingMessages] = useState(false)
  const [searchTerm, setSearchTerm] = useState('')
  const [searchResults, setSearchResults] = useState<SearchUser[]>([])
  const [sending, setSending] = useState(false)
  const [draft, setDraft] = useState('')
  const eventSourceRef = useRef<EventSource | null>(null)

  const selectedChat = useMemo(
    () => chats.find((c) => c.id_chat === selectedChatId) || null,
    [chats, selectedChatId],
  )

  const loadChats = async () => {
    setLoadingChats(true)
    try {
      const data = await fetchChats()
      setChats(data)
      if (!selectedChatId && data.length > 0) {
        setSelectedChatId(data[0].id_chat)
      }
    } finally {
      setLoadingChats(false)
    }
  }

  const loadMessages = async (chatId: number) => {
    setLoadingMessages(true)
    try {
      const data = await fetchMessages(chatId)
      setMessages(data)
    } finally {
      setLoadingMessages(false)
    }
  }

  useEffect(() => {
    loadChats()
  }, [])

  useEffect(() => {
    if (!selectedChatId) return
    loadMessages(selectedChatId)

    const mercureUrl = (import.meta.env.VITE_MERCURE_URL as string) || 'http://localhost:3000/.well-known/mercure'
    const url = `${mercureUrl}?topic=${encodeURIComponent(`/chats/${selectedChatId}`)}`
    const es = new EventSource(url)
    eventSourceRef.current = es

    es.onmessage = (event) => {
      try {
        const payload = JSON.parse(event.data)
        if (payload?.mensaje && payload.chat_id === selectedChatId) {
          setMessages((prev) => {
            const exists = prev.some((m) => m.id_mensaje === payload.mensaje.id_mensaje)
            if (exists) return prev
            return [...prev, payload.mensaje]
          })
        }
      } catch (err) {
        console.error('No se pudo parsear mensaje de Mercure', err)
      }
    }

    es.onerror = () => {
      console.warn('Conexión Mercure caída, reintenta al cambiar de chat')
    }

    return () => {
      es.close()
      eventSourceRef.current = null
    }
  }, [selectedChatId])

  const handleSelectChat = (chatId: number) => {
    setSelectedChatId(chatId)
  }

  const handleSearch = async () => {
    const term = searchTerm.trim()
    if (!term) {
      setSearchResults([])
      return
    }
    const data = await searchUsersForChat(term, selectedChat?.id_curso || null)
    setSearchResults(data)
  }

  const handleStartPrivate = async (userId: number) => {
    const chat = await startPrivateChat(userId)
    await loadChats()
    setSelectedChatId(chat.id_chat)
    setSearchResults([])
    setSearchTerm('')
  }

  const handleEnsureCurso = async (cursoId?: number | null) => {
    if (!cursoId) return
    const chat = await ensureCursoChat(cursoId)
    await loadChats()
    setSelectedChatId(chat.id_chat)
  }

  const handleSend = async () => {
    if (!selectedChatId || !draft.trim()) return
    setSending(true)
    try {
      const msg = await sendMessage(selectedChatId, draft.trim())
      setMessages((prev) => [...prev, msg])
      setDraft('')
    } finally {
      setSending(false)
    }
  }

  return (
    <div className="chats-shell">
      <div className="chats-list">
        <div className="chats-header">
          <h3>Mis chats</h3>
          <button className="btn" onClick={loadChats} disabled={loadingChats}>
            {loadingChats ? 'Actualizando…' : 'Actualizar'}
          </button>
        </div>
        <div className="search-box">
          <input
            placeholder="Buscar usuario para chat"
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
          />
          <button className="btn" onClick={handleSearch}>
            Buscar
          </button>
        </div>
        {searchResults.length > 0 && (
          <div className="search-results">
            {searchResults.map((u) => (
              <div key={u.id} className="result-item">
                <div>
                  <strong>{u.nombre}</strong>
                  <div className="muted">{u.correo}</div>
                </div>
                <button className="btn btn-ghost" onClick={() => handleStartPrivate(u.id)}>
                  Chatear
                </button>
              </div>
            ))}
          </div>
        )}

        <div className="list-body">
          {chats.map((chat) => (
            <button
              key={chat.id_chat}
              className={chat.id_chat === selectedChatId ? 'chat-item chat-item--active' : 'chat-item'}
              onClick={() => handleSelectChat(chat.id_chat)}
            >
              <div className="chat-title">{chat.titulo || chat.curso || 'Chat'}</div>
              <div className="chat-meta">
                <span className="pill pill--outline">{chat.tipo}</span>
                {chat.curso && <span className="muted">{chat.curso}</span>}
              </div>
              <div className="participants">
                {chat.participantes.slice(0, 3).map((p) => (
                  <span key={p.id} className="avatar">
                    {p.nombre?.charAt(0) || '?'}
                  </span>
                ))}
                {chat.participantes.length > 3 && <span className="muted">+{chat.participantes.length - 3}</span>}
              </div>
            </button>
          ))}
        </div>
      </div>

      <div className="chat-view">
        {selectedChat ? (
          <>
            <div className="chat-view__header">
              <div>
                <h3>{selectedChat.titulo || selectedChat.curso || 'Chat'}</h3>
                <div className="muted">
                  {selectedChat.tipo}
                  {selectedChat.curso ? ` • ${selectedChat.curso}` : ''}
                </div>
              </div>
              {selectedChat.id_curso && (
                <button className="btn btn-ghost" onClick={() => handleEnsureCurso(selectedChat.id_curso)}>
                  Re-sync curso
                </button>
              )}
            </div>

            <div className="messages" aria-busy={loadingMessages}>
              {messages.map((m) => (
                <div
                  key={m.id_mensaje}
                  className={m.emisor?.id === user?.uid ? 'msg msg--mine' : 'msg'}
                >
                  <div className="msg__meta">
                    <strong>{m.emisor?.nombre || 'Desconocido'}</strong>
                    <span className="muted">{new Date(m.fecha_envio).toLocaleString()}</span>
                  </div>
                  <div className="msg__text">{m.contenido}</div>
                </div>
              ))}
            </div>

            <div className="composer">
              <textarea
                placeholder="Escribe un mensaje"
                value={draft}
                onChange={(e) => setDraft(e.target.value)}
                rows={2}
              />
              <button className="btn" onClick={handleSend} disabled={sending || !draft.trim()}>
                {sending ? 'Enviando…' : 'Enviar'}
              </button>
            </div>
          </>
        ) : (
          <div className="empty">Selecciona o crea un chat</div>
        )}
      </div>
    </div>
  )
}
