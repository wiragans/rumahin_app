import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { KatalogSearchQueryPagePage } from './katalog-search-query-page.page';

const routes: Routes = [
  {
    path: '',
    component: KatalogSearchQueryPagePage
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class KatalogSearchQueryPagePageRoutingModule {}
