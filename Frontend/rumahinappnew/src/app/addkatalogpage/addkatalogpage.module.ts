import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';

import { IonicModule } from '@ionic/angular';

import { AddkatalogpagePageRoutingModule } from './addkatalogpage-routing.module';

import { AddkatalogpagePage } from './addkatalogpage.page';

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    IonicModule,
    //ReactiveFormsModule,
    AddkatalogpagePageRoutingModule,
    ReactiveFormsModule.withConfig({warnOnNgModelWithFormControl: 'never'})
  ],
  declarations: [AddkatalogpagePage],
  exports: [
    CommonModule,
    FormsModule,
    ReactiveFormsModule
  ]
})
export class AddkatalogpagePageModule {}