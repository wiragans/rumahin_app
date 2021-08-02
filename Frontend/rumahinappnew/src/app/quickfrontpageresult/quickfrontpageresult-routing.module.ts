import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { QuickfrontpageresultPage } from './quickfrontpageresult.page';

const routes: Routes = [
  {
    path: '',
    component: QuickfrontpageresultPage
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class QuickfrontpageresultPageRoutingModule {}
