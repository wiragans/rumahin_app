import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';

import { IonicModule } from '@ionic/angular';

import { EditmykatalogrumahPageRoutingModule } from './editmykatalogrumah-routing.module';

import { EditmykatalogrumahPage } from './editmykatalogrumah.page';

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    IonicModule,
    EditmykatalogrumahPageRoutingModule,
    ReactiveFormsModule.withConfig({warnOnNgModelWithFormControl: 'never'})
  ],
  declarations: [EditmykatalogrumahPage],
  exports: [
    CommonModule,
    FormsModule,
    ReactiveFormsModule
  ]
})
export class EditmykatalogrumahPageModule {}
