<template>
  <div class="page-container mod-page">
    <!-- Header -->
    <header class="mod-header animate-fadein-down">
      <div class="header-left">
        <button class="back-btn" @click="router.back()" aria-label="Volver">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        </button>
        <div>
          <h1 class="mod-title">Panel de Seguridad, Moderación y Auditoría</h1>
          <p class="mod-subtitle">Gestión de contenidos, usuarios, grupos RBAC, trazabilidad forense y resguardo (Puntos T02 / T03 / T06 / T07)</p>
        </div>
      </div>

      <div class="header-badge-wrap">
        <span class="badge badge-primary">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          {{ authStore.user?.tipoPerfil || 'Staff' }}
        </span>
      </div>
    </header>

    <!-- Navigation Tabs -->
    <div class="mod-tabs-bar animate-fadein-up">
      <button
        class="mod-tab-btn"
        :class="{ active: activeTab === 'publicaciones' }"
        @click="activeTab = 'publicaciones'"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
        <span>Publicaciones ({{ publicaciones.length }})</span>
      </button>

      <button
        class="mod-tab-btn"
        :class="{ active: activeTab === 'usuarios' }"
        @click="activeTab = 'usuarios'"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        <span>Usuarios y Claves ({{ usuarios.length }})</span>
      </button>

      <button
        class="mod-tab-btn"
        :class="{ active: activeTab === 'grupos' }"
        @click="activeTab = 'grupos'"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        <span>Grupos y Permisos ({{ gruposPermiso.length }})</span>
      </button>

      <button
        class="mod-tab-btn"
        :class="{ active: activeTab === 'auditoria' }"
        @click="activeTab = 'auditoria'"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
        <span>Auditoría T06 ({{ logs.length }})</span>
      </button>

      <button
        class="mod-tab-btn"
        :class="{ active: activeTab === 'resguardo' }"
        @click="activeTab = 'resguardo'"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        <span>Resguardo T07 (Backups)</span>
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="state-container">
      <div class="spinner"></div>
      <p class="text-secondary text-sm mt-2">Cargando módulos de seguridad y auditoría...</p>
    </div>

    <!-- Main Content -->
    <div v-else class="mod-content">
      <!-- ═════════════════════════════════════════════════════════════
           TAB 1: PUBLICACIONES (CU6)
           ═════════════════════════════════════════════════════════════ -->
      <section v-if="activeTab === 'publicaciones'" class="tab-pane animate-fadein-up">
        <div class="pane-toolbar">
          <div class="search-box">
            <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input
              type="text"
              v-model="searchPubs"
              placeholder="Buscar por título, autor, universidad o texto..."
              class="search-input"
            />
          </div>
          <div class="filter-pills">
            <button class="pill-btn" :class="{ active: filterPubEstado === 'todos' }" @click="filterPubEstado = 'todos'">
              Todos ({{ publicaciones.length }})
            </button>
            <button class="pill-btn" :class="{ active: filterPubEstado === 'activo' }" @click="filterPubEstado = 'activo'">
              Activas
            </button>
            <button class="pill-btn" :class="{ active: filterPubEstado === 'eliminado' }" @click="filterPubEstado = 'eliminado'">
              Ocultas/Eliminadas
            </button>
          </div>
        </div>

        <div v-if="filteredPublicaciones.length === 0" class="empty-state-wrap">
          <EmptyState
            icon="clipboard"
            title="No se encontraron publicaciones"
            subtitle="Prueba con otros términos de búsqueda o filtros de estado."
          />
        </div>

        <div v-else class="items-grid">
          <div
            v-for="pub in filteredPublicaciones"
            :key="pub.idPublicacion"
            class="mod-card"
            :class="{ 'card-deleted': pub.estado === 'eliminado', 'card-resolved': Number(pub.resuelta) > 0 }"
          >
            <div class="card-head">
              <div class="author-meta">
                <img :src="getProfilePic(pub.fotoPerfil)" alt="Avatar" class="avatar-sm" />
                <div>
                  <span class="author-name">{{ pub.nombre }} {{ pub.apellido }}</span>
                  <span class="pub-date">{{ pub.fecha }} • {{ pub.hora }}</span>
                </div>
              </div>
              <div class="status-badges">
                <span class="badge" :class="pub.tipoPublicacion === 'Experiencia' ? 'badge-primary' : 'badge-accent'">
                  {{ pub.tipoPublicacion }}
                </span>
                <span v-if="pub.estado === 'eliminado'" class="badge badge-danger">Eliminado</span>
                <span v-else-if="Number(pub.resuelta) > 0" class="badge badge-teal">Resuelta</span>
                <span v-else class="badge badge-success">Activa</span>
              </div>
            </div>

            <div class="card-body">
              <h4 v-if="pub.titulo" class="card-title">{{ pub.titulo }}</h4>
              <p class="card-text">{{ pub.texto }}</p>
              <div class="academic-tags">
                <span v-if="pub.nombreUniversidad" class="tag-chip">🏛 {{ pub.nombreUniversidad }}</span>
                <span v-if="pub.nombreCarrera" class="tag-chip">🎓 {{ pub.nombreCarrera }}</span>
              </div>
            </div>

            <div class="card-actions">
              <button class="btn btn-sm btn-secondary" @click="router.push(`/post/${pub.idPublicacion}`)">
                Ver Post
              </button>
              <button v-if="pub.estado !== 'eliminado'" class="btn btn-sm btn-danger-outline" @click="moderarPost(pub, 'eliminar')">
                Ocultar / Eliminar
              </button>
              <button v-if="pub.estado === 'eliminado'" class="btn btn-sm btn-success" @click="moderarPost(pub, 'reactivar')">
                Reactivar
              </button>
              <button v-if="pub.estado !== 'eliminado' && Number(pub.resuelta) === 0" class="btn btn-sm btn-secondary" @click="moderarPost(pub, 'cerrar')">
                Marcar Resuelta
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- ═════════════════════════════════════════════════════════════
           TAB 2: USUARIOS, ROLES Y CLAVES (PUNTOS T02 / T03 / CU5)
           ═════════════════════════════════════════════════════════════ -->
      <section v-else-if="activeTab === 'usuarios'" class="tab-pane animate-fadein-up">
        <div class="pane-toolbar">
          <div class="search-box">
            <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input
              type="text"
              v-model="searchUsers"
              placeholder="Buscar usuario por nombre, email o rol..."
              class="search-input"
            />
          </div>
          <button class="btn btn-primary btn-sm" @click="openNewUserModal">
            + Agregar Usuario
          </button>
        </div>

        <div v-if="filteredUsuarios.length === 0" class="empty-state-wrap">
          <EmptyState
            icon="book"
            title="No se encontraron usuarios"
            subtitle="Verifica el nombre o correo ingresado."
          />
        </div>

        <div v-else class="table-responsive">
          <table class="mod-table">
            <thead>
              <tr>
                <th>Usuario</th>
                <th>Email</th>
                <th>Perfil</th>
                <th>Rol de Sistema</th>
                <th>Asignar Grupo</th>
                <th>Estado</th>
                <th>Acciones de Seguridad</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="u in filteredUsuarios" :key="u.idUsuario">
                <td>
                  <div class="user-row-cell">
                    <img :src="getProfilePic(u.fotoPerfil)" alt="Avatar" class="avatar-xs" />
                    <div>
                      <strong>{{ u.nombre }} {{ u.apellido }}</strong>
                      <span class="text-xs text-muted d-block">ID: #{{ u.idUsuario }}</span>
                    </div>
                  </div>
                </td>
                <td class="text-sm">{{ u.email }}</td>
                <td>
                  <span class="badge badge-subtle">{{ u.tipoPerfil || 'Estudiante' }}</span>
                </td>
                <td>
                  <select
                    class="role-select"
                    :value="u.idRol"
                    @change="cambiarRol(u, $event.target.value)"
                  >
                    <option v-for="rol in roles" :key="rol.idRol" :value="rol.idRol">
                      {{ rol.nombreRol }}
                    </option>
                  </select>
                </td>
                <td>
                  <select
                    class="role-select"
                    @change="asignarGrupoUsuario(u, $event.target.value); $event.target.value = ''"
                  >
                    <option value="">+ Vincular grupo...</option>
                    <option v-for="g in gruposPermiso" :key="g.idGrupoPermiso" :value="g.idGrupoPermiso">
                      {{ g.nombre }}
                    </option>
                  </select>
                </td>
                <td>
                  <span class="badge" :class="u.estadoCuenta === 'activo' ? 'badge-success' : 'badge-danger'">
                    {{ u.estadoCuenta === 'activo' ? 'Activa' : 'Bloqueada' }}
                  </span>
                </td>
                <td>
                  <div class="btn-group-sm">
                    <button
                      class="btn btn-xs btn-secondary"
                      title="Resetear contraseña del usuario"
                      @click="resetearClave(u)"
                    >
                      🔑 Reset Clave
                    </button>
                    <button
                      v-if="u.estadoCuenta === 'activo'"
                      class="btn btn-xs btn-danger-outline"
                      @click="cambiarEstadoUsuario(u, 'bloquear')"
                    >
                      Bloquear
                    </button>
                    <button
                      v-else
                      class="btn btn-xs btn-success"
                      @click="cambiarEstadoUsuario(u, 'activar')"
                    >
                      Activar
                    </button>
                    <button
                      class="btn btn-xs btn-secondary"
                      @click="router.push(`/perfil/${u.idUsuario}`)"
                    >
                      Perfil
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- ═════════════════════════════════════════════════════════════
           TAB 3: GRUPOS Y PERMISOS RBAC (PUNTOS T02 y T03)
           ═════════════════════════════════════════════════════════════ -->
      <section v-else-if="activeTab === 'grupos'" class="tab-pane animate-fadein-up">
        <div class="pane-toolbar">
          <div>
            <h3 class="section-subtitle">Administración de Grupos de Permisos (Patrón Composite RBAC)</h3>
            <p class="text-xs text-muted">Agrega, modifica o elimina grupos de seguridad y asigna permisos granulares.</p>
          </div>
          <button class="btn btn-primary btn-sm" @click="openGrupoModal()">
            + Nuevo Grupo de Permisos
          </button>
        </div>

        <div class="items-grid">
          <div v-for="g in gruposPermiso" :key="g.idGrupoPermiso" class="mod-card">
            <div class="card-head">
              <div>
                <h4 class="card-title" style="margin-bottom:2px;">🛡️ {{ g.nombre }}</h4>
                <span class="text-xs text-muted">ID Grupo: #{{ g.idGrupoPermiso }} • {{ g.totalUsuarios || 0 }} miembro(s)</span>
              </div>
              <span class="badge badge-primary">{{ (g.permisos || []).length }} permisos</span>
            </div>

            <div class="card-body">
              <p class="card-text">{{ g.descripcion || 'Sin descripción especificada.' }}</p>
              <div class="academic-tags" v-if="g.permisos && g.permisos.length">
                <span v-for="p in g.permisos" :key="p.idPermiso" class="tag-chip font-mono">
                  ✓ {{ p.codigoAccion }}
                </span>
              </div>
              <p v-else class="text-xs text-muted">Permisos heredados por política de rol.</p>
            </div>

            <div class="card-actions">
              <button class="btn btn-sm btn-secondary" @click="openGrupoModal(g)">
                Editar Grupo y Permisos
              </button>
              <button class="btn btn-sm btn-danger-outline" @click="eliminarGrupo(g)">
                Eliminar
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- ═════════════════════════════════════════════════════════════
           TAB 4: LOG DE AUDITORÍA Y TRAZABILIDAD (PUNTO T06 / CU9)
           ═════════════════════════════════════════════════════════════ -->
      <section v-else-if="activeTab === 'auditoria'" class="tab-pane animate-fadein-up">
        <div class="pane-toolbar">
          <div class="search-box">
            <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input
              type="text"
              v-model="searchLogs"
              placeholder="Buscar por acción (LOGIN, LOGOUT, CAMBIAR_ROL, DELETE...), tabla o usuario..."
              class="search-input"
            />
          </div>
          <div class="filter-pills">
            <button class="pill-btn" :class="{ active: filterLogCategoria === 'todos' }" @click="filterLogCategoria = 'todos'">Todos</button>
            <button class="pill-btn" :class="{ active: filterLogCategoria === 'sesiones' }" @click="filterLogCategoria = 'sesiones'">Login / Logout</button>
            <button class="pill-btn" :class="{ active: filterLogCategoria === 'seguridad' }" @click="filterLogCategoria = 'seguridad'">Roles / Claves / Grupos</button>
            <button class="pill-btn" :class="{ active: filterLogCategoria === 'moderacion' }" @click="filterLogCategoria = 'moderacion'">Moderación y Contenido</button>
          </div>
          <button class="btn btn-secondary btn-sm" @click="fetchLogs">
            Refrescar Log
          </button>
        </div>

        <div v-if="filteredLogs.length === 0" class="empty-state-wrap">
          <EmptyState
            icon="clipboard"
            title="Sin registros de auditoría para este filtro"
            subtitle="Las acciones críticas del sistema y su estado original/nuevo se registran automáticamente."
          />
        </div>

        <div v-else class="table-responsive">
          <table class="mod-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Fecha y Hora</th>
                <th>Acción</th>
                <th>Usuario Actor</th>
                <th>Tabla</th>
                <th>Trazabilidad (Valor Original → Nuevo)</th>
                <th>Detalles e IP</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="log in filteredLogs" :key="log.idLog">
                <td class="text-xs text-muted font-mono">#{{ log.idLog }}</td>
                <td class="text-xs whitespace-nowrap">{{ log.fechaHora }}</td>
                <td>
                  <span class="badge badge-accent font-mono text-xs">{{ log.accion }}</span>
                </td>
                <td class="text-sm">
                  <span v-if="log.nombre">{{ log.nombre }} {{ log.apellido }} (#{{ log.idUsuario }})</span>
                  <span v-else class="text-muted">Sistema / Invitado (#{{ log.idUsuario || 0 }})</span>
                </td>
                <td>
                  <span class="tag-chip font-mono text-xs">{{ log.tablaAfectada || 'general' }}</span>
                </td>
                <td class="text-xs font-mono">
                  <div v-if="log.valorOriginal || log.valorNuevo" class="trace-box">
                    <span v-if="log.valorOriginal" class="trace-old" title="Estado Original">Antes: {{ log.valorOriginal }}</span>
                    <span v-if="log.valorNuevo" class="trace-new" title="Estado Transformado">Después: {{ log.valorNuevo }}</span>
                  </div>
                  <span v-else class="text-muted">—</span>
                </td>
                <td class="text-sm">
                  <div>{{ log.detalles || '—' }}</div>
                  <span v-if="log.ip" class="text-xs text-muted font-mono">IP: {{ log.ip }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- ═════════════════════════════════════════════════════════════
           TAB 5: RESGUARDO Y RESTAURACIÓN (PUNTO T07 / CU9)
           ═════════════════════════════════════════════════════════════ -->
      <section v-else-if="activeTab === 'resguardo'" class="tab-pane animate-fadein-up">
        <div class="mod-card" style="margin-bottom:20px;">
          <div class="card-head">
            <div>
              <h3 class="card-title" style="font-size:1.1rem;">📦 Subsistema de Resguardo y Restauración Transaccional (Punto T07)</h3>
              <p class="text-sm text-secondary" style="margin:4px 0 0;">
                Genera en tiempo real un volcado SQL completo de todas las tablas de U-Tell junto con su suma de verificación criptográfica <strong>SHA-256</strong> para garantizar la integridad y recuperabilidad ante desastres.
              </p>
            </div>
            <button class="btn btn-primary" :disabled="isGeneratingBackup" @click="generarYDescargarBackup">
              {{ isGeneratingBackup ? 'Generando Resguardo...' : '⬇ Generar y Descargar Backup SQL + SHA-256' }}
            </button>
          </div>

          <div class="policy-grid">
            <div class="policy-item">
              <strong>Estrategia y Política</strong>
              <span>Esquema GFS (Grandfather-Father-Son) + Regla 3-2-1</span>
            </div>
            <div class="policy-item">
              <strong>Frecuencia Programada</strong>
              <span>Diaria automatizada (03:00 AM UTC-3) + On-Demand</span>
            </div>
            <div class="policy-item">
              <strong>Retención</strong>
              <span>7 días (diario) • 4 semanas (semanal) • 12 meses (mensual)</span>
            </div>
            <div class="policy-item">
              <strong>Verificación de Integridad</strong>
              <span>Hash criptográfico SHA-256 + Transacción InnoDB consistente</span>
            </div>
          </div>
        </div>

        <div v-if="lastBackup" class="mod-card animate-fadein-up">
          <div class="card-head">
            <div>
              <h4 class="card-title">✅ Último Resguardo Generado: {{ lastBackup.nombreArchivo }}</h4>
              <span class="text-xs text-muted">Fecha: {{ lastBackup.fechaHora }} • Tamaño: {{ (lastBackup.tamanioBytes / 1024).toFixed(2) }} KB</span>
            </div>
            <div class="status-badges">
              <span class="badge badge-success">{{ lastBackup.totalTablas }} Tablas</span>
              <span class="badge badge-primary">{{ lastBackup.totalRegistros }} Registros</span>
            </div>
          </div>

          <div class="sha-box font-mono text-xs">
            <strong>Huella de Integridad SHA-256:</strong> {{ lastBackup.sha256 }}
          </div>

          <div class="academic-tags" style="margin-top:8px;">
            <span v-for="t in lastBackup.resumenTablas" :key="t.tabla" class="tag-chip font-mono">
              {{ t.tabla }}: <strong>{{ t.registros }}</strong> filas
            </span>
          </div>
        </div>
      </section>
    </div>

    <!-- Modal: Crear / Editar Grupo de Permisos (Puntos T02/T03) -->
    <div v-if="showGrupoModal" class="modal-backdrop" @click.self="showGrupoModal = false">
      <div class="modal-card">
        <h3 class="card-title" style="font-size:1.1rem; margin-bottom:12px;">
          {{ grupoForm.idGrupoPermiso ? 'Modificar Grupo de Permisos' : 'Nuevo Grupo de Permisos' }}
        </h3>
        <form @submit.prevent="guardarGrupo">
          <div class="form-field">
            <label>Nombre del Grupo</label>
            <input type="text" v-model="grupoForm.nombre" required class="search-input" style="padding-left:12px;" placeholder="Ej. Auditores Académicos" />
          </div>
          <div class="form-field">
            <label>Descripción</label>
            <input type="text" v-model="grupoForm.descripcion" class="search-input" style="padding-left:12px;" placeholder="Responsabilidades del grupo..." />
          </div>
          <div class="form-field">
            <label>Permisos Asignados al Grupo</label>
            <div class="perms-checklist">
              <label v-for="p in catalogoPermisos" :key="p.idPermiso" class="perm-check-item">
                <input type="checkbox" :value="Number(p.idPermiso)" v-model="grupoForm.permisos" />
                <span><strong>{{ p.codigoAccion }}</strong> — {{ p.nombre }}</span>
              </label>
            </div>
          </div>
          <div class="card-actions" style="justify-content:flex-end;">
            <button type="button" class="btn btn-sm btn-secondary" @click="showGrupoModal = false">Cancelar</button>
            <button type="submit" class="btn btn-sm btn-primary">Guardar Grupo</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal: Agregar Usuario desde Administración (Puntos T02/T03) -->
    <div v-if="showUserModal" class="modal-backdrop" @click.self="showUserModal = false">
      <div class="modal-card">
        <h3 class="card-title" style="font-size:1.1rem; margin-bottom:12px;">Agregar Nuevo Usuario</h3>
        <form @submit.prevent="guardarNuevoUsuario">
          <div class="form-field">
            <label>Nombre</label>
            <input type="text" v-model="userForm.nombre" required class="search-input" style="padding-left:12px;" />
          </div>
          <div class="form-field">
            <label>Apellido</label>
            <input type="text" v-model="userForm.apellido" required class="search-input" style="padding-left:12px;" />
          </div>
          <div class="form-field">
            <label>Email</label>
            <input type="email" v-model="userForm.email" required class="search-input" style="padding-left:12px;" />
          </div>
          <div class="form-field">
            <label>Contraseña Inicial</label>
            <input type="password" v-model="userForm.password" minlength="6" required class="search-input" style="padding-left:12px;" />
          </div>
          <div class="form-field">
            <label>Tipo de Perfil</label>
            <select v-model="userForm.tipoPerfil" class="search-input" style="padding-left:12px;">
              <option value="Aspirante">Aspirante / Postulante</option>
              <option value="Estudiante">Estudiante</option>
              <option value="Docente">Docente</option>
              <option value="Moderador">Moderador</option>
              <option value="Administrador">Administrador</option>
            </select>
          </div>
          <div class="card-actions" style="justify-content:flex-end;">
            <button type="button" class="btn btn-sm btn-secondary" @click="showUserModal = false">Cancelar</button>
            <button type="submit" class="btn btn-sm btn-primary">Crear Usuario</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../store/auth'
import { useToastStore } from '../store/toast'
import { getProfilePic } from '../utils'
import api from '../api'
import EmptyState from '../components/EmptyState.vue'

const router     = useRouter()
const authStore  = useAuthStore()
const toastStore = useToastStore()

const activeTab          = ref('publicaciones')
const isLoading          = ref(true)
const publicaciones      = ref([])
const usuarios           = ref([])
const roles              = ref([])
const gruposPermiso      = ref([])
const catalogoPermisos   = ref([])
const logs               = ref([])

const searchPubs         = ref('')
const filterPubEstado    = ref('todos')
const searchUsers        = ref('')
const searchLogs         = ref('')
const filterLogCategoria = ref('todos')

const showGrupoModal     = ref(false)
const grupoForm          = ref({ idGrupoPermiso: null, nombre: '', descripcion: '', permisos: [] })

const showUserModal      = ref(false)
const userForm           = ref({ nombre: '', apellido: '', email: '', password: '', tipoPerfil: 'Estudiante' })

const isGeneratingBackup = ref(false)
const lastBackup         = ref(null)

const fetchPublicaciones = async () => {
  try {
    const res = await api.get('/moderacion/publicaciones')
    publicaciones.value = Array.isArray(res.data) ? res.data : []
  } catch (e) {
    console.error('Error fetching publicaciones:', e)
  }
}

const fetchUsuarios = async () => {
  try {
    const [resUsers, resRoles] = await Promise.all([
      api.get('/moderacion/usuarios'),
      api.get('/roles')
    ])
    usuarios.value = Array.isArray(resUsers.data) ? resUsers.data : []
    roles.value    = Array.isArray(resRoles.data) ? resRoles.data : []
  } catch (e) {
    console.error('Error fetching usuarios y roles:', e)
  }
}

const fetchGruposYPermisos = async () => {
  try {
    const [resGrupos, resPerms] = await Promise.all([
      api.get('/grupos-permiso'),
      api.get('/permisos')
    ])
    gruposPermiso.value    = Array.isArray(resGrupos.data) ? resGrupos.data : []
    catalogoPermisos.value = Array.isArray(resPerms.data) ? resPerms.data : []
  } catch (e) {
    console.error('Error fetching grupos y permisos:', e)
  }
}

const fetchLogs = async () => {
  try {
    const res = await api.get('/moderacion/auditoria')
    logs.value = Array.isArray(res.data) ? res.data : []
  } catch (e) {
    console.error('Error fetching logs de auditoría:', e)
  }
}

const initData = async () => {
  isLoading.value = true
  try {
    await Promise.all([
      fetchPublicaciones(),
      fetchUsuarios(),
      fetchGruposYPermisos(),
      fetchLogs()
    ])
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  initData()
})

// ─── Filtered Computeds ───────────────────────────────────────────────────────
const filteredPublicaciones = computed(() => {
  let list = publicaciones.value
  if (filterPubEstado.value === 'activo') {
    list = list.filter(p => p.estado === 'activo')
  } else if (filterPubEstado.value === 'eliminado') {
    list = list.filter(p => p.estado === 'eliminado')
  }

  if (searchPubs.value.trim()) {
    const q = searchPubs.value.toLowerCase()
    list = list.filter(p =>
      (p.titulo && p.titulo.toLowerCase().includes(q)) ||
      (p.texto && p.texto.toLowerCase().includes(q)) ||
      (p.nombre && p.nombre.toLowerCase().includes(q)) ||
      (p.apellido && p.apellido.toLowerCase().includes(q)) ||
      (p.nombreUniversidad && p.nombreUniversidad.toLowerCase().includes(q))
    )
  }
  return list
})

const filteredUsuarios = computed(() => {
  if (!searchUsers.value.trim()) return usuarios.value
  const q = searchUsers.value.toLowerCase()
  return usuarios.value.filter(u =>
    (u.nombre && u.nombre.toLowerCase().includes(q)) ||
    (u.apellido && u.apellido.toLowerCase().includes(q)) ||
    (u.email && u.email.toLowerCase().includes(q)) ||
    (u.nombreRol && u.nombreRol.toLowerCase().includes(q))
  )
})

const filteredLogs = computed(() => {
  let list = logs.value
  if (filterLogCategoria.value === 'sesiones') {
    list = list.filter(l => /LOGIN|LOGOUT|REGISTRO/i.test(l.accion || ''))
  } else if (filterLogCategoria.value === 'seguridad') {
    list = list.filter(l => /ROL|CLAVE|GRUPO|BLOQUEAR|REACTIVAR|BACKUP/i.test(l.accion || ''))
  } else if (filterLogCategoria.value === 'moderacion') {
    list = list.filter(l => /PUBLICACION|POST|MENSAJE|MODER/i.test(l.accion || ''))
  }

  if (!searchLogs.value.trim()) return list
  const q = searchLogs.value.toLowerCase()
  return list.filter(l =>
    (l.accion && l.accion.toLowerCase().includes(q)) ||
    (l.detalles && l.detalles.toLowerCase().includes(q)) ||
    (l.tablaAfectada && l.tablaAfectada.toLowerCase().includes(q)) ||
    (l.nombre && l.nombre.toLowerCase().includes(q))
  )
})

// ─── Actions ──────────────────────────────────────────────────────────────────
const moderarPost = async (pub, accion) => {
  let motivo = null
  if (accion === 'eliminar') {
    motivo = prompt('Motivo de moderación (se registrará en auditoría):', 'Infracción a las normas comunitarias')
    if (motivo === null) return
  }

  try {
    await api.post('/moderacion/publicacion', {
      idPublicacion: pub.idPublicacion,
      accion,
      motivo
    })
    toastStore.success(`Publicación #${pub.idPublicacion} moderada (${accion}).`)
    await fetchPublicaciones()
    await fetchLogs()
  } catch (e) {
    toastStore.error('Error al moderar publicación.')
  }
}

const cambiarRol = async (user, nuevoIdRol) => {
  const rolId = Number(nuevoIdRol)
  if (rolId === Number(user.idRol)) return

  try {
    await api.put(`/usuarios/${user.idUsuario}/rol`, { idRol: rolId })
    user.idRol = rolId
    toastStore.success(`Rol de ${user.nombre} actualizado y auditado.`)
    await fetchLogs()
  } catch (e) {
    toastStore.error('No se pudo actualizar el rol.')
  }
}

const asignarGrupoUsuario = async (user, idGrupoPermiso) => {
  if (!idGrupoPermiso) return
  try {
    await api.post(`/usuarios/${user.idUsuario}/grupos`, { idGrupoPermiso: Number(idGrupoPermiso) })
    toastStore.success(`Usuario ${user.nombre} vinculado al grupo #${idGrupoPermiso}.`)
    await fetchGruposYPermisos()
    await fetchLogs()
  } catch (e) {
    toastStore.error('No se pudo asignar el grupo al usuario.')
  }
}

const resetearClave = async (user) => {
  const nuevaClave = prompt(`Ingresa la nueva contraseña temporal para ${user.nombre} ${user.apellido} (${user.email}):`, 'Utell2026!')
  if (!nuevaClave) return
  if (nuevaClave.length < 6) {
    toastStore.warning('La contraseña debe tener al menos 6 caracteres.')
    return
  }

  try {
    await api.put(`/usuarios/${user.idUsuario}/reset-clave`, { nuevaClave })
    toastStore.success(`Contraseña de ${user.email} reseteada y registrada en auditoría.`)
    await fetchLogs()
  } catch (e) {
    toastStore.error('Error al resetear la contraseña.')
  }
}

const cambiarEstadoUsuario = async (user, accion) => {
  const confirmMsg = accion === 'bloquear'
    ? `¿Estás seguro de bloquear la cuenta de ${user.nombre} ${user.apellido}?`
    : `¿Reactivar la cuenta de ${user.nombre} ${user.apellido}?`

  if (!confirm(confirmMsg)) return

  try {
    await api.put(`/usuarios/${user.idUsuario}/estado`, { accion })
    user.estadoCuenta = accion === 'bloquear' ? 'inactivo' : 'activo'
    toastStore.success(`Cuenta de ${user.nombre} actualizada a ${user.estadoCuenta}.`)
    await fetchLogs()
  } catch (e) {
    toastStore.error('Error al cambiar estado de cuenta.')
  }
}

// ─── Crear Usuario desde Admin (Puntos T02/T03) ───────────────────────────────
const openNewUserModal = () => {
  userForm.value = { nombre: '', apellido: '', email: '', password: '', tipoPerfil: 'Estudiante' }
  showUserModal.value = true
}

const guardarNuevoUsuario = async () => {
  try {
    await api.post('/usuarios/nuevo', {
      ...userForm.value,
      fNac: '2002-01-01',
      pais: 'Argentina',
      provincia: 'Santa Fe',
      ciudad: 'Rosario'
    })
    toastStore.success('Usuario creado exitosamente.')
    showUserModal.value = false
    await fetchUsuarios()
    await fetchLogs()
  } catch (e) {
    toastStore.error(e?.response?.data?.mensaje || 'Error al crear el usuario.')
  }
}

// ─── ABM Grupos de Permisos (Puntos T02/T03) ──────────────────────────────────
const openGrupoModal = (grupo = null) => {
  if (grupo) {
    grupoForm.value = {
      idGrupoPermiso: grupo.idGrupoPermiso,
      nombre: grupo.nombre,
      descripcion: grupo.descripcion || '',
      permisos: (grupo.permisos || []).map(p => Number(p.idPermiso))
    }
  } else {
    grupoForm.value = { idGrupoPermiso: null, nombre: '', descripcion: '', permisos: [] }
  }
  showGrupoModal.value = true
}

const guardarGrupo = async () => {
  try {
    if (grupoForm.value.idGrupoPermiso) {
      await api.put(`/grupos-permiso/${grupoForm.value.idGrupoPermiso}`, grupoForm.value)
      toastStore.success('Grupo de permisos actualizado.')
    } else {
      await api.post('/grupos-permiso', grupoForm.value)
      toastStore.success('Grupo de permisos creado.')
    }
    showGrupoModal.value = false
    await fetchGruposYPermisos()
    await fetchLogs()
  } catch (e) {
    toastStore.error('Error al guardar el grupo de permisos.')
  }
}

const eliminarGrupo = async (grupo) => {
  if (!confirm(`¿Eliminar el grupo de permisos "${grupo.nombre}"?`)) return
  try {
    await api.delete(`/grupos-permiso/${grupo.idGrupoPermiso}`)
    toastStore.success('Grupo eliminado.')
    await fetchGruposYPermisos()
    await fetchLogs()
  } catch (e) {
    toastStore.error('No se pudo eliminar el grupo.')
  }
}

// ─── Resguardo y Restauración SQL (Punto T07) ─────────────────────────────────
const generarYDescargarBackup = async () => {
  isGeneratingBackup.value = true
  try {
    const res = await api.get('/moderacion/backup/exportar')
    const data = res.data
    lastBackup.value = data

    const blob = new Blob([data.contenidoSql], { type: 'application/sql;charset=utf-8;' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = data.nombreArchivo || 'utell_backup.sql'
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    URL.revokeObjectURL(url)

    toastStore.success(`Resguardo ${data.nombreArchivo} descargado y verificado (SHA-256).`)
    await fetchLogs()
  } catch (e) {
    toastStore.error('Error al generar el resguardo de base de datos.')
  } finally {
    isGeneratingBackup.value = false
  }
}
</script>

<style scoped>
.mod-page {
  padding-bottom: 60px;
}

.mod-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 0;
  border-bottom: 1px solid var(--border-light);
  margin-bottom: 20px;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 14px;
}

.back-btn {
  background: var(--bg-card);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-sm);
  width: 38px;
  height: 38px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: var(--text-body);
  cursor: pointer;
  transition: all var(--duration-fast) var(--ease-out);
}

.back-btn:hover {
  background: var(--bg-subtle);
  color: var(--text-strong);
}

.mod-title {
  font-size: 1.35rem;
  font-weight: 700;
  color: var(--text-strong);
  margin: 0;
}

.mod-subtitle {
  font-size: 0.82rem;
  color: var(--text-secondary);
  margin: 2px 0 0;
}

.section-subtitle {
  margin: 0;
  font-size: 1rem;
  font-weight: 700;
  color: var(--text-strong);
}

.mod-tabs-bar {
  display: flex;
  gap: 8px;
  margin-bottom: 20px;
  border-bottom: 1px solid var(--border-light);
  padding-bottom: 8px;
  overflow-x: auto;
}

.mod-tab-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  border-radius: var(--radius-sm);
  background: transparent;
  border: none;
  color: var(--text-secondary);
  font-weight: 600;
  font-size: 0.88rem;
  cursor: pointer;
  transition: all var(--duration-fast) var(--ease-out);
  white-space: nowrap;
}

.mod-tab-btn:hover {
  background: var(--bg-subtle);
  color: var(--text-strong);
}

.mod-tab-btn.active {
  background: var(--primary-ghost);
  color: var(--primary);
}

.pane-toolbar {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 20px;
}

.search-box {
  position: relative;
  flex: 1;
  min-width: 240px;
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-muted);
}

.search-input {
  width: 100%;
  padding: 10px 14px 10px 38px;
  background: var(--bg-card);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-md);
  color: var(--text-strong);
  font-size: 0.88rem;
  outline: none;
  transition: border-color var(--duration-fast);
}

.search-input:focus {
  border-color: var(--primary);
}

.filter-pills {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}

.pill-btn {
  padding: 6px 12px;
  font-size: 0.8rem;
  font-weight: 600;
  border-radius: var(--radius-full);
  border: 1px solid var(--border-light);
  background: var(--bg-card);
  color: var(--text-secondary);
  cursor: pointer;
  transition: all var(--duration-fast);
}

.pill-btn.active {
  background: var(--primary);
  border-color: var(--primary);
  color: #fff;
}

.items-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 16px;
}

.mod-card {
  background: var(--bg-card);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-md);
  padding: 16px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  gap: 12px;
  transition: box-shadow var(--duration-fast);
}

.mod-card:hover {
  box-shadow: var(--shadow-sm);
}

.card-deleted {
  border-left: 4px solid var(--error);
  opacity: 0.75;
}

.card-resolved {
  border-left: 4px solid var(--teal);
}

.card-head {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 10px;
  flex-wrap: wrap;
}

.author-meta {
  display: flex;
  align-items: center;
  gap: 10px;
}

.avatar-sm {
  width: 34px;
  height: 34px;
  border-radius: var(--radius-full);
  object-fit: cover;
}

.avatar-xs {
  width: 28px;
  height: 28px;
  border-radius: var(--radius-full);
  object-fit: cover;
}

.author-name {
  display: block;
  font-weight: 600;
  font-size: 0.88rem;
  color: var(--text-strong);
}

.pub-date {
  font-size: 0.75rem;
  color: var(--text-muted);
}

.status-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  justify-content: flex-end;
}

.card-title {
  font-size: 0.95rem;
  font-weight: 700;
  color: var(--text-strong);
  margin: 0 0 6px;
}

.card-text {
  font-size: 0.85rem;
  color: var(--text-body);
  line-height: 1.4;
  margin: 0 0 10px;
}

.academic-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.tag-chip {
  font-size: 0.72rem;
  background: var(--bg-subtle);
  color: var(--text-secondary);
  padding: 3px 8px;
  border-radius: var(--radius-xs);
  font-weight: 500;
}

.card-actions {
  display: flex;
  gap: 8px;
  border-top: 1px solid var(--border-light);
  padding-top: 10px;
  flex-wrap: wrap;
}

.table-responsive {
  overflow-x: auto;
  background: var(--bg-card);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-md);
}

.mod-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
}

.mod-table th {
  padding: 12px 14px;
  font-size: 0.75rem;
  font-weight: 700;
  color: var(--text-secondary);
  background: var(--bg-subtle);
  border-bottom: 1px solid var(--border-light);
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.mod-table td {
  padding: 12px 14px;
  border-bottom: 1px solid var(--border-light);
  color: var(--text-body);
  vertical-align: middle;
}

.user-row-cell {
  display: flex;
  align-items: center;
  gap: 10px;
}

.role-select {
  padding: 4px 8px;
  border-radius: var(--radius-xs);
  border: 1px solid var(--border-light);
  background: var(--bg-card);
  color: var(--text-strong);
  font-size: 0.78rem;
  font-weight: 600;
  outline: none;
}

.btn-group-sm {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}

.btn-xs {
  padding: 4px 8px;
  font-size: 0.74rem;
  border-radius: var(--radius-xs);
  font-weight: 600;
  cursor: pointer;
  border: 1px solid transparent;
}

.badge-danger {
  background: var(--error-bg);
  color: var(--error);
}

.badge-teal {
  background: var(--teal-light);
  color: var(--teal);
}

.trace-box {
  display: flex;
  flex-direction: column;
  gap: 3px;
  max-width: 280px;
  word-break: break-all;
}

.trace-old {
  color: var(--error);
  background: rgba(239, 68, 68, 0.08);
  padding: 2px 6px;
  border-radius: 4px;
}

.trace-new {
  color: var(--teal);
  background: rgba(20, 184, 166, 0.08);
  padding: 2px 6px;
  border-radius: 4px;
}

.policy-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 12px;
  margin-top: 8px;
}

.policy-item {
  background: var(--bg-subtle);
  padding: 10px 12px;
  border-radius: var(--radius-sm);
  display: flex;
  flex-direction: column;
  gap: 4px;
  font-size: 0.82rem;
}

.sha-box {
  background: var(--bg-subtle);
  padding: 10px 12px;
  border-radius: var(--radius-xs);
  word-break: break-all;
  color: var(--text-strong);
}

.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.55);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
  z-index: 200;
}

.modal-card {
  background: var(--bg-card);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-md);
  padding: 22px;
  width: 100%;
  max-width: 480px;
  box-shadow: var(--shadow-lg);
}

.form-field {
  margin-bottom: 14px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-field label {
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--text-secondary);
}

.perms-checklist {
  max-height: 180px;
  overflow-y: auto;
  border: 1px solid var(--border-light);
  border-radius: var(--radius-xs);
  padding: 8px 10px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.perm-check-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.8rem;
  cursor: pointer;
}
</style>
