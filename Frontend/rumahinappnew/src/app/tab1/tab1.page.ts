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
  selector: 'app-tab1',
  templateUrl: 'tab1.page.html',
  styleUrls: ['tab1.page.scss']
})
export class Tab1Page implements OnInit {

  statusApi = true;
  profileLoaded = false;

  statusApiLogin = false;

  namaLengkap = "";

  images = [];

  popularLoaded = false;
  popularKatalogResult = [];

  connectionErrorPopularKatalog = false;

  //
  newestLoaded = false;
  newestKatalogResult = [];

  connectionErrorNewestKatalog = false;

  slideOpts = {
    initialSlide: 0,
    speed: 400,
    pagination: false
  };

  constructor(private router: Router, private activatedRoute: ActivatedRoute, private http: HttpClient, public loadingController: LoadingController, public toastController: ToastController, private statusBar: StatusBar)
  {

  }

  doRefresh(event)
  {
    this.profileLoaded = false;

    this.checkLoginSession();
    this.loadProfile();

    this.popularLoaded = false;
    this.connectionErrorPopularKatalog = false;

    this.newestLoaded = false;
    this.connectionErrorNewestKatalog = false;

    this.loadPopularKatalog();
    this.loadNewestKatalog();

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
          
          if(err['status'] == 401)
          {
            this.router.navigate(['/login']);
          }
        }
      );
  }

  loadPopularKatalog()
  {
    this.connectionErrorPopularKatalog = false;

    this.http.get(baseUrlData.apiV1 + 'front/popularKatalog', {
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
            this.popularLoaded = true;
            this.popularKatalogResult = res['data'];
          }

          if(res['status'] == false)
          {
            this.popularLoaded = false;
            this.popularKatalogResult = [];
          }
        },
        err => {
          console.log("Error occured");
          this.popularLoaded = false;
          this.popularKatalogResult = [];
          this.connectionErrorPopularKatalog = true;
        }
      );
  }

  loadNewestKatalog()
  {
    this.connectionErrorNewestKatalog = false;

    this.http.get(baseUrlData.apiV1 + 'front/newKatalog', {
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
            this.newestLoaded = true;
            this.newestKatalogResult = res['data'];
          }

          if(res['status'] ==  false)
          {
            this.newestLoaded = false;
            this.newestKatalogResult = [];
          }
        },
        err => {
          console.log("Error occured");
          console.log(err);
          console.log(err['status']);
          this.newestLoaded = false;
          this.newestKatalogResult = [];
          this.connectionErrorNewestKatalog = true;
        }
      );
  }

  katalogViewDetail(katalogUUIDnya: any)
  {
    console.log(katalogUUIDnya);

    this.router.navigate(['/katalogdetailview/' + katalogUUIDnya]);
  }

  ngOnInit():void
  {
    this.checkLoginSession();

    this.statusBar.overlaysWebView(false);
    this.statusBar.show();

    // LOAD PROFILE
    this.loadProfile();

    this.loadPopularKatalog();
    this.loadNewestKatalog();

    this.images = [
      {path: 'https://i.ytimg.com/vi/UFeSWGl_34U/hq720.jpg?sqp=-oaymwEcCNAFEJQDSFXyq4qpAw4IARUAAIhCGAFwAcABBg==&rs=AOn4CLAddoQLq7Kb-kc9ZrXKqJGW8LD-Iw'},
      {path: 'https://i.ytimg.com/an_webp/A7FzxDFdA6c/mqdefault_6s.webp?du=3000&sqp=CK2Sn4QG&rs=AOn4CLB8jI7-D7AsY29PWUUdybd5stegZg'}
  ]
  }

  ngAfterViewInit()
  {
    this.checkLoginSession();
    this.loadProfile();
  }

  ionViewWillEnter()
  {
    this.profileLoaded = false;

    this.checkLoginSession();
    this.loadProfile();

    this.popularLoaded = false;
    this.connectionErrorPopularKatalog = false;

    this.newestLoaded = false;
    this.connectionErrorNewestKatalog = false;

    this.loadPopularKatalog();
    this.loadNewestKatalog();
  }
}