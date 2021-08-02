import { NgModule, CUSTOM_ELEMENTS_SCHEMA } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { IonicModule } from '@ionic/angular';

import { RekomendasisawPageRoutingModule } from './rekomendasisaw-routing.module';

import { RekomendasisawPage } from './rekomendasisaw.page';

import { RekomendasisawlokasiprefPage } from '../rekomendasisawlokasipref/rekomendasisawlokasipref.page';
import { RekomendasisawjenisrumahprefPage } from '../rekomendasisawjenisrumahpref/rekomendasisawjenisrumahpref.page';

import { KriteriasawfilteringpopupPage } from '../kriteriasawfilteringpopup/kriteriasawfilteringpopup.page';

import { NgxDatatableModule } from '@swimlane/ngx-datatable';

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    IonicModule,
    RekomendasisawPageRoutingModule,
    NgxDatatableModule
  ],
  declarations: [RekomendasisawPage, RekomendasisawlokasiprefPage, RekomendasisawjenisrumahprefPage, KriteriasawfilteringpopupPage],
  schemas: [CUSTOM_ELEMENTS_SCHEMA]
})
export class RekomendasisawPageModule {}
