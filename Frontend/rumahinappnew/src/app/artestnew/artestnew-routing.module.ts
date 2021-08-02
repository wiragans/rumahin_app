import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { ArtestnewPage } from './artestnew.page';

const routes: Routes = [
  {
    path: '',
    component: ArtestnewPage
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class ArtestnewPageRoutingModule {}
