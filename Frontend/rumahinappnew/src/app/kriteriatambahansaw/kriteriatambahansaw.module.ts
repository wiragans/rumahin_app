import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { IonicModule } from '@ionic/angular';

import { KriteriatambahansawPageRoutingModule } from './kriteriatambahansaw-routing.module';

import { KriteriatambahansawPage } from './kriteriatambahansaw.page';

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    IonicModule,
    KriteriatambahansawPageRoutingModule
  ],
  declarations: [KriteriatambahansawPage]
})
export class KriteriatambahansawPageModule {}
