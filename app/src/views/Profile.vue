<template>
  <div class="page-container profile-page">
    <header class="app-header">
      <h1 class="title-primary" style="font-size: 20px;">Mi Perfil</h1>
      <button class="settings-btn">⚙️</button>
    </header>
    
    <div class="profile-header text-center mt-2">
      <div class="profile-avatar-container">
        <img src="/img/perfilDefault.svg" alt="Profile" class="profile-avatar" />
      </div>
      <h2 class="profile-name">Juan Pérez</h2>
      <p class="profile-role text-gray">Estudiante</p>
      <p class="profile-location text-gray">Rosario, Santa Fe</p>
    </div>
    
    <div class="profile-stats mt-3">
      <div class="stat-item">
        <span class="stat-value">120</span>
        <span class="stat-label">Amigos</span>
      </div>
      <div class="stat-item">
        <span class="stat-value">15</span>
        <span class="stat-label">Publicaciones</span>
      </div>
    </div>
    
    <div class="profile-menu mt-3">
      <button class="menu-btn card">
        <span>Mis XPs</span>
        <span>></span>
      </button>
      <button class="menu-btn card">
        <span>Mis Consultas</span>
        <span>></span>
      </button>
      <button class="menu-btn card">
        <span>Elementos Guardados</span>
        <span>></span>
      </button>
    </div>
  </div>
</template>

<style scoped>
.app-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-bottom: var(--spacing-md);
  padding-top: var(--spacing-sm);
}

.settings-btn {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
}

.profile-avatar-container {
  width: 100px;
  height: 100px;
  margin: 0 auto var(--spacing-md);
  border-radius: 50%;
  border: 3px solid var(--primary-color);
  padding: 4px;
}

.profile-avatar {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  background-color: var(--border-color);
  object-fit: cover;
}

.profile-name {
  font-size: 24px;
  margin-bottom: 4px;
}

.profile-role, .profile-location {
  font-size: 14px;
  margin-bottom: 4px;
}

.profile-stats {
  display: flex;
  justify-content: center;
  gap: var(--spacing-xl);
  padding: var(--spacing-md) 0;
  border-top: 1px solid var(--border-color);
  border-bottom: 1px solid var(--border-color);
}

.stat-item {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.stat-value {
  font-size: 20px;
  font-weight: 700;
  color: var(--text-dark);
}

.stat-label {
  font-size: 12px;
  color: var(--text-gray);
}

.profile-menu {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-sm);
}

.menu-btn {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  border: none;
  text-align: left;
  font-size: 16px;
  font-weight: 500;
  color: var(--text-dark);
  cursor: pointer;
  margin-bottom: 0; /* Override card margin */
  padding: var(--spacing-md);
}

.text-gray {
  color: var(--text-gray);
}
</style>
