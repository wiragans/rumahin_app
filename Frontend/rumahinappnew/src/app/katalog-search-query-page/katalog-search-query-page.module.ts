import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { IonicModule } from '@ionic/angular';

import { KatalogSearchQueryPagePageRoutingModule } from './katalog-search-query-page-routing.module';

import { KatalogSearchQueryPagePage } from './katalog-search-query-page.page';

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    IonicModule,
    KatalogSearchQueryPagePageRoutingModule
  ],
  declarations: [KatalogSearchQueryPagePage]
})
export class KatalogSearchQueryPagePageModule {}
