# 🎯 Endpoints Individual & Bulk - Testing Guide

This guide covers testing all three permission/role endpoints.

## 📋 Endpoints Overview

### 1. Update Permissions - Individual User
```
POST /api/v1/admin-actions/update-permissions/{userId}
Function: updateUserPermissions(userId, permissionsToAdd, permissionsToRemove)
Status: ✅ Implemented
```

### 2. Update Roles - Individual User
```
POST /api/v1/admin-actions/updated-roles/{userId}
Function: updateUserRoles(userId, rolesToAdd, rolesToRemove)
Status: ✅ Implemented
```

### 3. Update Permissions - Multiple Users (Bulk)
```
POST /api/v1/admin-actions/update-permissions
Function: updatePermissions() (modal-based)
Status: ✅ Implemented
```

---

## 🧪 Testing All Endpoints

### Test 1: Update Individual User Permissions

**Function Call:**
```javascript
const result = await updateUserPermissions(
  4, // userId
  ['reports.view', 'users.create'], // permissionsToAdd
  ['users.delete'] // permissionsToRemove
);
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "Permisos actualizados correctamente.",
  "data": {
    "updated": [{
      "userId": 4,
      "fullName": "Juan Perez",
      "permissions": {
        "added": ["reports.view", "users.create"],
        "removed": ["users.delete"]
      }
    }]
  }
}
```

**Validation Points:**
- ✓ User 4 receives new permissions
- ✓ Old permissions are revoked
- ✓ Notification shows success
- ✓ User list reloads

---

### Test 2: Update Individual User Roles

**Function Call:**
```javascript
const result = await updateUserRoles(
  4, // userId
  ['teacher', 'editor'], // rolesToAdd
  ['student'] // rolesToRemove
);
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "Roles actualizados correctamente.",
  "data": {
    "updated": {
      "userId": 4,
      "fullName": "Juan Perez",
      "roles": {
        "added": ["teacher", "editor"],
        "removed": ["student"]
      }
    }
  }
}
```

**Validation Points:**
- ✓ User 4 gets new roles
- ✓ Old roles are removed
- ✓ Notification shows success
- ✓ User list reloads

---

### Test 3: Update Permissions - Multiple Users (Bulk via CURPs)

**Trigger:**
1. Go to Roles page
2. Select multiple users
3. Click "Gestionar Permisos" button
4. Select permissions to add/remove
5. Click "Actualizar Permisos"

**Request Payload (Sent Automatically):**
```json
{
  "curps": [
    "GODE561231HDFABC09",
    "PEMJ800101MDFLRS08"
  ],
  "permissionsToAdd": ["reports.view", "users.create"],
  "permissionsToRemove": ["users.delete"]
}
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "Permisos actualizados correctamente.",
  "data": {
    "users_permissions": [{
      "fullName": "Juan Pérez",
      "curp": "GODE561231HDFABC09",
      "role": "student",
      "updatedPermissions": {
        "added": ["reports.view", "users.create"],
        "removed": ["users.delete"]
      },
      "metadata": {
        "totalFound": 2,
        "totalUpdated": 2,
        "failed": 0,
        "failedUsers": [],
        "operations": {
          "permissions_removed": ["users.delete"],
          "permissions_added": ["reports.view", "users.create"],
          "roles_processed": 0
        }
      }
    }]
  }
}
```

**Validation Points:**
- ✓ Multiple users selected correctly
- ✓ CURPs are filtered and validated
- ✓ Permissions updated for all users
- ✓ Metadata shows operation details
- ✓ User list reloads

---

### Test 4: Update Permissions - Multiple Users (Bulk via Role)

**Alternative Request (by Role):**
```json
{
  "role": "student",
  "permissionsToAdd": ["reports.view"],
  "permissionsToRemove": ["settings.update"]
}
```

**Note:** Use either `curps` OR `role`, not both.

---

## 💻 Browser Console Tests

### Test All Three Together

```javascript
// Get the component
const app = Alpine.$data(document.querySelector('[x-data="rolesData"]'));

// Test 1: Individual permissions
console.log('Test 1: Individual Permissions');
const perm = await app.updateUserPermissions(4, ['reports.view'], ['users.delete']);
console.log('Result:', perm);

// Test 2: Individual roles
console.log('\nTest 2: Individual Roles');
const roles = await app.updateUserRoles(4, ['teacher'], ['student']);
console.log('Result:', roles);

// Test 3: Bulk permissions (requires UI interaction)
console.log('\nTest 3: Bulk Permissions (via modal)');
// Need to set: app.selectedUsers, app.permissionsToAdd, app.permissionsToRemove
// Then call: await app.updatePermissions();
```

---

## 🧪 PowerShell Tests

### Test Individual Permissions Endpoint

```powershell
$token = "YOUR_TOKEN"
$userId = 4
$url = "https://nginx-production-728f.up.railway.app/api/v1/admin-actions/update-permissions/$userId"

$body = @{
    permissionsToAdd = @("reports.view", "users.create")
    permissionsToRemove = @("users.delete")
} | ConvertTo-Json

$headers = @{
    "Authorization" = "Bearer $token"
    "Content-Type" = "application/json"
    "X-User-Role" = "admin"
    "X-User-Permission" = "sync.permissions"
}

$response = Invoke-RestMethod -Uri $url -Method POST -Body $body -Headers $headers
$response | ConvertTo-Json -Depth 10 | Write-Host
```

### Test Individual Roles Endpoint

```powershell
$token = "YOUR_TOKEN"
$userId = 4
$url = "https://nginx-production-728f.up.railway.app/api/v1/admin-actions/updated-roles/$userId"

$body = @{
    rolesToAdd = @("teacher", "editor")
    rolesToRemove = @("student")
} | ConvertTo-Json

$headers = @{
    "Authorization" = "Bearer $token"
    "Content-Type" = "application/json"
    "X-User-Role" = "admin"
    "X-User-Permission" = "sync.roles"
}

$response = Invoke-RestMethod -Uri $url -Method POST -Body $body -Headers $headers
$response | ConvertTo-Json -Depth 10 | Write-Host
```

### Test Bulk Permissions Endpoint

```powershell
$token = "YOUR_TOKEN"
$url = "https://nginx-production-728f.up.railway.app/api/v1/admin-actions/update-permissions"

# Option 1: By CURPs
$body = @{
    curps = @("GODE561231HDFABC09", "PEMJ800101MDFLRS08")
    permissionsToAdd = @("reports.view", "users.create")
    permissionsToRemove = @("users.delete")
} | ConvertTo-Json

# Option 2: By Role (uncomment to use)
# $body = @{
#     role = "student"
#     permissionsToAdd = @("reports.view")
#     permissionsToRemove = @("settings.update")
# } | ConvertTo-Json

$headers = @{
    "Authorization" = "Bearer $token"
    "Content-Type" = "application/json"
    "X-User-Role" = "admin"
    "X-User-Permission" = "sync.permissions"
}

$response = Invoke-RestMethod -Uri $url -Method POST -Body $body -Headers $headers
$response | ConvertTo-Json -Depth 10 | Write-Host
```

---

## ✅ Test Checklist

### Individual Permissions
- [ ] Function exists: `updateUserPermissions()`
- [ ] Correct endpoint: `/update-permissions/{userId}`
- [ ] Headers include `sync.permissions`
- [ ] Response structure matches API spec
- [ ] Errors handled correctly (401, 422, 500)
- [ ] User data reloads after update

### Individual Roles
- [ ] Function exists: `updateUserRoles()`
- [ ] Correct endpoint: `/updated-roles/{userId}`
- [ ] Headers include `sync.roles`
- [ ] Response structure matches API spec
- [ ] Errors handled correctly
- [ ] User data reloads after update

### Bulk Permissions
- [ ] Function exists: `updatePermissions()`
- [ ] Correct endpoint: `/update-permissions` (no userId)
- [ ] Supports CURPS filtering
- [ ] Supports role-based selection
- [ ] Validates CURP format (18 chars)
- [ ] Response includes metadata
- [ ] Shows operation summary

---

## 🔍 Debugging Tips

### Check Logs
```javascript
// Open browser F12 → Console
// You should see:
// 📤 updateUserPermissions - Enviando para userId 4:
// 📥 updateUserPermissions - Respuesta:
// 📤 updateUserRoles - Enviando para userId 4:
// 📥 updateUserRoles - Respuesta:
// 📤 updatePermissions - Enviando:
// 📥 updatePermissions - Respuesta:
```

### Verify Token
```javascript
const token = localStorage.getItem('access_token');
console.log('Token:', token ? '✅ Present' : '❌ Missing');
console.log('Token preview:', token?.substring(0, 20) + '...');
```

### Check User Permissions
```javascript
const userData = JSON.parse(localStorage.getItem('user_data'));
console.log('User role:', userData.role);
console.log('User permissions:', userData.permissions);
```

---

## 🚨 Common Errors

### Error 401 (Unauthorized)
**Cause:** Missing or invalid token/role/permission

**Solution:**
```javascript
// Verify token
localStorage.getItem('access_token') // Should exist
// Verify user role/permissions
// User must have: admin/supervisor role + sync.permissions/sync.roles
```

### Error 422 (Validation Error)
**Cause:** Invalid data sent

**Validation Rules:**
- At least one of: permissionsToAdd OR permissionsToRemove
- At least one of: rolesToAdd OR rolesToRemove
- userId must exist
- CURPs must be 18 characters
- Either curps OR role (not both) for bulk endpoint

### Error 500 (Server Error)
**Cause:** Backend processing error

**Check:**
- Is the backend running?
- Are the permissions/roles valid?
- Is the user ID valid?

---

## 📊 Request/Response Comparison

| Aspect | Individual Permissions | Individual Roles | Bulk Permissions |
|--------|----------------------|------------------|------------------|
| **Method** | POST | POST | POST |
| **URL** | `/update-permissions/{userId}` | `/updated-roles/{userId}` | `/update-permissions` |
| **Identifier** | userId in URL | userId in URL | curps or role in body |
| **Request Body** | permissionsToAdd/Remove | rolesToAdd/Remove | curps/role + permissions |
| **Response Data** | updated[0] (array) | updated (object) | users_permissions (array) |
| **Header Perm** | sync.permissions | sync.roles | sync.permissions |

---

## 🎯 Test Scenarios

### Scenario 1: Grant All Reporting Permissions
```javascript
// Test: Give all reporting permissions to user 4
await updateUserPermissions(4, ['reports.view', 'reports.create', 'reports.edit'], []);
```

### Scenario 2: Revoke Delete Permissions
```javascript
// Test: Remove all delete permissions from user 4
await updateUserPermissions(4, [], ['users.delete', 'students.delete', 'content.delete']);
```

### Scenario 3: Promote Student to Teacher
```javascript
// Test: Change role from student to teacher
await updateUserRoles(4, ['teacher'], ['student']);
```

### Scenario 4: Bulk Grant Teacher Permissions
```javascript
// Test: Give all students reporting permission
// Use modal: Select all students → Add reports.view → Save
```

### Scenario 5: Error Handling - Invalid User
```javascript
// Test: Update non-existent user
await updateUserPermissions(99999, ['reports.view'], []);
// Should get 422 error: User not found
```

---

## ✨ Success Indicators

✅ **All three endpoints working if:**
- No console errors
- Notifications appear (green success or red error)
- User list refreshes automatically
- Metadata shows correct counts
- No 401/422/500 errors for valid requests

✅ **Implementation complete when:**
- All three functions implemented
- All endpoints tested successfully
- Error handling works
- Data persists in database

---

**Last Updated:** 29 January 2026  
**Status:** All endpoints implemented and documented  
**Next Step:** Run tests to verify functionality
