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
  selector: 'app-notificationarea',
  templateUrl: './notificationarea.page.html',
  styleUrls: ['./notificationarea.page.scss'],
})
export class NotificationareaPage implements OnInit
{
  resultText = "";
  statusApi = true;
  
  noPengumumanFound = false;
  pengumumanLoaded = false;

  dataLength = 0;

  resultDataArray = [];
  tempArray = {};
  arrayFix = [];

  constructor(private router: Router, private activatedRoute: ActivatedRoute, private http: HttpClient, public loadingController: LoadingController, public toastController: ToastController, private statusBar: StatusBar) { }

  loadNotification()
  {
    console.log("OKE");

    this.http.get(baseUrlData.apiV1 + 'notification', {
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
            this.pengumumanLoaded = false;
            this.noPengumumanFound = false;
          }

          if(res['status'] == true)
          {
            console.log(res['data'].length);

            this.dataLength = res['data'].length;

            if(this.dataLength <= 0)
            {
              this.noPengumumanFound = true;
            }

            if(this.dataLength > 0)
            {
              this.noPengumumanFound = false;
              this.resultDataArray = res['data'];

              for(let i = 0; i < this.resultDataArray.length; i++)
              {
                //console.log(this.resultDataArray[i]);

                this.tempArray = {
                  titlePengumuman: this.resultDataArray[i]['titlePengumuman'],
                  contentPengumuman: this.resultDataArray[i]['contentPengumuman'],
                  realDate: this.resultDataArray[i]['realDate'],
                  collapsed: true
                };

                this.arrayFix.push(this.tempArray);

              }

              //console.log(this.resultDataArray);

              console.log(JSON.stringify(this.arrayFix));

              localStorage.setItem("bookmarkValueJSON", JSON.stringify(this.arrayFix));
            }

            this.pengumumanLoaded = true;
          }
        },
        err => {
          console.log("Error occured");
          this.pengumumanLoaded = false;
          this.noPengumumanFound = false;
        }
      );
  }

  ngOnInit():void
  {
    this.statusBar.overlaysWebView(false);
    this.statusBar.show();

    this.loadNotification();
  }
}