import { Component, OnInit, ViewChild } from '@angular/core';
import { IonInfiniteScroll } from '@ionic/angular';
import { StatusBar } from '@ionic-native/status-bar/ngx';
import { Router, ActivatedRoute } from '@angular/router';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { mimeData } from '../../environments/environment';
import { baseAuthData } from '../../environments/environment';
import { baseUrlData } from '../../environments/environment';
import { ToastController } from '@ionic/angular';

@Component({
  selector: 'app-quickfrontpageresult',
  templateUrl: './quickfrontpageresult.page.html',
  styleUrls: ['./quickfrontpageresult.page.scss'],
})
export class QuickfrontpageresultPage implements OnInit {
  @ViewChild(IonInfiniteScroll) infiniteScroll: IonInfiniteScroll;

  getDataFromParams = "";

  tempKatalogArray = {};
  dataArrayRumah = [];

  katalogLoaded = false;
  katalogFound = false;

  notifAPIMsg = "";

  currentPage = 1;
  limit = 5;

  constructor(private activatedRoute: ActivatedRoute, private http: HttpClient, private statusBar: StatusBar, private router: Router, private toastController: ToastController)
  {

  }

  async presentToast(toastText: string, durationToast: number) {
    const toast = await this.toastController.create({
      message: toastText,
      duration: durationToast
    });
    toast.present();
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

  loadDataRumah(datanya)
  {
    this.http.post(baseUrlData.apiV1 + 'search/quick?page=' + this.currentPage + '&limit=' + this.limit, window.atob(datanya), {
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
            this.katalogLoaded = true;

            if(this.dataArrayRumah.length <= 0)
            {
              this.katalogFound = false;
              this.dataArrayRumah = [];
              this.notifAPIMsg = res['message'];
            }
            
            if(this.dataArrayRumah.length > 0)
            {
              this.infiniteScroll.disabled = true;
            }
          }

          if(res['status'] == true)
          {
            this.katalogLoaded = true;
            this.katalogFound = true;

            if(res['data'].length > 0)
            {
              for(let loopData = 0; loopData < res['data'].length; loopData++)
              {
                this.tempKatalogArray = {
                  katalogID: res['data'][loopData]['katalogID'],
                  katalogUUID: res['data'][loopData]['katalogUUID'],
                  katalogName: res['data'][loopData]['katalogName'],
                  luasTanah: res['data'][loopData]['luasTanah'],
                  luasBangunan: res['data'][loopData]['luasBangunan'],
                  alamat: res['data'][loopData]['alamat'],
                  priceInt: res['data'][loopData]['price']['priceInt'],
                  priceStr: res['data'][loopData]['price']['priceStr'],
                  thumbnailImageUrl: res['data'][loopData]['thumbnailImageUrl']
                };

                this.dataArrayRumah.push(this.tempKatalogArray);
              }

              this.currentPage += 1;
            }

            if(res['data'].length <= 0)
            {
              this.infiniteScroll.disabled = true;
            }
          }
        },
        err => {
          console.log("Error occured");

          this.katalogLoaded = true;
          this.katalogFound = false;
          this.dataArrayRumah = [];

          this.notifAPIMsg = "Terjadi kesalahan. Coba lagi ya!";

          if(err['status'] == 401)
          {
            this.refreshToken();
          }
        }
      );
  }

  viewKatalog(data)
  {
    this.router.navigate(['/katalogdetailview/' + data]);
  }

  loadData(event) {
    setTimeout(() => {
      console.log('Done');
      event.target.complete();

      this.loadDataRumah(this.getDataFromParams);
    }, 500);
  }

  toggleInfiniteScroll()
  {
    this.infiniteScroll.disabled = !this.infiniteScroll.disabled;
  }

  ngOnInit()
  {
    this.statusBar.overlaysWebView(false);
    this.statusBar.show();

    this.getDataFromParams = this.activatedRoute.snapshot.paramMap.get('data');
    console.log(this.getDataFromParams);
    console.log("ATOB: " + window.atob(this.getDataFromParams));

    this.loadDataRumah(this.getDataFromParams);

    this.checkLoginSession();
  }
}
