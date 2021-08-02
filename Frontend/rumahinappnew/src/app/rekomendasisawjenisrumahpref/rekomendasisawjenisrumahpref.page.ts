import { Component, OnInit } from '@angular/core';
import { ModalController } from '@ionic/angular';
import { Platform } from '@ionic/angular';
import { StatusBar } from '@ionic-native/status-bar/ngx';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { mimeData } from '../../environments/environment';
import { baseAuthData } from '../../environments/environment';
import { baseUrlData } from '../../environments/environment';
import { ToastController } from '@ionic/angular';

@Component({
  selector: 'app-rekomendasisawjenisrumahpref',
  templateUrl: './rekomendasisawjenisrumahpref.page.html',
  styleUrls: ['./rekomendasisawjenisrumahpref.page.scss'],
})
export class RekomendasisawjenisrumahprefPage implements OnInit {

  arrayTipeKatalogRumah = [];
  catalogType = "";

  constructor(private modalController: ModalController, public platform: Platform, private statusBar: StatusBar, private http: HttpClient, public toastController: ToastController) { }

  async closeModalPrefJenisRumah()
  {
    await this.modalController.dismiss();
  }

  savePreferenceButton()
  {
    this.savePreferensiJenisRumah(this.catalogType);

    this.closeModalPrefJenisRumah();
  }

  async presentToast(toastText: string, durationToast: number) {
    const toast = await this.toastController.create({
      message: toastText,
      duration: durationToast
    });
    toast.present();
  }

  savePreferensiJenisRumah(catalogTypenya)
  {
    this.http.post(baseUrlData.apiV1 + 'preferences/setCatalogType', {catalogType: catalogTypenya}, {
      headers: new HttpHeaders({
        'Content-Type': mimeData.json,
        'X-API-Key': baseAuthData.xApiKey,
        'Authorization': 'Bearer ' + localStorage.getItem("bearerAccessToken"),
        'X-App-ID': baseAuthData.appReqId,
        'X-App-Version': baseAuthData.appVersion,
        'X-Platform': baseAuthData.appPlatform
      })
    })
      .subscribe(
        res => {
          console.log(res);

          if(res['status'] == false)
          {
            this.presentToast("Terjadi kesalahan saat menyimpan preferensi data tipe katalog rumah. Coba lagi ya!", 3000);
          }

          if(res['status'] == true)
          {
            this.presentToast("Berhasil menyimpan preferensi data tipe katalog rumah!", 3000);
          }
        },
        err => {
          console.log("Error occured");
          this.presentToast("Terjadi kesalahan saat menyimpan preferensi data tipe katalog rumah. Coba lagi ya!", 3000);
        }
      );
  }

  loadCurrentPrefTipeRumah()
  {
    this.http.get(baseUrlData.apiV1 + 'preferences/getCatalogType', {
      headers: new HttpHeaders({
        'Content-Type': mimeData.urlEncoded,
        'X-API-Key': baseAuthData.xApiKey,
        'Authorization': 'Bearer ' + localStorage.getItem("bearerAccessToken"),
        'X-App-ID': baseAuthData.appReqId,
        'X-App-Version': baseAuthData.appVersion,
        'X-Platform': baseAuthData.appPlatform
      })
    })
      .subscribe(
        res => {
          console.log(res);

          if(res['status'] == true)
          {
            this.catalogType = res['data']['catalogType']['code'];
          }
        },
        err => {
          console.log("Error occured");
        }
      );

      console.log(this.catalogType);
  }

  loadJenisRumah()
  {
    this.http.get(baseUrlData.apiV1 + 'rumah/getCategory', {
      headers: new HttpHeaders({
        'Content-Type': mimeData.urlEncoded,
        'X-API-Key': baseAuthData.xApiKey,
        'Authorization': 'Bearer ' + localStorage.getItem("bearerAccessToken"),
        'X-App-ID': baseAuthData.appReqId,
        'X-App-Version': baseAuthData.appVersion,
        'X-Platform': baseAuthData.appPlatform
      })
    })
      .subscribe(
        res => {
          console.log(res);

          if(res['status'] == false)
          {
            this.arrayTipeKatalogRumah = [];
            this.presentToast("Terjadi kesalahan saat memuat data tipe katalog rumah. Coba lagi ya!", 3000);
          }

          if(res['status'] == true)
          {
            this.arrayTipeKatalogRumah = res['data'];
            this.loadCurrentPrefTipeRumah();
          }
        },
        err => {
          console.log("Error occured");
          this.arrayTipeKatalogRumah = [];
          this.presentToast("Terjadi kesalahan saat memuat data tipe katalog rumah. Coba lagi ya!", 3000);
        }
      );
  }

  ngOnInit()
  {
    this.statusBar.overlaysWebView(false);
    this.statusBar.show();

    //this.catalogType = "";

    this.loadJenisRumah();
  }
}
