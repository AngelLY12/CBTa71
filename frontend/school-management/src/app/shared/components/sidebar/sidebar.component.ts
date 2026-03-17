import { MenuItem } from './../../../core/models/menu-item.model';
import { CommonModule } from '@angular/common';
import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import { Router, RouterModule } from '@angular/router';
import { ButtonComponent } from '../button/button.component';
import { AvatarComponent } from '../avatar/avatar.component';
import { NAVIGATION } from '../../../core/navigation/navigation.config';

@Component({
  selector: 'app-sidebar',
  standalone: true,
  imports: [CommonModule, RouterModule, ButtonComponent, AvatarComponent],
  templateUrl: './sidebar.component.html',
  styleUrl: './sidebar.component.scss'
})
export class SidebarComponent implements OnInit {
  @Input() menuItems: MenuItem[] = [];
  @Input() collapsed: boolean = false;
  @Input() logoText: string = 'SIGEF';
  @Input() logoIcon: string = 'payments';

  @Input() role: string = '';
  @Input() userName: string = '';
  @Input() userAvatar: string = '';
  @Input() showUserInfo: boolean = true;

  @Output() collapsedChange = new EventEmitter<boolean>();

  activeItem: string = '';
  expandedMenus: Set<string> = new Set();

  constructor(private router: Router) {}

  ngOnInit() {
    this.activeItem = this.router.url;
  }


  mobileOpen = false;

  toggleSidebar() {
    if (window.innerWidth <= 768) {
      this.mobileOpen = !this.mobileOpen;
    } else {
      this.collapsed = !this.collapsed;
      this.collapsedChange.emit(this.collapsed);
    }
  }

  closeMobileSidebar() {
    if (window.innerWidth <= 768) {
      this.mobileOpen = false;
    }
  }

  toggleSubMenu(menuLabel: string) {
    if (this.expandedMenus.has(menuLabel)) {
      this.expandedMenus.delete(menuLabel);
    } else {
      this.expandedMenus.add(menuLabel);
    }
  }

  isMenuExpanded(menuLabel: string): boolean {
    return this.expandedMenus.has(menuLabel);
  }

  isActive(route: string, exact: boolean = false): boolean {
    if (exact) {
      return this.router.url === route;
    }
    return this.router.url.startsWith(route);
  }

  getBadgeClass(color: string = 'primary'): string {
    return `badge-${color}`;
  }

  onProfileClick() {
    this.router.navigate([NAVIGATION.profile.view]);
  }

}
