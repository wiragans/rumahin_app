import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { FrontpagePage } from './frontpage.page';

const routes: Routes = [
  {
    path: '',
    component: FrontpagePage
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class FrontpagePageRoutingModule {}
