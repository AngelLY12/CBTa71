import { Routes } from "@angular/router";
import { NotFoundComponent } from "./pages/not-found/not-found.component";
import { UnverifiedComponent } from "./pages/unverified/unverified.component";
import { MaintenanceComponent } from "./pages/maintenance/maintenance.component";
import { UnauthorizedComponent } from "./pages/unauthorized/unauthorized.component";

export const PUBLIC_ROUTES: Routes = [
  {
    path: '404',
    component:NotFoundComponent
  },
  {
    path: 'unverified',
    component: UnverifiedComponent

  },
  {
    path: 'maintenance',
    component: MaintenanceComponent
  },
  {
    path: 'unauthorized',
    component: UnauthorizedComponent
  },
  {path: '**', redirectTo: '/common/404'}


];
