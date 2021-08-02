import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { VerifyCredentialsPagePage } from './verify-credentials-page.page';

const routes: Routes = [
  {
    path: '',
    component: VerifyCredentialsPagePage
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class VerifyCredentialsPagePageRoutingModule {}
