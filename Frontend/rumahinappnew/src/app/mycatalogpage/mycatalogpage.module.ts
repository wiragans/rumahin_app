import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { IonicModule } from '@ionic/angular';

import { MycatalogpagePageRoutingModule } from './mycatalogpage-routing.module';

import { MycatalogpagePage } from './mycatalogpage.page';

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    IonicModule,
    MycatalogpagePageRoutingModule
  ],
  declarations: [MycatalogpagePage]
})
export class MycatalogpagePageModule {}
