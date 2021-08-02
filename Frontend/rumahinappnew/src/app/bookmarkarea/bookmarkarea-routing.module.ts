import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { BookmarkareaPage } from './bookmarkarea.page';

const routes: Routes = [
  {
    path: '',
    component: BookmarkareaPage
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class BookmarkareaPageRoutingModule {}
