import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { ArviewfixPage } from './arviewfix.page';

const routes: Routes = [
  {
    path: '',
    component: ArviewfixPage
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class ArviewfixPageRoutingModule {}
