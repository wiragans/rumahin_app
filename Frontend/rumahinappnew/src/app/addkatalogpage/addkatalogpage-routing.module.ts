import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { AddkatalogpagePage } from './addkatalogpage.page';

const routes: Routes = [
  {
    path: '',
    component: AddkatalogpagePage
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class AddkatalogpagePageRoutingModule {}
