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
import { AlertController } from '@ionic/angular';

@Component({
  selector: 'app-mycatalogpage',
  templateUrl: './mycatalogpage.page.html',
  styleUrls: ['./mycatalogpage.page.scss'],
})
export class MycatalogpagePage implements OnInit {

  isLoadingAPI = 0;
  punyaKatalog = false;
  statusAPI = false;
  dataArrayRumah = [];

  constructor(private router: Router, private activatedRoute: ActivatedRoute, private http: HttpClient, public loadingController: LoadingController, public toastController: ToastController, private statusBar: StatusBar, public alertController: AlertController)
  {

  }

  async presentToast(toastText: string, durationToast: number) {
    const toast = await this.toastController.create({
      message: toastText,
      duration: durationToast
    });
    toast.present();
  }

  async deleteKatalogRumahAlert(katalogUUID, katalogNamenya) {
    const alert = await this.alertController.create({
      cssClass: 'alertClass',
      header: 'Hapus Katalog Rumah',
      subHeader: 'Konfirmasi',
      message: 'Yakin ingin menghapus katalog rumah "' + katalogNamenya + '" ?',
      buttons: [
        {
          text: 'Batal',
          role: 'cancel',
          cssClass: 'secondary',
          handler: () => {
            console.log('Confirm Cancel');
          }
        }, {
          text: 'Lanjutkan',
          handler: () => {
            console.log('Confirm Ok');

            this.deleteKatalog(katalogUUID);
          }
        }
      ]
    });

    await alert.present();
  }

  doRefresh(event)
  {
    this.isLoadingAPI = 0;

    this.checkLoginSession();

    this.loadMyCatalog();

    event.target.complete();
  }

  viewKatalog(katalogUUID)
  {
    console.log(katalogUUID);

    this.router.navigate(['/katalogdetailview/' + katalogUUID]);
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

  loadMyCatalog()
  {
    this.http.get(baseUrlData.apiV1 + 'rumah/myRumahCatalog', {
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
              //
            }

            this.statusAPI = res['status'];
            this.isLoadingAPI = 2;
            this.punyaKatalog = false;
          }

          if(res['status'] == true)
          {
            //

            this.statusAPI = res['status'];
            this.dataArrayRumah = res['data'];

            console.log(this.dataArrayRumah);

            console.log("OK");

            this.isLoadingAPI = 1;

            if(this.dataArrayRumah.length > 0)
            {
              this.punyaKatalog = true;
            }

            if(this.dataArrayRumah.length <= 0)
            {
              this.punyaKatalog = false;
            }
          }
        },
        err => {
          console.log("Error occured");

          this.statusAPI = false;
          this.isLoadingAPI = 2;

          if(err['status'] == 401)
          {
            this.refreshToken();
          }
        }
      );
  }

  editKatalog(katalogUUID)
  {
    console.log(katalogUUID);

    this.router.navigate(['/editmykatalogrumah/' + katalogUUID]);
  }

  deleteKatalog(katalogUUID)
  {
    console.log(katalogUUID);

    this.http.post(baseUrlData.apiV1 + 'rumah/deleteRumahCatalog', {katalogUUID: katalogUUID}, {
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
            if(res['code'] == 'ACCOUNT_LOCKED')
            {
              localStorage.setItem("bearerAccessToken" , "");
              localStorage.setItem("refreshToken" , "");
              localStorage.setItem("ssoToken" , "");

              this.router.navigate(['/login']);
            }

            if(res['code'] != 'ACCOUNT_LOCKED')
            {
              //
              this.presentToast(res['message'], 3000);
            }
          }

          if(res['status'] == true)
          {
            //

            this.presentToast(res['message'], 3000);
          }
        },
        err => {
          console.log("Error occured");

          this.presentToast("Terjadi kesalahan saat menghapus katalog rumah, silakan coba lagi!", 3000);

          if(err['status'] == 401)
          {
            this.refreshToken();
          }
        }
      );

    this.loadMyCatalog();
  }

  ngOnInit()
  {
    //

    this.statusBar.overlaysWebView(false);
    this.statusBar.show();

    this.checkLoginSession();
    this.loadMyCatalog();
  }
}
