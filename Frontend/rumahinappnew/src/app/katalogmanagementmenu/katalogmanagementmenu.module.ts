import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { IonicModule } from '@ionic/angular';

import { KatalogmanagementmenuPageRoutingModule } from './katalogmanagementmenu-routing.module';

import { KatalogmanagementmenuPage } from './katalogmanagementmenu.page';

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    IonicModule,
    KatalogmanagementmenuPageRoutingModule
  ],
  declarations: [KatalogmanagementmenuPage]
})
export class KatalogmanagementmenuPageModule {}
