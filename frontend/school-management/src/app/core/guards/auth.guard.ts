import { CanActivateFn, Router } from "@angular/router";
import { inject } from "@angular/core";
import { AuthService } from "../services/auth.service";
import { NAVIGATION } from "../navigation/navigation.config";
import { NavigationService } from "../services/navigation.service";

export const authGuard: CanActivateFn = () => {
  const authService = inject(AuthService);
  const navigationService = inject(NavigationService);
  const router = inject(Router);
  const isAuthenticated = authService.isAuthenticated();
  const user = authService.currentUser();
  if(isAuthenticated && user){
    if(user.roles && user.roles.length > 1){
      navigationService.redirectByRole(user.roles);
    }
    if(user.roles && user.roles.length === 1) {
      const role = user.roles[0];
      navigationService.navigateToRoleDashboard(role);
    }
    return router.createUrlTree([NAVIGATION.common.unverified])
  }

  return true;

};
