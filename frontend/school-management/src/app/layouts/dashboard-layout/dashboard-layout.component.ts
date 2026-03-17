import { CommonModule } from '@angular/common';
import { Component, ContentChild, EventEmitter, inject, Input, OnInit, Output, TemplateRef } from '@angular/core';
import { SidebarComponent } from '../../shared/components/sidebar/sidebar.component';
import { NavigationService } from '../../core/services/navigation.service';
import { Role } from '../../core/models/enums/role.enum';
import { RoleSelectorComponent } from '../../shared/components/role-selector/role-selector.component';
import { AuthService } from '../../core/services/auth.service';
import { MenuItem } from '../../core/models/menu-item.model';
import { NAVIGATION } from '../../core/navigation/navigation.config';
import { SpinnerComponent } from '../../shared/components/spinner/spinner.component';
import { DashboardHeaderComponent } from '../../shared/components/dashboard-header/dashboard-header.component';
import { ButtonComponent } from '../../shared/components/button/button.component';

export type LoadingState = 'loading' | 'error' | 'success';

@Component({
  selector: 'app-dashboard-layout',
  standalone:true,
  imports: [CommonModule,SidebarComponent, RoleSelectorComponent, SpinnerComponent, DashboardHeaderComponent, ButtonComponent],
  templateUrl: './dashboard-layout.component.html',
  styleUrl: './dashboard-layout.component.scss'
})
export class DashboardLayoutComponent implements OnInit {
  @Input() title: string = '';
  @Input() welcomeMessage: string = '';
  @Input() icon: string = '';
  @Input() iconSize: 'sm' | 'md' | 'lg' | 'xl' = 'lg';
  @Input() loadingMessage: string = 'Cargando contenido...';
  @Input() state: LoadingState = 'success';
  @Input() errorMessage: string = 'Error al cargar los datos. Intenta de nuevo.';

  @Input() showAlertBadge = false;
  @Input() alertCount = 0;

  @ContentChild('headerContent') headerContent?: TemplateRef<any>;
  @ContentChild('headerActions') headerActions?: TemplateRef<any>;
  @ContentChild('headerBottom') headerBottom?: TemplateRef<any>;
  @ContentChild('content') content?: TemplateRef<any>;

  @Output() retry = new EventEmitter<void>();

  private navigationService = inject(NavigationService);
  private authService = inject(AuthService);

  userRoles: Role[] = [];
  userName: string = '';
  userAvatar: string = '';
  userRole: string = '';
  fechaActual = new Date();

  menuItems: MenuItem[] = [];
  collapsed: boolean = false;

  showRoleSelector = false;

  ngOnInit() {
    this.loadUserData();
    this.loadMenuByRole();

    this.navigationService.showRoleSelector$.subscribe(show => {
      this.showRoleSelector = show;
    });
  }

  loadUserData() {
    const user = this.authService.currentUser();
    if (user) {
      this.userRoles = user.roles || [];
      this.userName = user.fullName || '';
      this.userAvatar = '';
      this.userRole = this.userRoles[0] || '';
    }
  }

  loadMenuByRole() {
    const role = this.userRole;

    const menus: Record<string, MenuItem[]> = {
      [Role.ADMIN]: [
        { label: 'Dashboard', icon: 'dashboard', route: NAVIGATION.admin.dashboard },
        { label: 'Usuarios', icon: 'people', route: NAVIGATION.admin.users },
        { label: 'Reportes', icon: 'bar_chart', route: '/admin/reportes' },
        { label: 'Configuración', icon: 'settings', route: '/admin/configuracion' }
      ],
      [Role.STUDENT]: [
        { label: 'Inicio', icon: 'home', route: '/estudiante' },
        { label: 'Cursos', icon: 'menu_book', route: '/estudiante/cursos' },
        { label: 'Calificaciones', icon: 'grade', route: '/estudiante/calificaciones' }
      ],
      [Role.PARENT]: [
        { label: 'Inicio', icon: 'home', route: '/padre' },
        { label: 'Hijos', icon: 'family_history', route: '/padre/hijos' },
        { label: 'Pagos', icon: 'payments', route: '/padre/pagos' }
      ]
    };

    this.menuItems = menus[role] || [
      { label: 'Dashboard', icon: 'dashboard', route: '/dashboard' }
    ];
  }

  get isLoading(): boolean {
    return this.state === 'loading';
  }

  onCollapsedChange(collapsed: boolean) {
    this.collapsed = collapsed;
  }

  onRoleSelected(role: Role) {
    this.navigationService.saveRolePreference(role);
  }

  onSelectorClose() {
    this.navigationService.cancelRoleSelection();
  }
  onRetry(){
    this.retry.emit();
  }

}
