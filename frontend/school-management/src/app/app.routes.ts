import { Routes } from '@angular/router';
import { roleGuard } from './core/guards/role.guard';
import { Role } from './core/models/enums/role.enum';
import { protectedGuard } from './core/guards/protected.guard';
import { authGuard } from './core/guards/auth.guard';

export const routes: Routes = [

  {
    path: '',
    redirectTo: 'auth/login',
    pathMatch: 'full'
  },

  {
    path: 'auth',
    loadChildren: () =>
      import('./features/auth/auth.routes')
    .then(m => m.AUTH_ROUTES),
    canActivate: [authGuard]
  },

   {
    path: 'common',
    loadChildren: () =>
      import('./features/public/public.routes')
    .then(m => m.PUBLIC_ROUTES)
  },

  {
    path: 'admin',
      loadChildren: () =>
      import('./features/admin/admin.routes')
    .then(m => m.ADMIN_ROUTES),
    canActivate: [protectedGuard, roleGuard],
    data: {roles: [Role.ADMIN]}
  },

  {
    path: 'financial',
    loadChildren: () =>
      import('./features/financial/financial.routes')
    .then(m => m.FINANCIAL_ROUTES)
  },

  {
    path: 'client',
    loadChildren: () =>
      import('./features/client/client.routes')
    .then(m => m.CLIENT_ROUTES)
  },
  {
    path: 'profile',
    loadChildren: () =>
      import('./features/profile/profile.routes')
    .then(m => m.PROFILE_ROUTES),
    canActivate: [protectedGuard],
  },

  {
    path: '**',
    redirectTo: 'common/404'
  }

];
