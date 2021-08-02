import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { IonicModule } from '@ionic/angular';

import { VerifyCredentialsPagePageRoutingModule } from './verify-credentials-page-routing.module';

import { VerifyCredentialsPagePage } from './verify-credentials-page.page';

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    IonicModule,
    VerifyCredentialsPagePageRoutingModule
  ],
  declarations: [VerifyCredentialsPagePage]
})
export class VerifyCredentialsPagePageModule {}
