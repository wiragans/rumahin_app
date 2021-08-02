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
import { Pipe } from '@angular/core';
import { DomSanitizer} from '@angular/platform-browser';
import { ModalController } from '@ionic/angular';

import { RendertigadimensiPage } from '../rendertigadimensi/rendertigadimensi.page';

@Component({
  selector: 'app-katalogdetailview',
  templateUrl: './katalogdetailview.page.html',
  styleUrls: ['./katalogdetailview.page.scss'],
})

@Pipe({ name: 'safe' })
export class KatalogdetailviewPage implements OnInit{

  katalogUUIDGet = "";
  loadedAPI = false;

  katalogRumahDitemukan = true;

  //

  tayangTimestampReal = "";

  katalogName = "";
  katalogDesc = "";
  totalDilihat = "";
  fixedAlamat = "";
  alamatLengkapFull = "";

  isDisewakan = 0;
  priceStr = "";
  hargaTextLabelAttr = "";

  modeSewa = "";

  kondisi = 0;
  kondisiText = "";
  luasTanah = "";
  luasBangunan = "";
  jumlahKamarMandi = "";
  jumlahKamarTidur = "";
  jumlahRuangTamu = "";
  jumlahGarasi = "";
  jumlahRuangKeluarga = "";
  jumlahRuangMakan = "";
  jumlahDapur = "";
  jumlahGudang = "";
  jumlahSerambi = "";
  jumlahTingkat = "";
  tahunDibuat = "";

  //

  // misc rumah spec

  sertifikat = "";
  tipePropertiRumahInString = "";
  conditionMeasurement = "";
  perlengkapanPerabotan = "";
  dayaListrik = "";

  // info pengembang (developer) rumah

  developerName = "";
  developerEmail = "";
  developerWhatsAppNumber = "";
  developerWhatsAppUrl = "";
  developerWhatsAppButtonSimpleText = "";

  //

  imagesKatalogArray = [];
  imagesKatalogArrayParsed = [];
  selectedImageIndex = 0;
  imageObject = [];
  showFlag = false;
  currentIndex = 0;
  imageSlideShowable = true;

  isThisKatalogBookmarked = 0;
  bookmarkListingID = 0;

  katalogVideoDataVideoUrl = "";

  arButtonClicked = false;
  youtubeVideoButtonClicked = false;

  isUseAR = 0;
  arDataArray = [];
  linkARMarker = "";

  objectFileURL = "";
  objectFileDiffuseTextureURL = "";

  useYouTubeVideoUrl = 0;

  constructor(private router: Router, private activatedRoute: ActivatedRoute, private http: HttpClient, public loadingController: LoadingController, public toastController: ToastController, private statusBar: StatusBar, private sanitizer: DomSanitizer, private modalController: ModalController)
  {
    //
  }

  transform(url)
  {
    return this.sanitizer.bypassSecurityTrustResourceUrl(url);
  }

  async openModalRender3D()
  {
    const modal = await this.modalController.create({
      component: RendertigadimensiPage,
      componentProps: { 
        backHrefKatalogUUID: this.katalogUUIDGet,
        fbxUrl: this.objectFileURL,
        texturenya: this.objectFileDiffuseTextureURL,
        linkMarkernya: this.linkARMarker
      }
    });

    /*modal.onDidDismiss()
      .then((data) => {
        console.log(data);
        this.arButtonClicked = false;
        //window.location.reload();
        this.loadDataKatalogRumah();
    });*/

    modal.onDidDismiss()
      .then(() => {
        //this.router.navigate(['/katalogdetailview/' + this.katalogUUIDGet]);
        this.arButtonClicked = false;
    });

    return await modal.present();
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

  loadDataKatalogRumah()
  {
    this.imagesKatalogArrayParsed = [];
    this.imageObject = [];

    this.http.post(baseUrlData.apiV1 + 'rumah/detail', {katalogUUID: this.katalogUUIDGet}, {
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
            this.katalogRumahDitemukan = false;
            this.loadedAPI = true;
          }

          if(res['status'] == true)
          {
            this.tayangTimestampReal = res['data']['tayangTimestampReal'];
            this.katalogName = res['data']['katalogName'];
            this.katalogDesc = res['data']['katalogDesc'];
            this.isDisewakan = res['data']['isDisewakan'];
            this.priceStr = res['data']['price']['priceStr'];
            this.totalDilihat = '<i class="fas fa-eye"></i> ' + res['data']['totalViewed'].toString() + ' kali dilihat';
            this.fixedAlamat = res['data']['details']['fixedAlamat'];
            this.alamatLengkapFull = res['data']['details']['alamat'];
            this.kondisi = res['data']['isSecond'];

            if(this.kondisi == 0)
            {
              this.kondisiText = "<b>Kondisi:</b> " + "Baru";
            }

            if(this.kondisi == 1)
            {
              this.kondisiText = "<b>Kondisi:</b> " + "Second/Bekas";
            }

            this.luasTanah = "<b>Luas Tanah:</b> " + res['data']['details']['luasTanah'].toString() + "m²";
            this.luasBangunan = "<b>Luas Bangunan:</b> " + res['data']['details']['luasBangunan'].toString() + "m²";
            this.jumlahKamarMandi = "<b>Jumlah Kamar Mandi:</b> " + res['data']['details']['jumlahKamarMandi'].toString();
            this.jumlahKamarTidur = "<b>Jumlah Kamar Tidur:</b> " + res['data']['details']['jumlahKamarTidur'].toString();
            this.jumlahRuangTamu = "<b>Jumlah Ruang Tamu:</b> " + res['data']['details']['jumlahRuangTamu'].toString();
            this.jumlahGarasi = "<b>Jumlah Garasi:</b> " + res['data']['details']['jumlahGarasi'].toString();
            this.jumlahRuangKeluarga = "<b>Jumlah Ruang Keluarga:</b> " + res['data']['details']['jumlahRuangKeluarga'].toString();
            this.jumlahRuangMakan = "<b>Jumlah Ruang Makan:</b> " + res['data']['details']['jumlahRuangMakan'].toString();
            this.jumlahDapur = "<b>Jumlah Dapur:</b> " + res['data']['details']['jumlahDapur'].toString();
            this.jumlahGudang = "<b>Jumlah Gudang:</b> " + res['data']['details']['jumlahGudang'].toString();
            this.jumlahSerambi = "<b>Jumlah Serambi:</b> " + res['data']['details']['jumlahSerambi'].toString();
            this.jumlahTingkat = "<b>Jumlah Tingkat:</b> " + res['data']['details']['jumlahTingkat'].toString();
            this.tahunDibuat = "<b>Tahun Dibuat:</b> " + res['data']['details']['tahunDibuat'].toString();

            if(this.isDisewakan == 0)
            {
              this.hargaTextLabelAttr = "Dijual: " + this.priceStr;
              this.modeSewa = "<b>Sistem Sewa:</b> -";
            }

            if(this.isDisewakan == 1)
            {
              this.hargaTextLabelAttr = "Disewakan: " + this.priceStr;
              this.modeSewa = "<b>Sistem Sewa:</b> " + res['data']['modeSewa'].toString();
            }

            // misc spesifikasi rumah

            this.sertifikat = "<b>Tipe Sertifikat:</b> " + res['data']['details']['sertifikat'].toString();

            if(res['data']['details']['sertifikat'].toString() == "" || res['data']['details']['sertifikat'].toString() == null)
            {
              this.sertifikat = "-";
            }

            this.tipePropertiRumahInString = "<b>Tipe Rumah:</b> " + res['data']['miscDetails']['tipePropertiRumahInString'].toString();

            if(res['data']['miscDetails']['conditionMeasurement'].toString() == "" || res['data']['miscDetails']['conditionMeasurement'].toString() == null)
            {
              this.conditionMeasurement = "-";
            }

            if(res['data']['miscDetails']['conditionMeasurement'].toString() != "" && res['data']['miscDetails']['conditionMeasurement'].toString() != null)
            {
              this.conditionMeasurement = "<b>Pemastian Kondisi:</b> " + res['data']['miscDetails']['conditionMeasurement'].toString();
            }

            if(res['data']['miscDetails']['perlengkapanPerabotan'].toString() == "" || res['data']['miscDetails']['perlengkapanPerabotan'].toString() == null)
            {
              this.perlengkapanPerabotan = "-";
            }

            if(res['data']['miscDetails']['perlengkapanPerabotan'].toString() != "" && res['data']['miscDetails']['perlengkapanPerabotan'].toString() != null)
            {
              this.perlengkapanPerabotan = "<b>Perlengkapan/Perabotan:</b> " + res['data']['miscDetails']['perlengkapanPerabotan'].toString();
            }

            if(res['data']['miscDetails']['dayaListrik'].toString() == "" || res['data']['miscDetails']['dayaListrik'].toString() == null)
            {
              this.dayaListrik = "<b>Daya Listrik:</b> -";
            }

            if(res['data']['miscDetails']['dayaListrik'].toString() != "" && res['data']['miscDetails']['dayaListrik'].toString() != null)
            {
              this.dayaListrik = "<b>Daya Listrik:</b> " + res['data']['miscDetails']['dayaListrik'].toString() + " watt";
            }

            // info pengembang (developer) rumah

            this.developerName = "<b>Nama:</b> " + res['data']['developerInfo']['developerName'].toString();
            this.developerEmail = "<b>Email:</b> " + res['data']['developerInfo']['developerEmail'].toString();
            this.developerWhatsAppNumber = "<b>Nomor WhatsApp:</b> " + res['data']['developerInfo']['developerWhatsApp']['number'].toString();
            this.developerWhatsAppButtonSimpleText = res['data']['developerInfo']['developerWhatsApp']['clickTrackingParams']['simpleText'];
            this.developerWhatsAppUrl = res['data']['developerInfo']['developerWhatsApp']['clickTrackingParams']['href'];

            //

            this.imagesKatalogArray = res['data']['katalogImagesData'];

            for(let jumlahGambar = 0; jumlahGambar < this.imagesKatalogArray.length; jumlahGambar++)
            {
              let tempArrayGambar = {
                path: res['data']['katalogImagesData'][jumlahGambar]['imagesUrl']
              };

              let tempArrayGambarImageObject = {
                image: res['data']['katalogImagesData'][jumlahGambar]['imagesUrl']
              };

              this.imagesKatalogArrayParsed.push(tempArrayGambar);
              this.imageObject.push(tempArrayGambarImageObject);
            }

            console.log(this.imagesKatalogArrayParsed);

            this.isThisKatalogBookmarked = res['data']['isThisKatalogBookmarked'];
            this.bookmarkListingID = res['data']['bookmarkListingID'];

            // AR VIEW GET INFO

            this.isUseAR = res['data']['useAR'];

            if(this.isUseAR == 1)
            {
              this.arDataArray = res['data']['arData'];
              this.objectFileURL = res['data']['arData']['objectFileURL'];
              this.objectFileDiffuseTextureURL = res['data']['arData']['objectFileDiffuseTextureURL'];
              this.linkARMarker = res['data']['arData']['markerUrl'];
            }

            if(this.isUseAR != 1)
            {
              this.arDataArray = [];
              this.objectFileURL = "";
              this.objectFileDiffuseTextureURL = "";
              this.linkARMarker = "";
            }

            //

            this.useYouTubeVideoUrl = res['data']['useYouTubeVideoUrl'];

            if(this.useYouTubeVideoUrl == 1)
            {
              this.katalogVideoDataVideoUrl = res['data']['katalogVideoData']['videoUrl'];

              console.log(this.katalogVideoDataVideoUrl);
            }

            if(this.useYouTubeVideoUrl != 1)
            {
              this.katalogVideoDataVideoUrl = "";

              console.log(this.katalogVideoDataVideoUrl);
            }

            this.katalogRumahDitemukan = true;

            this.loadedAPI = true;
          }
        },
        err => {
          console.log("Error occured");

          if(err['status'] == 401)
          {
            this.refreshToken();
          }

          if(err['status'] != 401)
          {
            this.presentToast("Terjadi kesalahan, silakan coba lagi beberapa saat!", 3000);
          }
        }
      );
  }

  redirectContactPengembangWA()
  {
    window.open(this.developerWhatsAppUrl, '_system');
  }

  showLightbox(index)
  {
    this.selectedImageIndex = index;
    this.showFlag = true;
    this.imageSlideShowable = false;
  }

  closeEventHandler()
  {
    this.showFlag = false;
    this.currentIndex = -1;
    this.imageSlideShowable = true;
  }

  addToBookmarkKatalog()
  {
    this.http.post(baseUrlData.apiV1 + 'bookmark/addBookmark', {katalogUUID: this.katalogUUIDGet}, {
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
            this.presentToast(res['message'], 3000);
          }

          if(res['status'] == true)
          {
            this.isThisKatalogBookmarked = 1;
            this.presentToast(res['message'], 3000);
          }
        },
        err => {
          console.log("Error occured");

          if(err['status'] == 401)
          {
            this.refreshToken();
          }

          if(err['status'] != 401)
          {
            this.presentToast("Terjadi kesalahan, silakan coba lagi beberapa saat!", 3000);
          }
        }
      );
  }

  deleteFromBookmarkKatalog()
  {
    this.http.post(baseUrlData.apiV1 + 'bookmark/deleteBookmark', {useBookmarkListingID: 0, bookmarkListingID: null, katalogUUID: this.katalogUUIDGet}, {
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
            this.presentToast(res['message'], 3000);
          }

          if(res['status'] == true)
          {
            this.isThisKatalogBookmarked = 0;
            this.presentToast(res['message'], 3000);
          }
        },
        err => {
          console.log("Error occured");

          if(err['status'] == 401)
          {
            this.refreshToken();
          }

          if(err['status'] != 401)
          {
            this.presentToast("Terjadi kesalahan, silakan coba lagi beberapa saat!", 3000);
          }
        }
      );
  }

  arButtonClickBtn()
  {
    if(this.arButtonClicked == false)
    {
      this.arButtonClicked = true;
      this.youtubeVideoButtonClicked = false;

      this.openModalRender3D();
    }
  }

  arButtonClickBtn2()
  {
    if(this.arButtonClicked == true)
    {
      this.arButtonClicked = false;
    }
  }

  youtubeVideoButtonClickBtn()
  {
    if(this.youtubeVideoButtonClicked == false)
    {
      this.youtubeVideoButtonClicked = true;
      this.arButtonClicked = false;
    }
  }

  youtubeVideoButtonClickBtn2()
  {
    if(this.youtubeVideoButtonClicked == true)
    {
      this.youtubeVideoButtonClicked = false;
    }
  }

  ngOnInit()
  {
    this.statusBar.overlaysWebView(false);
    this.statusBar.show();

    this.katalogUUIDGet = this.activatedRoute.snapshot.paramMap.get('katalogUUID');

    console.log(this.katalogUUIDGet);

    this.checkLoginSession();

    this.loadDataKatalogRumah();

    this.arButtonClicked = false;
    this.youtubeVideoButtonClicked = false;
  }
}
