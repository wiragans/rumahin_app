import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { IonicModule } from '@ionic/angular';

import { RekomendasisawjenisrumahprefPageRoutingModule } from './rekomendasisawjenisrumahpref-routing.module';

import { RekomendasisawjenisrumahprefPage } from './rekomendasisawjenisrumahpref.page';

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    IonicModule,
    RekomendasisawjenisrumahprefPageRoutingModule
  ],
  declarations: [RekomendasisawjenisrumahprefPage]
})
export class RekomendasisawjenisrumahprefPageModule {}
