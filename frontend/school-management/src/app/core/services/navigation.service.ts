import { inject, Injectable } from "@angular/core";
import { Router } from "@angular/router";
import { Role } from "../models/enums/role.enum";
import { NAVIGATION } from "../navigation/navigation.config";
import { BehaviorSubject } from "rxjs";

@Injectable({ providedIn: 'root' })
export class NavigationService {
  private router = inject(Router);
  private roleSelector = new BehaviorSubject<boolean>(false);
  private pendingRoles: Role[] = [];
  showRoleSelector$ = this.roleSelector.asObservable();
  redirectByRole(roles: string[]): void {
    if(roles.includes(Role.UNVERIFIED)) {
      this.router.navigate([NAVIGATION.common.unverified]);
      return;
    }

    const validRoles = roles.filter(r => r !== Role.UNVERIFIED) as Role[];

    if (validRoles.length > 1) {
      this.pendingRoles = validRoles;
      this.roleSelector.next(true);
      return;
    }
    if (validRoles.length === 1) {
      this.navigateToRoleDashboard(validRoles[0]);
      return;
    }

    this.router.navigate([NAVIGATION.common.unauthorized]);

  }

  navigateToRoleDashboard(role: Role): void {
    const roleRoutes: Record<Role, string> = {
      [Role.ADMIN]: NAVIGATION.admin.dashboard,
      [Role.APPLICANT]: NAVIGATION.admin.dashboard,
      [Role.FINANCIAL_STAFF]: NAVIGATION.admin.dashboard,
      [Role.STUDENT]: NAVIGATION.admin.dashboard,
      [Role.SUPERVISOR]: NAVIGATION.admin.dashboard,
      [Role.PARENT]: NAVIGATION.admin.dashboard,
      [Role.UNVERIFIED]: NAVIGATION.common.unverified
    };

    this.router.navigate([roleRoutes[role]]);
  }

  getDashboardRoute(role: string): string {
    const routes: Record<Role, string> = {
      [Role.ADMIN]: NAVIGATION.admin.dashboard,
      [Role.APPLICANT]: NAVIGATION.admin.dashboard,
      [Role.FINANCIAL_STAFF]: NAVIGATION.admin.dashboard,
      [Role.STUDENT]: NAVIGATION.admin.dashboard,
      [Role.SUPERVISOR]: NAVIGATION.admin.dashboard,
      [Role.PARENT]: NAVIGATION.admin.dashboard,
      [Role.UNVERIFIED]: NAVIGATION.common.unverified
    };

    const roleEnum = role as Role;
    return routes[roleEnum] || NAVIGATION.common.unverified;
  }

  saveRolePreference(role: Role): void {
    localStorage.setItem('preferredRole', role);
    this.roleSelector.next(false);
    this.navigateToRoleDashboard(role);
  }

  cancelRoleSelection(): void {
    this.roleSelector.next(false);
    this.pendingRoles = [];
    this.router.navigate([NAVIGATION.auth.login]);
  }

}
