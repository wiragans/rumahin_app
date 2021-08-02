import { Component, OnInit, ViewChild, ChangeDetectorRef, ChangeDetectionStrategy } from '@angular/core';
import { IonInfiniteScroll } from '@ionic/angular';
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
  selector: 'app-katalog-search-query-page',
  templateUrl: './katalog-search-query-page.page.html',
  styleUrls: ['./katalog-search-query-page.page.scss'],
  changeDetection: ChangeDetectionStrategy.Default,
})
export class KatalogSearchQueryPagePage implements OnInit
{
  @ViewChild(IonInfiniteScroll) infiniteScroll: IonInfiniteScroll;

  statusApi = true;
  statusApiLogin = false;

  searchKatalogQuery = "";
  queryMessageResult = "";

  katalogArrayResult = [];
  katalogArrayResultNextPageScroll = [];
  nextPageUrl = "";

  tempKatalogArray = {};
  arrayFix = [];

  isOnLoadingQuery = false;

  currentPageExecution = 1;
  currentLimitExecution = 5;

  loadInfiniteEnded = false;

  constructor(private router: Router, private activatedRoute: ActivatedRoute, private http: HttpClient, public loadingController: LoadingController, public toastController: ToastController, private statusBar: StatusBar, private changeDetection: ChangeDetectorRef)
  { 

  }

  doRefresh(event)
  {
    event.target.complete();
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

  executeQuery()
  {
    console.log(this.searchKatalogQuery);
    this.loadInfiniteEnded = false;

    this.arrayFix = [];
    this.infiniteScroll.disabled = false;

    this.isOnLoadingQuery = true;
    this.queryMessageResult = "";

    this.currentPageExecution = 1;
    this.currentLimitExecution = 5;

    if(!this.searchKatalogQuery.trim())
    {
      this.isOnLoadingQuery = false;
      this.queryMessageResult = "";
      this.currentPageExecution = 1;
      this.currentLimitExecution = 5;

      return false;
    }

    this.http.get(baseUrlData.apiV1 + 'search/query?search_query=' + this.searchKatalogQuery + '&page=' + this.currentPageExecution + '&limit=' + this.currentLimitExecution, {
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

          if(this.statusApi == false)
          {
            //
            this.queryMessageResult = res['message'];
            this.katalogArrayResult = [];
            this.nextPageUrl = "";

            this.tempKatalogArray = [];
            this.arrayFix = [];
          }

          if(this.statusApi == true)
          {
            //
            this.katalogArrayResult = res['data']['katalogData'];

            if((this.katalogArrayResult).length <= 0)
            {
              this.queryMessageResult = "Tidak ada hasil pencarian ditemukan. Coba pakai kata kunci yang lain yah :)";
              this.tempKatalogArray = [];
              this.arrayFix = [];
            }

            if((this.katalogArrayResult).length > 0)
            {
              this.queryMessageResult = res['message'];
              this.nextPageUrl = res['data']['next'];

              let sizeData = this.katalogArrayResult.length;

              for(let loopData = 0; loopData < sizeData; loopData++)
              {
                this.tempKatalogArray = {
                  katalogID: this.katalogArrayResult[loopData]['katalogID'],
                  katalogUUID: this.katalogArrayResult[loopData]['katalogUUID'],
                  katalogName: this.katalogArrayResult[loopData]['katalogName'],
                  katalogDesc: this.katalogArrayResult[loopData]['katalogDesc'],
                  luasTanah: this.katalogArrayResult[loopData]['details']['luasTanah'],
                  luasBangunan: this.katalogArrayResult[loopData]['details']['luasBangunan'],
                  alamat: this.katalogArrayResult[loopData]['details']['alamat'],
                  priceInt: this.katalogArrayResult[loopData]['price']['priceInt'],
                  priceStr: this.katalogArrayResult[loopData]['price']['priceStr'],
                  katalogImageUrl: this.katalogArrayResult[loopData]['katalogImagesData'][0]['imagesUrl']
                };

                this.arrayFix.push(this.tempKatalogArray);
              }
            }

            console.log(this.tempKatalogArray);
          }

          this.isOnLoadingQuery = false;
        },
        err => {
          console.log("Error occured");

          this.isOnLoadingQuery = false;
          this.katalogArrayResult = [];
          this.tempKatalogArray = [];
          this.queryMessageResult = "Terjadi kesalahan. Silakan coba beberapa saat lagi!";
          this.nextPageUrl = "";
          
          if(err['status'] == 401)
          {
            this.router.navigate(['/login']);
          }
        }
      );
  }

  loadDataNextPage(event){
    setTimeout(() => {
      console.log('Done');

      this.currentPageExecution += 1;

      this.katalogArrayResultNextPageScroll = [];

      this.http.get(this.nextPageUrl, {
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
              //
              this.katalogArrayResultNextPageScroll = [];
              this.infiniteScroll.disabled = true;

              this.loadInfiniteEnded = true;
            }
  
            if(res['status'] == true)
            {
              //
              this.katalogArrayResultNextPageScroll = res['data']['katalogData'];
              console.log(this.katalogArrayResultNextPageScroll);
              console.log(this.katalogArrayResultNextPageScroll.length);

              let lengthArrayTemp = 0;
              lengthArrayTemp = this.katalogArrayResultNextPageScroll.length;
              console.log("Size Array: " + lengthArrayTemp);
  
              if(lengthArrayTemp <= 0)
              {
                event.target.disabled = true;
                this.infiniteScroll.disabled = true;

                this.loadInfiniteEnded = true;
              }
  
              if(lengthArrayTemp > 0)
              {
                this.katalogArrayResult = this.katalogArrayResultNextPageScroll;
                this.changeDetection.detectChanges();
                console.log(this.katalogArrayResult);

                this.nextPageUrl = res['data']['next'];

                let sizeData = this.katalogArrayResult.length;

                for(let loopData = 0; loopData < sizeData; loopData++)
                {
                  this.tempKatalogArray = {
                    katalogID: this.katalogArrayResult[loopData]['katalogID'],
                    katalogUUID: this.katalogArrayResult[loopData]['katalogUUID'],
                    katalogName: this.katalogArrayResult[loopData]['katalogName'],
                    katalogDesc: this.katalogArrayResult[loopData]['katalogDesc'],
                    luasTanah: this.katalogArrayResult[loopData]['details']['luasTanah'],
                    luasBangunan: this.katalogArrayResult[loopData]['details']['luasBangunan'],
                    alamat: this.katalogArrayResult[loopData]['details']['alamat'],
                    priceInt: this.katalogArrayResult[loopData]['price']['priceInt'],
                    priceStr: this.katalogArrayResult[loopData]['price']['priceStr'],
                    katalogImageUrl: this.katalogArrayResult[loopData]['katalogImagesData'][0]['imagesUrl']
                  };

                  let preventDuplikasiArrayKatalog = this.arrayFix.indexOf(this.katalogArrayResult[loopData]['katalogUUID']);

                  if(preventDuplikasiArrayKatalog <= -1)
                  {
                    this.arrayFix.push(this.tempKatalogArray);
                  }
                  
                  if(preventDuplikasiArrayKatalog >= 0)
                  {
                    // IGNORE ATAU ABAIKAN
                  }
                }
              }
  
              //console.log("Data New: " + JSON.stringify(res['data']['katalogData']));
            }
          },
          err => {
            console.log("Error occured");
  
            this.katalogArrayResultNextPageScroll = [];
            event.target.disabled = true;
            
            if(err['status'] == 401)
            {
              this.router.navigate(['/login']);
            }
          }
        );

      event.target.complete();

    }, 500);
  }

  toggleInfiniteScroll()
  {
    this.infiniteScroll.disabled = !this.infiniteScroll.disabled;
  }

  katalogViewDetail(katalogUUIDGet: any)
  {
    console.log(katalogUUIDGet);

    this.router.navigate(['/katalogdetailview/' + katalogUUIDGet]);
  }

  ngOnInit()
  {
    this.statusBar.overlaysWebView(false);
    this.statusBar.show();

    this.checkLoginSession();
  }
}
