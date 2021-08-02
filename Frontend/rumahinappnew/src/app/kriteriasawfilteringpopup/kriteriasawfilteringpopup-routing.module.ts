import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { KriteriasawfilteringpopupPage } from './kriteriasawfilteringpopup.page';

const routes: Routes = [
  {
    path: '',
    component: KriteriasawfilteringpopupPage
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class KriteriasawfilteringpopupPageRoutingModule {}
