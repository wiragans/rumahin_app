import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { IonicModule } from '@ionic/angular';

import { ArtestPageRoutingModule } from './artest-routing.module';

import { ArtestPage } from './artest.page';
import { WebcamModule } from 'ngx-webcam';

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    IonicModule,
    ArtestPageRoutingModule,
    WebcamModule
  ],
  declarations: [ArtestPage]
})
export class ArtestPageModule {}
