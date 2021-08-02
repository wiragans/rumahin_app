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
  selector: 'app-tab2',
  templateUrl: 'tab2.page.html',
  styleUrls: ['tab2.page.scss']
})
export class Tab2Page implements OnInit {

  statusApi = true;
  profileLoaded = false;

  namaLengkap = "";

  noKatalogFound = true;
  statusApiBookmark = false;
  arrayDataBookmark = [];
  bookmarkLoaded = false;

  descAttr = "Deskripsi: ";
  searchBookmarkKatalog;

  constructor(private router: Router, private activatedRoute: ActivatedRoute, private http: HttpClient, public loadingController: LoadingController, public toastController: ToastController, private statusBar: StatusBar) {}

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

  doRefresh(event)
  {
    this.bookmarkLoaded = false;
    this.noKatalogFound = true;
    this.statusApiBookmark = false;

    this.profileLoaded = false;

    this.loadProfile();
    this.loadKatalogBookmark();

    event.target.complete();
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

  loadKatalogBookmark()
  {
    /*console.log(localStorage.getItem("bookmarkValueJSONa"));
    let sizeBookmark = JSON.parse(localStorage.getItem("bookmarkValueJSONa")).length;

    console.log(sizeBookmark.length);

    if(sizeBookmark <= 0)
    {
      this.noKatalogFound = true;
      console.log("Tidak Ada Bookmarknya");
    }

    if(sizeBookmark > 0)
    {
      this.noKatalogFound = false;
      console.log("Ada Bookmarknya");
    }*/

    this.http.get(baseUrlData.apiV1 + 'bookmark/getBookmark', {
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
          this.statusApiBookmark = res['status'];

          if(this.statusApiBookmark == false)
          {
            console.log("Error occured");
            this.statusApiBookmark = false;
            this.noKatalogFound = true;
            this.bookmarkLoaded = true;
          }

          if(this.statusApiBookmark == true)
          {
            let bookmarkDataLength = res['data'].length;

            console.log(bookmarkDataLength);

            if(bookmarkDataLength <= 0)
            {
              console.log("Tidak ada bookmarknya");

              this.noKatalogFound = true;
              this.arrayDataBookmark = [];
              this.bookmarkLoaded = true;
            }

            if(bookmarkDataLength > 0)
            {
              console.log("Ada bookmarknya");

              this.noKatalogFound = false;
              this.arrayDataBookmark = res['data'];
              this.bookmarkLoaded = true;
            }
          }
        },
        err => {
          console.log("Error occured");
          this.statusApiBookmark = false;
          this.noKatalogFound = true;
          this.bookmarkLoaded = true;
        }
      );
  }

  deleteBookmark(bookmarkListingID: any)
  {
    console.log(bookmarkListingID);

    this.presentLoading();

    this.http.post(baseUrlData.apiV1 + 'bookmark/deleteBookmark', {useBookmarkListingID: 1, bookmarkListingID: bookmarkListingID, katalogUUID: ''}, {
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

          this.dismissLoading();

          if(res['status'] == true)
          {
            this.presentToast(res['message'], 3000);
          }

          if(res['status'] == false)
          {
            this.presentToast(res['message'], 3000);
          }

          this.loadKatalogBookmark();
        },
        err => {
          console.log("Error occured");
          this.presentToast("Terjadi kesalahan saat menghapus bookmark katalog, silakan coba lagi!", 3000);
        }
      );
  }

  viewKatalog(katalogUUIDnya: any)
  {
    console.log(katalogUUIDnya);

    this.router.navigate(['/katalogdetailview/' + katalogUUIDnya]);
  }

  ngOnInit():void
  {
    this.statusBar.overlaysWebView(false);
    this.statusBar.show();

    this.loadProfile();

    this.loadKatalogBookmark();
  }

  ionViewWillEnter()
  {
    this.profileLoaded = false;
    this.bookmarkLoaded = false;
    this.noKatalogFound = true;
    this.statusApiBookmark = false;

    this.loadProfile();

    this.loadKatalogBookmark();
  }
}