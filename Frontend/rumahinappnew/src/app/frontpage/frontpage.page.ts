import { Component, OnInit } from '@angular/core';
import { StatusBar } from '@ionic-native/status-bar/ngx';
import { ActivatedRoute } from '@angular/router';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { mimeData } from '../../environments/environment';
import { baseAuthData } from '../../environments/environment';
import { baseUrlData } from '../../environments/environment';
import { LoadingController } from '@ionic/angular';
import { Router } from '@angular/router';
import { ToastController } from '@ionic/angular';
import { ModalController } from '@ionic/angular';

import { RekomendasisawlokasiprefPage } from '../rekomendasisawlokasipref/rekomendasisawlokasipref.page';
import { RekomendasisawjenisrumahprefPage } from '../rekomendasisawjenisrumahpref/rekomendasisawjenisrumahpref.page';

@Component({
  selector: 'app-frontpage',
  templateUrl: './frontpage.page.html',
  styleUrls: ['./frontpage.page.scss'],
})
export class FrontpagePage implements OnInit {

  namaLengkap = "";

  statusApi = false;
  profileLoaded = false;

  minimalHarga = 0;
  maksimalHarga = 90000000000000;

  locationPref = "";
  jenisRumahPref = "";
  catalogTypeCurrent = "";

  currentDesaID = "";
  currentKecamatanID = "";
  currentKabupatenID = "";
  currentProvinsiID = "";

  buyOrRent = "";
  newOrSecond = "";

  usePreferensi = false;
  triggerAllowButton = true;

  constructor(private router: Router, private activatedRoute: ActivatedRoute, private http: HttpClient, public loadingController: LoadingController, public toastController: ToastController, private statusBar: StatusBar, private modalController: ModalController)
  {

  }

  usePreferensiChange(event)
  {
    console.log(event);

    this.usePreferensi = event;

    if(this.usePreferensi == false)
    {
      this.triggerAllowButton = true;
    }

    if(this.usePreferensi == true)
    {
      if(this.locationPref == "" || this.jenisRumahPref == "")
      {
        this.triggerAllowButton = false;
      }
    }
  }

  async openModalPrefLokasi()
  {
    const modal = await this.modalController.create({
      component: RekomendasisawlokasiprefPage
    });

    modal.onDidDismiss()
      .then((data) => {
        console.log(data);
        this.loadCurrentPreferencesLokasi();
    });

    return await modal.present();
  }

  async openModalPrefJenisRumah()
  {
    const modal = await this.modalController.create({
      component: RekomendasisawjenisrumahprefPage
    });

    modal.onDidDismiss()
      .then((data) => {
        console.log(data);
        this.loadCurrentPreferencesJenisRumah();
    });

    return await modal.present();
  }

  async presentToast(toastText: string, durationToast: number) {
    const toast = await this.toastController.create({
      message: toastText,
      duration: durationToast
    });
    toast.present();
  }

  cariKatalogRumahButton()
  {
    let payloadRequestBody = {
      usePreferences: this.usePreferensi,
      context: [
        {
          service: 'PRICE_PREFERENCES',
          params: [
            {
              key: 'MINIMUM_PRICE',
              value: this.minimalHarga
            },
            {
              key: 'MAXIMUM_PRICE',
              value: this.maksimalHarga
            }
          ]
        },
        {
          service: 'CATALOG_CONDITIONS_PREFERENCES',
          params: [
            {
              key: 'BUY_OR_RENT',
              value: this.buyOrRent
            },
            {
              key: 'NEW_OR_SECOND',
              value: this.newOrSecond
            }
          ]
        },
        {
          service: 'REGION_PREFERENCES',
          params: [
            {
              key: 'provinsi_id',
              value: this.currentProvinsiID
            },
            {
              key: 'kabupaten_id',
              value: this.currentKabupatenID
            },
            {
              key: 'kecamatan_id',
              value: this.currentKecamatanID
            },
            {
              key: 'desa_id',
              value: this.currentDesaID
            }
          ]
        },
        {
          service: 'CATALOG_PREFERENCES',
          params: [
            {
              key: 'CATALOG_TYPE_PREFERENCES',
              value: this.catalogTypeCurrent
            }
          ]
        }
      ]
    };

    console.log(JSON.stringify(payloadRequestBody));
    console.log(window.btoa(JSON.stringify(payloadRequestBody)));

    let encodeDataParams = window.btoa(JSON.stringify(payloadRequestBody));

    this.router.navigate(['/quickfrontpageresult/' + encodeDataParams]);
  }

  loadCurrentPreferencesLokasi()
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

          if(res['status'] == false)
          {
            this.locationPref = "";
            this.currentDesaID = "";
            this.currentKecamatanID = "";
            this.currentKabupatenID = "";
            this.currentProvinsiID = "";
          }

          if(res['status'] == true)
          {
            this.locationPref = "";

            if(res['data']['desa']['nama'] != null && res['data']['desa']['nama'] != undefined)
            {
              this.locationPref = res['data']['desa']['nama'] + ", ";
              this.currentDesaID = res['data']['desa']['id'];
            }

            if(res['data']['kecamatan']['nama'] != null && res['data']['kecamatan']['nama'] != undefined)
            {
              this.locationPref += res['data']['kecamatan']['nama'] + ", ";
              this.currentKecamatanID = res['data']['kecamatan']['id'];
            }

            if(res['data']['kabupaten']['nama'] != null && res['data']['kabupaten']['nama'] != undefined)
            {
              this.locationPref += res['data']['kabupaten']['nama'] + ", ";
              this.currentKabupatenID = res['data']['kabupaten']['id'];
            }

            if(res['data']['provinsi']['nama'] != null && res['data']['provinsi']['nama'] != undefined)
            {
              this.locationPref += res['data']['provinsi']['nama'];
              this.currentProvinsiID = res['data']['provinsi']['id'];
            }
          }
        },
        err => {
          console.log("Error occured");
          this.locationPref = "";
          this.currentDesaID = "";
          this.currentKecamatanID = "";
          this.currentKabupatenID = "";
          this.currentProvinsiID = "";
        }
      );
  }

  loadCurrentPreferencesJenisRumah()
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

          if(res['status'] == false)
          {
            this.jenisRumahPref = "";
            this.catalogTypeCurrent = "";
          }

          if(res['status'] == true)
          {
            this.jenisRumahPref = "";

            if(res['data']['catalogType']['namaPendek'] != null && res['data']['catalogType']['namaPendek'] != undefined)
            {
              this.jenisRumahPref = res['data']['catalogType']['namaPendek'];
              this.catalogTypeCurrent = res['data']['catalogType']['code'];
            }
          }
        },
        err => {
          console.log("Error occured");
          this.jenisRumahPref = "";
          this.catalogTypeCurrent = "";
        }
      );
  }

  loadProfile()
  {
    this.http.get(baseUrlData.apiV1 + 'user/profile', {
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
          this.statusApi = res['status'];

          if(res['status'] == false)
          {
            this.profileLoaded = false;
          }

          if(res['status'] == true)
          {
            this.namaLengkap = res['data']['namaLengkap'];

            this.profileLoaded = true;
          }
        },
        err => {
          console.log("Error occured");
          this.profileLoaded = false;
        }
      );
  }

  checkLoginSession()
  {
    this.http.get(baseUrlData.apiV1 + 'oauth/tokeninfo', {
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
            if(res['code'] == 'ACCOUNT_LOCKED')
            {
              localStorage.setItem("bearerAccessToken" , "");
              localStorage.setItem("refreshToken" , "");
              localStorage.setItem("ssoToken" , "");

              this.router.navigate(['/login']);
            }

            if(res['code'] != 'ACCOUNT_LOCKED')
            {
              this.refreshToken();
            }
          }

          if(res['status'] == true)
          {
            //
            console.log("OK");
          }
        },
        err => {
          console.log("Error occured");

          if(err['status'] == 401)
          {
            this.refreshToken();
          }
        }
      );
  }

  refreshToken()
  {
    this.http.post(baseUrlData.apiV1 + 'oauth/refreshToken', {refreshToken: localStorage.getItem("refreshToken")}, {
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
            localStorage.setItem("bearerAccessToken" , "");
            localStorage.setItem("refreshToken" , "");
            localStorage.setItem("ssoToken" , "");

            this.router.navigate(['/login']);
          }

          if(res['status'] == true)
          {
            localStorage.setItem("bearerAccessToken" , res['data']['access_token']);
            localStorage.setItem("refreshToken" , res['data']['refresh_token']);
            localStorage.setItem("ssoToken" , res['data']['sso_token']);
          }
        },
        err => {
          console.log("Error occured");

          if(err['status'] == 401)
          {
            localStorage.setItem("bearerAccessToken" , "");
            localStorage.setItem("refreshToken" , "");
            localStorage.setItem("ssoToken" , "");

            this.presentToast("Session telah berakhir. Silakan login ulang!", 3000);

            this.router.navigate(['/login']);
          }
        }
      );
  }

  customPopoverOptions: any = {
    header: 'Ingin beli atau sewa rumah?',
    //subHeader: 'Pilih pilihanmu!',
    //message: 'aaaa'
  };

  customPopoverOptions2: any = {
    header: 'Rumah baru / second?',
    //subHeader: 'Pilih pilihanmu!',
    //message: 'aaaa'
  };

  ngOnInit()
  {
    this.statusBar.overlaysWebView(false);
    this.statusBar.show();

    this.checkLoginSession();
    this.loadProfile();
  }

  ionViewWillEnter()
  {
    this.profileLoaded = false;

    this.checkLoginSession();
    this.loadProfile();

    this.loadCurrentPreferencesLokasi();
    this.loadCurrentPreferencesJenisRumah();
  }
}
