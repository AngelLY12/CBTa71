import { CommonModule } from '@angular/common';
import { Component, inject } from '@angular/core';
import { PublicLayoutComponent } from '../../../../layouts/public-layout/public-layout.component';
import { AuthService } from '../../../../core/services/auth.service';
import { Router } from '@angular/router';
import { NAVIGATION } from '../../../../core/navigation/navigation.config';

@Component({
  selector: 'app-unverified',
  standalone: true,
  imports: [CommonModule, PublicLayoutComponent],
  templateUrl: './unverified.component.html',
  styleUrl: './unverified.component.scss'
})
export class UnverifiedComponent {

}
