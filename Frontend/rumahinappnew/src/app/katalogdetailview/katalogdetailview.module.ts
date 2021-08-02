import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { IonicModule } from '@ionic/angular';

import { KatalogdetailviewPageRoutingModule } from './katalogdetailview-routing.module';

import { KatalogdetailviewPage } from './katalogdetailview.page';

import { IvyCarouselModule } from 'angular-responsive-carousel';
import { NgImageFullscreenViewModule } from 'ng-image-fullscreen-view';
//import { YouTubePlayerModule } from "@angular/youtube-player";

import { RendertigadimensiPage } from '../rendertigadimensi/rendertigadimensi.page';

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    IonicModule,
    KatalogdetailviewPageRoutingModule,
    IvyCarouselModule,
    NgImageFullscreenViewModule
    //YouTubePlayerModule
  ],
  declarations: [KatalogdetailviewPage, RendertigadimensiPage]
})
export class KatalogdetailviewPageModule {}
