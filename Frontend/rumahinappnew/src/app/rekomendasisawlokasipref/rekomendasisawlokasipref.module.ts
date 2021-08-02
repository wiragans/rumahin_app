import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { IonicModule } from '@ionic/angular';

import { RekomendasisawlokasiprefPageRoutingModule } from './rekomendasisawlokasipref-routing.module';

import { RekomendasisawlokasiprefPage } from './rekomendasisawlokasipref.page';

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    IonicModule,
    RekomendasisawlokasiprefPageRoutingModule
  ],
  declarations: [RekomendasisawlokasiprefPage]
})
export class RekomendasisawlokasiprefPageModule {}
