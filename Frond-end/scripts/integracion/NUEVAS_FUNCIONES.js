/* ==============================================
   NUEVAS FUNCIONES PARA ALPINE DATA
   Agregar dentro del objeto Alpine.data('rolesData')
   ============================================== */

// Estado para nuevos paneles
isStudentPanelOpen: false,
isRolesPanelOpen: false,
isPermissionsPanelOpen: false,
isImportPanelOpen: false,
studentForm: {
    user_id: null,
    career_id: null,
    n_control: '',
    semestre: 1,
    group: '',
    workshop: ''
},
rolesForm: {
    curps: [],
    rolesToAdd: [],
    rolesToRemove: []
},
permissionsForm: {
    curps: [],
    role: '',
    permissionsToAdd: [],
    permissionsToRemove: []
},
availableRoles: [],
availablePermissions: [],
importType: 'users', // 'users' o 'students'
selectedCurps: [],

// Abrir panel de estudiantes
openStudentPanel(userId = null) {
    this.isStudentPanelOpen = true;
    if (userId) {
        this.studentForm.user_id = userId;
        // Cargar datos del estudiante si existe
        this.loadStudentDetails(userId);
    } else {
        this.studentForm = {
            user_id: null,
            career_id: null,
            n_control: '',
            semestre: 1,
            group: '',
            workshop: ''
        };
    }
},

async loadStudentDetails(userId) {
    try {
        const response = await this.authenticatedFetch(
            `${this.apiBaseUrl}/v1/admin-actions/get-student/${userId}`,
            {
                method: 'GET',
                headers: {
                    'X-User-Role': 'admin',
                    'X-User-Permission': 'view.student'
                }
            }
        );
        
        if (response.ok) {
            const result = await response.json();
            if (result.success && result.data?.user) {
                this.studentForm = {
                    user_id: userId,
                    ...result.data.user
                };
            }
        }
    } catch (error) {
        console.error('Error al cargar detalles de estudiante:', error);
    }
},

async saveStudentDetails() {
    this.isSaving = true;
    try {
        const isUpdate = this.studentForm.id;
        const url = isUpdate 
            ? `${this.apiBaseUrl}/v1/admin-actions/update-student/${this.studentForm.id}`
            : `${this.apiBaseUrl}/v1/admin-actions/attach-student`;
        
        const response = await this.authenticatedFetch(url, {
            method: isUpdate ? 'PATCH' : 'POST',
            headers: {
                'X-User-Role': 'admin',
                'X-User-Permission': isUpdate ? 'update.student' : 'attach.student'
            },
            body: JSON.stringify(this.studentForm)
        });
        
        const result = await response.json();
        if (response.ok && result.success) {
            this.showNotify(result.message || 'Detalles de estudiante guardados', 'success');
            this.isStudentPanelOpen = false;
            await this.loadUsers(localStorage.getItem('access_token'));
        } else {
            this.showNotify(result.message || 'Error al guardar', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        this.showNotify('Error de conexión', 'error');
    } finally {
        this.isSaving = false;
    }
},

// Abrir panel de roles
async openRolesPanel() {
    this.isRolesPanelOpen = true;
    await this.loadRoles();
},

async loadRoles() {
    try {
        const response = await this.authenticatedFetch(
            `${this.apiBaseUrl}/v1/admin-actions/find-roles`,
            {
                method: 'GET',
                headers: {
                    'X-User-Role': 'admin',
                    'X-User-Permission': 'view.roles'
                }
            }
        );
        
        if (response.ok) {
            const result = await response.json();
            this.availableRoles = result.data?.roles || [];
        }
    } catch (error) {
        console.error('Error al cargar roles:', error);
    }
},

async updateRoles() {
    if (this.selectedCurps.length === 0) {
        this.showNotify('Selecciona al menos un usuario', 'error');
        return;
    }
    
    this.isSaving = true;
    try {
        const response = await this.authenticatedFetch(
            `${this.apiBaseUrl}/v1/admin-actions/updated-roles`,
            {
                method: 'POST',
                headers: {
                    'X-User-Role': 'admin',
                    'X-User-Permission': 'sync.roles'
                },
                body: JSON.stringify({
                    curps: this.selectedCurps,
                    rolesToAdd: this.rolesForm.rolesToAdd,
                    rolesToRemove: this.rolesForm.rolesToRemove
                })
            }
        );
        
        const result = await response.json();
        if (response.ok && result.success) {
            this.showNotify(result.message || 'Roles actualizados correctamente', 'success');
            this.isRolesPanelOpen = false;
            this.selectedCurps = [];
            await this.loadUsers(localStorage.getItem('access_token'));
        } else {
            this.showNotify(result.message || 'Error al actualizar roles', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        this.showNotify('Error de conexión', 'error');
    } finally {
        this.isSaving = false;
    }
},

// Abrir panel de permisos
async openPermissionsPanel() {
    this.isPermissionsPanelOpen = true;
    await this.loadPermissions();
},

async loadPermissions() {
    try {
        const response = await this.authenticatedFetch(
            `${this.apiBaseUrl}/v1/admin-actions/find-permissions`,
            {
                method: 'POST',
                headers: {
                    'X-User-Role': 'admin',
                    'X-User-Permission': 'view.permissions'
                },
                body: JSON.stringify({
                    curps: [],
                    role: ''
                })
            }
        );
        
        if (response.ok) {
            const result = await response.json();
            this.availablePermissions = result.data?.permissions?.permissions || [];
        }
    } catch (error) {
        console.error('Error al cargar permisos:', error);
    }
},

async updatePermissions() {
    if (this.selectedCurps.length === 0 && !this.permissionsForm.role) {
        this.showNotify('Selecciona usuarios o un rol', 'error');
        return;
    }
    
    this.isSaving = true;
    try {
        const response = await this.authenticatedFetch(
            `${this.apiBaseUrl}/v1/admin-actions/update-permissions`,
            {
                method: 'POST',
                headers: {
                    'X-User-Role': 'admin',
                    'X-User-Permission': 'sync.permissions'
                },
                body: JSON.stringify({
                    curps: this.selectedCurps.length > 0 ? this.selectedCurps : undefined,
                    role: this.permissionsForm.role || undefined,
                    permissionsToAdd: this.permissionsForm.permissionsToAdd,
                    permissionsToRemove: this.permissionsForm.permissionsToRemove
                })
            }
        );
        
        const result = await response.json();
        if (response.ok && result.success) {
            this.showNotify(result.message || 'Permisos actualizados correctamente', 'success');
            this.isPermissionsPanelOpen = false;
            this.selectedCurps = [];
            await this.loadUsers(localStorage.getItem('access_token'));
        } else {
            this.showNotify(result.message || 'Error al actualizar permisos', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        this.showNotify('Error de conexión', 'error');
    } finally {
        this.isSaving = false;
    }
},

// Abrir panel de importación
openImportPanel(type = 'users') {
    this.importType = type;
    this.isImportPanelOpen = true;
},

async processFullImport() {
    if (!this.selectedFile) {
        this.showNotify('Selecciona un archivo', 'error');
        return;
    }
    
    this.isSaving = true;
    try {
        const formData = new FormData();
        formData.append('file', this.selectedFile);
        
        const endpoint = this.importType === 'users' 
            ? '/v1/admin-actions/import'
            : '/v1/admin-actions/import-students';
        
        const response = await fetch(`${this.apiBaseUrl}${endpoint}`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('access_token')}`,
                'Accept': 'application/json',
                'X-User-Role': 'admin',
                'X-User-Permission': 'import.users'
            },
            body: formData
        });
        
        const result = await response.json();
        if (response.ok && result.success) {
            const summary = result.data?.summary?.summary;
            this.showNotify(
                `✅ Importación completada: ${summary?.rows_inserted || 0} insertados, ${summary?.rows_failed || 0} fallidos`,
                'success'
            );
            this.isImportPanelOpen = false;
            this.selectedFile = null;
            await this.loadUsers(localStorage.getItem('access_token'));
        } else {
            this.showNotify(result.message || 'Error al importar', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        this.showNotify('Error de conexión', 'error');
    } finally {
        this.isSaving = false;
    }
},

// Activar usuarios seleccionados
async activateUsers() {
    if (this.selectedUsers.length === 0) {
        this.showNotify('Selecciona al menos un usuario', 'error');
        return;
    }
    
    if (!confirm(`¿Activar ${this.selectedUsers.length} usuarios?`)) {
        return;
    }
    
    try {
        const response = await this.authenticatedFetch(
            `${this.apiBaseUrl}/v1/admin-actions/activate-users`,
            {
                method: 'POST',
                headers: {
                    'X-User-Role': 'admin',
                    'X-User-Permission': 'activate.users'
                },
                body: JSON.stringify({ ids: this.selectedUsers })
            }
        );
        
        const result = await response.json();
        if (response.ok && result.success) {
            this.selectedUsers.forEach(id => {
                const user = this.users.find(u => u.id === id);
                if (user) user.status = 'activo';
            });
            this.selectedUsers = [];
            this.showNotify(result.message || 'Usuarios activados correctamente', 'success');
        } else {
            this.showNotify(result.message || 'Error al activar usuarios', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        this.showNotify('Error de conexión', 'error');
    }
},

// Obtener CURPs de usuarios seleccionados
get selectedCurps() {
    return this.users
        .filter(u => this.selectedUsers.includes(u.id))
        .map(u => u.curp)
        .filter(Boolean);
},
