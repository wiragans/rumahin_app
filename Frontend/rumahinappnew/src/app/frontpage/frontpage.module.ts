import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { IonicModule } from '@ionic/angular';

import { FrontpagePageRoutingModule } from './frontpage-routing.module';

import { FrontpagePage } from './frontpage.page';

import { RekomendasisawlokasiprefPage } from '../rekomendasisawlokasipref/rekomendasisawlokasipref.page';
import { RekomendasisawjenisrumahprefPage } from '../rekomendasisawjenisrumahpref/rekomendasisawjenisrumahpref.page';

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    IonicModule,
    FrontpagePageRoutingModule
  ],
  declarations: [FrontpagePage, RekomendasisawlokasiprefPage, RekomendasisawjenisrumahprefPage]
})
export class FrontpagePageModule {}
