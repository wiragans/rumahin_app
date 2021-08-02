import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { ArtestPage } from './artest.page';

const routes: Routes = [
  {
    path: '',
    component: ArtestPage
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class ArtestPageRoutingModule {}
