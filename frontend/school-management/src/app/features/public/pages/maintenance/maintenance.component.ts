import { CommonModule } from '@angular/common';
import { Component, inject } from '@angular/core';
import { PublicLayoutComponent } from '../../../../layouts/public-layout/public-layout.component';
import { AuthService } from '../../../../core/services/auth.service';
import { Router } from '@angular/router';
import { NAVIGATION } from '../../../../core/navigation/navigation.config';

@Component({
  selector: 'app-maintenance',
  imports: [CommonModule, PublicLayoutComponent],
  templateUrl: './maintenance.component.html',
  styleUrl: './maintenance.component.scss'
})
export class MaintenanceComponent {
 private authService = inject(AuthService);
  private router = inject(Router);

  hasActiveSession = false;
  primaryButton: any = null;

  ngOnInit() {
    this.hasActiveSession = this.authService.checkSession();

    if (this.hasActiveSession) {
      this.primaryButton = {
        text: 'Cerrar sesión',
        icon: 'logout',
        variant: 'primary',
        action: 'logout',
      };
    }
  }
}
