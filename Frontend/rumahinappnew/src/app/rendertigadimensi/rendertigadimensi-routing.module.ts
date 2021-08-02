import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { RendertigadimensiPage } from './rendertigadimensi.page';

const routes: Routes = [
  {
    path: '',
    component: RendertigadimensiPage
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class RendertigadimensiPageRoutingModule {}
