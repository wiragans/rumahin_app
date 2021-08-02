import { NgModule } from '@angular/core';
import { PreloadAllModules, RouterModule, Routes } from '@angular/router';

const routes: Routes = [
  {
    path: '',
    loadChildren: () => import('./tabs/tabs.module').then(m => m.TabsPageModule)
    //loadChildren: () => import('./login/login.module').then(m => m.LoginPageModule)
  },
  {
    path: 'login',
    loadChildren: () => import('./login/login.module').then( m => m.LoginPageModule)
  },
  {
    path: 'verify-credentials-page/:grant_type',
    loadChildren: () => import('./verify-credentials-page/verify-credentials-page.module').then( m => m.VerifyCredentialsPagePageModule)
  },
  {
    path: 'artest',
    loadChildren: () => import('./artest/artest.module').then( m => m.ArtestPageModule)
  },
  {
    path: 'forgotpassword/:pos_type',
    loadChildren: () => import('./forgotpassword/forgotpassword.module').then( m => m.ForgotpasswordPageModule)
  },
  {
    path: 'register',
    loadChildren: () => import('./register/register.module').then( m => m.RegisterPageModule)
  },
  {
    path: 'dashboard',
    loadChildren: () => import('./dashboard/dashboard.module').then( m => m.DashboardPageModule)
  },
  {
    path: 'notificationarea',
    loadChildren: () => import('./notificationarea/notificationarea.module').then( m => m.NotificationareaPageModule)
  },
  {
    path: 'messagearea',
    loadChildren: () => import('./messagearea/messagearea.module').then( m => m.MessageareaPageModule)
  },
  {
    path: 'bookmarkarea',
    loadChildren: () => import('./bookmarkarea/bookmarkarea.module').then( m => m.BookmarkareaPageModule)
  },
  {
    path: 'artestnew',
    loadChildren: () => import('./artestnew/artestnew.module').then( m => m.ArtestnewPageModule)
  },
  {
    path: 'katalog-search-query-page',
    loadChildren: () => import('./katalog-search-query-page/katalog-search-query-page.module').then( m => m.KatalogSearchQueryPagePageModule)
  },
  {
    path: 'rekomendasisaw',
    loadChildren: () => import('./rekomendasisaw/rekomendasisaw.module').then( m => m.RekomendasisawPageModule)
  },
  {
    path: 'addkatalogpage',
    loadChildren: () => import('./addkatalogpage/addkatalogpage.module').then( m => m.AddkatalogpagePageModule)
  },
  {
    path: 'katalogmanagementmenu',
    loadChildren: () => import('./katalogmanagementmenu/katalogmanagementmenu.module').then( m => m.KatalogmanagementmenuPageModule)
  },
  {
    path: 'mycatalogpage',
    loadChildren: () => import('./mycatalogpage/mycatalogpage.module').then( m => m.MycatalogpagePageModule)
  },
  {
    path: 'rekomendasisawlokasipref',
    loadChildren: () => import('./rekomendasisawlokasipref/rekomendasisawlokasipref.module').then( m => m.RekomendasisawlokasiprefPageModule)
  },
  {
    path: 'rekomendasisawjenisrumahpref',
    loadChildren: () => import('./rekomendasisawjenisrumahpref/rekomendasisawjenisrumahpref.module').then( m => m.RekomendasisawjenisrumahprefPageModule)
  },
  {
    path: 'katalogdetailview/:katalogUUID',
    loadChildren: () => import('./katalogdetailview/katalogdetailview.module').then( m => m.KatalogdetailviewPageModule)
  },
  {
    path: 'rendertigadimensi',
    loadChildren: () => import('./rendertigadimensi/rendertigadimensi.module').then( m => m.RendertigadimensiPageModule)
  },
  {
    path: 'arviewfix/:data',
    loadChildren: () => import('./arviewfix/arviewfix.module').then( m => m.ArviewfixPageModule)
  },
  {
    path: 'editmykatalogrumah/:katalogUUID',
    loadChildren: () => import('./editmykatalogrumah/editmykatalogrumah.module').then( m => m.EditmykatalogrumahPageModule)
  },
  {
    path: 'frontpage',
    loadChildren: () => import('./frontpage/frontpage.module').then( m => m.FrontpagePageModule)
  },
  {
    path: 'quickfrontpageresult/:data',
    loadChildren: () => import('./quickfrontpageresult/quickfrontpageresult.module').then( m => m.QuickfrontpageresultPageModule)
  },
  {
    path: 'kriteriatambahansaw',
    loadChildren: () => import('./kriteriatambahansaw/kriteriatambahansaw.module').then( m => m.KriteriatambahansawPageModule)
  },
  {
    path: 'kriteriasawfilteringpopup',
    loadChildren: () => import('./kriteriasawfilteringpopup/kriteriasawfilteringpopup.module').then( m => m.KriteriasawfilteringpopupPageModule)
  },
  {
    path: '**', pathMatch: 'full', redirectTo: 'login'
  }
];
@NgModule({
  imports: [
    RouterModule.forRoot(routes, { preloadingStrategy: PreloadAllModules })
  ],
  exports: [RouterModule]
})
export class AppRoutingModule {}
