import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { RekomendasisawPage } from './rekomendasisaw.page';

const routes: Routes = [
  {
    path: '',
    component: RekomendasisawPage
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class RekomendasisawPageRoutingModule {}
