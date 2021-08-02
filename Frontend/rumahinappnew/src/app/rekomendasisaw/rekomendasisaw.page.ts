import { Component, OnInit, ViewEncapsulation } from '@angular/core';
import { StatusBar } from '@ionic-native/status-bar/ngx';
import { ActivatedRoute } from '@angular/router';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { mimeData } from '../../environments/environment';
import { baseAuthData } from '../../environments/environment';
import { baseUrlData } from '../../environments/environment';
import { LoadingController } from '@ionic/angular';
import { Router } from '@angular/router';
import { ToastController } from '@ionic/angular';
import { ModalController } from '@ionic/angular';

import { RekomendasisawlokasiprefPage } from '../rekomendasisawlokasipref/rekomendasisawlokasipref.page';
import { RekomendasisawjenisrumahprefPage } from '../rekomendasisawjenisrumahpref/rekomendasisawjenisrumahpref.page';

import { KriteriasawfilteringpopupPage } from '../kriteriasawfilteringpopup/kriteriasawfilteringpopup.page';

@Component({
  selector: 'app-rekomendasisaw',
  templateUrl: './rekomendasisaw.page.html',
  styleUrls: ['./rekomendasisaw.page.scss'],
  encapsulation: ViewEncapsulation.None
})
export class RekomendasisawPage implements OnInit {

  resultText = "";
  statusApi = true;

  profileLoaded = false;

  namaLengkap = "";

  //
  hargaIsBenefit = false;
  luasTanahIsBenefit = true;
  luasBangunanIsBenefit = true;
  jumlahKamarMandiIsBenefit = true;
  jumlahKamarTidurIsBenefit = true;
  jumlahRuangTamuIsBenefit = true;
  jumlahGarasiIsBenefit = true;
  jumlahRuangKeluargaIsBenefit = true;
  jumlahRuangMakanIsBenefit = true;
  jumlahGudangIsBenefit = true;
  jumlahSerambiIsBenefit = true;
  jumlahTingkatIsBenefit = true;
  jumlahDapurIsBenefit = true;
  totalViewedIsBenefit = true;
  //

  //
  harga = "5";
  luasTanah = "5";
  luasBangunan = "5";
  jumlahKamarMandi = "5";
  jumlahKamarTidur = "5";
  jumlahRuangTamu = "5";
  jumlahGarasi = "5";
  jumlahRuangKeluarga = "5";
  jumlahRuangMakan = "5";
  jumlahGudang = "5";
  jumlahSerambi = "5";
  jumlahTingkat = "5";
  jumlahDapur = "5";
  totalViewed = "5";
  //

  resultAPIRekomendasiRumahTinggalSukses = false;
  noKatalogFound = false;
  arrayDataRumah = [];

  errorOthers = false;

  useFilterTambahanCheckbox = false;

  //

  public locationPref = "";
  public jenisRumahPref = "";

  //

  currentProvinsiID = "";
  currentKabupatenID = "";
  currentKecamatanID = "";
  currentDesaID = "";

  catalogTypeCurrent = "";

  //

  hargaUsedByUser = false;
  luasTanahUsedByUser = false;
  luasBangunanUsedByUser = false;
  jumlahKamarMandiUsedByUser = false;
  jumlahKamarTidurUsedByUser = false;
  jumlahRuangTamuUsedByUser = false;
  jumlahGarasiUsedByUser = false;
  jumlahRuangKeluargaUsedByUser = false;
  jumlahRuangMakanUsedByUser = false;
  jumlahDapurUsedByUser = false;
  jumlahGudangUsedByUser = false;
  jumlahSerambiUsedByUser = false;
  jumlahTingkatUsedByUser = false;
  totalViewedUsedByUser = false;

  //

  columnNgxTable = [];
  rowNgxTable = [];
  datatableShow = false;

  dataPayloadSAW = [];

  dataSAWNotFound = true;

  useDataWhenKriteriaPopupClosed = false;

  constructor(private router: Router, private activatedRoute: ActivatedRoute, private http: HttpClient, public loadingController: LoadingController, public toastController: ToastController, private statusBar: StatusBar, private modalController: ModalController)
  {
    this.columnNgxTable = [
      
    ];

    this.rowNgxTable = [
      
  ];
  }

  async presentToast(toastText: string, durationToast: number) {
    const toast = await this.toastController.create({
      message: toastText,
      duration: durationToast
    });
    toast.present();
  }

  async openModalPrefLokasi()
  {
    const modal = await this.modalController.create({
      component: RekomendasisawlokasiprefPage
    });

    modal.onDidDismiss()
      .then((data) => {
        console.log(data);
        this.loadCurrentPreferencesLokasi();
    });

    return await modal.present();
  }

  async openModalPrefJenisRumah()
  {
    const modal = await this.modalController.create({
      component: RekomendasisawjenisrumahprefPage
    });

    modal.onDidDismiss()
      .then((data) => {
        console.log(data);
        this.loadCurrentPreferencesJenisRumah();
    });

    return await modal.present();
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

  showDatatables()
  {
    this.datatableShow = true;
  }

  async bukaFilterKriteria()
  {
    const modal = await this.modalController.create({
      component: KriteriasawfilteringpopupPage
    });

    modal.onDidDismiss()
      .then((data) => {
        console.log(data);

        this.dataPayloadSAW = [];

        //
        let tempArrayData: any;
        tempArrayData = data['data'];

        //console.log(tempArrayData.length);

        let setTempCriteriaName = "";
        let setTempUseThisCriteria = false;
        
        for(let loopTempData = 0; loopTempData < tempArrayData.length; loopTempData++)
        {
          setTempCriteriaName = tempArrayData[loopTempData]['criteriaAttr'];

          if(setTempCriteriaName == "harga")
          {
            let tempAddArrayColumnDataTable = {
              name: 'Harga'
            };

            this.columnNgxTable.push(tempAddArrayColumnDataTable);

            setTempUseThisCriteria = tempArrayData[loopTempData]['useThisCriteria'];

            this.hargaUsedByUser = setTempUseThisCriteria;

            if(this.hargaUsedByUser == true)
            {
              let tempAdd = {
                criteriaAttr: 'harga',
                isBenefit: this.hargaIsBenefit,
                priorityLevel: parseInt(this.harga),
                positionOrder: 1
              };

              this.dataPayloadSAW.push(tempAdd);
            }
          }

          if(setTempCriteriaName == "luasTanah")
          {
            let tempAddArrayColumnDataTable = {
              name: 'Luas Tanah'
            };

            this.columnNgxTable.push(tempAddArrayColumnDataTable);

            setTempUseThisCriteria = tempArrayData[loopTempData]['useThisCriteria'];

            this.luasTanahUsedByUser = setTempUseThisCriteria;

            if(this.luasTanahUsedByUser == true)
            {
              let tempAdd = {
                criteriaAttr: 'luasTanah',
                isBenefit: this.luasTanahIsBenefit,
                priorityLevel: parseInt(this.luasTanah),
                positionOrder: 2
              };

              this.dataPayloadSAW.push(tempAdd);
            }
          }

          if(setTempCriteriaName == "luasBangunan")
          {
            let tempAddArrayColumnDataTable = {
              name: 'Luas Bangunan'
            };

            this.columnNgxTable.push(tempAddArrayColumnDataTable);

            setTempUseThisCriteria = tempArrayData[loopTempData]['useThisCriteria'];

            this.luasBangunanUsedByUser = setTempUseThisCriteria;

            if(this.luasBangunanUsedByUser == true)
            {
              let tempAdd = {
                criteriaAttr: 'luasBangunan',
                isBenefit: this.luasBangunanIsBenefit,
                priorityLevel: parseInt(this.luasBangunan),
                positionOrder: 3
              };

              this.dataPayloadSAW.push(tempAdd);
            }
          }

          if(setTempCriteriaName == "jumlahKamarMandi")
          {
            let tempAddArrayColumnDataTable = {
              name: 'Jumlah Kamar Mandi'
            };

            this.columnNgxTable.push(tempAddArrayColumnDataTable);

            setTempUseThisCriteria = tempArrayData[loopTempData]['useThisCriteria'];

            this.jumlahKamarMandiUsedByUser = setTempUseThisCriteria;

            if(this.jumlahKamarMandiUsedByUser == true)
            {
              let tempAdd = {
                criteriaAttr: 'jumlahKamarMandi',
                isBenefit: this.jumlahKamarMandiIsBenefit,
                priorityLevel: parseInt(this.jumlahKamarMandi),
                positionOrder: 4
              };

              this.dataPayloadSAW.push(tempAdd);
            }
          }

          if(setTempCriteriaName == "jumlahKamarTidur")
          {
            let tempAddArrayColumnDataTable = {
              name: 'Jumlah Kamar Tidur'
            };

            this.columnNgxTable.push(tempAddArrayColumnDataTable);

            setTempUseThisCriteria = tempArrayData[loopTempData]['useThisCriteria'];

            this.jumlahKamarTidurUsedByUser = setTempUseThisCriteria;

            if(this.jumlahKamarTidurUsedByUser == true)
            {
              let tempAdd = {
                criteriaAttr: 'jumlahKamarTidur',
                isBenefit: this.jumlahKamarTidurIsBenefit,
                priorityLevel: parseInt(this.jumlahKamarTidur),
                positionOrder: 5
              };

              this.dataPayloadSAW.push(tempAdd);
            }
          }

          if(setTempCriteriaName == "jumlahRuangTamu")
          {
            let tempAddArrayColumnDataTable = {
              name: 'Jumlah Ruang Tamu'
            };

            this.columnNgxTable.push(tempAddArrayColumnDataTable);

            setTempUseThisCriteria = tempArrayData[loopTempData]['useThisCriteria'];

            this.jumlahRuangTamuUsedByUser = setTempUseThisCriteria;

            if(this.jumlahRuangTamuUsedByUser == true)
            {
              let tempAdd = {
                criteriaAttr: 'jumlahRuangTamu',
                isBenefit: this.jumlahRuangTamuIsBenefit,
                priorityLevel: parseInt(this.jumlahRuangTamu),
                positionOrder: 6
              };

              this.dataPayloadSAW.push(tempAdd);
            }
          }

          if(setTempCriteriaName == "jumlahGarasi")
          {
            let tempAddArrayColumnDataTable = {
              name: 'Jumlah Garasi'
            };

            this.columnNgxTable.push(tempAddArrayColumnDataTable);

            setTempUseThisCriteria = tempArrayData[loopTempData]['useThisCriteria'];

            this.jumlahGarasiUsedByUser = setTempUseThisCriteria;

            if(this.jumlahGarasiUsedByUser == true)
            {
              let tempAdd = {
                criteriaAttr: 'jumlahGarasi',
                isBenefit: this.jumlahGarasiIsBenefit,
                priorityLevel: parseInt(this.jumlahGarasi),
                positionOrder: 7
              };

              this.dataPayloadSAW.push(tempAdd);
            }
          }

          if(setTempCriteriaName == "jumlahRuangKeluarga")
          {
            let tempAddArrayColumnDataTable = {
              name: 'Jumlah Ruang Keluarga'
            };

            this.columnNgxTable.push(tempAddArrayColumnDataTable);

            setTempUseThisCriteria = tempArrayData[loopTempData]['useThisCriteria'];

            this.jumlahRuangKeluargaUsedByUser = setTempUseThisCriteria;

            if(this.jumlahRuangKeluargaUsedByUser == true)
            {
              let tempAdd = {
                criteriaAttr: 'jumlahRuangKeluarga',
                isBenefit: this.jumlahRuangKeluargaIsBenefit,
                priorityLevel: parseInt(this.jumlahRuangKeluarga),
                positionOrder: 8
              };

              this.dataPayloadSAW.push(tempAdd);
            }
          }

          if(setTempCriteriaName == "jumlahRuangMakan")
          {
            let tempAddArrayColumnDataTable = {
              name: 'Jumlah Ruang Makan'
            };

            this.columnNgxTable.push(tempAddArrayColumnDataTable);

            setTempUseThisCriteria = tempArrayData[loopTempData]['useThisCriteria'];

            this.jumlahRuangMakanUsedByUser = setTempUseThisCriteria;

            if(this.jumlahRuangMakanUsedByUser == true)
            {
              let tempAdd = {
                criteriaAttr: 'jumlahRuangMakan',
                isBenefit: this.jumlahRuangMakanIsBenefit,
                priorityLevel: parseInt(this.jumlahRuangMakan),
                positionOrder: 9
              };

              this.dataPayloadSAW.push(tempAdd);
            }
          }

          if(setTempCriteriaName == "jumlahGudang")
          {
            let tempAddArrayColumnDataTable = {
              name: 'Jumlah Gudang'
            };

            this.columnNgxTable.push(tempAddArrayColumnDataTable);

            setTempUseThisCriteria = tempArrayData[loopTempData]['useThisCriteria'];

            this.jumlahGudangUsedByUser = setTempUseThisCriteria;

            if(this.jumlahGudangUsedByUser == true)
            {
              let tempAdd = {
                criteriaAttr: 'jumlahGudang',
                isBenefit: this.jumlahGudangIsBenefit,
                priorityLevel: parseInt(this.jumlahGudang),
                positionOrder: 10
              };

              this.dataPayloadSAW.push(tempAdd);
            }
          }

          if(setTempCriteriaName == "jumlahSerambi")
          {
            let tempAddArrayColumnDataTable = {
              name: 'Jumlah Serambi'
            };

            this.columnNgxTable.push(tempAddArrayColumnDataTable);

            setTempUseThisCriteria = tempArrayData[loopTempData]['useThisCriteria'];

            this.jumlahSerambiUsedByUser = setTempUseThisCriteria;

            if(this.jumlahSerambiUsedByUser == true)
            {
              let tempAdd = {
                criteriaAttr: 'jumlahSerambi',
                isBenefit: this.jumlahSerambiIsBenefit,
                priorityLevel: parseInt(this.jumlahSerambi),
                positionOrder: 11
              };

              this.dataPayloadSAW.push(tempAdd);
            }
          }

          if(setTempCriteriaName == "jumlahTingkat")
          {
            let tempAddArrayColumnDataTable = {
              name: 'Jumlah Tingkat'
            };

            this.columnNgxTable.push(tempAddArrayColumnDataTable);

            setTempUseThisCriteria = tempArrayData[loopTempData]['useThisCriteria'];

            this.jumlahTingkatUsedByUser = setTempUseThisCriteria;

            if(this.jumlahTingkatUsedByUser == true)
            {
              let tempAdd = {
                criteriaAttr: 'jumlahTingkat',
                isBenefit: this.jumlahTingkatIsBenefit,
                priorityLevel: parseInt(this.jumlahTingkat),
                positionOrder: 12
              };

              this.dataPayloadSAW.push(tempAdd);
            }
          }

          if(setTempCriteriaName == "jumlahDapur")
          {
            let tempAddArrayColumnDataTable = {
              name: 'Jumlah Dapur'
            };

            this.columnNgxTable.push(tempAddArrayColumnDataTable);

            setTempUseThisCriteria = tempArrayData[loopTempData]['useThisCriteria'];

            this.jumlahDapurUsedByUser = setTempUseThisCriteria;

            if(this.jumlahDapurUsedByUser == true)
            {
              let tempAdd = {
                criteriaAttr: 'jumlahDapur',
                isBenefit: this.jumlahDapurIsBenefit,
                priorityLevel: parseInt(this.jumlahDapur),
                positionOrder: 13
              };

              this.dataPayloadSAW.push(tempAdd);
            }
          }

          if(setTempCriteriaName == "totalViewed")
          {
            let tempAddArrayColumnDataTable = {
              name: 'Jumlah Dilihat'
            };

            this.columnNgxTable.push(tempAddArrayColumnDataTable);

            setTempUseThisCriteria = tempArrayData[loopTempData]['useThisCriteria'];

            this.totalViewedUsedByUser = setTempUseThisCriteria;

            if(this.totalViewedUsedByUser == true)
            {
              let tempAdd = {
                criteriaAttr: 'totalViewed',
                isBenefit: this.totalViewedIsBenefit,
                priorityLevel: parseInt(this.totalViewed),
                positionOrder: 14
              };

              this.dataPayloadSAW.push(tempAdd);
            }
          }
        }

        if(this.dataPayloadSAW.length <= 0)
        {
          this.dataSAWNotFound = true;
        }

        if(this.dataPayloadSAW.length > 0)
        {
          this.dataSAWNotFound = false;
        }
    });

    return await modal.present();
  }

  cariRekomendasiRumahTinggal()
  {
    this.errorOthers = false;

    let jsonRequestBodyPayload = {};

    /*let jsonRequestBodyPayload = {
      data: [
        {
          criteriaAttr: 'harga',
          isBenefit: this.hargaIsBenefit,
          priorityLevel: parseInt(this.harga),
          positionOrder: 1
        },
        {
          criteriaAttr: 'luasTanah',
          isBenefit: this.luasTanahIsBenefit,
          priorityLevel: parseInt(this.luasTanah),
          positionOrder: 2
        },
        {
          criteriaAttr: 'luasBangunan',
          isBenefit: this.luasBangunanIsBenefit,
          priorityLevel: parseInt(this.luasBangunan),
          positionOrder: 3
        },
        {
          criteriaAttr: 'jumlahKamarMandi',
          isBenefit: this.jumlahKamarMandiIsBenefit,
          priorityLevel: parseInt(this.jumlahKamarMandi),
          positionOrder: 4
        },
        {
          criteriaAttr: 'jumlahKamarTidur',
          isBenefit: this.jumlahKamarTidurIsBenefit,
          priorityLevel: parseInt(this.jumlahKamarTidur),
          positionOrder: 5
        },
        {
          criteriaAttr: 'jumlahRuangTamu',
          isBenefit: this.jumlahRuangTamuIsBenefit,
          priorityLevel: parseInt(this.jumlahRuangTamu),
          positionOrder: 6
        },
        {
          criteriaAttr: 'jumlahGarasi',
          isBenefit: this.jumlahGarasiIsBenefit,
          priorityLevel: parseInt(this.jumlahGarasi),
          positionOrder: 7
        },
        {
          criteriaAttr: 'jumlahRuangKeluarga',
          isBenefit: this.jumlahRuangKeluargaIsBenefit,
          priorityLevel: parseInt(this.jumlahRuangKeluarga),
          positionOrder: 8
        },
        {
          criteriaAttr: 'jumlahRuangMakan',
          isBenefit: this.jumlahRuangMakanIsBenefit,
          priorityLevel: parseInt(this.jumlahRuangMakan),
          positionOrder: 9
        },
        {
          criteriaAttr: 'jumlahGudang',
          isBenefit: this.jumlahGudangIsBenefit,
          priorityLevel: parseInt(this.jumlahGudang),
          positionOrder: 10
        },
        {
          criteriaAttr: 'jumlahSerambi',
          isBenefit: this.jumlahSerambiIsBenefit,
          priorityLevel: parseInt(this.jumlahSerambi),
          positionOrder: 11
        },
        {
          criteriaAttr: 'jumlahTingkat',
          isBenefit: this.jumlahTingkatIsBenefit,
          priorityLevel: parseInt(this.jumlahTingkat),
          positionOrder: 12
        },
        {
          criteriaAttr: 'jumlahDapur',
          isBenefit: this.jumlahDapurIsBenefit,
          priorityLevel: parseInt(this.jumlahDapur),
          positionOrder: 13
        },
        {
          criteriaAttr: 'totalViewed',
          isBenefit: this.totalViewedIsBenefit,
          priorityLevel: parseInt(this.totalViewed),
          positionOrder: 14
        }
      ],
      useAdditionalPreferences: this.useFilterTambahanCheckbox,
      additionalPreferencesContext: [
        {
          service: 'REGION_PREFERENCES',
          //enableServiceFiltering: true,
          params: [
            {
              key: 'provinsi_id',
              value: this.currentProvinsiID
            },
            {
              key: 'kabupaten_id',
              value: this.currentKabupatenID
            },
            {
              key: 'kecamatan_id',
              value: this.currentKecamatanID
            },
            {
              key: 'desa_id',
              value: this.currentDesaID
            }
          ]
        },
        {
          service: 'CATALOG_PREFERENCES',
          //enableServiceFiltering: true,
          params: [
            {
              key: 'CATALOG_TYPE_PREFERENCES',
              value: this.catalogTypeCurrent
            }
          ]
        }
      ]
    };*/

    //

      jsonRequestBodyPayload = {
      data: this.dataPayloadSAW,
      useAdditionalPreferences: this.useFilterTambahanCheckbox,
      additionalPreferencesContext: [
        {
          service: 'REGION_PREFERENCES',
          //enableServiceFiltering: true,
          params: [
            {
              key: 'provinsi_id',
              value: this.currentProvinsiID
            },
            {
              key: 'kabupaten_id',
              value: this.currentKabupatenID
            },
            {
              key: 'kecamatan_id',
              value: this.currentKecamatanID
            },
            {
              key: 'desa_id',
              value: this.currentDesaID
            }
          ]
        },
        {
          service: 'CATALOG_PREFERENCES',
          //enableServiceFiltering: true,
          params: [
            {
              key: 'CATALOG_TYPE_PREFERENCES',
              value: this.catalogTypeCurrent
            }
          ]
        }
      ]
    };

    //

    this.presentLoading().then(() => {

      this.http.post(baseUrlData.apiV1 + 'search/recommendation', jsonRequestBodyPayload, {
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
            this.resultAPIRekomendasiRumahTinggalSukses = res['status'];
  
            if(this.resultAPIRekomendasiRumahTinggalSukses == false)
            {
              console.log("Error occured");
              this.arrayDataRumah = [];
              this.resultAPIRekomendasiRumahTinggalSukses = false;
              this.noKatalogFound = true;
  
              this.dismissLoading();
              
              this.presentToast(res['message'], 3000);
            }
  
            if(this.resultAPIRekomendasiRumahTinggalSukses == true)
            {
              let rumahDataLength = res['data'].length;
  
              console.log(rumahDataLength);
  
              if(rumahDataLength <= 0)
              {
                console.log("Tidak ada hasilnya");
  
                this.noKatalogFound = true;
                this.arrayDataRumah = [];
                this.resultAPIRekomendasiRumahTinggalSukses = true;
  
                this.dismissLoading();
              }
  
              if(rumahDataLength > 0)
              {
                console.log("Ada hasilnya");
  
                this.noKatalogFound = false;
                this.arrayDataRumah = res['data'];
                //this.arrayDataRumah = [];
                this.resultAPIRekomendasiRumahTinggalSukses = true;
  
                this.dismissLoading();
              }
            }
          },
          err => {
            this.dismissLoading();
  
            console.log("Error occured");
            this.arrayDataRumah = [];
            this.resultAPIRekomendasiRumahTinggalSukses = false;
            this.noKatalogFound = true;

            this.presentToast("Wah, tampaknya terjadi kesalahan. Silakan coba lagi ya!", 3000);
  
            if(err['status'] == 401)
            {
              this.dismissLoading();
  
              this.router.navigate(['/login']);
            }

            if(err['status'] != 401)
            {
              this.errorOthers = true;
            }
          }
        );

    });
  }

  viewKatalog(datanya)
  {
    console.log(datanya);

    this.router.navigate(['/katalogdetailview/' + datanya]);
  }

  hargaChange(eventnya)
  {
    console.log(eventnya);

    if(parseInt(eventnya) > 5)
    {
      this.harga = "5";
    }

    if(parseInt(eventnya) <= 0)
    {
      this.harga = "1";
    }

    this.controlSAWFilter();
  }

  luasTanahChange(eventnya)
  {
    console.log(eventnya);

    if(parseInt(eventnya) > 5)
    {
      this.luasTanah = "5";
    }

    if(parseInt(eventnya) <= 0)
    {
      this.luasTanah = "1";
    }

    this.controlSAWFilter();
  }

  luasBangunanChange(eventnya)
  {
    console.log(eventnya);

    if(parseInt(eventnya) > 5)
    {
      this.luasBangunan = "5";
    }

    if(parseInt(eventnya) <= 0)
    {
      this.luasBangunan = "1";
    }

    this.controlSAWFilter();
  }

  jumlahKamarMandiChange(eventnya)
  {
    console.log(eventnya);

    if(parseInt(eventnya) > 5)
    {
      this.jumlahKamarMandi = "5";
    }

    if(parseInt(eventnya) <= 0)
    {
      this.jumlahKamarMandi = "1";
    }

    this.controlSAWFilter();
  }

  jumlahKamarTidurChange(eventnya)
  {
    console.log(eventnya);

    if(parseInt(eventnya) > 5)
    {
      this.jumlahKamarTidur = "5";
    }

    if(parseInt(eventnya) <= 0)
    {
      this.jumlahKamarTidur = "1";
    }

    this.controlSAWFilter();
  }

  jumlahRuangTamuChange(eventnya)
  {
    console.log(eventnya);

    if(parseInt(eventnya) > 5)
    {
      this.jumlahRuangTamu = "5";
    }

    if(parseInt(eventnya) <= 0)
    {
      this.jumlahRuangTamu = "1";
    }

    this.controlSAWFilter();
  }

  jumlahGarasiChange(eventnya)
  {
    console.log(eventnya);

    if(parseInt(eventnya) > 5)
    {
      this.jumlahGarasi = "5";
    }

    if(parseInt(eventnya) <= 0)
    {
      this.jumlahGarasi = "1";
    }

    this.controlSAWFilter();
  }

  jumlahRuangKeluargaChange(eventnya)
  {
    console.log(eventnya);

    if(parseInt(eventnya) > 5)
    {
      this.jumlahRuangKeluarga = "5";
    }

    if(parseInt(eventnya) <= 0)
    {
      this.jumlahRuangKeluarga = "1";
    }

    this.controlSAWFilter();
  }

  jumlahRuangMakanChange(eventnya)
  {
    console.log(eventnya);

    if(parseInt(eventnya) > 5)
    {
      this.jumlahRuangMakan = "5";
    }

    if(parseInt(eventnya) <= 0)
    {
      this.jumlahRuangMakan = "1";
    }

    this.controlSAWFilter();
  }

  jumlahDapurChange(eventnya)
  {
    console.log(eventnya);

    if(parseInt(eventnya) > 5)
    {
      this.jumlahDapur = "5";
    }

    if(parseInt(eventnya) <= 0)
    {
      this.jumlahDapur = "1";
    }

    this.controlSAWFilter();
  }

  jumlahGudangChange(eventnya)
  {
    console.log(eventnya);

    if(parseInt(eventnya) > 5)
    {
      this.jumlahGudang = "5";
    }

    if(parseInt(eventnya) <= 0)
    {
      this.jumlahGudang = "1";
    }

    this.controlSAWFilter();
  }

  jumlahSerambiChange(eventnya)
  {
    console.log(eventnya);

    if(parseInt(eventnya) > 5)
    {
      this.jumlahSerambi = "5";
    }

    if(parseInt(eventnya) <= 0)
    {
      this.jumlahSerambi = "1";
    }

    this.controlSAWFilter();
  }

  jumlahTingkatChange(eventnya)
  {
    console.log(eventnya);

    if(parseInt(eventnya) > 5)
    {
      this.jumlahTingkat = "5";
    }

    if(parseInt(eventnya) <= 0)
    {
      this.jumlahTingkat = "1";
    }

    this.controlSAWFilter();
  }

  totalViewedChange(eventnya)
  {
    console.log(eventnya);

    if(parseInt(eventnya) > 5)
    {
      this.totalViewed = "5";
    }

    if(parseInt(eventnya) <= 0)
    {
      this.totalViewed = "1";
    }

    this.controlSAWFilter();
  }

  onUseFilterTambahanCheckbox(event)
  {
    console.log(event);

    this.useFilterTambahanCheckbox = event;
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

          if(err['status'] == 401)
          {
            this.router.navigate(['/login']);
          }
        }
      );
  }

  loadCurrentPreferencesLokasi()
  {
    this.http.get(baseUrlData.apiV1 + 'preferences/getCatalogLocation', {
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
            this.locationPref = "";
            this.currentDesaID = "";
            this.currentKecamatanID = "";
            this.currentKabupatenID = "";
            this.currentProvinsiID = "";
          }

          if(res['status'] == true)
          {
            this.locationPref = "";

            if(res['data']['desa']['nama'] != null && res['data']['desa']['nama'] != undefined)
            {
              this.locationPref = res['data']['desa']['nama'] + ", ";
              this.currentDesaID = res['data']['desa']['id'];
            }

            if(res['data']['kecamatan']['nama'] != null && res['data']['kecamatan']['nama'] != undefined)
            {
              this.locationPref += res['data']['kecamatan']['nama'] + ", ";
              this.currentKecamatanID = res['data']['kecamatan']['id'];
            }

            if(res['data']['kabupaten']['nama'] != null && res['data']['kabupaten']['nama'] != undefined)
            {
              this.locationPref += res['data']['kabupaten']['nama'] + ", ";
              this.currentKabupatenID = res['data']['kabupaten']['id'];
            }

            if(res['data']['provinsi']['nama'] != null && res['data']['provinsi']['nama'] != undefined)
            {
              this.locationPref += res['data']['provinsi']['nama'];
              this.currentProvinsiID = res['data']['provinsi']['id'];
            }
          }
        },
        err => {
          console.log("Error occured");
          this.locationPref = "";
          this.currentDesaID = "";
          this.currentKecamatanID = "";
          this.currentKabupatenID = "";
          this.currentProvinsiID = "";
        }
      );
  }

  loadCurrentPreferencesJenisRumah()
  {
    this.http.get(baseUrlData.apiV1 + 'preferences/getCatalogType', {
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
            this.jenisRumahPref = "";
            this.catalogTypeCurrent = "";
          }

          if(res['status'] == true)
          {
            this.jenisRumahPref = "";

            if(res['data']['catalogType']['namaPendek'] != null && res['data']['catalogType']['namaPendek'] != undefined)
            {
              this.jenisRumahPref = res['data']['catalogType']['namaPendek'];
              this.catalogTypeCurrent = res['data']['catalogType']['code'];
            }
          }
        },
        err => {
          console.log("Error occured");
          this.jenisRumahPref = "";
          this.catalogTypeCurrent = "";
        }
      );
  }

  controlSAWFilter()
  {
    //console.log("OK");

    let responseContextGet: any;
    let canContinue = false;

    if(localStorage.getItem("tempSAWFilteringData") != "" && localStorage.getItem("tempSAWFilteringData") != undefined)
    {
      responseContextGet = JSON.parse(window.atob(localStorage.getItem("tempSAWFilteringData")));
      canContinue = true;
    }
    
    //console.log(responseContextGet);

    let criteriaAttrNameTemp = "";
    let useThisCriteriaAttrTemp = false;

    this.dataPayloadSAW = [];

    if(canContinue == true)
    {
    for(let ok = 0; ok < responseContextGet.length; ok++)
    {
      criteriaAttrNameTemp = responseContextGet[ok]['criteriaAttr'];
      useThisCriteriaAttrTemp = responseContextGet[ok]['useThisCriteria'];

      if(criteriaAttrNameTemp == "harga")
      {
        this.hargaUsedByUser = useThisCriteriaAttrTemp;

        if(this.hargaUsedByUser == true)
        {
          this.dataSAWNotFound = false;

          let tempAdd = {
            criteriaAttr: 'harga',
            isBenefit: this.hargaIsBenefit,
            priorityLevel: parseInt(this.harga),
            positionOrder: 1
          };

          this.dataPayloadSAW.push(tempAdd);
        }
      }

      if(criteriaAttrNameTemp == "luasTanah")
      {
        this.luasTanahUsedByUser = useThisCriteriaAttrTemp;

        if(this.luasTanahUsedByUser == true)
        {
          this.dataSAWNotFound = false;

          let tempAdd = {
            criteriaAttr: 'luasTanah',
            isBenefit: this.luasTanahIsBenefit,
            priorityLevel: parseInt(this.luasTanah),
            positionOrder: 2
          };

          this.dataPayloadSAW.push(tempAdd);
        }
      }

      if(criteriaAttrNameTemp == "luasBangunan")
      {
        this.luasBangunanUsedByUser = useThisCriteriaAttrTemp;

        if(this.luasBangunanUsedByUser == true)
        {
          this.dataSAWNotFound = false;

          let tempAdd = {
            criteriaAttr: 'luasBangunan',
            isBenefit: this.luasBangunanIsBenefit,
            priorityLevel: parseInt(this.luasBangunan),
            positionOrder: 3
          };

          this.dataPayloadSAW.push(tempAdd);
        }
      }

      if(criteriaAttrNameTemp == "jumlahKamarMandi")
      {
        this.jumlahKamarMandiUsedByUser = useThisCriteriaAttrTemp;

        if(this.jumlahKamarMandiUsedByUser == true)
        {
          this.dataSAWNotFound = false;

          let tempAdd = {
            criteriaAttr: 'jumlahKamarMandi',
            isBenefit: this.jumlahKamarMandiIsBenefit,
            priorityLevel: parseInt(this.jumlahKamarMandi),
            positionOrder: 4
          };

          this.dataPayloadSAW.push(tempAdd);
        }
      }

      if(criteriaAttrNameTemp == "jumlahKamarTidur")
      {
        this.jumlahKamarTidurUsedByUser = useThisCriteriaAttrTemp;

        if(this.jumlahKamarTidurUsedByUser == true)
        {
          this.dataSAWNotFound = false;

          let tempAdd = {
            criteriaAttr: 'jumlahKamarTidur',
            isBenefit: this.jumlahKamarTidurIsBenefit,
            priorityLevel: parseInt(this.jumlahKamarTidur),
            positionOrder: 5
          };

          this.dataPayloadSAW.push(tempAdd);
        }
      }

      if(criteriaAttrNameTemp == "jumlahRuangTamu")
      {
        this.jumlahRuangTamuUsedByUser = useThisCriteriaAttrTemp;

        if(this.jumlahRuangTamuUsedByUser == true)
        {
          this.dataSAWNotFound = false;

          let tempAdd = {
            criteriaAttr: 'jumlahRuangTamu',
            isBenefit: this.jumlahRuangTamuIsBenefit,
            priorityLevel: parseInt(this.jumlahRuangTamu),
            positionOrder: 6
          };

          this.dataPayloadSAW.push(tempAdd);
        }
      }

      if(criteriaAttrNameTemp == "jumlahGarasi")
      {
        this.jumlahGarasiUsedByUser = useThisCriteriaAttrTemp;

        if(this.jumlahGarasiUsedByUser == true)
        {
          this.dataSAWNotFound = false;

          let tempAdd = {
            criteriaAttr: 'jumlahGarasi',
            isBenefit: this.jumlahGarasiIsBenefit,
            priorityLevel: parseInt(this.jumlahGarasi),
            positionOrder: 7
          };

          this.dataPayloadSAW.push(tempAdd);
        }
      }

      if(criteriaAttrNameTemp == "jumlahRuangKeluarga")
      {
        this.jumlahRuangKeluargaUsedByUser = useThisCriteriaAttrTemp;

        if(this.jumlahRuangKeluargaUsedByUser == true)
        {
          this.dataSAWNotFound = false;

          let tempAdd = {
            criteriaAttr: 'jumlahRuangKeluarga',
            isBenefit: this.jumlahRuangKeluargaIsBenefit,
            priorityLevel: parseInt(this.jumlahRuangKeluarga),
            positionOrder: 8
          };

          this.dataPayloadSAW.push(tempAdd);
        }
      }

      if(criteriaAttrNameTemp == "jumlahRuangMakan")
      {
        this.jumlahRuangMakanUsedByUser = useThisCriteriaAttrTemp;

        if(this.jumlahRuangMakanUsedByUser == true)
        {
          this.dataSAWNotFound = false;

          let tempAdd = {
            criteriaAttr: 'jumlahRuangMakan',
            isBenefit: this.jumlahRuangMakanIsBenefit,
            priorityLevel: parseInt(this.jumlahRuangMakan),
            positionOrder: 9
          };

          this.dataPayloadSAW.push(tempAdd);
        }
      }

      if(criteriaAttrNameTemp == "jumlahGudang")
      {
        this.jumlahGudangUsedByUser = useThisCriteriaAttrTemp;

        if(this.jumlahGudangUsedByUser == true)
        {
          this.dataSAWNotFound = false;

          let tempAdd = {
            criteriaAttr: 'jumlahGudang',
            isBenefit: this.jumlahGudangIsBenefit,
            priorityLevel: parseInt(this.jumlahGudang),
            positionOrder: 10
          };

          this.dataPayloadSAW.push(tempAdd);
        }
      }

      if(criteriaAttrNameTemp == "jumlahSerambi")
      {
        this.jumlahSerambiUsedByUser = useThisCriteriaAttrTemp;

        if(this.jumlahSerambiUsedByUser == true)
        {
          this.dataSAWNotFound = false;

          let tempAdd = {
            criteriaAttr: 'jumlahSerambi',
            isBenefit: this.jumlahSerambiIsBenefit,
            priorityLevel: parseInt(this.jumlahSerambi),
            positionOrder: 11
          };

          this.dataPayloadSAW.push(tempAdd);
        }
      }

      if(criteriaAttrNameTemp == "jumlahTingkat")
      {
        this.jumlahTingkatUsedByUser = useThisCriteriaAttrTemp;

        if(this.jumlahTingkatUsedByUser == true)
        {
          this.dataSAWNotFound = false;

          let tempAdd = {
            criteriaAttr: 'jumlahTingkat',
            isBenefit: this.jumlahTingkatIsBenefit,
            priorityLevel: parseInt(this.jumlahTingkat),
            positionOrder: 12
          };

          this.dataPayloadSAW.push(tempAdd);
        }
      }

      if(criteriaAttrNameTemp == "jumlahDapur")
      {
        this.jumlahDapurUsedByUser = useThisCriteriaAttrTemp;

        if(this.jumlahDapurUsedByUser == true)
        {
          this.dataSAWNotFound = false;

          let tempAdd = {
            criteriaAttr: 'jumlahDapur',
            isBenefit: this.jumlahDapurIsBenefit,
            priorityLevel: parseInt(this.jumlahDapur),
            positionOrder: 13
          };

          this.dataPayloadSAW.push(tempAdd);
        }
      }

      if(criteriaAttrNameTemp == "totalViewed")
      {
        this.totalViewedUsedByUser = useThisCriteriaAttrTemp;

        if(this.totalViewedUsedByUser == true)
        {
          this.dataSAWNotFound = false;

          let tempAdd = {
            criteriaAttr: 'totalViewed',
            isBenefit: this.totalViewedIsBenefit,
            priorityLevel: parseInt(this.totalViewed),
            positionOrder: 14
          };

          this.dataPayloadSAW.push(tempAdd);
        }
      }
    }
    }
  }

  ngOnInit()
  {
    this.statusBar.overlaysWebView(false);
    this.statusBar.show();

    this.loadProfile();
    this.loadCurrentPreferencesLokasi();
    this.loadCurrentPreferencesJenisRumah();

    this.controlSAWFilter();
  }
}
