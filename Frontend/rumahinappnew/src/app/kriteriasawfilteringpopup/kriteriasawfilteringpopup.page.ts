import { Component, OnInit } from '@angular/core';
import { ModalController } from '@ionic/angular';
import { Platform } from '@ionic/angular';
import { StatusBar } from '@ionic-native/status-bar/ngx';

@Component({
  selector: 'app-kriteriasawfilteringpopup',
  templateUrl: './kriteriasawfilteringpopup.page.html',
  styleUrls: ['./kriteriasawfilteringpopup.page.scss'],
})
export class KriteriasawfilteringpopupPage implements OnInit {

  hargaBool = true;
  luasTanahBool = true;
  luasBangunanBool = true;
  jumlahKamarMandiBool = false;
  jumlahKamarTidurBool = false;
  jumlahRuangTamuBool = false;
  jumlahGarasiBool = false;
  jumlahRuangKeluargaBool = false;
  jumlahRuangMakanBool = false;
  jumlahDapurBool = false;
  jumlahGudangBool = false;
  jumlahSerambiBool = false;
  jumlahTingkatBool = false;
  jumlahDilihatBool = false;

  responseContext = [];

  constructor(private modalController: ModalController, public platform: Platform, private statusBar: StatusBar)
  {

  }

  async closeModalSAWFilteringPopup()
  {
    this.detectOptionChange();

    await this.modalController.dismiss(this.responseContext);
  }

  hargaBoolChange(event)
  {
    this.hargaBool = event;

    this.detectOptionChange();
  }

  luasTanahBoolChange(event)
  {
    this.luasTanahBool = event;

    this.detectOptionChange();
  }

  luasBangunanBoolChange(event)
  {
    this.luasBangunanBool = event;

    this.detectOptionChange();
  }

  jumlahKamarMandiBoolChange(event)
  {
    this.jumlahKamarMandiBool = event;

    this.detectOptionChange();
  }

  jumlahKamarTidurBoolChange(event)
  {
    this.jumlahKamarMandiBool = event;

    this.detectOptionChange();
  }

  jumlahRuangTamuBoolChange(event)
  {
    this.jumlahRuangTamuBool = event;

    this.detectOptionChange();
  }

  jumlahGarasiBoolChange(event)
  {
    this.jumlahGarasiBool = event;

    this.detectOptionChange();
  }

  jumlahRuangKeluargaBoolChange(event)
  {
    this.jumlahRuangKeluargaBool = event;

    this.detectOptionChange();
  }

  jumlahRuangMakanBoolChange(event)
  {
    this.jumlahRuangMakanBool = event;

    this.detectOptionChange();
  }

  jumlahDapurBoolChange(event)
  {
    this.jumlahDapurBool = event;

    this.detectOptionChange();
  }

  jumlahGudangBoolChange(event)
  {
    this.jumlahGudangBool = event;

    this.detectOptionChange();
  }

  jumlahSerambiBoolChange(event)
  {
    this.jumlahSerambiBool = event;

    this.detectOptionChange();
  }

  jumlahTingkatBoolChange(event)
  {
    this.jumlahTingkatBool = event;

    this.detectOptionChange();
  }

  jumlahDilihatBoolChange(event)
  {
    this.jumlahDilihatBool = event;

    this.detectOptionChange();
  }


  //

  detectOptionChange()
  {
    this.responseContext = [
      {
        criteriaAttr: 'harga',
        useThisCriteria: this.hargaBool
      },
      {
        criteriaAttr: 'luasTanah',
        useThisCriteria: this.luasTanahBool
      },
      {
        criteriaAttr: 'luasBangunan',
        useThisCriteria: this.luasBangunanBool
      },
      {
        criteriaAttr: 'jumlahKamarMandi',
        useThisCriteria: this.jumlahKamarMandiBool
      },
      {
        criteriaAttr: 'jumlahKamarTidur',
        useThisCriteria: this.jumlahKamarMandiBool
      },
      {
        criteriaAttr: 'jumlahRuangTamu',
        useThisCriteria: this.jumlahRuangTamuBool
      },
      {
        criteriaAttr: 'jumlahGarasi',
        useThisCriteria: this.jumlahGarasiBool
      },
      {
        criteriaAttr: 'jumlahRuangKeluarga',
        useThisCriteria: this.jumlahRuangKeluargaBool
      },
      {
        criteriaAttr: 'jumlahRuangMakan',
        useThisCriteria: this.jumlahRuangMakanBool
      },
      {
        criteriaAttr: 'jumlahGudang',
        useThisCriteria: this.jumlahGudangBool
      },
      {
        criteriaAttr: 'jumlahSerambi',
        useThisCriteria: this.jumlahSerambiBool
      },
      {
        criteriaAttr: 'jumlahTingkat',
        useThisCriteria: this.jumlahTingkatBool
      },
      {
        criteriaAttr: 'jumlahDapur',
        useThisCriteria: this.jumlahDapurBool
      },
      {
        criteriaAttr: 'totalViewed',
        useThisCriteria: this.jumlahDilihatBool
      }
    ];

    localStorage.setItem("tempSAWFilteringData", window.btoa(JSON.stringify(this.responseContext)));
  }

  ngOnInit()
  {
    this.statusBar.overlaysWebView(false);
    this.statusBar.show();

    //console.log(localStorage.getItem("tempSAWFilteringData"));
    let responseContextGet = JSON.parse(window.atob(localStorage.getItem("tempSAWFilteringData")));
    //console.log(responseContextGet);

    let criteriaAttrNameTemp = "";
    let useThisCriteriaAttrTemp = false;

    for(let ok = 0; ok < responseContextGet.length; ok++)
    {
      criteriaAttrNameTemp = responseContextGet[ok]['criteriaAttr'];
      useThisCriteriaAttrTemp = responseContextGet[ok]['useThisCriteria'];

      if(criteriaAttrNameTemp == "harga")
      {
        this.hargaBool = useThisCriteriaAttrTemp;
      }

      if(criteriaAttrNameTemp == "luasTanah")
      {
        this.luasTanahBool = useThisCriteriaAttrTemp;
      }

      if(criteriaAttrNameTemp == "luasBangunan")
      {
        this.luasBangunanBool = useThisCriteriaAttrTemp;
      }

      if(criteriaAttrNameTemp == "jumlahKamarMandi")
      {
        this.jumlahKamarMandiBool = useThisCriteriaAttrTemp;
      }

      if(criteriaAttrNameTemp == "jumlahKamarTidur")
      {
        this.jumlahKamarTidurBool = useThisCriteriaAttrTemp;
      }

      if(criteriaAttrNameTemp == "jumlahRuangTamu")
      {
        this.jumlahRuangTamuBool = useThisCriteriaAttrTemp;
      }

      if(criteriaAttrNameTemp == "jumlahGarasi")
      {
        this.jumlahGarasiBool = useThisCriteriaAttrTemp;
      }

      if(criteriaAttrNameTemp == "jumlahRuangKeluarga")
      {
        this.jumlahRuangKeluargaBool = useThisCriteriaAttrTemp;
      }

      if(criteriaAttrNameTemp == "jumlahRuangMakan")
      {
        this.jumlahRuangMakanBool = useThisCriteriaAttrTemp;
      }

      if(criteriaAttrNameTemp == "jumlahGudang")
      {
        this.jumlahGudangBool = useThisCriteriaAttrTemp;
      }

      if(criteriaAttrNameTemp == "jumlahSerambi")
      {
        this.jumlahSerambiBool = useThisCriteriaAttrTemp;
      }

      if(criteriaAttrNameTemp == "jumlahTingkat")
      {
        this.jumlahTingkatBool = useThisCriteriaAttrTemp;
      }

      if(criteriaAttrNameTemp == "jumlahDapur")
      {
        this.jumlahDapurBool = useThisCriteriaAttrTemp;
      }

      if(criteriaAttrNameTemp == "totalViewed")
      {
        this.jumlahDilihatBool = useThisCriteriaAttrTemp;
      }
    }
  }
}
