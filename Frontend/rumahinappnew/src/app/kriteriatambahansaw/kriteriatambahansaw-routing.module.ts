import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { KriteriatambahansawPage } from './kriteriatambahansaw.page';

const routes: Routes = [
  {
    path: '',
    component: KriteriatambahansawPage
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class KriteriatambahansawPageRoutingModule {}
