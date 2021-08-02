import { NgModule, CUSTOM_ELEMENTS_SCHEMA } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { IonicModule } from '@ionic/angular';

import { NotificationareaPageRoutingModule } from './notificationarea-routing.module';

import { NotificationareaPage } from './notificationarea.page';

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    IonicModule,
    NotificationareaPageRoutingModule
  ],
  declarations: [NotificationareaPage],
  schemas: [ CUSTOM_ELEMENTS_SCHEMA ]
})
export class NotificationareaPageModule {}
