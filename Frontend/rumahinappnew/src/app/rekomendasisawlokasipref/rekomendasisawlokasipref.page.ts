import { Component, OnInit, ViewChild, ElementRef } from '@angular/core';
import { ModalController } from '@ionic/angular';
import { Platform } from '@ionic/angular';
import { StatusBar } from '@ionic-native/status-bar/ngx';
import { ActivatedRoute } from '@angular/router';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { mimeData } from '../../environments/environment';
import { baseAuthData } from '../../environments/environment';
import { baseUrlData } from '../../environments/environment';
import { ToastController } from '@ionic/angular';

@Component({
  selector: 'app-rekomendasisawlokasipref',
  templateUrl: './rekomendasisawlokasipref.page.html',
  styleUrls: ['./rekomendasisawlokasipref.page.scss'],
})
export class RekomendasisawlokasiprefPage implements OnInit {

  @ViewChild('containerProvinsiID') containerProvinsiID: ElementRef;

  useLocationPref = "";

  provinsiID = "";
  kabupatenID = "";
  kecamatanID = "";
  desaID = "";

  arrayProvinsiData = [];
  arrayKabupatenData = [];
  arrayKecamatanData = [];
  arrayDesaData = [];

  constructor(private modalController: ModalController, public platform: Platform, private statusBar: StatusBar, private http: HttpClient, public toastController: ToastController)
  {
    //
  }

  async presentToast(toastText: string, durationToast: number) {
    const toast = await this.toastController.create({
      message: toastText,
      duration: durationToast
    });
    toast.present();
  }

  async closeModalPrefLokasi()
  {
    /*const arrayPrefLokasi = [
      {
        id: '',
        nama: ''
      }
    ];*/

    await this.modalController.dismiss(this.provinsiID);
  }

  simpanPreferensiLokasiButton()
  {
    this.simpanPreferensiLokasi()

    this.closeModalPrefLokasi();
  }

  simpanPreferensiLokasi()
  {
    this.http.post(baseUrlData.apiV1 + 'preferences/setCatalogLocation', {provinsiId: this.provinsiID, kabupatenId: this.kabupatenID, kecamatanId: this.kecamatanID, desaId: this.desaID}, {
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
            this.presentToast("Terjadi kesalahan saat menyimpan data preferensi lokasi katalog rumah. Coba lagi ya!", 3000);
          }

          if(res['status'] == true)
          {
            this.presentToast(res['message'], 3000);
          }
        },
        err => {
          console.log("Error occured");
          this.presentToast("Terjadi kesalahan saat menyimpan data preferensi lokasi katalog rumah. Coba lagi ya!", 3000);
        }
      );
  }

  onUseLocaltionPrefCheckbox(event)
  {
    console.log(event);

    this.useLocationPref = event;
  }

  onProvinsiSelectBox(event)
  {
    this.provinsiID = event;
    this.kabupatenID = "";
    this.kecamatanID = "";
    this.desaID = "";

    //console.log(this.containerProvinsiID.nativeElement.innerHTML);

    console.log(event.target);

    this.getKabupaten(this.provinsiID);
  }

  onKabupatenSelectBox(event)
  {
    this.kabupatenID = event;
    this.kecamatanID = "";
    this.desaID = "";

    this.getKecamatan(this.kabupatenID);
  }

  onKecamatanSelectBox(event)
  {
    this.kecamatanID = event;
    this.desaID = "";

    this.getDesa(this.kecamatanID);
  }

  onDesaSelectBox(event)
  {
    this.desaID = event;
  }

  getProvinsi()
  {
    this.http.get(baseUrlData.apiV1 + 'wilayah_api/getProvinsi', {
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
            
          }

          if(res['status'] == true)
          {
            this.arrayProvinsiData = res['data'];

            //this.getCurrentLocation();
          }
        },
        err => {
          console.log("Error occured");
        }
      );
  }

  getKabupaten(provinsiId)
  {
    this.http.get(baseUrlData.apiV1 + 'wilayah_api/getKabKota?provinsi_id=' + provinsiId, {
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
            
          }

          if(res['status'] == true)
          {
            this.arrayKabupatenData = res['data'];

            //this.getCurrentLocation();
          }
        },
        err => {
          console.log("Error occured");
        }
      );
  }

  getKecamatan(kabupatenId)
  {
    this.http.get(baseUrlData.apiV1 + 'wilayah_api/getKecamatan?kabupaten_id=' + kabupatenId, {
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
            
          }

          if(res['status'] == true)
          {
            this.arrayKecamatanData = res['data'];
          }
        },
        err => {
          console.log("Error occured");
        }
      );
  }

  getDesa(kecamatanId)
  {
    this.http.get(baseUrlData.apiV1 + 'wilayah_api/getDesa?kecamatan_id=' + kecamatanId, {
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
            
          }

          if(res['status'] == true)
          {
            this.arrayDesaData = res['data'];
          }
        },
        err => {
          console.log("Error occured");
        }
      );
  }

  getCurrentLocation()
  {
    this.http.get(baseUrlData.apiV1 + 'preferences/getCatalogLocation', {
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

          if(res['status'] ==  false)
          {
            this.provinsiID = "";
            this.kabupatenID = "";
            this.kecamatanID = "";
            this.desaID = "";
          }

          if(res['status'] == true)
          {
            this.provinsiID = res['data']['provinsi']['id'];

            if(this.provinsiID != null && this.provinsiID != undefined)
            {
              //this.getKabupaten(this.provinsiID);
            }

            this.kabupatenID = res['data']['kabupaten']['id'];

            if(this.kabupatenID != null && this.kabupatenID != undefined)
            {
              //this.getKecamatan(this.kabupatenID);
            }

            this.kecamatanID = res['data']['kecamatan']['id'];

            if(this.kecamatanID != null && this.kecamatanID != undefined)
            {
              //this.getDesa(this.kecamatanID);
            }

            this.desaID = res['data']['desa']['id'];
          }
        },
        err => {
          console.log("Error occured");
          this.provinsiID = "";
          this.kabupatenID = "";
          this.kecamatanID = "";
          this.desaID = "";
        }
      );
  }

  ngOnInit()
  {
    this.statusBar.overlaysWebView(false);
    this.statusBar.show();

    //this.getCurrentLocation();

    this.getProvinsi();
    //this.getKabupaten(this.provinsiID);
    //this.getKecamatan(this.kabupatenID);
    //this.getDesa(this.kecamatanID);
  }
}
