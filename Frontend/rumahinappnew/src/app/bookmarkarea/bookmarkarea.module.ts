import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { IonicModule } from '@ionic/angular';

import { BookmarkareaPageRoutingModule } from './bookmarkarea-routing.module';

import { BookmarkareaPage } from './bookmarkarea.page';

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    IonicModule,
    BookmarkareaPageRoutingModule
  ],
  declarations: [BookmarkareaPage]
})
export class BookmarkareaPageModule {}
