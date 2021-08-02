import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { IonicModule } from '@ionic/angular';

import { MessageareaPageRoutingModule } from './messagearea-routing.module';

import { MessageareaPage } from './messagearea.page';

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    IonicModule,
    MessageareaPageRoutingModule
  ],
  declarations: [MessageareaPage]
})
export class MessageareaPageModule {}
