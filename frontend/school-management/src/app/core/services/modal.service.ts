import { Injectable } from "@angular/core";
import { BehaviorSubject } from "rxjs";
import { DisplayType } from "../models/types/display-type.type";
import { ModalType } from "../models/types/modal-error.type";

@Injectable({ providedIn: 'root' })
export class ModalService {
  public modalData = new BehaviorSubject<{message?: string, errors?: string[], type?: ModalType, display?: DisplayType} | null>(null);

  show(data: {message?: string, errors?: string[], type?: ModalType, display?: DisplayType}) {
    this.modalData.next({ ...data, display: data.display ?? 'modal' });
  }

  close() {
    this.modalData.next(null);
  }
}
