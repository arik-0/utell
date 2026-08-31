<template>
  <div class="dashboard-page">
    <!-- ═════════════════════════════════════════════════════════════
         LOGIN MODAL / AUTHENTICATION GATE (SOLO UNIVERSIDADES Y ADMINS)
         ═════════════════════════════════════════════════════════════ -->
    <div v-if="!isAuthorized" class="auth-gate-container animate-fadein-up">
      <div class="auth-gate-card">
        <div class="auth-gate-header">
          <div class="uai-badge">UAI • Bases de Datos Aplicada</div>
          <div class="access-restriction-tag">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <span>Acceso Restringido: Universidades y Staff</span>
          </div>
          <h2 class="auth-title">Tablero de Control Académico</h2>
          <p class="auth-subtitle">Módulo analítico institucional reservado exclusivamente para autoridades universitarias, administradores y moderadores de cátedra.</p>
        </div>

        <div v-if="authStore.user && !isAuthorized" class="alert-box alert-warning">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <div>
            <strong>Perfil no autorizado:</strong> Ha iniciado sesión como <em>{{ authStore.user.tipoPerfil }}</em> ({{ authStore.user.nombre }} {{ authStore.user.apellido }}). Ingrese con una cuenta autorizada para acceder al tablero.
          </div>
        </div>

        <form @submit.prevent="handleDashboardLogin" class="auth-form">
          <div class="form-group">
            <label class="form-label">Correo Institucional / Administrativo</label>
            <div class="input-with-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
              <input 
                type="email" 
                v-model="loginForm.email" 
                placeholder="ej: rectorado@uai.edu.ar o admin@uai.edu.ar" 
                required
                class="form-control"
              />
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Contraseña</label>
            <div class="input-with-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              <input 
                type="password" 
                v-model="loginForm.password" 
                placeholder="••••••••" 
                required
                class="form-control"
              />
            </div>
          </div>

          <div v-if="loginError" class="alert-box alert-danger">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span>{{ loginError }}</span>
          </div>

          <button type="submit" class="btn btn-primary btn-block" :disabled="isLoggingIn">
            <span v-if="isLoggingIn" class="spinner-sm"></span>
            <span>{{ isLoggingIn ? 'Validando Roles y Credenciales...' : 'Ingresar al Tablero' }}</span>
          </button>
        </form>

        <div class="auth-demo-hint">
          <strong>Cuentas autorizadas para evaluación de cátedra:</strong>
          <div class="demo-accounts-grid">
            <div class="demo-account-item" @click="setDemoAccount('rectorado@uai.edu.ar')">
              <span class="demo-role-badge badge-uni">🏛️ Universidad UAI</span>
              <code>rectorado@uai.edu.ar</code> (Pass: 123456)
            </div>
            <div class="demo-account-item" @click="setDemoAccount('admin@uai.edu.ar')">
              <span class="demo-role-badge badge-admin">🛡️ Administrador</span>
              <code>admin@uai.edu.ar</code> (Pass: 123456)
            </div>
            <div class="demo-account-item" @click="setDemoAccount('moderador@uai.edu.ar')">
              <span class="demo-role-badge badge-mod">⚖️ Moderador</span>
              <code>moderador@uai.edu.ar</code> (Pass: 123456)
            </div>
            <div class="demo-account-item" @click="setDemoAccount('lucia.albarracin@uai.edu.ar')">
              <span class="demo-role-badge badge-doc">🎓 Docente Cátedra</span>
              <code>lucia.albarracin@uai.edu.ar</code> (Pass: 123456)
            </div>
          </div>
        </div>

      </div>
    </div>


    <!-- ═════════════════════════════════════════════════════════════
         DASHBOARD MAIN CONTAINER (AUTHENTICATED)
         ═════════════════════════════════════════════════════════════ -->
    <div v-else class="dashboard-wrapper animate-fadein-up">
      <!-- ─── TOP NAVBAR & BREADCRUMBS ─── -->
      <header class="dashboard-header">
        <div class="header-main-row">
          <div class="header-brand">
            <div class="header-logo-badge">U-TELL BI</div>
            <div>
              <h1 class="header-title">Tablero de Control Académico e Institucional</h1>
              <span class="header-subtitle">Cátedra de Bases de Datos Aplicada — Universidad Abierta Interamericana</span>
            </div>
          </div>

          <div class="header-user-actions">
            <div class="user-pill">
              <span class="user-dot"></span>
              <span class="user-name">{{ authStore.user.nombre }} {{ authStore.user.apellido }}</span>
              <span class="user-role-badge">{{ authStore.user.tipoPerfil || 'Analista' }}</span>
            </div>
            <button class="btn btn-secondary btn-sm" @click="handleLogout">Cerrar Sesión</button>
          </div>
        </div>

        <!-- ─── BREADCRUMBS MULTINIVEL (DRILL-UP) ─── -->
        <nav class="drill-breadcrumbs">
          <button 
            class="breadcrumb-item" 
            :class="{ active: currentLevel === 1 }"
            @click="goToLevel1"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
            <span>Nivel 1: Panorama Global</span>
          </button>

          <span v-if="currentLevel >= 2" class="breadcrumb-separator">/</span>

          <button 
            v-if="currentLevel >= 2" 
            class="breadcrumb-item" 
            :class="{ active: currentLevel === 2 }"
            @click="goToLevel2(selectedUni.idUniversidad)"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
            <span class="truncate">{{ selectedUni?.universidad || 'Universidad' }}</span>
          </button>

          <span v-if="currentLevel === 3" class="breadcrumb-separator">/</span>

          <button 
            v-if="currentLevel === 3" 
            class="breadcrumb-item active"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
            <span class="truncate">{{ level3Data?.contexto?.carrera || 'Detalle Carrera' }} ({{ level3Data?.contexto?.sede }})</span>
          </button>
        </nav>
      </header>

      <!-- ─── LEYENDA VISUAL DE SEMAFORIZACIÓN (OBLIGATORIA) ─── -->
      <div class="semaphore-legend-card">
        <div class="legend-header">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
          <span class="legend-title">Criterio Formal de Semaforización (Escala Relacional 1.00 a 5.00)</span>
        </div>
        <div class="legend-pills">
          <div class="legend-pill pill-green">
            <span class="status-indicator indicator-green"></span>
            <strong>🟢 Favorable / Excelente:</strong> Promedio ≥ 4.50 / 5.00
          </div>
          <div class="legend-pill pill-yellow">
            <span class="status-indicator indicator-yellow"></span>
            <strong>🟡 Advertencia / Regular:</strong> Promedio 3.50 a 4.49 / 5.00
          </div>
          <div class="legend-pill pill-red">
            <span class="status-indicator indicator-red"></span>
            <strong>🔴 Crítico / Insatisfactorio:</strong> Promedio &lt; 3.50 / 5.00
          </div>
        </div>
      </div>

      <!-- ═════════════════════════════════════════════════════════════
           VISTA NIVEL 1: PANORAMA GLOBAL
           ═════════════════════════════════════════════════════════════ -->
      <section v-if="currentLevel === 1" class="dashboard-level-view">
        <!-- ─── CARDS DE KPIS CONSOLIDADOS ─── -->
        <div class="kpi-grid">
          <div class="kpi-card">
            <div class="kpi-icon icon-blue">
              <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
            </div>
            <div class="kpi-data">
              <span class="kpi-label">Universidades</span>
              <h3 class="kpi-value">{{ globalKpis?.total_universidades || 0 }}</h3>
              <span class="kpi-sub">Entidades registradas</span>
            </div>
          </div>

          <div class="kpi-card">
            <div class="kpi-icon icon-indigo">
              <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
            </div>
            <div class="kpi-data">
              <span class="kpi-label">Sedes Territoriales</span>
              <h3 class="kpi-value">{{ globalKpis?.total_sedes || 0 }}</h3>
              <span class="kpi-sub">Campus y facultades</span>
            </div>
          </div>

          <div class="kpi-card">
            <div class="kpi-icon icon-purple">
              <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
            </div>
            <div class="kpi-data">
              <span class="kpi-label">Carreras Ofertadas</span>
              <h3 class="kpi-value">{{ globalKpis?.total_carreras || 0 }}</h3>
              <span class="kpi-sub">Oferta académica activa</span>
            </div>
          </div>

          <div class="kpi-card">
            <div class="kpi-icon icon-orange">
              <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            </div>
            <div class="kpi-data">
              <span class="kpi-label">Publicaciones Totales</span>
              <h3 class="kpi-value">{{ globalKpis?.total_publicaciones || 0 }}</h3>
              <span class="kpi-sub">{{ globalKpis?.total_experiencias || 0 }} reseñas • {{ globalKpis?.total_consultas || 0 }} consultas</span>
            </div>
          </div>

          <div class="kpi-card kpi-highlight" :class="getSemaphoreCardClass(globalKpis?.score_satisfaccion_global)">
            <div class="kpi-data">
              <span class="kpi-label">Satisfacción Global</span>
              <div class="kpi-score-row">
                <h3 class="kpi-value">{{ globalKpis?.score_satisfaccion_global || '0.00' }}</h3>
                <span class="kpi-max">/ 5.00</span>
                <span class="semaphore-badge" :class="getSemaphoreBadgeClass(globalKpis?.score_satisfaccion_global)">
                  {{ getSemaphoreText(globalKpis?.score_satisfaccion_global) }}
                </span>
              </div>
              <span class="kpi-sub">Promedio consolidado país</span>
            </div>
          </div>
        </div>

        <!-- ─── GRÁFICO COMPARATIVO CHART.JS ─── -->
        <div class="chart-section-card">
          <div class="chart-header">
            <div>
              <h3 class="chart-title">Comparativa Multidimensional por Universidad</h3>
              <p class="chart-subtitle">Evaluación contrastada de Rigor Académico, Clima Estudiantil e Infraestructura</p>
            </div>
            <div class="chart-legend-mini">
              <span class="legend-box box-blue">Académico</span>
              <span class="legend-box box-amber">Clima</span>
              <span class="legend-box box-emerald">Infraestructura</span>
            </div>
          </div>
          <div class="chart-canvas-wrap">
            <canvas ref="globalChartCanvas"></canvas>
          </div>
        </div>

        <!-- ─── TABLA INTERACTIVA DE UNIVERSIDADES (DRILL-DOWN TRIGGER) ─── -->
        <div class="table-card">
          <div class="table-toolbar">
            <div class="toolbar-title-wrap">
              <h3 class="table-title">Consolidado de Instituciones Universitarias</h3>
              <span class="badge-count">{{ filteredUniversities.length }} instituciones</span>
            </div>

            <div class="toolbar-search">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
              <input 
                type="text" 
                v-model="searchQuery" 
                placeholder="Filtrar por universidad o email..." 
                class="search-input"
              />
            </div>
          </div>

          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Universidad</th>
                  <th class="text-center">Sedes</th>
                  <th class="text-center">Carreras</th>
                  <th class="text-center">Publicaciones</th>
                  <th class="text-center">Académico</th>
                  <th class="text-center">Clima</th>
                  <th class="text-center">Infraest.</th>
                  <th class="text-center">Promedio</th>
                  <th class="text-center">Semáforo</th>
                  <th class="text-right">Acción (Drill-Down)</th>
                </tr>
              </thead>
              <tbody>
                <tr 
                  v-for="uni in filteredUniversities" 
                  :key="uni.idUniversidad"
                  class="table-row-hover clickable-row"
                  @click="goToLevel2(uni.idUniversidad)"
                >
                  <td class="td-primary">
                    <div class="uni-cell">
                      <div class="uni-avatar-mini">{{ uni.universidad.charAt(0) }}</div>
                      <div>
                        <span class="uni-name-bold">{{ uni.universidad }}</span>
                        <span class="uni-email-sub">{{ uni.email }}</span>
                      </div>
                    </div>
                  </td>
                  <td class="text-center font-mono">{{ uni.total_sedes }}</td>
                  <td class="text-center font-mono">{{ uni.total_carreras }}</td>
                  <td class="text-center font-mono">{{ uni.total_publicaciones }}</td>
                  <td class="text-center font-mono">{{ uni.promedio_academico }}</td>
                  <td class="text-center font-mono">{{ uni.promedio_clima }}</td>
                  <td class="text-center font-mono">{{ uni.promedio_infraestructura }}</td>
                  <td class="text-center font-bold font-mono">{{ uni.promedio_general }}</td>
                  <td class="text-center">
                    <span class="semaphore-badge" :class="`badge-${uni.estado_semaforo.toLowerCase()}`">
                      <span class="status-dot"></span>
                      {{ uni.estado_semaforo }}
                    </span>
                  </td>
                  <td class="text-right">
                    <button class="btn btn-outline-primary btn-xs" @click.stop="goToLevel2(uni.idUniversidad)">
                      <span>Explorar Sedes</span>
                      <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- ═════════════════════════════════════════════════════════════
           VISTA NIVEL 2: DRILL-DOWN UNIVERSIDAD (SEDES Y CARRERAS)
           ═════════════════════════════════════════════════════════════ -->
      <section v-else-if="currentLevel === 2" class="dashboard-level-view">
        <!-- ─── CONTEXT HEADER DE LA UNIVERSIDAD ─── -->
        <div class="context-hero-card">
          <div class="context-hero-left">
            <button class="btn-back" @click="goToLevel1" title="Volver al Panorama Global">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
              <span>Volver a Nivel 1</span>
            </button>
            <div class="context-info-wrap">
              <h2 class="context-title">{{ selectedUni?.universidad }}</h2>
              <div class="context-meta-tags">
                <span class="meta-tag">📧 {{ selectedUni?.email }}</span>
                <span v-if="selectedUni?.celular" class="meta-tag">📞 {{ selectedUni?.celular }}</span>
                <span class="meta-tag">🏛️ {{ level2Data?.sedes?.length || 0 }} Sedes Activas</span>
                <span class="meta-tag">📚 {{ level2Data?.sedes_carreras?.length || 0 }} Carreras Ofertadas</span>
              </div>
            </div>
          </div>

          <div class="context-hero-right">
            <div class="score-box-big" :class="getSemaphoreCardClass(selectedUni?.promedio_general)">
              <span class="score-label">Promedio Institucional</span>
              <div class="score-val-big">{{ selectedUni?.promedio_general || '0.00' }} / 5.00</div>
              <span class="semaphore-badge" :class="`badge-${selectedUni?.estado_semaforo?.toLowerCase() || 'verde'}`">
                {{ selectedUni?.estado_semaforo || 'VERDE' }}
              </span>
            </div>
          </div>
        </div>

        <!-- ─── DESGLOSE DE SCORES INSTITUCIONALES ─── -->
        <div class="scores-triad-grid">
          <div class="triad-card">
            <span class="triad-title">Rigor Académico</span>
            <div class="triad-val">{{ selectedUni?.promedio_academico || '0.00' }}</div>
            <div class="triad-bar"><div class="triad-fill fill-blue" :style="{ width: `${(Number(selectedUni?.promedio_academico || 0) / 5) * 100}%` }"></div></div>
          </div>
          <div class="triad-card">
            <span class="triad-title">Clima Estudiantil</span>
            <div class="triad-val">{{ selectedUni?.promedio_clima || '0.00' }}</div>
            <div class="triad-bar"><div class="triad-fill fill-amber" :style="{ width: `${(Number(selectedUni?.promedio_clima || 0) / 5) * 100}%` }"></div></div>
          </div>
          <div class="triad-card">
            <span class="triad-title">Infraestructura</span>
            <div class="triad-val">{{ selectedUni?.promedio_infraestructura || '0.00' }}</div>
            <div class="triad-bar"><div class="triad-fill fill-emerald" :style="{ width: `${(Number(selectedUni?.promedio_infraestructura || 0) / 5) * 100}%` }"></div></div>
          </div>
        </div>

        <!-- ─── LISTADO DE SEDES Y CARRERAS (OFERTA ACADÉMICA) ─── -->
        <div class="table-card">
          <div class="table-toolbar">
            <div>
              <h3 class="table-title">Desglose por Sedes y Carreras Vinculadas</h3>
              <p class="table-subtitle">Seleccione una carrera para acceder al detalle granular de publicaciones y autores (Nivel 3)</p>
            </div>
          </div>

          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Sede Territorial</th>
                  <th>Carrera</th>
                  <th>Ubicación</th>
                  <th class="text-center">Publicaciones</th>
                  <th class="text-center">Académico</th>
                  <th class="text-center">Clima</th>
                  <th class="text-center">Infraest.</th>
                  <th class="text-center">Score Carrera</th>
                  <th class="text-center">Semáforo</th>
                  <th class="text-right">Acción (Drill-Down)</th>
                </tr>
              </thead>
              <tbody>
                <tr 
                  v-for="sc in level2Data?.sedes_carreras || []" 
                  :key="`${sc.idSede}-${sc.idCarrera}`"
                  class="table-row-hover clickable-row"
                  @click="goToLevel3(sc.idSede, sc.idCarrera)"
                >
                  <td class="font-bold td-primary">{{ sc.sede }}</td>
                  <td>
                    <span class="carrera-name-highlight">{{ sc.carrera }}</span>
                  </td>
                  <td>
                    <span class="geo-badge">{{ sc.ciudad }}, {{ sc.provincia }}</span>
                  </td>
                  <td class="text-center font-mono">{{ sc.total_publicaciones }}</td>
                  <td class="text-center font-mono">{{ sc.promedio_academico }}</td>
                  <td class="text-center font-mono">{{ sc.promedio_clima }}</td>
                  <td class="text-center font-mono">{{ sc.promedio_infraestructura }}</td>
                  <td class="text-center font-bold font-mono">{{ sc.promedio_carrera }}</td>
                  <td class="text-center">
                    <span class="semaphore-badge" :class="`badge-${sc.estado_semaforo.toLowerCase()}`">
                      <span class="status-dot"></span>
                      {{ sc.estado_semaforo }}
                    </span>
                  </td>
                  <td class="text-right">
                    <button class="btn btn-primary btn-xs" @click.stop="goToLevel3(sc.idSede, sc.idCarrera)">
                      <span>Ver Publicaciones</span>
                      <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- ═════════════════════════════════════════════════════════════
           VISTA NIVEL 3: DETALLE GRANULAR CARRERA/SEDE (PUBLICACIONES E INTERACCIONES)
           ═════════════════════════════════════════════════════════════ -->
      <section v-else-if="currentLevel === 3" class="dashboard-level-view">
        <!-- ─── CONTEXT HEADER DE CARRERA Y SEDE ─── -->
        <div class="context-hero-card">
          <div class="context-hero-left">
            <button class="btn-back" @click="goToLevel2(level3Data.contexto.idUniversidad)" title="Volver al Nivel 2">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
              <span>Volver a Sedes (Nivel 2)</span>
            </button>
            <div class="context-info-wrap">
              <div class="badge-accent-sm">{{ level3Data?.contexto?.universidad }}</div>
              <h2 class="context-title">{{ level3Data?.contexto?.carrera }}</h2>
              <div class="context-meta-tags">
                <span class="meta-tag">📍 {{ level3Data?.contexto?.sede }} ({{ level3Data?.contexto?.ciudad }}, {{ level3Data?.contexto?.provincia }})</span>
                <span v-if="level3Data?.contexto?.telefono" class="meta-tag">📞 {{ level3Data?.contexto?.telefono }}</span>
                <span v-if="level3Data?.contexto?.web" class="meta-tag">🌐 <a :href="level3Data?.contexto?.web" target="_blank" class="link-inline">{{ level3Data?.contexto?.web }}</a></span>
              </div>
              <p v-if="level3Data?.contexto?.descripcion" class="context-desc">{{ level3Data?.contexto?.descripcion }}</p>
            </div>
          </div>

          <div class="context-hero-right">
            <div class="score-box-big" :class="getSemaphoreCardClass(level3Data?.contexto?.promedio_general)">
              <span class="score-label">Score Carrera en Sede</span>
              <div class="score-val-big">{{ level3Data?.contexto?.promedio_general || '0.00' }} / 5.00</div>
              <span class="semaphore-badge" :class="`badge-${level3Data?.contexto?.estado_semaforo?.toLowerCase() || 'verde'}`">
                {{ level3Data?.contexto?.estado_semaforo || 'VERDE' }}
              </span>
            </div>
          </div>
        </div>

        <!-- ─── LISTADO GRANULAR DE PUBLICACIONES Y EXPERIENCIAS ─── -->
        <div class="table-card">
          <div class="table-toolbar">
            <div>
              <h3 class="table-title">Registro Granular de Publicaciones y Reseñas</h3>
              <p class="table-subtitle">Desglose de experiencias evaluativas, autores, consultas comunitarias y respuestas recibidas</p>
            </div>
            <span class="badge-count">{{ level3Data?.publicaciones?.length || 0 }} publicaciones</span>
          </div>

          <div v-if="!level3Data?.publicaciones || level3Data.publicaciones.length === 0" class="empty-state-card">
            <p>No se registran publicaciones ni experiencias para esta carrera en la sede seleccionada.</p>
          </div>

          <div v-else class="publications-feed-grid">
            <article 
              v-for="pub in level3Data.publicaciones" 
              :key="pub.idPublicacion"
              class="publication-dashboard-card"
            >
              <!-- Cabecera de Autor -->
              <div class="pub-header">
                <div class="pub-author-info">
                  <img :src="getProfilePic(pub.autor_foto)" alt="Avatar" class="pub-avatar" />
                  <div>
                    <h4 class="pub-author-name">{{ pub.autor_nombre }} {{ pub.autor_apellido }}</h4>
                    <span class="pub-author-meta">{{ pub.autor_email }} • <strong>{{ pub.autor_perfil }}</strong></span>
                  </div>
                </div>

                <div class="pub-badges">
                  <span class="type-pill" :class="pub.tipoPublicacion === 'Experiencia' ? 'pill-xp' : 'pill-query'">
                    {{ pub.tipoPublicacion }}
                  </span>
                  <span class="pub-date">{{ pub.fecha }} • {{ pub.hora }}</span>
                </div>
              </div>

              <!-- Título y Texto de Publicación -->
              <div class="pub-body">
                <h4 v-if="pub.titulo" class="pub-title">{{ pub.titulo }}</h4>
                <p class="pub-text">{{ pub.texto }}</p>
              </div>

              <!-- Matriz de Puntuaciones (Si es Experiencia) -->
              <div v-if="pub.tipoPublicacion === 'Experiencia'" class="pub-scores-matrix">
                <div class="matrix-item">
                  <span class="matrix-label">Académico</span>
                  <span class="matrix-score">⭐ {{ pub.puntuacionAcademica }}/5</span>
                </div>
                <div class="matrix-item">
                  <span class="matrix-label">Clima</span>
                  <span class="matrix-score">👥 {{ pub.puntuacionClima }}/5</span>
                </div>
                <div class="matrix-item">
                  <span class="matrix-label">Infraestructura</span>
                  <span class="matrix-score">🏢 {{ pub.puntuacionInfraestructura }}/5</span>
                </div>
                <div class="matrix-item matrix-total">
                  <span class="matrix-label">Promedio Reseña</span>
                  <span class="matrix-score-bold font-mono">{{ pub.promedio_experiencia }}</span>
                </div>
              </div>

              <!-- Métricas de Interacción Social -->
              <div class="pub-footer-metrics">
                <div class="metric-pill">
                  <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="currentColor" class="text-red"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                  <span>{{ pub.likes || 0 }} Me Gusta</span>
                </div>
                <div class="metric-pill">
                  <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                  <span>{{ pub.respuestas?.length || 0 }} Respuestas Comunitarias</span>
                </div>
                <div class="metric-pill">
                  <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                  <span>{{ pub.total_favoritos || 0 }} Guardados</span>
                </div>
              </div>

              <!-- Hilo de Respuestas Comunitarias Desglosadas -->
              <div v-if="pub.respuestas && pub.respuestas.length > 0" class="replies-thread-section">
                <h5 class="replies-title">Respuestas en la BD ({{ pub.respuestas.length }})</h5>
                <div class="replies-list">
                  <div 
                    v-for="resp in pub.respuestas" 
                    :key="resp.idRespuesta"
                    class="reply-bubble"
                  >
                    <div class="reply-header">
                      <span class="reply-author font-bold">{{ resp.autor_respuesta_nombre }} {{ resp.autor_respuesta_apellido }} ({{ resp.autor_respuesta_perfil }})</span>
                      <span class="reply-date">{{ resp.fecha }} {{ resp.hora }}</span>
                    </div>
                    <p class="reply-text">{{ resp.texto }}</p>
                  </div>
                </div>
              </div>
            </article>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../store/auth'
import { useToastStore } from '../store/toast'
import { getProfilePic } from '../utils'
import { API_BASE_URL } from '../api'
import axios from 'axios'
import Chart from 'chart.js/auto'

const router = useRouter()
const authStore = useAuthStore()
const toastStore = useToastStore()

// State
const currentLevel = ref(1) // 1: Global, 2: Universidad, 3: Carrera/Sede
const isLoading = ref(true)
const isLoggingIn = ref(false)
const loginError = ref('')
const searchQuery = ref('')

const isAuthorized = computed(() => {
  if (!authStore.user) return false
  const rol = Number(authStore.user.idRol || 0)
  const perfil = authStore.user.tipoPerfil || ''
  return [1, 3, 5, 6].includes(rol) || ['Administrador', 'Moderador', 'Universidad', 'Institución', 'Docente'].includes(perfil)
})

const setDemoAccount = (email) => {
  loginForm.value.email = email
  loginForm.value.password = '123456'
}

const loginForm = ref({
  email: 'rectorado@uai.edu.ar',
  password: ''
})


// Data Stores
const globalKpis = ref(null)
const universitiesList = ref([])
const selectedUni = ref(null)
const level2Data = ref(null)
const level3Data = ref(null)

const globalChartCanvas = ref(null)
let chartInstance = null

// Filtered Universities for Level 1 Table
const filteredUniversities = computed(() => {
  if (!searchQuery.value.trim()) return universitiesList.value
  const q = searchQuery.value.toLowerCase()
  return universitiesList.value.filter(u => 
    u.universidad.toLowerCase().includes(q) || 
    (u.email && u.email.toLowerCase().includes(q))
  )
})

// ─── AUTHENTICATION HANDLER ──────────────────────────────────────────────────
const handleDashboardLogin = async () => {
  loginError.value = ''
  isLoggingIn.value = true
  try {
    const res = await axios.post(`${API_BASE_URL}/dashboard/login`, {
      email: loginForm.value.email.trim(),
      password: loginForm.value.password.trim()
    })

    if (res.data && !res.data.error && res.data.usuario) {
      authStore.user = res.data.usuario
      localStorage.setItem('utell_user', JSON.stringify(res.data.usuario))
      toastStore.success('¡Bienvenido al Tablero de Control!')
      await fetchLevel1Data()
    } else {
      loginError.value = res.data.mensaje || 'Error al iniciar sesión.'
    }
  } catch (error) {
    loginError.value = error.response?.data?.mensaje || 'Error de autenticación. Verifique sus credenciales.'
  } finally {
    isLoggingIn.value = false
  }
}

const handleLogout = () => {
  authStore.logout()
  toastStore.info('Sesión finalizada.')
}

// ─── DATA LOADERS (DRILL-DOWN & DRILL-UP) ─────────────────────────────────────
const fetchLevel1Data = async () => {
  isLoading.value = true
  try {
    const res = await axios.get(`${API_BASE_URL}/dashboard/global`)
    if (res.data && !res.data.error) {
      globalKpis.value = res.data.kpis
      universitiesList.value = res.data.universidades || []
      currentLevel.value = 1
      nextTick(() => renderGlobalChart())
    }
  } catch (error) {
    console.error('Error fetching global dashboard:', error)
    toastStore.error('Error al cargar métricas globales.')
  } finally {
    isLoading.value = false
  }
}

const goToLevel1 = () => {
  currentLevel.value = 1
  selectedUni.value = null
  level2Data.value = null
  level3Data.value = null
  nextTick(() => renderGlobalChart())
}

const goToLevel2 = async (idUniversidad) => {
  isLoading.value = true
  try {
    const res = await axios.get(`${API_BASE_URL}/dashboard/universidad/${idUniversidad}`)
    if (res.data && !res.data.error) {
      selectedUni.value = res.data.universidad
      level2Data.value = res.data
      currentLevel.value = 2
      window.scrollTo({ top: 0, behavior: 'smooth' })
    }
  } catch (error) {
    console.error('Error fetching university drilldown:', error)
    toastStore.error('Error al cargar el detalle institucional.')
  } finally {
    isLoading.value = false
  }
}

const goToLevel3 = async (idSede, idCarrera) => {
  isLoading.value = true
  try {
    const res = await axios.get(`${API_BASE_URL}/dashboard/carrera-detalle/${idSede}/${idCarrera}`)
    if (res.data && !res.data.error) {
      level3Data.value = res.data
      currentLevel.value = 3
      window.scrollTo({ top: 0, behavior: 'smooth' })
    }
  } catch (error) {
    console.error('Error fetching carrera drilldown:', error)
    toastStore.error('Error al cargar publicaciones de la carrera.')
  } finally {
    isLoading.value = false
  }
}

// ─── SEMAPHORE UTILITIES ─────────────────────────────────────────────────────
const getSemaphoreBadgeClass = (score) => {
  const s = Number(score || 0)
  if (s >= 4.5) return 'badge-verde'
  if (s >= 3.5) return 'badge-amarillo'
  return 'badge-rojo'
}

const getSemaphoreCardClass = (score) => {
  const s = Number(score || 0)
  if (s >= 4.5) return 'border-verde'
  if (s >= 3.5) return 'border-amarillo'
  return 'border-rojo'
}

const getSemaphoreText = (score) => {
  const s = Number(score || 0)
  if (s >= 4.5) return 'VERDE'
  if (s >= 3.5) return 'AMARILLO'
  return 'ROJO'
}

// ─── CHART.JS RENDERING ──────────────────────────────────────────────────────
const renderGlobalChart = () => {
  if (!globalChartCanvas.value) return
  if (chartInstance) chartInstance.destroy()

  // Take top 8 universities with publications
  const topUnis = universitiesList.value
    .filter(u => Number(u.total_publicaciones) > 0)
    .slice(0, 7)

  const labels = topUnis.map(u => u.universidad.length > 22 ? u.universidad.substring(0, 20) + '...' : u.universidad)
  const dataAcademico = topUnis.map(u => Number(u.promedio_academico))
  const dataClima = topUnis.map(u => Number(u.promedio_clima))
  const dataInfra = topUnis.map(u => Number(u.promedio_infraestructura))

  chartInstance = new Chart(globalChartCanvas.value, {
    type: 'bar',
    data: {
      labels,
      datasets: [
        {
          label: 'Rigor Académico',
          data: dataAcademico,
          backgroundColor: 'rgba(59, 130, 246, 0.85)',
          borderRadius: 6
        },
        {
          label: 'Clima Estudiantil',
          data: dataClima,
          backgroundColor: 'rgba(245, 158, 11, 0.85)',
          borderRadius: 6
        },
        {
          label: 'Infraestructura',
          data: dataInfra,
          backgroundColor: 'rgba(16, 185, 129, 0.85)',
          borderRadius: 6
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          min: 0,
          max: 5,
          ticks: { stepSize: 1 }
        }
      },
      plugins: {
        legend: { display: false }
      }
    }
  })
}

onMounted(async () => {
  if (isAuthorized.value) {
    await fetchLevel1Data()
  }
})
</script>

<style scoped>
.dashboard-page {
  padding: 24px 20px 80px;
  background: var(--bg-main);
  min-height: 100vh;
  font-family: var(--font-body);
  color: var(--text-body);
}

/* ─── Auth Gate Card ─── */
.auth-gate-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 75vh;
}

.auth-gate-card {
  background: var(--bg-card);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-xl);
  padding: 36px 32px;
  max-width: 480px;
  width: 100%;
  box-shadow: var(--shadow-lg);
}

.access-restriction-tag {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #FEF3C7;
  color: #92400E;
  border: 1px solid #FDE68A;
  font-size: 11px;
  font-weight: 800;
  padding: 3px 8px;
  border-radius: var(--radius-full);
  margin-left: 8px;
  margin-bottom: 12px;
}

.alert-warning {
  background: #FFFBEB;
  border: 1px solid #FDE68A;
  color: #92400E;
  font-size: 12.5px;
  line-height: 1.4;
}

.demo-accounts-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 8px;
  margin-top: 8px;
}

.demo-account-item {
  background: var(--bg-subtle);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-md);
  padding: 8px 12px;
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 12px;
  transition: all var(--duration-fast);
}

.demo-account-item:hover {
  background: var(--primary-ghost);
  border-color: var(--primary);
}

.demo-role-badge {
  font-size: 10.5px;
  font-weight: 800;
  padding: 2px 6px;
  border-radius: 4px;
}

.badge-uni { background: #EDE9FE; color: #6D28D9; }
.badge-admin { background: #EFF6FF; color: #1D4ED8; }
.badge-mod { background: #FEF3C7; color: #B45309; }
.badge-doc { background: #ECFDF5; color: #047857; }

.uai-badge {
  display: inline-block;
  background: rgba(229, 90, 43, 0.1);
  color: var(--primary);
  font-weight: 800;
  font-size: 11px;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  padding: 4px 10px;
  border-radius: var(--radius-full);
  margin-bottom: 12px;
}


.auth-title {
  font-size: 22px;
  font-weight: 800;
  color: var(--text-strong);
  margin: 0 0 6px;
}

.auth-subtitle {
  font-size: 13.5px;
  color: var(--text-secondary);
  margin: 0 0 24px;
}

.form-group {
  margin-bottom: 18px;
}

.form-label {
  display: block;
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: var(--text-secondary);
  margin-bottom: 6px;
}

.input-with-icon {
  display: flex;
  align-items: center;
  gap: 10px;
  background: var(--bg-subtle);
  border: 1.5px solid var(--border-light);
  border-radius: var(--radius-md);
  padding: 10px 14px;
}

.input-with-icon input {
  border: none;
  background: transparent;
  outline: none;
  width: 100%;
  font-size: 14px;
  color: var(--text-strong);
}

.alert-box {
  padding: 10px 14px;
  border-radius: var(--radius-md);
  font-size: 13px;
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 16px;
}

.alert-danger {
  background: #FEF2F2;
  border: 1px solid rgba(220, 38, 38, 0.3);
  color: #DC2626;
}

.auth-demo-hint {
  margin-top: 24px;
  padding-top: 18px;
  border-top: 1px dashed var(--border-light);
  font-size: 12px;
  color: var(--text-secondary);
}

.demo-accounts {
  margin-top: 6px;
  line-height: 1.5;
}

.demo-accounts code {
  background: var(--bg-subtle);
  padding: 2px 6px;
  border-radius: 4px;
  color: var(--primary);
  font-weight: 700;
}

/* ─── Dashboard Shell ─── */
.dashboard-wrapper {
  display: flex;
  flex-direction: column;
  gap: 20px;
  max-width: 1280px;
  margin: 0 auto;
}

.dashboard-header {
  background: var(--bg-card);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-xl);
  padding: 20px 24px;
  box-shadow: var(--shadow-xs);
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.header-main-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 14px;
}

.header-brand {
  display: flex;
  align-items: center;
  gap: 14px;
}

.header-logo-badge {
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
  color: white;
  font-weight: 900;
  font-size: 13px;
  padding: 8px 12px;
  border-radius: var(--radius-md);
  letter-spacing: 0.5px;
  box-shadow: var(--shadow-primary);
}

.header-title {
  font-size: 20px;
  font-weight: 800;
  color: var(--text-strong);
  margin: 0 0 2px;
}

.header-subtitle {
  font-size: 12.5px;
  color: var(--text-secondary);
}

.header-user-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-pill {
  display: flex;
  align-items: center;
  gap: 8px;
  background: var(--bg-subtle);
  border: 1px solid var(--border-light);
  padding: 6px 12px;
  border-radius: var(--radius-full);
}

.user-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #10B981;
}

.user-name {
  font-size: 13px;
  font-weight: 700;
  color: var(--text-strong);
}

.user-role-badge {
  background: var(--primary-ghost);
  color: var(--primary);
  font-size: 10.5px;
  font-weight: 800;
  padding: 2px 6px;
  border-radius: 4px;
}

/* ─── Breadcrumbs (Drill-Up) ─── */
.drill-breadcrumbs {
  display: flex;
  align-items: center;
  gap: 8px;
  padding-top: 12px;
  border-top: 1px solid var(--border-light);
  flex-wrap: wrap;
}

.breadcrumb-item {
  display: flex;
  align-items: center;
  gap: 6px;
  background: none;
  border: none;
  font-family: var(--font-family);
  font-size: 13px;
  font-weight: 600;
  color: var(--text-secondary);
  cursor: pointer;
  padding: 4px 8px;
  border-radius: var(--radius-sm);
  transition: all var(--duration-fast);
}

.breadcrumb-item:hover {
  background: var(--primary-ghost);
  color: var(--primary);
}

.breadcrumb-item.active {
  color: var(--primary);
  font-weight: 800;
  cursor: default;
}

.breadcrumb-separator {
  color: var(--text-muted);
  font-size: 12px;
}

/* ─── Semaphore Legend ─── */
.semaphore-legend-card {
  background: var(--bg-card);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-lg);
  padding: 14px 20px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.legend-header {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12.5px;
  font-weight: 700;
  color: var(--text-strong);
}

.legend-pills {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.legend-pill {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 12px;
  border-radius: var(--radius-md);
  font-size: 12px;
}

.pill-green { background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; }
.pill-yellow { background: #FFFBEB; border: 1px solid #FDE68A; color: #92400E; }
.pill-red { background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; }

.status-indicator {
  width: 10px;
  height: 10px;
  border-radius: 50%;
}
.indicator-green { background: #10B981; box-shadow: 0 0 6px rgba(16,185,129,0.5); }
.indicator-yellow { background: #F59E0B; box-shadow: 0 0 6px rgba(245,158,11,0.5); }
.indicator-red { background: #EF4444; box-shadow: 0 0 6px rgba(239,68,68,0.5); }

/* ─── KPIs Grid ─── */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
}

.kpi-card {
  background: var(--bg-card);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-xl);
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: var(--shadow-xs);
}

.kpi-icon {
  width: 48px;
  height: 48px;
  border-radius: var(--radius-lg);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.icon-blue { background: #EFF6FF; color: #2563EB; }
.icon-indigo { background: #EEF2FF; color: #4F46E5; }
.icon-purple { background: #F5F3FF; color: #7C3AED; }
.icon-orange { background: rgba(229, 90, 43, 0.1); color: var(--primary); }

.kpi-label {
  font-size: 11.5px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: var(--text-secondary);
}

.kpi-value {
  font-size: 24px;
  font-weight: 900;
  color: var(--text-strong);
  margin: 2px 0;
  font-family: var(--font-family);
}

.kpi-sub {
  font-size: 11px;
  color: var(--text-muted);
}

.kpi-score-row {
  display: flex;
  align-items: baseline;
  gap: 6px;
}

.kpi-max {
  font-size: 13px;
  color: var(--text-muted);
  font-weight: 600;
}

/* ─── Semaphore Badges ─── */
.semaphore-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 11px;
  font-weight: 800;
  padding: 3px 8px;
  border-radius: var(--radius-full);
  letter-spacing: 0.5px;
}

.badge-verde { background: #D1FAE5; color: #065F46; border: 1px solid #10B981; }
.badge-amarillo { background: #FEF3C7; color: #92400E; border: 1px solid #F59E0B; }
.badge-rojo { background: #FEE2E2; color: #991B1B; border: 1px solid #EF4444; }

.status-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: currentColor;
}

.border-verde { border-left: 5px solid #10B981 !important; }
.border-amarillo { border-left: 5px solid #F59E0B !important; }
.border-rojo { border-left: 5px solid #EF4444 !important; }

/* ─── Chart Section ─── */
.chart-section-card {
  background: var(--bg-card);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-xl);
  padding: 22px;
  box-shadow: var(--shadow-xs);
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 18px;
  flex-wrap: wrap;
  gap: 10px;
}

.chart-title {
  font-size: 16.5px;
  font-weight: 800;
  color: var(--text-strong);
  margin: 0 0 2px;
}

.chart-subtitle {
  font-size: 12.5px;
  color: var(--text-secondary);
}

.chart-legend-mini {
  display: flex;
  gap: 10px;
}

.legend-box {
  font-size: 11.5px;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 4px;
}

.box-blue { background: #EFF6FF; color: #1D4ED8; }
.box-amber { background: #FFFBEB; color: #B45309; }
.box-emerald { background: #ECFDF5; color: #047857; }

.chart-canvas-wrap {
  height: 280px;
  position: relative;
}

/* ─── Tables ─── */
.table-card {
  background: var(--bg-card);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-xl);
  padding: 22px;
  box-shadow: var(--shadow-xs);
}

.table-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 18px;
  flex-wrap: wrap;
  gap: 12px;
}

.table-title {
  font-size: 16px;
  font-weight: 800;
  color: var(--text-strong);
  margin: 0;
}

.table-subtitle {
  font-size: 12px;
  color: var(--text-secondary);
  margin: 2px 0 0;
}

.badge-count {
  background: var(--bg-subtle);
  color: var(--text-secondary);
  font-size: 11px;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: var(--radius-full);
}

.toolbar-search {
  display: flex;
  align-items: center;
  gap: 8px;
  background: var(--bg-subtle);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-md);
  padding: 6px 12px;
  width: 260px;
}

.search-input {
  border: none;
  background: transparent;
  outline: none;
  width: 100%;
  font-size: 12.5px;
  color: var(--text-strong);
}

.table-responsive {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}

.data-table th {
  background: var(--bg-subtle);
  color: var(--text-secondary);
  font-weight: 700;
  text-transform: uppercase;
  font-size: 11px;
  letter-spacing: 0.5px;
  padding: 12px 14px;
  border-bottom: 1.5px solid var(--border-light);
}

.data-table td {
  padding: 12px 14px;
  border-bottom: 1px solid var(--border-light);
  color: var(--text-body);
}

.table-row-hover:hover {
  background-color: var(--primary-ghost);
}

.clickable-row {
  cursor: pointer;
}

.uni-cell {
  display: flex;
  align-items: center;
  gap: 12px;
}

.uni-avatar-mini {
  width: 32px;
  height: 32px;
  border-radius: var(--radius-md);
  background: linear-gradient(135deg, var(--primary), var(--primary-hover));
  color: white;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  flex-shrink: 0;
}

.uni-name-bold {
  font-weight: 700;
  color: var(--text-strong);
  display: block;
}

.uni-email-sub {
  font-size: 11px;
  color: var(--text-muted);
}

.font-mono {
  font-family: monospace;
}

.geo-badge {
  background: var(--bg-subtle);
  padding: 3px 8px;
  border-radius: 4px;
  font-size: 11.5px;
  color: var(--text-secondary);
}

.carrera-name-highlight {
  font-weight: 700;
  color: var(--text-strong);
}

/* ─── Level 2 & 3 Context Hero ─── */
.context-hero-card {
  background: var(--bg-card);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-xl);
  padding: 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 20px;
  box-shadow: var(--shadow-sm);
}

.btn-back {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: var(--bg-subtle);
  border: 1px solid var(--border-light);
  color: var(--text-secondary);
  font-family: var(--font-family);
  font-weight: 700;
  font-size: 12px;
  padding: 6px 12px;
  border-radius: var(--radius-md);
  cursor: pointer;
  margin-bottom: 12px;
  transition: all var(--duration-fast);
}

.btn-back:hover {
  background: var(--primary-ghost);
  color: var(--primary);
  border-color: var(--primary);
}

.context-title {
  font-size: 22px;
  font-weight: 800;
  color: var(--text-strong);
  margin: 0 0 8px;
}

.context-meta-tags {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.meta-tag {
  font-size: 12.5px;
  color: var(--text-secondary);
  background: var(--bg-subtle);
  padding: 4px 10px;
  border-radius: var(--radius-sm);
}

.context-desc {
  margin: 12px 0 0;
  font-size: 13.5px;
  color: var(--text-body);
  max-width: 680px;
  line-height: 1.5;
}

.score-box-big {
  background: var(--bg-subtle);
  border-radius: var(--radius-lg);
  padding: 16px 20px;
  text-align: center;
  min-width: 180px;
}

.score-label {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  color: var(--text-secondary);
}

.score-val-big {
  font-size: 24px;
  font-weight: 900;
  color: var(--text-strong);
  margin: 4px 0 8px;
  font-family: var(--font-family);
}

.badge-accent-sm {
  display: inline-block;
  background: var(--primary-ghost);
  color: var(--primary);
  font-weight: 800;
  font-size: 11px;
  padding: 3px 8px;
  border-radius: 4px;
  margin-bottom: 6px;
}

/* ─── Scores Triad Grid ─── */
.scores-triad-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 16px;
}

.triad-card {
  background: var(--bg-card);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-lg);
  padding: 16px 18px;
}

.triad-title {
  font-size: 12px;
  font-weight: 700;
  color: var(--text-secondary);
}

.triad-val {
  font-size: 22px;
  font-weight: 900;
  color: var(--text-strong);
  margin: 4px 0 8px;
}

.triad-bar {
  height: 6px;
  background: var(--bg-subtle);
  border-radius: var(--radius-full);
  overflow: hidden;
}

.triad-fill { height: 100%; border-radius: var(--radius-full); }
.fill-blue { background: #3B82F6; }
.fill-amber { background: #F59E0B; }
.fill-emerald { background: #10B981; }

/* ─── Level 3 Publication Cards ─── */
.publications-feed-grid {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.publication-dashboard-card {
  background: var(--bg-subtle);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-lg);
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.pub-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 10px;
}

.pub-author-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.pub-avatar {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  object-fit: cover;
  border: 1.5px solid var(--border-light);
}

.pub-author-name {
  font-size: 14.5px;
  font-weight: 800;
  color: var(--text-strong);
  margin: 0;
}

.pub-author-meta {
  font-size: 11.5px;
  color: var(--text-secondary);
}

.pub-badges {
  display: flex;
  align-items: center;
  gap: 8px;
}

.type-pill {
  font-size: 11px;
  font-weight: 800;
  padding: 3px 8px;
  border-radius: var(--radius-full);
}

.pill-xp { background: var(--primary-ghost); color: var(--primary); }
.pill-query { background: #FEF3C7; color: #92400E; }

.pub-date {
  font-size: 11.5px;
  color: var(--text-muted);
}

.pub-title {
  font-size: 15px;
  font-weight: 800;
  color: var(--text-strong);
  margin: 0 0 4px;
}

.pub-text {
  font-size: 13.5px;
  line-height: 1.5;
  color: var(--text-body);
  margin: 0;
}

.pub-scores-matrix {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
  gap: 10px;
  background: var(--bg-card);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-md);
  padding: 12px 14px;
}

.matrix-item {
  display: flex;
  flex-direction: column;
}

.matrix-label {
  font-size: 11px;
  color: var(--text-secondary);
}

.matrix-score {
  font-size: 13px;
  font-weight: 700;
  color: var(--text-strong);
}

.matrix-score-bold {
  font-size: 15px;
  font-weight: 900;
  color: var(--primary);
}

.pub-footer-metrics {
  display: flex;
  gap: 14px;
  flex-wrap: wrap;
  padding-top: 10px;
  border-top: 1px solid var(--border-light);
}

.metric-pill {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  color: var(--text-secondary);
}

.text-red { color: #EF4444; }

/* ─── Replies Thread ─── */
.replies-thread-section {
  background: var(--bg-card);
  border-radius: var(--radius-md);
  padding: 14px;
  border: 1px solid var(--border-light);
  margin-top: 6px;
}

.replies-title {
  font-size: 12.5px;
  font-weight: 800;
  color: var(--text-strong);
  margin: 0 0 10px;
}

.replies-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.reply-bubble {
  background: var(--bg-subtle);
  border-radius: var(--radius-sm);
  padding: 10px 12px;
  border-left: 3px solid var(--primary);
}

.reply-header {
  display: flex;
  justify-content: space-between;
  font-size: 11.5px;
  margin-bottom: 4px;
}

.reply-author { color: var(--text-strong); }
.reply-date { color: var(--text-muted); }
.reply-text { font-size: 12.5px; margin: 0; color: var(--text-body); }

.empty-state-card {
  text-align: center;
  padding: 36px;
  color: var(--text-secondary);
}

.spinner-sm {
  display: inline-block;
  width: 14px;
  height: 14px;
  border: 2px solid rgba(255,255,255,0.4);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }
</style>
