import { inject } from "@angular/core";
import { CanActivateFn, Router } from "@angular/router";
import { AuthService } from "../services/auth.service";
import { NAVIGATION } from "../navigation/navigation.config";

export const roleGuard: CanActivateFn = (route) => {
  const authService = inject(AuthService);
  const router = inject(Router);
  const expectedRoles = route.data?.['roles'] as string[];
  const user = authService.currentUser();
  if(!user){
    return router.createUrlTree([NAVIGATION.auth.login]);
  }
  const hasRole = user.roles.some(role => expectedRoles.includes(role));
  return hasRole ? true : router.createUrlTree([NAVIGATION.common.unauthorized])
}
