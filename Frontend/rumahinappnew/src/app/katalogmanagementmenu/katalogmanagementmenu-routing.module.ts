import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { KatalogmanagementmenuPage } from './katalogmanagementmenu.page';

const routes: Routes = [
  {
    path: '',
    component: KatalogmanagementmenuPage
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class KatalogmanagementmenuPageRoutingModule {}
