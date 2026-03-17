import { CommonModule } from '@angular/common';
import { Component, inject, OnInit, TemplateRef, ViewChild } from '@angular/core';
import { ButtonComponent } from '../../../../shared/components/button/button.component';
import { DashboardHeaderComponent } from '../../../../shared/components/dashboard-header/dashboard-header.component';
import { AuthService } from '../../../../core/services/auth.service';
import { ProfileService } from '../../../../core/services/profile.service';
import { UserProfile } from '../../models/user-profile.model';
import { SpinnerComponent } from '../../../../shared/components/spinner/spinner.component';
import { Router } from '@angular/router';
import { NAVIGATION } from '../../../../core/navigation/navigation.config';
import { InfoCardComponent } from '../../../../shared/components/info-card/info-card.component';
import { InfoCardItemComponent } from "../../../../shared/components/info-card-item/info-card-item.component";

@Component({
  selector: 'app-profile-view',
  imports: [CommonModule, ButtonComponent, InfoCardComponent, InfoCardItemComponent, DashboardHeaderComponent, SpinnerComponent],
  templateUrl: './profile-view.component.html',
  styleUrl: './profile-view.component.scss'
})
export class ProfileViewComponent implements OnInit{

  private authService = inject(AuthService);
  private profileService = inject(ProfileService);
  private router = inject(Router);
  profile: UserProfile | null = null;
  currentName = this.authService.currentUser()?.fullName;
  isLoading = true;
  error = false;
  fechaActual = new Date();
  ngOnInit(): void {
    this.loadProfile()
  }

  loadProfile(){
    this.isLoading = true;
    this.error = false;

    this.profileService.profile().subscribe({
      next: (response) => {
        this.profile = response.data.user;
        this.isLoading = false;
      },
      error: () => {
        this.error = true;
        this.isLoading = false;
      }
    });
  }

  editProfile()
  {
    this.router.navigate([NAVIGATION.profile.edit])
  }

  changePassword()
  {
    this.router.navigate([NAVIGATION.profile.changePassword])
  }


}
