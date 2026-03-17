import { CommonModule } from '@angular/common';
import { Component, inject } from '@angular/core';
import { ButtonComponent } from '../../../../shared/components/button/button.component';
import { InputComponent } from '../../../../shared/components/input/input.component';
import { AuthLayoutComponent } from '../../../../layouts/auth-layout/auth-layout.component';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { AuthService } from '../../../../core/services/auth.service';
import { ModalService } from '../../../../core/services/modal.service';
import { Router } from '@angular/router';
import { AuthNavigationHelper } from '../../../../core/helpers/navigation/auth-navigation.helper';

@Component({
  selector: 'app-forgot-password',
  imports: [CommonModule, ReactiveFormsModule,ButtonComponent, InputComponent, AuthLayoutComponent],
  templateUrl: './forgot-password.component.html',
  styleUrl: './forgot-password.component.scss'
})
export class ForgotPasswordComponent {
  private fb = inject(FormBuilder);
  private authService = inject(AuthService);
  private modalService = inject(ModalService);
  protected navHelper = inject(AuthNavigationHelper);

  loading = false;
  form = this.fb.group({
    email: ['', [Validators.required, Validators.email]],
  });

  submit() {
    if(this.form.invalid) return;

    this.loading = true;

    const { email } = this.form.value;

    this.authService.forgotPassword(email!)
    .subscribe({
      next: (res) => {
        this.modalService.show({ message: res.message, type: 'success', display: 'modal' })
      },
      error: (err) => {
        this.loading = false;
      }
    })

  }

}
