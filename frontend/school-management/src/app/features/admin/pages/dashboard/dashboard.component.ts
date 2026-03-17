import { DemographicItem } from './../../../../shared/components/demographics-grid/demographics-grid.component';
import { ChartConfiguration, ChartData } from 'chart.js';
import { Component, inject, OnInit } from '@angular/core';
import { AdminService } from '../../../../core/services/admin.service';
import { DashboardSummary } from '../../models/dashboard-summary.model';
import { CommonModule } from '@angular/common';
import { SpinnerComponent } from '../../../../shared/components/spinner/spinner.component';
import { ButtonComponent } from '../../../../shared/components/button/button.component';
import { getPercentage } from '../../../../core/helpers';
import { KpiCardComponent } from '../../../../shared/components/kpi-card/kpi-card.component';
import { ChartCardComponent } from '../../../../shared/components/chart-card/chart-card.component';
import { DashboardHeaderComponent } from '../../../../shared/components/dashboard-header/dashboard-header.component';
import { InfoCardComponent } from '../../../../shared/components/info-card/info-card.component';
import { DemographicsGridComponent } from '../../../../shared/components/demographics-grid/demographics-grid.component';
import { ProgressItem, ProgressListComponent } from '../../../../shared/components/progress-list/progress-list.component';
import { AlertItem, AlertsListComponent } from '../../../../shared/components/alerts-list/alerts-list.component';

@Component({
  selector: 'app-dashboard',
  imports: [CommonModule, SpinnerComponent, ButtonComponent,
     KpiCardComponent, ChartCardComponent, DashboardHeaderComponent,
      InfoCardComponent, DemographicsGridComponent, ProgressListComponent, AlertsListComponent ],
  templateUrl: './dashboard.component.html',
  styleUrl: './dashboard.component.scss'
})
export class DashboardComponent implements OnInit {
  private dashboardService = inject(AdminService);
  summary?: DashboardSummary;
  isLoading = true;
  error = false;
  fechaActual = new Date();
  private styles = getComputedStyle(document.documentElement);
  private primary = this.styles.getPropertyValue('--card-accent-primary');
  private success = this.styles.getPropertyValue('--card-accent-success');
  private warning = this.styles.getPropertyValue('--card-accent-warning');
  private danger = this.styles.getPropertyValue('--card-accent-danger');

  private borderColor = this.styles.getPropertyValue('--card-border-light');
  private textColor = this.styles.getPropertyValue('--card-text-secondary');

  userDistributionChartData: ChartData<'doughnut'> = {
    labels: [],
    datasets: [
    {
      data: [],
      backgroundColor: [
        this.primary,
        this.success,
        this.warning,
        this.danger
      ],
      borderColor: this.styles.getPropertyValue('--card-bg-primary'),
      borderWidth: 2,
      hoverOffset: 6
    }
  ]
  };

  userDistributionChartOptions: ChartConfiguration['options'] = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: { backgroundColor: this.textColor },

    },

  };

  activityChartData: ChartData<'line'> = {
    labels: [],
    datasets: [
    {
      label: 'Activity',
      data: [],
      borderColor: this.primary,
      backgroundColor: this.primary + '33',
      fill: true,
      pointBackgroundColor: this.primary,
      pointBorderColor: '#fff',
      pointHoverRadius: 6
    }
  ]
  };

  activityChartOptions: ChartConfiguration['options'] = {
  responsive: true,
  maintainAspectRatio: false,

  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: this.styles.getPropertyValue('--card-bg-secondary'),
      titleColor: this.textColor,
      bodyColor: this.textColor
    }
  },

  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        color: this.textColor
      },
      grid: {
        color: this.borderColor
      }
    },

    x: {
      ticks: {
        color: this.textColor
      },
      grid: {
        display: false
      }
    }
  },

  elements: {
    line: {
      tension: 0.4,
      borderWidth: 3
    },

    point: {
      radius: 4
    }
  }
};

  roleChartData: ChartData<'doughnut'> = {
   labels: [],
   datasets: [
    {
      data: [],
      backgroundColor: [
        this.primary,
        this.success,
        this.warning,
        this.danger
      ],
      borderColor: this.styles.getPropertyValue('--card-bg-primary'),
      borderWidth: 2,
      hoverOffset: 6
    }
  ]
  };

academicChartData: ChartData<'doughnut'> = {
  labels: [],
  datasets: [
    {
      data: [],
      backgroundColor: [
        this.success,
        this.warning,
        this.danger
      ],
      borderColor: this.styles.getPropertyValue('--card-bg-primary'),
      borderWidth: 2
    }
  ]
};

  get hasAlerts(): boolean {
    if (!this.summary) return false;
    return (
      this.summary.systemAlerts.users_without_role > 0 ||
      this.summary.systemAlerts.students_without_n_control > 0 ||
      this.summary.systemAlerts.students_without_student_details > 0
    );
  }

  get totalInactiveUsers(): number {
    if (!this.summary) return 0;
    return this.summary.populationSummary.inactive_users +
           this.summary.populationSummary.temporal_inactive_users;
  }

  get totalDeleteUsers(): number {
    if(!this.summary) return 0;
    return this.summary.populationSummary.deleted_users;
  }

  getTotalAlerts(): number {
  if (!this.summary) return 0;
  return (
    this.summary.systemAlerts.users_without_role +
    this.summary.systemAlerts.students_without_n_control +
    this.summary.systemAlerts.students_without_student_details
  );
}

  ngOnInit() {
    this.styles = getComputedStyle(document.documentElement);
    this.loadSummary();
  }

  loadSummary() {
    this.dashboardService.getSummary().subscribe({
      next: (response) => {
        this.summary = response.data.summary;
        this.initCharts();
        this.isLoading = false;
      },
      error: () => {
        this.error = true;
        this.isLoading = false;
      }
    });
  }

   private initCharts() {
    if (!this.summary) return;

    this.userDistributionChartData = {
      labels: ['Activos', 'Inactivos', 'Temp. Inactivos', 'Eliminados'],
      datasets: [{
        data: [
          this.summary.populationSummary.active_users,
          this.summary.populationSummary.inactive_users,
          this.summary.populationSummary.temporal_inactive_users,
          this.summary.populationSummary.deleted_users
        ],
        backgroundColor: [
          'var(--card-accent-success)',
          'var(--card-accent-warning)',
          'var(--card-accent-warning)',
          'var(--card-accent-danger)'
        ],
        borderWidth: 0,
        hoverOffset: 4
      }]
    };

    this.roleChartData = {
      labels: ['Admin', 'Aspirante', 'Personal financiero', 'Padres', 'Estudiantes', 'Supervisor', 'No verificado'],
      datasets: [{
        data: [
          this.summary.usersByRoleSummary.admin,
          this.summary.usersByRoleSummary.applicant,
          this.summary.usersByRoleSummary['financial-staff'],
          this.summary.usersByRoleSummary.parent,
          this.summary.usersByRoleSummary.student,
          this.summary.usersByRoleSummary.supervisor,
          this.summary.usersByRoleSummary.unverified
        ],
        backgroundColor: [
          'var(--card-accent-primary)',
        'var(--card-accent-warning)',
        'var(--card-accent-warning)',
        'var(--card-accent-success)',
        'var(--card-accent-success)',
        'var(--card-accent-primary)',
        'var(--card-accent-danger)'
        ],
        borderWidth: 0
      }]
    };

    this.activityChartData = {
      labels: ['Hoy', 'Esta semana', 'Este mes'],
      datasets: [{
        label: 'Nuevos usuarios',
        data: [
          this.summary.recentActivity.new_users_today,
          this.summary.recentActivity.new_users_this_week,
          this.summary.recentActivity.new_users_this_month
        ],
        borderColor: 'var(--card-accent-primary)',
        backgroundColor: 'var(--card-accent-primary)',
        tension: 0.4,
        fill: false
      }]
    };

    this.academicChartData = {
      labels: ['Con carrera', 'Sin carrera', 'Sin semestre', 'Sin grupo'],
      datasets: [{
        data: [
          this.summary.academicSummary.students_with_career,
          this.summary.academicSummary.students_without_career,
          this.summary.academicSummary.students_without_semester,
          this.summary.academicSummary.students_without_group
        ],
        backgroundColor: [
          'var(--card-accent-success)',
          'var(--card-accent-warning)',
          'var(--card-accent-warning)',
          'var(--card-accent-danger)'
        ],
        borderWidth: 0
      }]
    };
  }

  getPercentage(value: number, total: number): number {
    return getPercentage(value, total);
  }


get academicProgressItems(): ProgressItem[] {
  if (!this.summary) return [];
  return [
    {
      label: 'Con carrera',
      value: this.summary.academicSummary.students_with_career,
      percentage: this.getPercentage(this.summary.academicSummary.students_with_career, this.summary.academicSummary.students_total),
      type: 'success'
    },
    {
      label: 'Sin carrera',
      value: this.summary.academicSummary.students_without_career,
      percentage: this.getPercentage(this.summary.academicSummary.students_without_career, this.summary.academicSummary.students_total),
      type: 'warning'
    },
    {
      label: 'Sin semestre',
      value: this.summary.academicSummary.students_without_semester,
      percentage: this.getPercentage(this.summary.academicSummary.students_without_semester, this.summary.academicSummary.students_total),
      type: 'warning'
    }
  ];
}

get alertItems(): AlertItem[] {
  if (!this.summary) return [];
  const alerts = [];

  if (this.summary.usersByRoleSummary.unverified > 0) {
    alerts.push({
      icon: 'verified',
      title: 'Usuarios sin verificar',
      count: this.summary.usersByRoleSummary.unverified,
    });
  }

  if (this.summary.systemAlerts.users_without_role > 0) {
    alerts.push({
      icon: 'error',
      title: 'Usuarios sin rol',
      count: this.summary.systemAlerts.users_without_role,

    });
  }

  if (this.summary.systemAlerts.students_without_n_control > 0) {
    alerts.push({
      icon: 'badge',
      title: 'Sin número de control',
      count: this.summary.systemAlerts.students_without_n_control,
    });
  }

  if (this.summary.systemAlerts.students_without_student_details > 0) {
    alerts.push({
      icon: 'assignment_late',
      title: 'Datos incompletos',
      count: this.summary.systemAlerts.students_without_student_details,
    });
  }

  return alerts;
}

get demographicItems(): DemographicItem[] {
  if (!this.summary) return [];
  return [
    {
      label: 'Inactivos temporales',
      value: this.summary.populationSummary.temporal_inactive_users,
      type: 'warning'
    },
    {
      label: 'Cuentas eliminadas',
      value: this.summary.populationSummary.deleted_users,
      type: 'danger'
    },
    {
      label: 'Tasa de actividad',
      value: this.getPercentage(this.summary.populationSummary.active_users, this.summary.populationSummary.total_users),
      type: 'success',
      suffix: '%'
    },
    {
      label: 'Retención',
      value: this.getPercentage(
        this.summary.populationSummary.active_users + this.summary.populationSummary.inactive_users,
        this.summary.populationSummary.total_users
      ),
      type: 'primary',
      suffix: '%'
    }
  ];
 }


}
