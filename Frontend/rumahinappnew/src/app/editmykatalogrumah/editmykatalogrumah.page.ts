import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, FormControl, Validators } from '@angular/forms';
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
  selector: 'app-editmykatalogrumah',
  templateUrl: './editmykatalogrumah.page.html',
  styleUrls: ['./editmykatalogrumah.page.scss'],
})
export class EditmykatalogrumahPage implements OnInit {

  currentKatalogUUID = "";
  currentTempEditArray = [];

  resultApiText = "";
  statusApi = true;

  katalogNameGet = "";
  katalogDescriptionDesc = "";
  luasTanahGet = "";
  luasBangunanGet = "";
  jumlahKamarMandiGet = "";
  jumlahKamarTidurGet = "";
  jumlahRuangTamuGet = "";
  jumlahGarasiGet = "";
  jumlahRuangKeluargaGet = "";
  jumlahRuangMakanGet = "";
  jumlahDapurGet = "";
  jumlahGudangGet = "";
  jumlahSerambiGet = "";
  jumlahTingkatGet = "";
  tahunDibuatGet = "";
  hargaGet = "";
  developerNameGet = "";
  developerEmailGet = "";
  contactNumberGet = "+628";
  alamatGet = "";
  sertifikatGet = "";
  isDisewakanGet = false;
  isSecondGet = false;
  dayaListrikGet = "";
  conditionMeasurementGet = "";
  perlengkapanPerabotanGet = "";
  fileKatalogImages:string [] = [];
  provinsiID = "";
  kabupatenID = "";
  kecamatanID = "";
  desaID = "";

  arrayProvinsiData = [];
  arrayKabupatenData = [];
  arrayKecamatanData = [];
  arrayDesaData = [];
  
  sertifikatCode = "";
  needManualInputSertifikatName = "0";
  needManualInputSertifikatNameInputOpen = false;
  arrayJenisSertifikat = [];

  rumahTipe = "";
  arrayKodeTipeRumah = [];

  isDisewakan = false;

  kondisiRumah = "";

  modeSewaValue = "";

  youtubeVideoKatalogGet = "";

  useARGet = false;

  showARInputForm = false;

  ARFBXFileGet = "";

  ARFBXFileDiffuseTextureGet = "";

  ARMarkerImageGet = "";

  uploadForm = new FormGroup({
    katalogName: new FormControl('', [Validators.required, Validators.minLength(10), Validators.maxLength(128)]),
    katalogDesc: new FormControl('', [Validators.required]),
    luasTanah: new FormControl('', []),
    luasBangunan: new FormControl('', []),
    jumlahKamarMandi: new FormControl('0', [Validators.required]),
    jumlahKamarTidur: new FormControl('0', [Validators.required]),
    jumlahRuangTamu: new FormControl('0', [Validators.required]),
    jumlahGarasi: new FormControl('0', [Validators.required]),
    jumlahRuangKeluarga: new FormControl('0', [Validators.required]),
    jumlahRuangMakan: new FormControl('0', [Validators.required]),
    jumlahDapur: new FormControl('0', [Validators.required]),
    jumlahGudang: new FormControl('0', [Validators.required]),
    jumlahSerambi: new FormControl('0', [Validators.required]),
    jumlahTingkat: new FormControl('1', [Validators.required]),
    tahunDibuat: new FormControl('', []),
    harga: new FormControl('', [Validators.required]),
    developerName: new FormControl('', [Validators.required, Validators.minLength(4), Validators.maxLength(64)]),
    //developerEmail: new FormControl('', [Validators.required, Validators.email]),
    developerEmail: new FormControl('', []),
    contactNumber: new FormControl('', [Validators.required]),
    alamat: new FormControl('', [Validators.required]),
    sertifikat: new FormControl('', [Validators.required]),
    dayaListrik: new FormControl('', []),
    conditionMeasurement: new FormControl('', [Validators.required]),
    perlengkapanPerabotan: new FormControl('', [Validators.required]),
    katalogImages: new FormControl('', [Validators.required]),
    provinsiSelectBox: new FormControl('', [Validators.required]),
    kabupatenSelectBox: new FormControl('', [Validators.required]),
    kecamatanSelectBox: new FormControl('', [Validators.required]),
    desaSelectBox: new FormControl('', [Validators.required]),
    sertifikatTypeSelectBox: new FormControl('', [Validators.required]),
    sertifikatNameInput: new FormControl('', [Validators.required]),
    rumahTipeSelectBox: new FormControl('', [Validators.required]),
    kondisiRumahSelectBox: new FormControl('', [Validators.required]),
    isDisewakanCheckBox: new FormControl('', []),
    modeSewa: new FormControl('', [Validators.required]),
    youtubeVideoKatalog: new FormControl('', []),
    useAR: new FormControl('', []),
    ARFBXFile: new FormControl('', [Validators.required]),
    ARFBXFileDiffuseTexture: new FormControl('', [Validators.required]),
    ARMarkerImage: new FormControl('', [Validators.required])
  });

  constructor(private formBuilder: FormBuilder, private router: Router, private activatedRoute: ActivatedRoute, private http: HttpClient, public loadingController: LoadingController, public toastController: ToastController, private statusBar: StatusBar)
  {
    //this.safeURL = this.sanitizer.sanitize(SecurityContext.RESOURCE_URL, this.sanitizer.bypassSecurityTrustResourceUrl(''));
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

  get formValidatornya()
  {
    return this.uploadForm.controls;
  }

  onKatalogNameChange(event)
  {
    this.katalogNameGet = event;
  }

  onKatalogDescriptionChange(event)
  {
    this.katalogDescriptionDesc = event;
  }

  onLuasTanahChange(event)
  {
    this.luasTanahGet = event;
  }

  onLuasBangunanChange(event)
  {
    this.luasBangunanGet = event;
  }

  onJumlahKamarMandiChange(event)
  {
    this.jumlahKamarMandiGet = event;
  }

  onJumlahKamarTidurChange(event)
  {
    this.jumlahKamarTidurGet = event;
  }

  onJumlahRuangTamuChange(event)
  {
    this.jumlahRuangTamuGet = event;
  }

  onJumlahGarasiChange(event)
  {
    this.jumlahGarasiGet = event;
  }

  onJumlahRuangKeluargaChange(event)
  {
    this.jumlahRuangKeluargaGet = event;
  }

  onJumlahRuangMakanChange(event)
  {
    this.jumlahRuangMakanGet = event;
  }

  onJumlahDapurChange(event)
  {
    this.jumlahDapurGet = event;
  }

  onJumlahGudangChange(event)
  {
    this.jumlahGudangGet = event;
  }

  onJumlahSerambiChange(event)
  {
    this.jumlahSerambiGet = event;
  }

  onJumlahTingkatChange(event)
  {
    this.jumlahTingkatGet = event;
  }

  onTahunDibuatChange(event)
  {
    this.tahunDibuatGet = event;
  }

  onHargaChange(event)
  {
    this.hargaGet = event;
  }

  onDeveloperNameChange(event)
  {
    this.developerNameGet = event;
  }

  onDeveloperEmailChange(event)
  {
    this.developerEmailGet = event;
  }

  onContactNumberChange(event)
  {
    this.contactNumberGet = event;
    console.log(event);
  }

  onFileChange(event)
  {
    console.log(event.target.files.length);

    if(event.target.files.length > 10)
    {
      alert("Hanya diperbolehkan maksimal 10 file gambar!");
    }

    if(event.target.files.length <= 10)
    {
      for (let i = 0; i < event.target.files.length; i++)
      { 
          this.fileKatalogImages.push(event.target.files[i]);
      }
    }
  }

  onSubmit()
  {
    const formData = new FormData();

    formData.append("katalogUUID", this.currentKatalogUUID);
    formData.append("katalogName", this.katalogNameGet);
    formData.append("katalogDesc", this.katalogDescriptionDesc);
    formData.append("luasTanah", this.luasTanahGet);
    formData.append("luasBangunan", this.luasBangunanGet);
    formData.append("jumlahKamarMandi", this.jumlahKamarMandiGet);
    formData.append("jumlahKamarTidur", this.jumlahKamarTidurGet);
    formData.append("jumlahRuangTamu", this.jumlahRuangTamuGet);
    formData.append("jumlahGarasi", this.jumlahGarasiGet);
    formData.append("jumlahRuangKeluarga", this.jumlahRuangKeluargaGet);
    formData.append("jumlahRuangMakan", this.jumlahRuangMakanGet);
    formData.append("jumlahDapur", this.jumlahDapurGet);
    formData.append("jumlahGudang", this.jumlahGudangGet);
    formData.append("jumlahSerambi", this.jumlahSerambiGet);
    formData.append("jumlahTingkat", this.jumlahTingkatGet);
    formData.append("tahunDibuat", this.tahunDibuatGet);

    formData.append("harga", this.hargaGet);
    formData.append("developerName", this.developerNameGet);
    formData.append("developerEmail", this.developerEmailGet);

    formData.append("contactNumber", this.contactNumberGet);

    formData.append("alamat", this.alamatGet);

    formData.append("provinsi_id", this.provinsiID);
    formData.append("kabupaten_id", this.kabupatenID);
    formData.append("kecamatan_id", this.kecamatanID);
    formData.append("desa_id", this.desaID);

    formData.append("sertifikatCode", this.sertifikatCode);
    formData.append("sertifikat", this.sertifikatGet);
    formData.append("sertifikatNameInput", this.sertifikatGet);

    formData.append("kodeTipeRumah", this.rumahTipe);

    if(this.isDisewakan == true)
    {
      formData.append("isDisewakan", "1");
      formData.append("modeSewa", this.modeSewaValue);
    }

    if(this.isDisewakan == false)
    {
      formData.append("isDisewakan", "0");
      formData.append("modeSewa", "");
    }

    formData.append("isSecond", this.kondisiRumah);

    formData.append("dayaListrik", this.dayaListrikGet);
    formData.append("conditionMeasurement", this.conditionMeasurementGet);
    formData.append("perlengkapanPerabotan", this.perlengkapanPerabotanGet);

    formData.append("katalogVideoLink", this.youtubeVideoKatalogGet);

    if(this.useARGet == true)
    {
      formData.append("useAR", "1");
      formData.append("fbxFileLink", this.ARFBXFileGet);
      formData.append("fbxLinkDiffuseTexture", this.ARFBXFileDiffuseTextureGet);
      formData.append("linkGambarMarker", this.ARMarkerImageGet);
    }

    if(this.useARGet == false)
    {
      formData.append("useAR", "0");
      formData.append("fbxFileLink", "");
      formData.append("fbxLinkDiffuseTexture", "");
      formData.append("linkGambarMarker", "");
    }
    
    for (let i = 0; i < this.fileKatalogImages.length; i++)
    { 
      formData.append("katalogImages[]", this.fileKatalogImages[i]);
    }

    this.presentLoading().then(() => {
      this.http.post(baseUrlData.apiV1 + 'rumah/editRumahCatalog', formData, {
        headers: new HttpHeaders({
          //'Content-Type': mimeData.json,
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
  
            if(res['status'] == false)
            {
              if(res['code'] == "UPDATE_NEEDED")
              {
                this.resultApiText = res['message'];
              }
  
              else if(res['code'] == "EMAIL_NOT_VERIFIED")
              {
                this.resultApiText = res['message'];
              }
  
              else
              {
                this.resultApiText = res['message'];
              }

              this.presentToast(this.resultApiText, 2000);
            }
  
            if(res['status'] == true)
            {
              this.resultApiText = res['message'];

              this.presentToast(this.resultApiText, 2000);

              console.log("sukses kirim/upload!");
            }
          },
          err => {
            this.dismissLoading();
            console.log("Error occured");
            this.resultApiText = "Terjadi kesalahan, silakan coba lagi!";
            this.presentToast(this.resultApiText, 2000);

            if(err['status'] == 401)
            {
              this.router.navigate(['/login'], { replaceUrl: true });
            }
          }
        );
    });
  }

  onProvinsiSelectBox(event)
  {
    console.log(event);

    this.provinsiID = event;
    this.kabupatenID = "";
    this.kecamatanID = "";
    this.desaID = "";

    this.getKabupatenSelectBoxValue(event);
  }

  onKabupatenSelectBox(event)
  {
    console.log(event);

    this.kabupatenID = event;
    this.kecamatanID = "";
    this.desaID = "";

    this.getKecamatanSelectBoxValue(event);
  }

  onKecamatanSelectBox(event)
  {
    console.log(event);

    this.kecamatanID = event;
    this.desaID = "";

    this.getDesaSelectBoxValue(event);
  }

  onDesaSelectBox(event)
  {
    console.log(event);

    this.desaID = event;
  }

  getProvinsiSelectBoxValue()
  {
      this.http.get(baseUrlData.apiV1 + 'wilayah_api/getProvinsi', {
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
              
            }
  
            if(res['status'] == true)
            {
              this.arrayProvinsiData = res['data'];
            }
          },
          err => {
            console.log("Error occured");
          }
        );
  }

  getKabupatenSelectBoxValue(valueProvinsi)
  {
      this.http.get(baseUrlData.apiV1 + 'wilayah_api/getKabKota?provinsi_id=' + valueProvinsi, {
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
              
            }
  
            if(res['status'] == true)
            {
              this.arrayKabupatenData = res['data'];
            }
          },
          err => {
            console.log("Error occured");
          }
        );
  }

  getKecamatanSelectBoxValue(valueKabupaten)
  {
      this.http.get(baseUrlData.apiV1 + 'wilayah_api/getKecamatan?kabupaten_id=' + valueKabupaten, {
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
              
            }
  
            if(res['status'] == true)
            {
              this.arrayKecamatanData = res['data'];
            }
          },
          err => {
            console.log("Error occured");
          }
        );
  }

  getDesaSelectBoxValue(valueDesa)
  {
      this.http.get(baseUrlData.apiV1 + 'wilayah_api/getDesa?kecamatan_id=' + valueDesa, {
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
              
            }
  
            if(res['status'] == true)
            {
              this.arrayDesaData = res['data'];
            }
          },
          err => {
            console.log("Error occured");
          }
        );
  }

  // get sertifikat rumah type

  getSertifikatType()
  {
    this.http.get(baseUrlData.apiV1 + 'sertifikat/sertifikatType', {
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
            
          }

          if(res['status'] == true)
          {
            this.arrayJenisSertifikat = res['data'];
          }
        },
        err => {
          console.log("Error occured");
        }
      );
  }

  onSertifikatTypeSelectBox(event)
  {
    console.log(event);

    let splitValue = event.split(",");

    console.log(splitValue);

    this.needManualInputSertifikatName = splitValue[1];

    console.log(this.needManualInputSertifikatName);

    if(this.needManualInputSertifikatName == "0")
    {
      this.sertifikatCode = splitValue[0];
      this.needManualInputSertifikatNameInputOpen = false;
      console.log("No Manual Input Sertifikat Required");
    }

    if(this.needManualInputSertifikatName == "1")
    {
      this.sertifikatCode = splitValue[0];
      this.needManualInputSertifikatNameInputOpen = true;
      console.log("Manual Input Sertifikat Required");
    }
  }

  //

  onSertifikatNameChange(event)
  {
    console.log(event);

    this.sertifikatGet = event;
  }

  onAlamatChange(event)
  {
    console.log(event);

    this.alamatGet = event;
  }

  getKodeTipeRumah()
  {
    this.http.get(baseUrlData.apiV1 + 'rumah/getCategory', {
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
            
          }

          if(res['status'] == true)
          {
            this.arrayKodeTipeRumah = res['data'];
          }
        },
        err => {
          console.log("Error occured");
        }
      );
  }

  onRumahTipeSelectBox(event)
  {
    console.log(event);
  }

  onKondisiRumahSelectBox(event)
  {
    console.log(event);
  }

  onIsDisewakanCheckBox(event)
  {
    this.isDisewakan = event;
    console.log(this.isDisewakan);

    if(this.isDisewakan == false)
    {
      this.modeSewaValue = "-";
    }

    if(this.isDisewakan == true)
    {
      this.modeSewaValue = "";
    }
  }

  onModeSewaChange(event)
  {
    console.log(event);

    this.modeSewaValue = event;
  }

  onDayaListrikChange(event)
  {
    console.log(event);

    this.dayaListrikGet = event;
  }

  onPerlengkapanPerabotanChange(event)
  {
    console.log(event);

    this.perlengkapanPerabotanGet = event;
  }

  onConditionMeasurementChange(event)
  {
    console.log(event);

    this.conditionMeasurementGet = event;
  }

  onYoutubeVideoKatalogChange(event)
  {
    console.log(event);

    this.youtubeVideoKatalogGet = event;
  }

  onUseARCheckBox(event)
  {
    console.log(event);

    this.useARGet = event;

    if(this.useARGet == true)
    {
      this.showARInputForm = true;
    }

    if(this.useARGet == false)
    {
      this.showARInputForm = false;
    }
  }

  onARFBXFileChange(event)
  {
    console.log(event);

    this.ARFBXFileGet = event;
  }

  onARFBXFileDiffuseTextureChange(event)
  {
    console.log(event);

    this.ARFBXFileDiffuseTextureGet = event;
  }

  onARMarkerImageTextureChange(event)
  {
    console.log(event);

    this.ARMarkerImageGet = event;
  }

  loadCurrentData()
  {
    this.http.post(baseUrlData.apiV1 + 'rumah/currentDetail', {katalogUUID: this.currentKatalogUUID}, {
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
            this.currentTempEditArray = [];
          }

          if(res['status'] == true)
          {
            this.currentTempEditArray = res['data'];

            this.uploadForm.patchValue({katalogName: res['data']['katalogName']});
            this.uploadForm.patchValue({katalogDesc: res['data']['katalogDesc'].replace("<br>", "\n")});

            this.uploadForm.patchValue({luasTanah: res['data']['details']['luasTanah']});
            this.uploadForm.patchValue({luasBangunan: res['data']['details']['luasBangunan']});

            this.uploadForm.patchValue({jumlahKamarMandi: res['data']['details']['jumlahKamarMandi']});
            this.uploadForm.patchValue({jumlahKamarTidur: res['data']['details']['jumlahKamarTidur']});
            this.uploadForm.patchValue({jumlahRuangTamu: res['data']['details']['jumlahRuangTamu']});
            this.uploadForm.patchValue({jumlahGarasi: res['data']['details']['jumlahGarasi']});
            this.uploadForm.patchValue({jumlahRuangKeluarga: res['data']['details']['jumlahRuangKeluarga']});
            this.uploadForm.patchValue({jumlahRuangMakan: res['data']['details']['jumlahRuangMakan']});
            this.uploadForm.patchValue({jumlahDapur: res['data']['details']['jumlahDapur']});
            this.uploadForm.patchValue({jumlahGudang: res['data']['details']['jumlahGudang']});
            this.uploadForm.patchValue({jumlahSerambi: res['data']['details']['jumlahSerambi']});
            this.uploadForm.patchValue({jumlahTingkat: res['data']['details']['jumlahTingkat']});
            this.uploadForm.patchValue({tahunDibuat: res['data']['details']['tahunDibuat']});

            this.uploadForm.patchValue({isDisewakanCheckBox: res['data']['isDisewakan']});
            this.uploadForm.patchValue({harga: res['data']['price']['priceInt']});
            this.uploadForm.patchValue({modeSewa: res['data']['modeSewa']});

            this.uploadForm.patchValue({developerName: res['data']['developerInfo']['developerName']});
            this.uploadForm.patchValue({developerEmail: res['data']['developerInfo']['developerEmail']});
            this.uploadForm.patchValue({contactNumber: res['data']['developerInfo']['developerWhatsApp']['number']});

            this.provinsiID = res['data']['details']['provinsiID'];
            this.kabupatenID = res['data']['details']['kabupatenID'];
            this.kecamatanID = res['data']['details']['kecamatanID'];
            this.desaID = res['data']['details']['desaID'];

            console.log("Provinsi ID: " + this.provinsiID);
            console.log("Kabupaten ID: " + this.kabupatenID);
            console.log("Kecamatan ID: " + this.kecamatanID);
            console.log("Desa ID: " + this.desaID);

            this.uploadForm.patchValue({alamat: res['data']['details']['alamat']});

            this.sertifikatCode = res['data']['details']['sertifikatCode'];
            console.log("Ini sertifikat code: " + this.sertifikatCode);
            this.uploadForm.patchValue({sertifikat: res['data']['details']['sertifikatCode']});
            this.uploadForm.patchValue({sertifikatTypeSelectBox: res['data']['details']['sertifikatCode'] + ',' + res['data']['details']['needManualInputSertifikat']});

            this.needManualInputSertifikatNameInputOpen = res['data']['details']['needManualInputSertifikat'];
            this.uploadForm.patchValue({sertifikatNameInput: res['data']['details']['sertifikat']});
            this.sertifikatGet = res['data']['details']['sertifikat'];

            //this.rumahTipe = 
            this.uploadForm.patchValue({rumahTipeSelectBox: res['data']['miscDetails']['tipePropertiRumah']});

            this.uploadForm.patchValue({kondisiRumahSelectBox: (res['data']['isSecond'].toString())});

            this.uploadForm.patchValue({dayaListrik: res['data']['miscDetails']['dayaListrik']});
            this.uploadForm.patchValue({perlengkapanPerabotan: res['data']['miscDetails']['perlengkapanPerabotan']});
            this.uploadForm.patchValue({conditionMeasurement: res['data']['miscDetails']['conditionMeasurement']});

            console.log("Use YouTube Video: " + res['data']['useYouTubeVideoUrl']);

            if(res['data']['useYouTubeVideoUrl'] == 1)
            {
              this.uploadForm.patchValue({youtubeVideoKatalog: res['data']['katalogVideoData']['videoUrl']});
            }
            
            if(res['data']['useYouTubeVideoUrl'] != 1)
            {
              this.uploadForm.patchValue({youtubeVideoKatalog: ""});
            }

            this.useARGet = res['data']['useAR'];
            this.ARFBXFileGet = res['data']['arData']['objectFileURL'];
            this.uploadForm.patchValue({ARFBXFile: res['data']['arData']['objectFileURL']});
            this.uploadForm.patchValue({ARFBXFileDiffuseTexture: res['data']['arData']['objectFileDiffuseTextureURL']});
            this.ARFBXFileDiffuseTextureGet = res['data']['arData']['objectFileDiffuseTextureURL'];
            this.uploadForm.patchValue({ARMarkerImage: res['data']['arData']['markerUrl']});
            this.ARMarkerImageGet = res['data']['arData']['markerUrl'];

            console.log("OK: " + res['data']['details']['sertifikatCode'] + ',' + res['data']['details']['needManualInputSertifikat']);
            console.log(this.uploadForm.getRawValue());

            //console.log(this.katalogNameGet);
          }
        },
        err => {
          console.log("Error occured");
          this.currentTempEditArray = [];
        }
      );
  }

  ngOnInit()
  {
    this.statusBar.overlaysWebView(false);
    this.statusBar.show();

    this.currentKatalogUUID = this.activatedRoute.snapshot.paramMap.get('katalogUUID');
    console.log(this.currentKatalogUUID);

    this.getProvinsiSelectBoxValue();
    this.getSertifikatType();
    this.getKodeTipeRumah();

    this.loadCurrentData();

    this.jumlahTingkatGet = "1";
  }
}