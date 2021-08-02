import { Component, OnInit } from '@angular/core';
import { StatusBar } from '@ionic-native/status-bar/ngx';
import { ActivatedRoute } from '@angular/router';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { mimeData } from '../../environments/environment';
import { baseAuthData } from '../../environments/environment';
import { baseUrlData } from '../../environments/environment';
import { tokenData } from '../../environments/environment';
import { LoadingController } from '@ionic/angular';
import { Router } from '@angular/router';
import { ToastController } from '@ionic/angular';

@Component({
  selector: 'app-forgotpassword',
  templateUrl: './forgotpassword.page.html',
  styleUrls: ['./forgotpassword.page.scss'],
})

export class ForgotpasswordPage implements OnInit{

  email = "";
  resultText = "";
  statusApi = true;

  pos_type = "";
  redirectorText = "";
  redirectorRouteTo = "";

  statusApiLogin = false;

  constructor(private router: Router, private activatedRoute: ActivatedRoute, private http: HttpClient, public loadingController: LoadingController, public toastController: ToastController, private statusBar: StatusBar)
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

  forgotPasswordRequest()
  {
    this.presentLoading();

    this.http.post(baseUrlData.apiV1 + 'syshelper/requestResetPassword', {email: this.email}, {
      headers: new HttpHeaders({
        'Content-Type': mimeData.json,
        'X-API-Key': baseAuthData.xApiKey,
        'Authorization': 'Basic ' + baseAuthData.basicToken,
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

          if(res['status'] == false)
          {
            if(res['code'] == "UPDATE_NEEDED")
            {
              this.resultText = res['message'];
              this.presentToast(this.resultText, 3000);
            }

            else if(res['code'] == "EMAIL_NOT_VERIFIED")
            {
              this.resultText = res['message'];
              this.presentToast(this.resultText, 3000);
            }

            else
            {
              this.resultText = res['message'];
              this.presentToast(this.resultText, 3000);
            }
          }

          if(res['status'] == true)
          {
            this.resultText = res['message'];
            this.presentToast(this.resultText, 5000);
          }
        },
        err => {
          this.dismissLoading();
          console.log("Error occured");
          this.presentToast("Terjadi kesalahan, silakan coba lagi!", 3000);
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

  routeTo()
  {
    this.router.navigate([this.redirectorRouteTo]);
  }

  ngOnInit():void
  {
    //
    this.pos_type = this.activatedRoute.snapshot.paramMap.get('pos_type');

    console.log(this.pos_type);

    if(this.pos_type == "sudahLogin")
    {
      this.checkLoginSession();

      this.redirectorText = "Kembali ke Profile";
      this.redirectorRouteTo = '/dashboardPage/profile';
    }

    else if(this.pos_type == "belumLogin")
    {
      this.redirectorText = "Kembali Login";
      this.redirectorRouteTo = '/login';
    }

    else
    {
      this.redirectorText = "Kembali Login";
      this.redirectorRouteTo = '/login';
    }

    this.statusBar.overlaysWebView(false);
    this.statusBar.show();

    this.email = "";
  }
}
