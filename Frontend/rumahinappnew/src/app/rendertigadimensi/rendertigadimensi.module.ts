import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { IonicModule } from '@ionic/angular';

import { RendertigadimensiPageRoutingModule } from './rendertigadimensi-routing.module';

import { RendertigadimensiPage } from './rendertigadimensi.page';

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    IonicModule,
    RendertigadimensiPageRoutingModule
  ],
  declarations: [RendertigadimensiPage]
})
export class RendertigadimensiPageModule {}
