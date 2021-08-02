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

@Component({
  selector: 'app-register',
  templateUrl: './register.page.html',
  styleUrls: ['./register.page.scss'],
})
export class RegisterPage implements OnInit {

  resultText = "";
  statusApi = true;

  namaLengkap = "";
  username = "";
  email = "";
  password = "";
  repeatPassword = "";
  nomorWA = "";
  alamat = "";

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

  registerProcess()
  {
    this.resultText = "";
    this.presentLoading();

    this.http.post(baseUrlData.apiV1 + 'register', {namaLengkap: this.namaLengkap, username: this.username, email: this.email, password: this.password, repeatPassword: this.repeatPassword, nomorWA: "+62" + this. nomorWA, alamat: this.alamat}, {
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
            }

            else if(res['code'] == "EMAIL_NOT_VERIFIED")
            {
              this.resultText = res['message'];
            }

            else
            {
              this.resultText = res['message'];
            }
          }

          if(res['status'] == true)
          {
            this.resultText = res['message'];
            this.presentToast(this.resultText, 5000);
            this.namaLengkap = "";
            this.username = "";
            this.email = "";
            this.password = "";
            this.repeatPassword = "";
            this.nomorWA = "";
            this.alamat = "";
            this.router.navigate(['/login']);
          }
        },
        err => {
          this.dismissLoading();
          console.log("Error occured");
          this.resultText = "Terjadi kesalahan, silakan coba lagi!";
        }
      );
  }

  ngOnInit():void
  {
    //
    this.statusBar.overlaysWebView(false);
    this.statusBar.show();

    this.namaLengkap = "";
    this.username = "";
    this.email = "";
    this.password = "";
    this.repeatPassword = "";
    this.nomorWA = "";
    this.alamat = "";
  }
}
