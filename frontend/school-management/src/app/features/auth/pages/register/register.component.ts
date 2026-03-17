import { CommonModule } from '@angular/common';
import { Component, inject } from '@angular/core';
import { AbstractControl, FormBuilder, FormControl, FormGroup, ReactiveFormsModule, ValidationErrors, Validators } from '@angular/forms';
import { InputComponent } from '../../../../shared/components/input/input.component';
import { Router, RouterLink } from '@angular/router';
import { AuthService } from '../../../../core/services/auth.service';
import { RegisterUser } from '../../models/register.model';
import { Gender } from '../../../../core/models/types/gender.type';
import { BloodType } from '../../../../core/models/types/blood-type.type';
import { AuthLayoutComponent } from '../../../../layouts/auth-layout/auth-layout.component';
import { BloodType as BloodTypeEnum } from '../../../../core/models/enums/blood-type.enum';
import { SelectComponent } from '../../../../shared/components/select/select.component';
import { Gender as GenderEnum } from '../../../../core/models/enums/gender.enum';
import { StepperComponent } from '../../../../shared/components/stepper/stepper.component';
import { PasswordInputComponent } from '../../../../shared/components/password-input/password-input.component';
import { ModalService } from '../../../../core/services/modal.service';
import { AddressComponent } from '../../../../shared/components/address/address.component';
import { cleanObject } from '../../../../core/helpers';
import { AuthNavigationHelper } from '../../../../core/helpers/navigation/auth-navigation.helper';

@Component({
  selector: 'app-register',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, AddressComponent,InputComponent, PasswordInputComponent, AuthLayoutComponent, SelectComponent, StepperComponent],
  templateUrl: './register.component.html',
  styleUrl: './register.component.scss'
})
export class RegisterComponent {
  protected navHelper = inject(AuthNavigationHelper);

  readonly bloodTypeOptions = Object.values(BloodTypeEnum).map(type => ({
    label: type,
    value: type
  }));

  readonly genderOptions = Object.values(GenderEnum).map(type => ({
    label: type,
    value: type
  }));

  passwordControl: FormControl<string>;
  confirmPasswordControl: FormControl<string>;

  constructor() {
    this.passwordControl = this.form.get('password') as FormControl<string>;
    this.confirmPasswordControl = this.form.get('confirmPassword') as FormControl<string>;
  }

  private fb = inject(FormBuilder).nonNullable;
  private authService = inject(AuthService);
  private modalService = inject(ModalService);
  private router = inject(Router);
  currentStep = 0;
  loading = false;
  addressCustomErrors = {
    cp: {
      pattern: 'El código postal debe tener 5 dígitos'
    },
    street: {
      maxlength: 'La calle no puede tener más de 100 caracteres'
    },
    number: {
      maxlength: 'El número no puede tener más de 10 caracteres'
    },
    neighborhood: {
      maxlength: 'La colonia no puede tener más de 100 caracteres'
    },
    state: {
      maxlength: 'El estado no puede tener más de 50 caracteres'
    },
    city: {
      maxlength: 'La ciudad no puede tener más de 50 caracteres'
    }
  };

  form = this.fb.group({
    name: this.fb.control('', [
      Validators.required,
      Validators.minLength(2),
      Validators.maxLength(50),
      Validators.pattern(/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/)
    ]),
    last_name: this.fb.control('', [
      Validators.required,
      Validators.minLength(2),
      Validators.maxLength(50),
      Validators.pattern(/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/)
    ]),
    email: this.fb.control('', [
      Validators.required,
      Validators.email

    ]),
    password: this.fb.control('', [
      Validators.required,
      Validators.minLength(8),
      Validators.pattern(/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&#=¿¡])/)
    ]),
    confirmPassword: this.fb.control('', Validators.required),
    phone_number: this.fb.control('', [
      Validators.required,
      Validators.pattern(/^\+52[0-9]{10}$/)
    ]),
    birthdate: this.fb.control('', [Validators.required, this.dateValidator]),
    gender: this.fb.control<Gender>('' as Gender),
    curp: this.fb.control('', [
      Validators.required,
      Validators.minLength(18),
      Validators.maxLength(18),
    ]),
    blood_type: this.fb.control<BloodType>('' as BloodType),
    address: this.fb.group({
      cp: ['', [Validators.pattern(/^[0-9]{5}$/)]],
      street: ['', [Validators.maxLength(100)]],
      number: ['', [Validators.maxLength(10)]],
      neighborhood: ['', [Validators.maxLength(100)]],
      state: ['', [Validators.maxLength(50)]],
      city: ['', [Validators.maxLength(50)]]
    })
  }, {
    validators: this.passwordsMatchValidator
  });

  private passwordsMatchValidator(form: AbstractControl) {
    const password = form.get('password')?.value;
    const confirm = form.get('confirmPassword')?.value;

    return password === confirm ? null : { passwordsMismatch: true };
  }

  private dateValidator(control: AbstractControl): ValidationErrors | null {
    if (!control.value) return null;

    const date = new Date(control.value);
    const today = new Date();
    const minDate = new Date();
    minDate.setFullYear(today.getFullYear() - 100);
    const maxDate = new Date();
    maxDate.setFullYear(today.getFullYear() - 10);

    if (isNaN(date.getTime())) {
      return { invalidDate: true };
    }

    if (date > maxDate) {
      return { underage: true };
    }

    if (date < minDate) {
      return { tooOld: true };
    }

    return null;
  }

  isCurrentStepValid(): boolean {

    const get = (control: string) => this.form.get(control)?.valid;
    const getAddress = (control: string) => this.form.get(`address.${control}`)?.valid;

    switch (this.currentStep) {

      case 0:
        return !!(
          get('name') &&
          get('last_name') &&
          get('birthdate') &&
          get('gender') &&
          get('curp') &&
          get('blood_type')
        );

      case 1:
        return !!(
          get('email') &&
          get('phone_number')
        );

      case 2:
        return true;
      case 3:
        return !!(
          get('password') &&
          get('confirmPassword') &&
          !this.form.hasError('passwordsMismatch')
        );

      default:
        return false;
    }
  }


  submit() {
    if(this.form.invalid) return;

    this.loading = true;

    const {
    confirmPassword,
    address,
    ...rest
  } = this.form.getRawValue();

    const cleanedRest = cleanObject(rest);


  const addressArray = [
    address.cp || '',
    address.street || '',
    address.number || '',
    address.neighborhood || '',
    address.state || '',
    address.city || ''
  ];

  const user: RegisterUser = {
    ...cleanedRest,
    ...(addressArray.length > 0 && { address: addressArray }),
    status: 'activo'
  } as RegisterUser;


    this.authService.register(user)
    .subscribe({
      next: (res) => {
        this.loading = false;
        this.modalService.show({ message: res.message ?? 'Usuario creado correctamente', type: 'success', display: 'alert'})
        this.router.navigate(['/auth/login']);
      },
      error: (err) => {
        this.loading = false;
      }
    })

  }

}
