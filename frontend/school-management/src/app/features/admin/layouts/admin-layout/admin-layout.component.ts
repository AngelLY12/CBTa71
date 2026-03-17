import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { DashboardLayoutComponent } from '../../../../layouts/dashboard-layout/dashboard-layout.component';
import { RouterModule } from '@angular/router';

@Component({
  selector: 'app-admin-layout',
  imports: [CommonModule, DashboardLayoutComponent, RouterModule],
  templateUrl: './admin-layout.component.html',
  styleUrl: './admin-layout.component.scss'
})
export class AdminLayoutComponent {

}
