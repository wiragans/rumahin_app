import { Component, OnInit, Renderer2 } from '@angular/core';
import { StatusBar } from '@ionic-native/status-bar/ngx';
import { ActivatedRoute } from '@angular/router';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { mimeData } from '../../environments/environment';
import { baseAuthData } from '../../environments/environment';
import { baseUrlData } from '../../environments/environment';
import { LoadingController } from '@ionic/angular';
import { Router } from '@angular/router';
import { ToastController } from '@ionic/angular';
import { ThrowStmt } from '@angular/compiler';
import { AlertController } from '@ionic/angular';

@Component({
  selector: 'app-tab3',
  templateUrl: 'tab3.page.html',
  styleUrls: ['tab3.page.scss']
})
export class Tab3Page implements OnInit {

  resultText = "";
  statusApi = true;
  profileLoaded = false;

  namaLengkap = "";
  namaLengkapTemp = "";
  email = "";
  nomorWA = "";
  alamat = "";
  newPassword = "";
  currentPassword = "";

  // ERROR HANDLING
  namaLengkapError = "";
  emailError = "";
  waNumberError = "";
  alamatError = "";
  newPasswordError = "";
  currentPasswordError = "";

  statusApiLogin = false;

  constructor(private router: Router, private activatedRoute: ActivatedRoute, private http: HttpClient, public loadingController: LoadingController, public toastController: ToastController, private statusBar: StatusBar, private renderer: Renderer2, public alertController: AlertController)
  {

  }

  async presentLoading() {
    const loading = await this.loadingController.create({
      cssClass: 'loadingCssnya',
      message: 'Sedang memuat...'
    });
    await loading.present();
  }

  dismissLoading()
  {
    this.loadingController.dismiss();
  }

  async presentToast(toastText: string, durationToast: number) {
    const toast = await this.toastController.create({
      message: toastText,
      duration: durationToast
    });
    toast.present();
  }

  simpanProfile()
  {
    //

    this.presentLoading();

    let requestBody = "namaLengkap=" + this.namaLengkapTemp + "&email=" + this.email + "&newPassword=" + this.newPassword + "&waNumber=" + this.nomorWA + "&alamat=" + this.alamat + "&currentPassword=" + this.currentPassword;

    this.http.post(baseUrlData.apiV1 + 'account/edit', requestBody, {
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
          this.dismissLoading();

          this.presentToast(res['message'], 3000);

          if(res['code'] != "EDIT_ACCOUNT_ERROR")
          {
            this.namaLengkapError = "";
            this.emailError = "";
            this.waNumberError = "";
            this.alamatError = "";
            this.newPasswordError = "";
            this.currentPasswordError = "";
          }

          if(res['code'] == "EDIT_ACCOUNT_ERROR")
          {
            this.namaLengkapError = "";
            this.emailError = "";
            this.waNumberError = "";
            this.alamatError = "";
            this.newPasswordError = "";
            this.currentPasswordError = "";

            let sizeErrorElements = res['data']['errors'].length;
            let isError = false;
            let errorHighlight = "";
            let errorMsg = "";

            for(let i = 0; i < sizeErrorElements; i++)
            {
              isError = res['data']['errors'][i]['isError'],
              errorHighlight = res['data']['errors'][i]['errorsFieldHighlight'];

              console.log(isError);
              console.log(errorHighlight);

              if(isError == true)
              {
                errorMsg = res['data']['errors'][i]['errorsMessage']['message'];
                console.log(errorMsg);

                if(errorHighlight == "namaLengkapError")
                {
                  this.namaLengkapError = errorMsg;
                }

                if(errorHighlight == "emailError")
                {
                  this.emailError = errorMsg;
                }

                if(errorHighlight == "newPasswordError")
                {
                  this.newPasswordError = errorMsg;
                }

                if(errorHighlight == "waNumberError")
                {
                  this.waNumberError = errorMsg;
                }

                if(errorHighlight == "alamatError")
                {
                  this.alamatError = errorMsg;
                }

                if(errorHighlight == "currentPasswordError")
                {
                  this.currentPasswordError = errorMsg;
                }
              }
            }
          }

          if(res['status'] == true)
          {
            this.newPasswordError = "";
            this.currentPasswordError = "";

            this.newPassword = "";
            this.currentPassword = "";

            this.loadProfile();
          }
        },
        err => {
          this.presentToast("Terjadi kesalahan. Silakan coba lagi!", 3000);
        }
      );
  }

  async logoutAlert() {
    const alert = await this.alertController.create({
      cssClass: 'alertClass',
      header: 'Logout Akun',
      subHeader: 'Konfirmasi',
      message: 'Yakin ingin keluar akun?',
      buttons: [
        {
          text: 'Batal',
          role: 'cancel',
          cssClass: 'secondary',
          handler: () => {
            console.log('Confirm Cancel');
          }
        }, {
          text: 'Logout From This Device Only',
          handler: () => {
            console.log('Confirm Ok');

            this.logoutAkun('logout?logoutFromAllDevices=0');
          }
        },
        {
          text: 'Logout From All Devices',
          handler: () => {
            console.log('Confirm Ok2');

            this.logoutAkun('logout?logoutFromAllDevices=1');
          }
        }
      ]
    });

    await alert.present();
  }

  doRefresh(event)
  {
    this.profileLoaded = false;

    this.checkLoginSession();
    this.loadProfile();

    event.target.complete();
  }

  logoutAkun(logoutUrl: any)
  {
    this.presentLoading();

    this.http.get(baseUrlData.apiV1 + logoutUrl, {
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
          this.dismissLoading();
          this.statusApi = res['status'];

          this.namaLengkap = "";
          this.namaLengkapTemp = "";
          this.email = "";
          this.nomorWA = "";
          this.alamat = "";
          this.newPassword = "";
          this.currentPassword = "";

          // ERROR HANDLING
          this.namaLengkapError = "";
          this.emailError = "";
          this.waNumberError = "";
          this.alamatError = "";
          this.newPasswordError = "";
          this.currentPasswordError = "";

          if(res['status'] == false)
          {
            if(res['code'] == "UPDATE_NEEDED")
            {
              this.resultText = res['message'];
            }

            else if(res['code'] == "EMAIL_NOT_VERIFIED")
            {
              this.resultText = res['message'];
            }

            else
            {
              this.resultText = res['message'];
            }

            localStorage.setItem("bearerAccessToken", "");
            localStorage.setItem("refreshToken", "");
            localStorage.setItem("ssoToken", "");
            this.presentToast("Logout Berhasil", 3000);
            this.router.navigate(['/login'], { replaceUrl: true });
          }

          if(res['status'] == true)
          {
            localStorage.setItem("bearerAccessToken", "");
            localStorage.setItem("refreshToken", "");
            localStorage.setItem("ssoToken", "");
            this.presentToast("Logout Berhasil", 3000);
            this.router.navigate(['/login'], { replaceUrl: true });
          }
        },
        err => {
          console.log("Error occured");
          localStorage.setItem("bearerAccessToken", "");
          localStorage.setItem("refreshToken", "");
          localStorage.setItem("ssoToken", "");
          this.presentToast("Logout Berhasil", 3000);
          this.router.navigate(['/login'], { replaceUrl: true });
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
            this.namaLengkapTemp = res['data']['namaLengkap'];
            this.email = res['data']['email'];
            this.nomorWA = res['data']['nomorWA'];
            this.alamat = res['data']['alamat'];

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
          this.statusApiLogin = res['status'];

          if(res['status'] == false)
          {
            this.router.navigate(['/login']);
          }

          if(res['status'] == true)
          {
            //
          }
        },
        err => {
          console.log("Error occured");
          this.router.navigate(['/login']);
        }
      );
  }

  ngOnInit():void
  {
    if(localStorage.getItem("bearerAccessToken") == "" || localStorage.getItem("bearerAccessToken") == null)
    {
      this.router.navigate(['/login']);
    }

    this.checkLoginSession();

    this.statusBar.overlaysWebView(false);
    this.statusBar.show();

    // LOAD PROFILE
    this.loadProfile();
  }

  ngAfterViewInit()
  {
    this.checkLoginSession();
    this.loadProfile();

    console.log("Ah");
  }

  ionViewWillEnter()
  {
    this.profileLoaded = false;

    if(localStorage.getItem("bearerAccessToken") == "" || localStorage.getItem("bearerAccessToken") == null)
    {
      this.router.navigate(['/login']);
    }

    this.checkLoginSession();

    // LOAD PROFILE
    this.loadProfile();
  }
}
