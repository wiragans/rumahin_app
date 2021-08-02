import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { IonicModule } from '@ionic/angular';

import { KriteriasawfilteringpopupPageRoutingModule } from './kriteriasawfilteringpopup-routing.module';

import { KriteriasawfilteringpopupPage } from './kriteriasawfilteringpopup.page';

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    IonicModule,
    KriteriasawfilteringpopupPageRoutingModule
  ],
  declarations: [KriteriasawfilteringpopupPage]
})
export class KriteriasawfilteringpopupPageModule {}
