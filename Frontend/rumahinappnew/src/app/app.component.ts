import { Component, OnInit } from '@angular/core';
import { Platform } from '@ionic/angular';
import { SplashScreen } from '@ionic-native/splash-screen/ngx';
import { StatusBar } from '@ionic-native/status-bar/ngx';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { mimeData } from '../environments/environment';
import { baseAuthData } from '../environments/environment';
import { baseUrlData } from '../environments/environment';
import { tokenData } from '../environments/environment';
import { Router } from '@angular/router';
import { AndroidPermissions } from '@ionic-native/android-permissions/ngx';

import { darkModeEnvironment } from '../environments/environment';

import { WebcamImage } from 'ngx-webcam';
import { Subject, Observable } from 'rxjs';

@Component({
  selector: 'app-root',
  templateUrl: 'app.component.html',
  styleUrls: ['app.component.scss'],
})
export class AppComponent implements OnInit {

  dark = false;
  statusApinya = true;

  hasWriteAccess = false;
  
  constructor(
    private platform: Platform,
    private splashScreen: SplashScreen,
    private statusBar: StatusBar,
    private http: HttpClient,
    private router: Router,
    private androidPermissions: AndroidPermissions
  ) {
    this.initializeApp();
    this.initializePreferColors();
  }

  initializePreferColors()
  {
    const prefersColor = window.matchMedia('(prefers-color-scheme: dark)');
    this.dark = prefersColor.matches;
    this.updateDarkMode();

    prefersColor.addEventListener(
      'change',
      mediaQuery => {
        this.dark = mediaQuery.matches;
        this.updateDarkMode();
      }
    );
  }

  updateDarkMode()
  {
    document.body.classList.toggle('dark', this.dark);
  }

  public webcamImage: WebcamImage = null;
  private trigger: Subject<void> = new Subject<void>();

  triggerSnapshot(): void
  {
   this.trigger.next();
  }

  handleImage(webcamImage: WebcamImage): void
  {
   console.info('Saved webcam image', webcamImage);
   this.webcamImage = webcamImage;
  }
   
  public get triggerObservable(): Observable<void>
  {
   return this.trigger.asObservable();
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
          this.statusApinya = res['status'];

          if(res['status'] == false)
          {
            this.router.navigate(['/login']);
          }

          if(res['status'] == true)
          {
            //this.router.navigate(['/dashboard']);
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

  doRefreshToken()
  {

  }

  ngOnInit(): void
  {
    this.androidPermissions.checkPermission(this.androidPermissions.PERMISSION.CAMERA).then(
      result => console.log('Has permission?',result.hasPermission),
      err => this.androidPermissions.requestPermission(this.androidPermissions.PERMISSION.CAMERA)
    );
    
    this.androidPermissions.requestPermissions([this.androidPermissions.PERMISSION.CAMERA, this.androidPermissions.PERMISSION.GET_ACCOUNTS]);

    // check permission save file

    this.androidPermissions
   .checkPermission(this.androidPermissions
   .PERMISSION.WRITE_EXTERNAL_STORAGE)
   .then((result) => {
    console.log('Has permission?',result.hasPermission);
    this.hasWriteAccess = result.hasPermission;
  },(err) => {
      this.androidPermissions
        .requestPermission(this.androidPermissions
        .PERMISSION.WRITE_EXTERNAL_STORAGE);
   });
   if (!this.hasWriteAccess) {
     this.androidPermissions
       .requestPermissions([this.androidPermissions
       .PERMISSION.WRITE_EXTERNAL_STORAGE]);
   }

    console.log("Siap");

    if(window.matchMedia('(prefers-color-scheme)').media !== 'not all')
    {
      console.log('🎉 Dark mode is supported');
      this.updateDarkMode();
    }

    else
    {
      console.log('🎉 Dark mode is not supported');
    }

    console.log(this.dark);
    this.checkLoginSession();
  }

  initializeApp() {
    this.platform.ready().then(() => {
      this.statusBar.styleDefault();
      this.statusBar.overlaysWebView(true);
      this.splashScreen.hide();
    });
  }
}