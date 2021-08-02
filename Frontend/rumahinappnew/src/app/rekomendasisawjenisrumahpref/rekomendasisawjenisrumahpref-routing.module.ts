import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { RekomendasisawjenisrumahprefPage } from './rekomendasisawjenisrumahpref.page';

const routes: Routes = [
  {
    path: '',
    component: RekomendasisawjenisrumahprefPage
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class RekomendasisawjenisrumahprefPageRoutingModule {}
