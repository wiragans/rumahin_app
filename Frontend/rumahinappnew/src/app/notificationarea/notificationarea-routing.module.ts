import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { NotificationareaPage } from './notificationarea.page';

const routes: Routes = [
  {
    path: '',
    component: NotificationareaPage
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class NotificationareaPageRoutingModule {}
