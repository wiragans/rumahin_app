import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { TabsPage } from './tabs.page';

const routes: Routes = [
  {
    path: 'dashboardPage',
    component: TabsPage,
    children: [
      /*{
        path: 'home',
        loadChildren: () => import('../tab1/tab1.module').then(m => m.Tab1PageModule)
      },*/
      {
        path: 'home',
        loadChildren: () => import('../frontpage/frontpage.module').then(m => m.FrontpagePageModule)
      },
      /*{
        path: 'arviewfix',
        loadChildren: () => import('../artestnew/artestnew.module').then( m => m.ArtestnewPageModule)
      },*/
      {
        path: 'arviewfix',
        loadChildren: () => import('../arviewfix/arviewfix.module').then(m => m.ArviewfixPageModule)
      },
      {
        path: 'bookmark',
        loadChildren: () => import('../tab2/tab2.module').then(m => m.Tab2PageModule)
      },
      {
        path: 'profile',
        loadChildren: () => import('../tab3/tab3.module').then(m => m.Tab3PageModule)
      },
      {
        path: '',
        redirectTo: '/dashboardPage/home',
        pathMatch: 'full'
      }
    ]
  },
  {
    path: '',
    redirectTo: '/dashboardPage/home',
    pathMatch: 'full'
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
})
export class TabsPageRoutingModule {}
