import { CommonModule } from '@angular/common';
import { Component, EventEmitter, inject, Input, Output } from '@angular/core';
import { ThemeButtonComponent } from '../theme-button/theme-button.component';
import { Router } from '@angular/router';
import { NAVIGATION } from '../../../core/navigation/navigation.config';
import { SpinnerComponent } from '../spinner/spinner.component';
import { LogoutService } from '../../../core/services/logout.service';

@Component({
  selector: 'app-avatar',
  imports: [CommonModule, ThemeButtonComponent, SpinnerComponent],
  templateUrl: './avatar.component.html',
  styleUrl: './avatar.component.scss'
})
export class AvatarComponent {
  private logoutService = inject(LogoutService);
  private router = inject(Router);
  isLoading = false;
  @Input() role: string = '';
  @Input() userName: string = '';
  @Input() userAvatar: string = '';
  @Input() collapsed: boolean = false;
  @Input() showUserInfo: boolean = true;

  @Output() profileClick = new EventEmitter<void>();
  isMenuOpen = false;

  toggleMenu() {
    this.isMenuOpen = !this.isMenuOpen;
  }

  closeMenu() {
    this.isMenuOpen = false;
  }

  onProfileClick() {
    this.profileClick.emit();
    this.closeMenu();
  }

  logout(): void {
    this.isLoading = true;
    this.logoutService.logout().subscribe({
        complete: () => this.isLoading = false
    });
  }

}
