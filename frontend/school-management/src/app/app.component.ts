import { Component } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import { ModalComponent } from './shared/components/modal/modal.component';
import { AlertComponent } from './shared/components/alert/alert.component';
import { CommonModule } from '@angular/common';
import { ThemeButtonComponent } from './shared/components/theme-button/theme-button.component';

@Component({
  selector: 'app-root',
  imports: [RouterOutlet, CommonModule ,ModalComponent, AlertComponent],
  templateUrl: './app.component.html',
  styleUrl: './app.component.scss'
})
export class AppComponent {
  title = 'school-management';
}
