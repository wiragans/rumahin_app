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
import { interval } from 'rxjs';
import { takeWhile } from 'rxjs/operators';

@Component({
  selector: 'app-verify-credentials-page',
  templateUrl: './verify-credentials-page.page.html',
  styleUrls: ['./verify-credentials-page.page.scss'],
})
export class VerifyCredentialsPagePage implements OnInit {

  challengeToken = localStorage.getItem("challengeToken");
  placeHolder = localStorage.getItem("messageLoginRequest");
  messageLoginRequestTitle = localStorage.getItem("messageLoginRequestTitle");
  grantType = "password";
  length = "";
  type = "";
  credentials = "";
  resultLoginRequest = "";
  statusApi = true;

  canResendOTP = false;
  canResendInSeconds = 30;
  resendText = "";

  constructor(private router: Router, private activatedRoute: ActivatedRoute, private http: HttpClient, public loadingController: LoadingController, public toastController: ToastController, private statusBar: StatusBar) { }

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

  async presentToastLoginSukses() {
    const toast = await this.toastController.create({
      message: 'Login Sukses',
      duration: 2000
    });
    toast.present();

    //this.router.navigate(['/login']);
  }

  async presentToastResendOTP() {
    const toast = await this.toastController.create({
      message: 'Resend OTP Berhasil',
      duration: 2000
    });
    toast.present();
  }

  changeResendOTPButtonText()
  {
    this.canResendInSeconds -= 1;

    if(this.canResendInSeconds > 0)
    {
      this.resendText = "Kirim Ulang OTP (" + this.canResendInSeconds + "s)";
      console.log(this.resendText);
    }

    if(this.canResendInSeconds <= 0)
    {
      this.resendText = "Kirim Ulang OTP";
      console.log(this.resendText);
      this.canResendOTP = true;
    }
  }

  resendOTPCountdownController()
  {
    interval(1000)
        .pipe(takeWhile(() => this.canResendInSeconds > 0))
        .subscribe(() => {
          this.changeResendOTPButtonText();
        });
  }

  resendOTP()
  {
    if(this.canResendOTP == false)
    {
      return false;
    }

    this.presentLoading();

    this.http.post(baseUrlData.apiV1 + 'oauth/login/request', {grant_type: this.grantType, client_id: baseAuthData.clientId, client_secret: baseAuthData.clientSecret, account: localStorage.getItem("tempUsernameOrEmail"), scopes: 'user'}, {
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
              this.resultLoginRequest = res['message'];
            }

            else if(res['code'] == "EMAIL_NOT_VERIFIED")
            {
              this.resultLoginRequest = res['message'];
            }

            else
            {
              this.resultLoginRequest = res['message'];
            }
          }

          if(res['status'] == true)
          {
            this.resultLoginRequest = res['message'];
            this.length = res['data']['input']['length'];
            this.type = res['data']['input']['type'];
            this.canResendInSeconds = parseInt(res['data']['canResendInSeconds']);

            this.challengeToken = res['data']['challengeToken'];
            this.canResendOTP = false;
            this.presentToastResendOTP();
            this.canResendOTP = false;
            this.resendOTPCountdownController();
          }
        },
        err => {
          this.dismissLoading();
          console.log("Error occured");
          this.resultLoginRequest = "Terjadi kesalahan, silakan coba lagi!";
        }
      );
  }

  verifyLogin()
  {
    this.presentLoading();

    this.http.post(baseUrlData.apiV1 + 'oauth/token', {grant_type: this.grantType, client_id: baseAuthData.clientId, client_secret: baseAuthData.clientSecret, scopes: 'user', data: {challengeToken: this.challengeToken, credentials: this.credentials}}, {
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
              this.resultLoginRequest = res['message'];
            }

            else if(res['code'] == "EMAIL_NOT_VERIFIED")
            {
              this.resultLoginRequest = res['message'];
            }

            else
            {
              this.resultLoginRequest = res['message'];
            }
          }

          if(res['status'] == true)
          {
            this.presentToastLoginSukses();

            localStorage.setItem("bearerAccessToken", res['data']['access_token']);
            localStorage.setItem("refreshToken", res['data']['refresh_token']);
            localStorage.setItem("refreshToken", res['data']['sso_token']);

            this.router.navigate(['/dashboardPage/home'], { replaceUrl: true });
          }
        },
        err => {
          this.dismissLoading();
          console.log("Error occured");
          this.resultLoginRequest = "Terjadi kesalahan, silakan coba lagi!";
        }
      );
  }

  ngOnInit():void
  {
    this.statusBar.overlaysWebView(false);
    this.statusBar.show();

    this.challengeToken = localStorage.getItem("challengeToken");
    this.placeHolder = localStorage.getItem("messageLoginRequest");
    this.messageLoginRequestTitle = localStorage.getItem("messageLoginRequestTitle");

    this.length = localStorage.getItem("confirmationLoginLength");
    this.type = localStorage.getItem("confirmationLoginType");

    if(this.challengeToken == null || this.challengeToken == "")
    {
      this.router.navigate(['/login']);
    }

    if(this.challengeToken != null && this.challengeToken != "")
    {
      this.grantType = this.activatedRoute.snapshot.paramMap.get('grant_type');
      
      if(this.grantType == "password")
      {
        console.log("Using Password");
        localStorage.setItem("challengeToken", "");
      }

      else if(this.grantType == "otp")
      {
        console.log("Using OTP");
        localStorage.setItem("challengeToken", "");
        this.canResendOTP = JSON.parse(localStorage.getItem("canResendOTP"));
        this.canResendInSeconds = parseInt(localStorage.getItem("canResendInSeconds"));
        console.log(this.canResendOTP);
        this.canResendOTP = false;
        this.resendOTPCountdownController();
      }

      else
      {
        this.router.navigate(['/login']);
      }
    }
  }
}
