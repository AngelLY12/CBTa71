import { Routes } from "@angular/router";
import { ProfileViewComponent } from "./pages/profile-view/profile-view.component";
import { ProfileEditComponent } from "./pages/profile-edit/profile-edit.component";
import { ChangePasswordComponent } from "./pages/change-password/change-password.component";
import { ProfileLayoutComponent } from "./layouts/profile-layout/profile-layout.component";

export const PROFILE_ROUTES: Routes = [
  {
    path: '',
    component: ProfileLayoutComponent,
    children: [
      {
        path: '',
        redirectTo: 'view',
        pathMatch: 'full'
      },
      {
        path: 'view',
        component: ProfileViewComponent
      },
      {
        path: 'update',
        component: ProfileEditComponent
      },
      {
        path: 'change-password',
        component: ChangePasswordComponent
      }


    ]
  }


];
