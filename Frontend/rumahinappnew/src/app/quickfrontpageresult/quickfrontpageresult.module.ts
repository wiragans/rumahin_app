import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { IonicModule } from '@ionic/angular';

import { QuickfrontpageresultPageRoutingModule } from './quickfrontpageresult-routing.module';

import { QuickfrontpageresultPage } from './quickfrontpageresult.page';

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    IonicModule,
    QuickfrontpageresultPageRoutingModule
  ],
  declarations: [QuickfrontpageresultPage]
})
export class QuickfrontpageresultPageModule {}
