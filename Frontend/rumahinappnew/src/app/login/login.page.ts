import { Component, OnInit } from '@angular/core';
import { StatusBar } from '@ionic-native/status-bar/ngx';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { mimeData } from '../../environments/environment';
import { baseAuthData } from '../../environments/environment';
import { baseUrlData } from '../../environments/environment';
import { LoadingController } from '@ionic/angular';
import { Router } from '@angular/router';

@Component({
  selector: 'app-login',
  templateUrl: './login.page.html',
  styleUrls: ['./login.page.scss'],
})
export class LoginPage implements OnInit
{

  postId = "";
  usernameOrEmail = "";
  loginMethod = "";
  resultLoginRequest = "";
  placeHolderMessage = "";
  messageLoginRequestTitle = "";
  length = "";
  type = "";
  statusApi = true;

  constructor(private http: HttpClient, public loadingController: LoadingController, private router: Router, private statusBar: StatusBar)
  {
    this.usernameOrEmail = "";
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

  loginRequest()
  {
    this.presentLoading();

    this.http.post(baseUrlData.apiV1 + 'oauth/login/request', {grant_type: this.loginMethod, client_id: baseAuthData.clientId, client_secret: baseAuthData.clientSecret, account: this.usernameOrEmail, scopes: 'user'}, {
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
            localStorage.setItem("challengeToken", res['data']['challengeToken']);
            this.resultLoginRequest = res['message'];
            this.length = res['data']['input']['length'];
            this.type = res['data']['input']['type'];
            this.placeHolderMessage = res['data']['input']['placeholder'];
            localStorage.setItem("tempUsernameOrEmail", this.usernameOrEmail);
            localStorage.setItem("messageLoginRequest", this.placeHolderMessage);
            localStorage.setItem("messageLoginRequestTitle", this.resultLoginRequest);
            localStorage.setItem("confirmationLoginLength", this.length);
            localStorage.setItem("confirmationLoginType", this.type);
            localStorage.setItem("canResendOTP", res['data']['canResend']);
            localStorage.setItem("canResendInSeconds", res['data']['canResendInSeconds']);
            this.router.navigate(['/verify-credentials-page/' + res['data']['grantType']]);
          }
        },
        err => {
          this.dismissLoading();
          console.log("Error occured");
          this.resultLoginRequest = "Terjadi kesalahan, silakan coba lagi!";
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

          if(res['status'] == true)
          {
            this.router.navigate(['/dashboarddashboardPage/home']);
          }
        },
        err => {
          console.log("Error occured");
        }
      );
  }

  ngOnInit():void
  {
    this.checkLoginSession();

    localStorage.setItem("messageLoginRequest", "");
    this.statusBar.overlaysWebView(false); 
    this.statusBar.show();
  }
}