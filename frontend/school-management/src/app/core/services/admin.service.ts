import { HttpClient } from "@angular/common/http";
import { inject, Injectable } from "@angular/core";
import { Observable } from "rxjs";
import { ADMIN_URL } from "../constants/api.constants";
import { DashboardSummary } from "../../features/admin/models/dashboard-summary.model";

@Injectable({ providedIn: 'root' })
export class AdminService {
  private http = inject(HttpClient);

  getSummary(): Observable<{ data: { summary: DashboardSummary } }> {
    return this.http.get<{ data: { summary: DashboardSummary } }>(
      `${ADMIN_URL}/users-summary`
    );
  }
}
