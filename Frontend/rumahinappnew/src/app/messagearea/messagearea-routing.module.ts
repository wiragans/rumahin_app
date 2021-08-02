import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { MessageareaPage } from './messagearea.page';

const routes: Routes = [
  {
    path: '',
    component: MessageareaPage
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class MessageareaPageRoutingModule {}
