# 📚 Complete API Reference: All Endpoints

## 🎯 Quick Reference

All three endpoints are **fully implemented** in [roles.astro](src/pages/roles.astro).

| Endpoint | Function | Status | Location |
|----------|----------|--------|----------|
| `POST /update-permissions/{userId}` | `updateUserPermissions()` | ✅ Implemented | Line ~3491 |
| `POST /updated-roles/{userId}` | `updateUserRoles()` | ✅ Implemented | Line ~3580 |
| `POST /update-permissions` | `updatePermissions()` | ✅ Implemented | Line ~3382 |

---

## 1️⃣ Update Permissions - Individual User

### Overview
Update permissions for a single user by their `userId`.

### Endpoint
```
POST /api/v1/admin-actions/update-permissions/{userId}
```

### Function
```javascript
async updateUserPermissions(userId, permissionsToAdd = [], permissionsToRemove = [])
```

### Headers
```javascript
{
  "Authorization": "Bearer {token}",
  "X-User-Role": "admin|supervisor",
  "X-User-Permission": "sync.permissions",
  "Content-Type": "application/json"
}
```

### Request Body
```json
{
  "permissionsToAdd": ["users.create", "reports.view"],
  "permissionsToRemove": ["users.delete", "settings.update"]
}
```

### Response (200 Success)
```json
{
  "success": true,
  "message": "Permisos actualizados correctamente.",
  "data": {
    "updated": [{
      "userId": 4,
      "fullName": "Juan Perez",
      "permissions": {
        "added": ["users.create", "reports.view"],
        "removed": ["users.delete", "settings.update"]
      }
    }]
  }
}
```

### Example Usage
```javascript
// Add permissions
const result = await updateUserPermissions(4, ['reports.view'], []);

// Remove permissions
const result = await updateUserPermissions(4, [], ['users.delete']);

// Both add and remove
const result = await updateUserPermissions(
  4,
  ['reports.view', 'users.create'],
  ['users.delete']
);

// With validation
if (result.success) {
  console.log('✅ Updated:', result.data);
} else {
  console.error('❌ Error:', result.message);
}
```

### Error Responses

**401 Unauthorized:**
```json
{
  "success": false,
  "message": "No autorizado",
  "error_code": "UNAUTHORIZED"
}
```

**422 Validation Error:**
```json
{
  "success": false,
  "message": "Error descriptivo para el usuario",
  "error_code": "VALIDATION_ERROR",
  "errors": {
    "userId": ["Usuario no encontrado"],
    "permissionsToAdd": ["El campo debe ser un array"]
  }
}
```

### Validation Rules
- ✓ At least one of: permissionsToAdd OR permissionsToRemove
- ✓ userId must exist
- ✓ Permissions must be valid
- ✓ User must have token + admin/supervisor role + sync.permissions

---

## 2️⃣ Update Roles - Individual User

### Overview
Update roles for a single user by their `userId`.

### Endpoint
```
POST /api/v1/admin-actions/updated-roles/{userId}
```

### Function
```javascript
async updateUserRoles(userId, rolesToAdd = [], rolesToRemove = [])
```

### Headers
```javascript
{
  "Authorization": "Bearer {token}",
  "X-User-Role": "admin|supervisor",
  "X-User-Permission": "sync.roles",
  "Content-Type": "application/json"
}
```

### Request Body
```json
{
  "rolesToAdd": ["editor", "supervisor"],
  "rolesToRemove": ["viewer", "assistant"]
}
```

### Response (200 Success)
```json
{
  "success": true,
  "message": "Roles actualizados correctamente.",
  "data": {
    "updated": {
      "userId": 4,
      "fullName": "Juan Perez",
      "roles": {
        "added": ["editor"],
        "removed": ["guest"]
      }
    }
  }
}
```

### Example Usage
```javascript
// Add roles
const result = await updateUserRoles(4, ['teacher'], []);

// Remove roles
const result = await updateUserRoles(4, [], ['student']);

// Both add and remove
const result = await updateUserRoles(
  4,
  ['teacher', 'editor'],
  ['student', 'guest']
);

// With validation
if (result.success) {
  console.log('✅ Updated roles');
} else {
  console.error('❌ Error:', result.message);
}
```

### Error Responses
Same structure as permissions endpoint (401, 422, 500)

### Validation Rules
- ✓ At least one of: rolesToAdd OR rolesToRemove
- ✓ userId must exist
- ✓ Roles must be valid
- ✓ User must have token + admin/supervisor role + sync.roles

---

## 3️⃣ Update Permissions - Multiple Users (Bulk)

### Overview
Update permissions for multiple users at once using CURPs or by role.

### Endpoint
```
POST /api/v1/admin-actions/update-permissions
```

### Function
```javascript
async updatePermissions()
// Called from modal - auto-builds payload from:
// - this.selectedUsers (converted to CURPs)
// - this.permissionTargetRole (if CURPs not available)
// - this.permissionsToAdd
// - this.permissionsToRemove
```

### Headers
```javascript
{
  "Authorization": "Bearer {token}",
  "X-User-Role": "admin|supervisor",
  "X-User-Permission": "sync.permissions",
  "Content-Type": "application/json"
}
```

### Request Body - Option A: By CURPs
```json
{
  "curps": [
    "GODE561231HDFABC09",
    "PEMJ800101MDFLRS08"
  ],
  "permissionsToAdd": ["users.create", "reports.view"],
  "permissionsToRemove": ["users.delete", "settings.update"]
}
```

### Request Body - Option B: By Role
```json
{
  "role": "admin",
  "permissionsToAdd": ["users.create", "reports.view"],
  "permissionsToRemove": ["users.delete", "settings.update"]
}
```

**Note:** Use EITHER `curps` OR `role`, not both.

### Response (200 Success)
```json
{
  "success": true,
  "message": "Permisos actualizados correctamente.",
  "data": {
    "users_permissions": [
      {
        "fullName": "Juan Pérez",
        "curp": "GODE561231HDFABC09",
        "role": "student",
        "updatedPermissions": {
          "added": ["users.create", "reports.view"],
          "removed": ["users.delete", "settings.update"]
        },
        "metadata": {
          "totalFound": 2,
          "totalUpdated": 2,
          "failed": 0,
          "failedUsers": [],
          "operations": {
            "permissions_removed": ["users.delete", "settings.update"],
            "permissions_added": ["users.create", "reports.view"],
            "roles_processed": 0
          }
        }
      }
    ]
  }
}
```

### How to Use (from UI)
```
1. Go to Roles page
2. Click "Gestionar Permisos" button
3. Select users (builds CURPs internally)
4. Check permissions to add
5. Check permissions to remove
6. Click "Actualizar Permisos"
7. Function sends payload automatically
```

### How to Use (programmatically)
```javascript
// Set up component state
app.selectedUsers = [4, 5, 6];
app.permissionsToAdd = ['reports.view'];
app.permissionsToRemove = ['users.delete'];

// Call function
await app.updatePermissions();
```

### Validation Rules
- ✓ Either curps OR role (not both)
- ✓ At least one permission in add or remove arrays
- ✓ CURPs must be 18 characters
- ✓ Users/role must exist
- ✓ User must have token + admin/supervisor role + sync.permissions

---

## 🔄 Comparison Matrix

| Feature | Individual Perms | Individual Roles | Bulk Perms |
|---------|------------------|------------------|-----------|
| **URL Path** | `{userId}` | `{userId}` | No userId |
| **User ID Param** | URL | URL | Body (curps) |
| **Field Names** | permissionsToAdd/Remove | rolesToAdd/Remove | permissionsToAdd/Remove |
| **Header Permission** | sync.permissions | sync.roles | sync.permissions |
| **Response Data** | updated[0] (array) | updated (object) | users_permissions (array) |
| **Supports Bulk** | No | No | Yes |
| **Metadata Included** | No | No | Yes |
| **Best For** | Quick individual updates | Quick role assignment | Bulk operations |

---

## 🎯 Real-World Scenarios

### Scenario 1: Give Student Reporting Access
```javascript
// Endpoint: Individual Permissions
await updateUserPermissions(4, ['reports.view'], []);
```

### Scenario 2: Promote Student to Teacher
```javascript
// Endpoint: Individual Roles
await updateUserRoles(4, ['teacher'], ['student']);
```

### Scenario 3: Grant All Students Reporting Permission
```javascript
// Endpoint: Bulk Permissions
// Select all students with role = 'student'
// Add 'reports.view' permission
// Send to bulk endpoint
```

### Scenario 4: Remove Delete Rights from Supervisors
```javascript
// Endpoint: Bulk Permissions
// Select users with role = 'supervisor'
// Remove 'users.delete' and 'students.delete'
// Send to bulk endpoint
```

### Scenario 5: Quick Permission Fix
```javascript
// Endpoint: Individual Permissions
// User reported they can't access reports
await updateUserPermissions(userId, ['reports.view'], []);
```

---

## 🔐 Security Requirements

### All Endpoints Require:
✓ Valid JWT token in Authorization header  
✓ User role: `admin` OR `supervisor`  
✓ Specific permission:
  - `sync.permissions` for permission endpoints
  - `sync.roles` for role endpoints

### Additional Validation:
✓ User IDs must exist in database  
✓ CURPs must be valid (18 characters)  
✓ Permissions/roles must exist in system  
✓ At least one change (add OR remove)  

---

## 📊 Response Formats

### Individual Endpoint Response
```javascript
{
  success: boolean,
  message: string,
  data: {
    updated: [{ userId, fullName, permissions/roles: { added, removed } }]
  }
}
```

### Bulk Endpoint Response
```javascript
{
  success: boolean,
  message: string,
  data: {
    users_permissions: [{
      fullName,
      curp,
      role,
      updatedPermissions: { added, removed },
      metadata: {
        totalFound,
        totalUpdated,
        failed,
        failedUsers: [],
        operations: { permissions_removed, permissions_added, roles_processed }
      }
    }]
  }
}
```

---

## 🧪 Quick Test Commands

### Browser Console
```javascript
const app = Alpine.$data(document.querySelector('[x-data="rolesData"]'));

// Test 1
await app.updateUserPermissions(4, ['reports.view'], []);

// Test 2
await app.updateUserRoles(4, ['teacher'], ['student']);

// Test 3
app.selectedUsers = [4, 5]; // Set users
app.permissionsToAdd = ['reports.view']; // Set permissions
await app.updatePermissions(); // Send bulk update
```

### PowerShell
```powershell
$token = "YOUR_TOKEN"

# Test 1: Individual Permissions
$body = @{permissionsToAdd=@('reports.view')} | ConvertTo-Json
Invoke-RestMethod -Uri "https://api/v1/admin-actions/update-permissions/4" `
  -Method POST -Headers @{"Authorization"="Bearer $token"} -Body $body

# Test 2: Individual Roles
$body = @{rolesToAdd=@('teacher')} | ConvertTo-Json
Invoke-RestMethod -Uri "https://api/v1/admin-actions/updated-roles/4" `
  -Method POST -Headers @{"Authorization"="Bearer $token"} -Body $body

# Test 3: Bulk Permissions
$body = @{curps=@('CURP1','CURP2'); permissionsToAdd=@('reports.view')} | ConvertTo-Json
Invoke-RestMethod -Uri "https://api/v1/admin-actions/update-permissions" `
  -Method POST -Headers @{"Authorization"="Bearer $token"} -Body $body
```

---

## 📝 Implementation Details

### updateUserPermissions()
- Location: [roles.astro](src/pages/roles.astro) line ~3491
- Size: ~80 lines
- Features:
  - Token validation
  - Parameter validation
  - Error handling (401, 422, 500)
  - Automatic data reload
  - User notifications
  - Detailed logging

### updateUserRoles()
- Location: [roles.astro](src/pages/roles.astro) line ~3580
- Size: ~80 lines
- Features: Same as updateUserPermissions()

### updatePermissions()
- Location: [roles.astro](src/pages/roles.astro) line ~3382
- Size: ~110 lines
- Features:
  - CURP filtering (18 chars validation)
  - Role-based selection
  - Bulk metadata processing
  - Detailed logging
  - UI modal integration

---

## ✅ Status Summary

| Component | Status | Notes |
|-----------|--------|-------|
| **updateUserPermissions()** | ✅ Implemented | Fully tested |
| **updateUserRoles()** | ✅ Implemented | Fully tested |
| **updatePermissions()** | ✅ Implemented | Fully tested |
| **Error Handling** | ✅ Complete | All error codes |
| **Logging** | ✅ Detailed | Console + notifications |
| **Data Reload** | ✅ Automatic | After successful update |
| **Documentation** | ✅ Complete | 4+ guides |
| **Testing Scripts** | ✅ Provided | PowerShell + browser |

---

## 🚀 Getting Started

1. **Read:** [TESTING_ALL_ENDPOINTS.md](TESTING_ALL_ENDPOINTS.md)
2. **Test:** Run test commands in browser console
3. **Verify:** Check console logs and notifications
4. **Deploy:** All endpoints ready for production

---

**Last Updated:** 29 January 2026  
**Version:** Complete  
**Status:** ✅ All Endpoints Implemented & Documented
