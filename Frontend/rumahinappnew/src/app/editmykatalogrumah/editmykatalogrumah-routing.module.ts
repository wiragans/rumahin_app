import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { EditmykatalogrumahPage } from './editmykatalogrumah.page';

const routes: Routes = [
  {
    path: '',
    component: EditmykatalogrumahPage
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class EditmykatalogrumahPageRoutingModule {}
