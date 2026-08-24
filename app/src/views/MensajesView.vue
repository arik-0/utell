<template>
  <div class="page-container mensajes-page" :class="{ 'in-chat': activeContact }">
    <!-- ═════════════════════════════════════════════════════════════
         VIEW A: CONVERSATION LIST (INBOX)
         ═════════════════════════════════════════════════════════════ -->
    <template v-if="!activeContact">
      <header class="inbox-header">
        <div class="header-top">
          <div class="header-left">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
              <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
            <h1>Mensajes</h1>
            <span v-if="totalNoLeidos > 0" class="unread-pill">{{ totalNoLeidos }}</span>
          </div>
          <button class="new-chat-btn" @click="openNewChatModal" aria-label="Nuevo mensaje">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            <span>Nuevo</span>
          </button>
        </div>

        <!-- Search Bar -->
        <div class="search-box">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" v-model="searchQuery" placeholder="Buscar conversaciones..." />
          <button v-if="searchQuery" @click="searchQuery = ''" class="clear-btn">✕</button>
        </div>
      </header>

      <!-- Loading State -->
      <div v-if="isLoadingConversaciones" class="state-container">
        <div class="spinner"></div>
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredConversaciones.length === 0" class="empty-inbox">
        <EmptyState
          icon="mail"
          :title="searchQuery ? 'Sin resultados' : 'No tienes mensajes directos'"
          :subtitle="searchQuery ? 'No encontramos conversaciones que coincidan con la búsqueda.' : 'Inicia una conversación con tus amigos y compañeros de universidad.'"
          :buttonText="searchQuery ? '' : 'Iniciar conversación'"
          @action="openNewChatModal"
        />
      </div>

      <!-- Conversation List -->
      <div v-else class="conversations-list">
        <div
          v-for="conv in filteredConversaciones"
          :key="conv.idUsuario"
          class="conv-card animate-fadein-up"
          :class="{ 'has-unread': Number(conv.noLeidos) > 0 }"
          @click="openChat(conv)"
        >
          <div class="avatar-wrap">
            <img v-if="conv.fotoPerfil" :src="getProfilePic(conv.fotoPerfil)" alt="Avatar" class="avatar-img" />
            <span v-else class="avatar-initial">{{ conv.nombre?.charAt(0) }}</span>
          </div>

          <div class="conv-content">
            <div class="conv-header">
              <span class="contact-name">{{ conv.nombre }} {{ conv.apellido }}</span>
              <span class="conv-time">{{ timeAgo(conv.ultimaFecha, conv.ultimaHora) }}</span>
            </div>
            <div class="conv-body">
              <p class="last-message">
                <span v-if="Number(conv.ultimoEmisor) === Number(authStore.user?.idUsuario)" class="self-tag">Tú: </span>
                {{ conv.ultimoMensaje }}
              </p>
              <span v-if="Number(conv.noLeidos) > 0" class="unread-badge">{{ conv.noLeidos }}</span>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- ═════════════════════════════════════════════════════════════
         VIEW B: ACTIVE DIRECT CHAT
         ═════════════════════════════════════════════════════════════ -->
    <template v-else>
      <!-- Chat Header -->
      <header class="chat-header">
        <button class="back-btn" @click="closeChat" aria-label="Volver">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        </button>

        <div class="contact-info">
          <div class="contact-avatar-small">
            <img v-if="activeContact.fotoPerfil" :src="getProfilePic(activeContact.fotoPerfil)" alt="Avatar" />
            <span v-else>{{ activeContact.nombre?.charAt(0) }}</span>
          </div>
          <div class="contact-text">
            <span class="chat-name">{{ activeContact.nombre }} {{ activeContact.apellido }}</span>
            <span class="chat-status">{{ activeContact.tipoPerfil || 'Estudiante' }}</span>
          </div>
        </div>

        <div style="width: 32px;"></div>
      </header>

      <!-- Chat Messages Scroll Container -->
      <div class="chat-messages" ref="messagesContainer">
        <div v-if="isLoadingChat" class="state-container compact">
          <div class="spinner"></div>
        </div>

        <div v-else-if="messages.length === 0" class="empty-chat-state">
          <div class="chat-start-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          </div>
          <p class="start-title">Conversación con {{ activeContact.nombre }}</p>
          <p class="start-sub">Sé el primero en enviar un mensaje.</p>
        </div>

        <div v-else class="messages-flow">
          <div
            v-for="msg in messages"
            :key="msg.idMensajeDirecto"
            class="message-wrapper"
            :class="{ 'is-me': isMe(msg), 'is-contact': !isMe(msg) }"
          >
            <div class="message-bubble">
              <p class="msg-text">{{ msg.textoMensaje }}</p>
              <div class="msg-meta">
                <span class="msg-time">{{ formatTime(msg.horaMensaje) }}</span>
                <span v-if="isMe(msg)" class="read-status">
                  <svg v-if="msg.leido > 0" xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                  <svg v-else xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Chat Bottom Input Bar -->
      <div class="chat-input-bar">
        <input
          type="text"
          v-model="newMessageText"
          placeholder="Escribe un mensaje directo..."
          class="chat-input"
          @keyup.enter="sendMessage"
          :disabled="isSending"
          ref="messageInput"
        />
        <button
          class="chat-send-btn"
          @click="sendMessage"
          :disabled="!newMessageText.trim() || isSending"
          aria-label="Enviar mensaje"
        >
          <svg v-if="!isSending" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
          <div v-else class="btn-spinner"></div>
        </button>
      </div>
    </template>

    <!-- ═════════════════════════════════════════════════════════════
         MODAL: SELECT FRIEND FOR NEW CONVERSATION
         ═════════════════════════════════════════════════════════════ -->
    <div v-if="showNewModal" class="modal-overlay" @click.self="showNewModal = false">
      <div class="modal-card animate-fadein-up">
        <div class="modal-header">
          <h3>Nueva Conversación</h3>
          <button class="modal-close" @click="showNewModal = false" aria-label="Cerrar">✕</button>
        </div>

        <div class="modal-body">
          <div v-if="isLoadingAmigos" class="state-container compact">
            <div class="spinner"></div>
          </div>

          <div v-else-if="amigosDisponibles.length === 0" class="empty-friends-modal">
            <p class="modal-empty-title">Sin amigos disponibles</p>
            <p class="modal-empty-sub">Agrega contactos desde la sección de Amigos para enviarles mensajes directos.</p>
            <router-link to="/amigos" class="btn btn-secondary mt-2" @click="showNewModal = false">Ir a Conexiones</router-link>
          </div>

          <div v-else class="friends-picker-list">
            <div
              v-for="amigo in amigosDisponibles"
              :key="amigo.idUsuario"
              class="friend-pick-item"
              @click="startChatWithFriend(amigo)"
            >
              <div class="avatar-wrap-sm">
                <img v-if="amigo.fotoPerfil" :src="getProfilePic(amigo.fotoPerfil)" alt="Avatar" />
                <span v-else>{{ amigo.nombre?.charAt(0) }}</span>
              </div>
              <div class="friend-pick-details">
                <span class="friend-pick-name">{{ amigo.nombre }} {{ amigo.apellido }}</span>
                <span class="friend-pick-tipo">{{ amigo.tipoPerfil || 'Estudiante' }}</span>
              </div>
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="pick-chevron"><polyline points="9 18 15 12 9 6"/></svg>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../store/auth'
import { timeAgo, getProfilePic } from '../utils'
import { API_BASE_URL } from '../api'
import EmptyState from '../components/EmptyState.vue'
import axios from 'axios'

const route     = useRoute()
const router    = useRouter()
const authStore = useAuthStore()

// State
const conversaciones        = ref([])
const totalNoLeidos         = ref(0)
const isLoadingConversaciones = ref(true)
const searchQuery           = ref('')

// Active Chat State
const activeContact         = ref(null)
const messages              = ref([])
const newMessageText        = ref('')
const isLoadingChat         = ref(false)
const isSending             = ref(false)
const messagesContainer     = ref(null)
const messageInput          = ref(null)

// Modal State
const showNewModal          = ref(false)
const amigosDisponibles     = ref([])
const isLoadingAmigos       = ref(false)

// Polling timer
let pollInterval = null

const isMe = (msg) => {
  return Number(msg.idEmisor) === Number(authStore.user?.idUsuario)
}

const formatTime = (timeStr) => {
  if (!timeStr) return ''
  return timeStr.substring(0, 5)
}

const filteredConversaciones = computed(() => {
  if (!searchQuery.value.trim()) return conversaciones.value
  const q = searchQuery.value.toLowerCase()
  return conversaciones.value.filter(c => 
    `${c.nombre} ${c.apellido}`.toLowerCase().includes(q) ||
    (c.ultimoMensaje && c.ultimoMensaje.toLowerCase().includes(q))
  )
})

// ─── Data Loaders ─────────────────────────────────────────────────────────────
const fetchConversaciones = async () => {
  if (!authStore.user) return
  try {
    const res = await axios.get(`${API_BASE_URL}/mensajes/conversaciones?idUsuario=${authStore.user.idUsuario}`)
    if (Array.isArray(res.data)) {
      conversaciones.value = res.data
      totalNoLeidos.value = res.data.reduce((acc, c) => acc + Number(c.noLeidos || 0), 0)
    }
  } catch (e) {
    console.error('Error fetching conversations:', e)
  } finally {
    isLoadingConversaciones.value = false
  }
}

const fetchChatMessages = async (contactId, silent = false) => {
  if (!authStore.user || !contactId) return
  if (!silent) isLoadingChat.value = true
  try {
    const res = await axios.get(`${API_BASE_URL}/mensajes/chat/${contactId}?idUsuario=${authStore.user.idUsuario}`)
    if (Array.isArray(res.data)) {
      const prevLength = messages.value.length
      messages.value = res.data
      if (res.data.length > prevLength) {
        await scrollToBottom()
      }
    }
  } catch (e) {
    console.error('Error fetching chat messages:', e)
  } finally {
    if (!silent) isLoadingChat.value = false
  }
}

// ─── Actions ──────────────────────────────────────────────────────────────────
const openChat = async (contact) => {
  activeContact.value = contact
  messages.value = []
  await fetchChatMessages(contact.idUsuario)
  await scrollToBottom()
  startPolling(contact.idUsuario)
}

const closeChat = () => {
  stopPolling()
  activeContact.value = null
  messages.value = []
  fetchConversaciones()
}

const scrollToBottom = async () => {
  await nextTick()
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

const sendMessage = async () => {
  if (!newMessageText.value.trim() || isSending.value || !activeContact.value) return

  const texto = newMessageText.value.trim()
  newMessageText.value = ''
  isSending.value = true

  // Optimistic message append
  const tempMsg = {
    idMensajeDirecto: Date.now(),
    idEmisor: authStore.user.idUsuario,
    idReceptor: activeContact.value.idUsuario,
    fechaMensaje: new Date().toISOString().split('T')[0],
    horaMensaje: new Date().toTimeString().split(' ')[0],
    textoMensaje: texto,
    leido: 0
  }
  messages.value.push(tempMsg)
  await scrollToBottom()

  try {
    const fd = new FormData()
    fd.append('idEmisor', authStore.user.idUsuario)
    fd.append('idReceptor', activeContact.value.idUsuario)
    fd.append('texto', texto)

    const res = await axios.post(`${API_BASE_URL}/mensajes/enviar`, fd)
    if (res.data && res.data.data) {
      // Replace optimistic entry with backend response
      const idx = messages.value.findIndex(m => m.idMensajeDirecto === tempMsg.idMensajeDirecto)
      if (idx !== -1) {
        messages.value[idx] = res.data.data
      }
    }
  } catch (e) {
    console.error('Error sending message:', e)
    // Remove failed optimistic msg
    messages.value = messages.value.filter(m => m.idMensajeDirecto !== tempMsg.idMensajeDirecto)
  } finally {
    isSending.value = false
    await scrollToBottom()
  }
}

// ─── Polling ──────────────────────────────────────────────────────────────────
const startPolling = (contactId) => {
  stopPolling()
  pollInterval = setInterval(() => {
    fetchChatMessages(contactId, true)
  }, 3500)
}

const stopPolling = () => {
  if (pollInterval) {
    clearInterval(pollInterval)
    pollInterval = null
  }
}

// ─── Modal: New Chat Picker ───────────────────────────────────────────────────
const openNewChatModal = async () => {
  showNewModal.value = true
  isLoadingAmigos.value = true
  try {
    const res = await axios.get(`${API_BASE_URL}/usuarios/${authStore.user.idUsuario}/amigos`)
    amigosDisponibles.value = Array.isArray(res.data) ? res.data : []
  } catch (e) {
    amigosDisponibles.value = []
  } finally {
    isLoadingAmigos.value = false
  }
}

const startChatWithFriend = (amigo) => {
  showNewModal.value = false
  openChat({
    idUsuario: amigo.idUsuario,
    nombre: amigo.nombre,
    apellido: amigo.apellido,
    fotoPerfil: amigo.fotoPerfil,
    tipoPerfil: amigo.tipoPerfil
  })
}

// Check route params to open direct chat (e.g. from profile or search)
onMounted(async () => {
  await fetchConversaciones()
  if (route.query.contacto) {
    const cid = Number(route.query.contacto)
    // Load contact info
    try {
      const res = await axios.get(`${API_BASE_URL}/usuarios/${cid}`)
      if (res.data && res.data.idUsuario) {
        openChat(res.data)
      }
    } catch (_) {}
  }
})

onUnmounted(() => {
  stopPolling()
})
</script>

<style scoped>
.mensajes-page {
  background-color: var(--bg-main);
  padding-bottom: 84px;
  min-height: 100vh;
  min-height: 100dvh;
}

.mensajes-page.in-chat {
  position: fixed;
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 100%;
  max-width: 680px;
  padding-bottom: 0;
  height: 100vh;
  height: 100dvh;
  display: flex;
  flex-direction: column;
  z-index: 1050;
  background-color: var(--bg-main);
  box-shadow: 0 0 50px rgba(15, 23, 42, 0.08);
}

@media (min-width: 1200px) {
  .mensajes-page.in-chat {
    max-width: 720px;
  }
}

/* ─── Inbox Header ─── */
.inbox-header {
  background: var(--bg-card);
  border-bottom: 1px solid var(--border-light);
  padding: 14px 16px 10px;
  position: sticky;
  top: 0;
  z-index: 10;
}

.header-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 8px;
  color: var(--text-strong);
}

.header-left h1 {
  font-family: var(--font-family);
  font-size: 19px;
  font-weight: 800;
  margin: 0;
  letter-spacing: -0.02em;
}

.unread-pill {
  background-color: var(--primary);
  color: white;
  font-size: 11px;
  font-weight: 700;
  padding: 1px 7px;
  border-radius: var(--radius-full);
}

.new-chat-btn {
  background: var(--primary-ghost);
  color: var(--primary);
  border: none;
  border-radius: var(--radius-full);
  padding: 6px 14px;
  font-family: var(--font-family);
  font-size: 13px;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  transition: all var(--duration-fast);
}

.new-chat-btn:active { transform: scale(0.94); }

.search-box {
  display: flex;
  align-items: center;
  gap: 8px;
  background: var(--bg-subtle);
  border-radius: var(--radius-full);
  padding: 8px 14px;
  border: 1px solid var(--border-light);
}

.search-box input {
  flex: 1;
  border: none;
  background: transparent;
  outline: none;
  font-size: 13.5px;
  color: var(--text-strong);
  font-family: var(--font-body);
}

.search-box input::placeholder { color: var(--text-muted); }

.clear-btn {
  background: none;
  border: none;
  color: var(--text-muted);
  cursor: pointer;
  font-size: 12px;
}

/* ─── State Container ─── */
.state-container {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 40vh;
  padding: 24px;
}

.state-container.compact { min-height: 120px; }

.spinner {
  width: 32px;
  height: 32px;
  border: 3px solid var(--border-light);
  border-top-color: var(--primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

.btn-spinner {
  width: 14px;
  height: 14px;
  border: 2px solid rgba(255, 255, 255, 0.4);
  border-top-color: #FFFFFF;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

/* ─── Conversations List ─── */
.conversations-list {
  padding: 10px 16px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.conv-card {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 14px;
  background: var(--bg-card);
  border-radius: var(--radius-lg);
  border: 1px solid var(--border-light);
  box-shadow: var(--shadow-xs);
  cursor: pointer;
  transition: all var(--duration-fast);
}

.conv-card:hover {
  border-color: rgba(255, 107, 53, 0.25);
  box-shadow: var(--shadow-sm);
}

.conv-card:active { transform: scale(0.985); }

.conv-card.has-unread {
  background: #FFFBF9;
  border-color: rgba(255, 107, 53, 0.3);
}

.avatar-wrap {
  width: 46px;
  height: 46px;
  border-radius: 50%;
  padding: 1.5px;
  background: linear-gradient(135deg, var(--primary), var(--primary-hover));
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  overflow: hidden;
}

.avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
}

.avatar-initial {
  color: white;
  font-family: var(--font-family);
  font-size: 18px;
  font-weight: 800;
}

.conv-content {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.conv-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.contact-name {
  font-family: var(--font-family);
  font-size: 14.5px;
  font-weight: 700;
  color: var(--text-strong);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.conv-time {
  font-size: 11.5px;
  color: var(--text-muted);
  flex-shrink: 0;
}

.conv-body {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.last-message {
  font-size: 13px;
  color: var(--text-secondary);
  margin: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  max-width: 220px;
}

.self-tag {
  color: var(--text-muted);
  font-weight: 600;
}

.unread-badge {
  background: var(--primary);
  color: white;
  font-size: 10.5px;
  font-weight: 700;
  padding: 1px 6px;
  border-radius: var(--radius-full);
  min-width: 18px;
  text-align: center;
}

/* ═════════════════════════════════════════════════════════════
   CHAT VIEW STYLES
   ═════════════════════════════════════════════════════════════ */
.chat-header {
  background: rgba(255, 255, 255, 0.94);
  backdrop-filter: blur(18px);
  -webkit-backdrop-filter: blur(18px);
  border-bottom: 1px solid var(--border-light);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 14px;
  position: sticky;
  top: 0;
  z-index: 20;
}

.back-btn {
  background: none;
  border: none;
  padding: 6px;
  cursor: pointer;
  color: var(--text-strong);
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
}

.back-btn:active { background: var(--bg-subtle); }

.contact-info {
  display: flex;
  align-items: center;
  gap: 10px;
}

.contact-avatar-small {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--primary), var(--primary-hover));
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 14px;
  overflow: hidden;
}

.contact-avatar-small img { width: 100%; height: 100%; object-fit: cover; }

.contact-text {
  display: flex;
  flex-direction: column;
}

.chat-name {
  font-family: var(--font-family);
  font-size: 14.5px;
  font-weight: 700;
  color: var(--text-strong);
}

.chat-status {
  font-size: 11px;
  color: var(--text-secondary);
}

.chat-messages {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
  display: flex;
  flex-direction: column;
}

.empty-chat-state {
  margin: auto;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  padding: 24px;
}

.chat-start-icon {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  background: var(--primary-ghost);
  color: var(--primary);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 8px;
}

.start-title {
  font-family: var(--font-family);
  font-size: 15px;
  font-weight: 700;
  color: var(--text-strong);
  margin: 0;
}

.start-sub {
  font-size: 13px;
  color: var(--text-secondary);
  margin: 0;
}

.messages-flow {
  display: flex;
  flex-direction: column;
  gap: 10px;
  justify-content: flex-end;
}

.message-wrapper {
  display: flex;
  width: 100%;
}

.message-wrapper.is-me {
  justify-content: flex-end;
}

.message-wrapper.is-contact {
  justify-content: flex-start;
}

.message-bubble {
  max-width: 78%;
  padding: 10px 14px;
  border-radius: var(--radius-lg);
  position: relative;
  box-shadow: var(--shadow-xs);
}

.is-me .message-bubble {
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
  color: #FFFFFF;
  border-bottom-right-radius: 4px;
}

.is-contact .message-bubble {
  background: var(--bg-card);
  color: var(--text-strong);
  border: 1px solid var(--border-light);
  border-bottom-left-radius: 4px;
}

.msg-text {
  font-size: 14px;
  line-height: 1.45;
  margin: 0 0 4px;
  word-break: break-word;
}

.msg-meta {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 4px;
}

.msg-time {
  font-size: 10px;
  opacity: 0.75;
}

.is-me .read-status {
  display: flex;
  align-items: center;
  opacity: 0.85;
}

/* ─── Chat Input Bar ─── */
.chat-input-bar {
  background: rgba(255, 255, 255, 0.94);
  backdrop-filter: blur(18px);
  -webkit-backdrop-filter: blur(18px);
  border-top: 1px solid var(--border-light);
  padding: 10px 14px;
  padding-bottom: max(10px, env(safe-area-inset-bottom));
  display: flex;
  align-items: center;
  gap: 8px;
}

.chat-input {
  flex: 1;
  border: 1.5px solid var(--border-light);
  border-radius: var(--radius-full);
  padding: 10px 16px;
  font-size: 14px;
  font-family: var(--font-body);
  background: var(--bg-subtle);
  color: var(--text-strong);
  outline: none;
  transition: all var(--duration-fast);
}

.chat-input:focus {
  border-color: var(--primary);
  background: #FFFFFF;
  box-shadow: 0 0 0 3px var(--primary-ghost);
}

.chat-input::placeholder { color: var(--text-muted); }

.chat-send-btn {
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
  color: white;
  border: none;
  width: 38px;
  height: 38px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  flex-shrink: 0;
  box-shadow: var(--shadow-primary);
  transition: all var(--duration-fast);
}

.chat-send-btn:active:not(:disabled) { transform: scale(0.92); }
.chat-send-btn:disabled { opacity: 0.4; cursor: not-allowed; box-shadow: none; }

/* ═════════════════════════════════════════════════════════════
   MODAL: NEW CHAT FRIEND PICKER
   ═════════════════════════════════════════════════════════════ */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(15, 23, 42, 0.45);
  z-index: 1100;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
  backdrop-filter: blur(4px);
}

.modal-card {
  background: var(--bg-card);
  border-radius: var(--radius-xl);
  max-width: 440px;
  width: 100%;
  max-height: 80vh;
  display: flex;
  flex-direction: column;
  box-shadow: var(--shadow-lg);
  border: 1px solid var(--border-light);
  overflow: hidden;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  border-bottom: 1px solid var(--border-light);
}

.modal-header h3 {
  font-size: 16.5px;
  font-weight: 800;
  margin: 0;
  color: var(--text-strong);
}

.modal-close {
  background: none;
  border: none;
  font-size: 16px;
  color: var(--text-secondary);
  cursor: pointer;
  padding: 4px;
}

.modal-body {
  padding: 12px 16px 20px;
  overflow-y: auto;
}

.empty-friends-modal {
  text-align: center;
  padding: 24px 12px;
}

.modal-empty-title {
  font-weight: 700;
  font-size: 15px;
  color: var(--text-strong);
  margin: 0 0 4px;
}

.modal-empty-sub {
  font-size: 13px;
  color: var(--text-secondary);
  margin: 0 0 12px;
  line-height: 1.4;
}

.friends-picker-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.friend-pick-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 12px;
  background: var(--bg-subtle);
  border-radius: var(--radius-md);
  cursor: pointer;
  transition: all var(--duration-fast);
}

.friend-pick-item:hover {
  background: var(--bg-muted);
}

.friend-pick-item:active { transform: scale(0.98); }

.avatar-wrap-sm {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--primary), var(--primary-hover));
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 15px;
  overflow: hidden;
  flex-shrink: 0;
}

.avatar-wrap-sm img { width: 100%; height: 100%; object-fit: cover; }

.friend-pick-details {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.friend-pick-name {
  font-family: var(--font-family);
  font-size: 14px;
  font-weight: 700;
  color: var(--text-strong);
}

.friend-pick-tipo {
  font-size: 12px;
  color: var(--text-secondary);
}

.pick-chevron {
  color: var(--text-muted);
}
</style>
