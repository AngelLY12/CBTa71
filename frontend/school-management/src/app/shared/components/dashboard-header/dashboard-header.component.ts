import { CommonModule } from '@angular/common';
import { Component, ContentChild, Input, TemplateRef } from '@angular/core';

@Component({
  selector: 'app-dashboard-header',
  imports: [CommonModule],
  templateUrl: './dashboard-header.component.html',
  styleUrl: './dashboard-header.component.scss'
})
export class DashboardHeaderComponent {
  @Input() title!: string;
  @Input() icon: string = '';
  @Input() iconSize: 'sm' | 'md' | 'lg' | 'xl' = 'lg';
  @Input() welcomeMessage!: string;
  @Input() date: Date = new Date();
  @Input() showAlertBadge = false;
  @Input() alertCount = 0;

  @ContentChild(TemplateRef) headerBottomContent?: TemplateRef<any>;

  get hasHeaderBottomContent(): boolean {
    return !!this.headerBottomContent;
  }
}
