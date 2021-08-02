import { Component, OnInit } from '@angular/core';
import { AlertController } from '@ionic/angular';

@Component({
  selector: 'app-katalogmanagementmenu',
  templateUrl: './katalogmanagementmenu.page.html',
  styleUrls: ['./katalogmanagementmenu.page.scss'],
})
export class KatalogmanagementmenuPage implements OnInit {

  constructor(public alertController: AlertController)
  {

  }

  async presentAlert() {
    const alert = await this.alertController.create({
      cssClass: 'alertClass',
      header: 'Selamat Datang di Katalog Center',
      //subHeader: 'Subtitle',
      message: 'Selamat datang di Katalog Center Area. Di sini kamu dapat menambahkan, melihat, mengedit, dan menghapus daftar katalog rumah (properti) milik kamu. Dongkrak promosi kamu dan dapatkan lebih banyak audience bersama RumahinApp. Yuk cobain :)',
      buttons: ['Mengerti']
    });

    await alert.present();

    const { role } = await alert.onDidDismiss();
    console.log('onDidDismiss resolved with role', role);
    localStorage.setItem('katalogCenterConsent', 'ok');
  }

  ngOnInit()
  {
    if(localStorage.getItem('katalogCenterConsent') != 'ok')
    {
      this.presentAlert();
    }
  }
}
