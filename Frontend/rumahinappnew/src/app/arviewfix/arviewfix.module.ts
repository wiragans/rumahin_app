import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { IonicModule } from '@ionic/angular';

import { ArviewfixPageRoutingModule } from './arviewfix-routing.module';

import { ArviewfixPage } from './arviewfix.page';

import { CUSTOM_ELEMENTS_SCHEMA } from '@angular/core';
import { WebcamModule } from 'ngx-webcam';

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    IonicModule,
    ArviewfixPageRoutingModule,
    WebcamModule
  ],
  declarations: [ArviewfixPage],
  schemas: [
    CUSTOM_ELEMENTS_SCHEMA
  ]
})
export class ArviewfixPageModule {}
