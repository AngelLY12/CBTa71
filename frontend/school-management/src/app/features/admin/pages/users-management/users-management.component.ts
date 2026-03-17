import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { DashboardLayoutComponent } from '../../../../layouts/dashboard-layout/dashboard-layout.component';

@Component({
  selector: 'app-users-management',
  standalone: true,
  imports: [CommonModule, DashboardLayoutComponent],
  templateUrl: './users-management.component.html',
  styleUrl: './users-management.component.scss'
})
export class UsersManagementComponent {

}
