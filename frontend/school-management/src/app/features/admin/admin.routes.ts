import { Routes } from "@angular/router";
import { UsersManagementComponent } from "./pages/users-management/users-management.component";
import { DashboardComponent } from "./pages/dashboard/dashboard.component";
import { AdminLayoutComponent } from "./layouts/admin-layout/admin-layout.component";

export const ADMIN_ROUTES: Routes = [
  {
    path: '',
    component: AdminLayoutComponent,
    children: [
      {
        path: '',
        redirectTo: 'dashboard',
        pathMatch: 'full'
      },
      {
        path: 'dashboard',
        component: DashboardComponent
      },
      {
        path: 'users',
        component: UsersManagementComponent
      },

    ]
  }

];
