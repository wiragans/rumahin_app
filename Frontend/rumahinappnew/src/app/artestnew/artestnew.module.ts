import { NgModule } from '@angular/core';
import { CUSTOM_ELEMENTS_SCHEMA } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { IonicModule } from '@ionic/angular';

import { ArtestnewPageRoutingModule } from './artestnew-routing.module';

import { ArtestnewPage } from './artestnew.page';
import { WebcamModule } from 'ngx-webcam';

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    IonicModule,
    ArtestnewPageRoutingModule,
    WebcamModule
  ],
  declarations: [ArtestnewPage],
  schemas: [
    CUSTOM_ELEMENTS_SCHEMA
  ]
})
export class ArtestnewPageModule {}
