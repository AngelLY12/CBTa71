import { HttpClient } from "@angular/common/http";
import { inject, Injectable, TemplateRef } from "@angular/core";
import { ApiSuccessResponse } from "../models/api-success-response.model";
import { PROFILE_URL } from "../constants/api.constants";
import { UserProfileResponse } from "../models/responses/profile-response.model";
import { BehaviorSubject } from "rxjs";

@Injectable({ providedIn: 'root'})
export class ProfileService {
  private http = inject(HttpClient);

  profile()
  {
    return this.http.get<ApiSuccessResponse<UserProfileResponse>>(`${PROFILE_URL}/user`);
  }

  private actionsTemplateSubject = new BehaviorSubject<TemplateRef<any> | null>(null);
  private bottomTemplateSubject = new BehaviorSubject<TemplateRef<any> | null>(null);

  actionsTemplate$ = this.actionsTemplateSubject.asObservable();
  bottomTemplate$ = this.bottomTemplateSubject.asObservable();

  setActionsTemplate(template: TemplateRef<any> | null) {
    this.actionsTemplateSubject.next(template);
  }

  setBottomTemplate(template: TemplateRef<any> | null) {
    this.bottomTemplateSubject.next(template);
  }



}
