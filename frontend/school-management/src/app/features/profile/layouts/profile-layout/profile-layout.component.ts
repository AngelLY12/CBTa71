import { CommonModule } from '@angular/common';
import { Component, EventEmitter, inject, Input, OnInit, Output, TemplateRef } from '@angular/core';
import { RouterModule } from '@angular/router';
import { DashboardLayoutComponent, LoadingState } from '../../../../layouts/dashboard-layout/dashboard-layout.component';
import { ProfileService } from '../../../../core/services/profile.service';

@Component({
  selector: 'app-profile-layout',
  standalone:true,
  imports: [CommonModule, RouterModule, DashboardLayoutComponent],
  templateUrl: './profile-layout.component.html',
  styleUrl: './profile-layout.component.scss'
})
export class ProfileLayoutComponent {


}
