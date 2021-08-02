import { Component, OnInit, ViewChild, ElementRef } from '@angular/core';
import { Platform } from '@ionic/angular';
//import { Camera, CameraOptions } from '@ionic-native/camera/ngx';
import { NavController } from '@ionic/angular';
import * as THREE from 'three';
import { FBXLoader } from 'three/examples/jsm/loaders/FBXLoader.js';
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls';
import { Router } from '@angular/router';
import { ModalController } from '@ionic/angular';
//import { AnonymousSubject } from 'rxjs/internal/Subject';
import { StatusBar } from '@ionic-native/status-bar/ngx';
import { AlertController } from '@ionic/angular';
import { v4 as uuidv4 } from 'uuid';
import { DomSanitizer } from '@angular/platform-browser';
import { Base64ToGallery } from '@ionic-native/base64-to-gallery/ngx';

@Component({
  selector: 'app-rendertigadimensi',
  templateUrl: './rendertigadimensi.page.html',
  styleUrls: ['./rendertigadimensi.page.scss'],
})
export class RendertigadimensiPage implements OnInit {

  @ViewChild('rendererContainer', null) rendererContainer: ElementRef;

  renderer = new THREE.WebGLRenderer({antialias:true, alpha:true}); // ANTI ALIASING MENYALA
  scene = null;
  mesh = null;
  cameraView = null;
  loader = new FBXLoader();
  object3d = null;
  hlight = null;
  directionalLight = null;
  light = null;
  light2 = null;
  light3 = null;
  light4 = null;
  controls = null;
  texturenya = "";
  textureLoader = null;
  linkMarkernya = "";
  backHrefKatalogUUID = "";

  fbxUrl = "";

  exitModalBackButtonSubscribe: any;

  progressLoading = "";
  markerOnTutorialAlert: any;
  fileMarkerImageToDownloadUrl: any;

  constructor(private modalController: ModalController, public navCtrl: NavController, private router: Router, public platform: Platform, private statusBar: StatusBar, public alertController: AlertController, private sanitizer: DomSanitizer, private base64ToGallery: Base64ToGallery)
  {
    //
    //this.renderer.setPixelRatio( window.devicePixelRatio );
    //this.renderer.setSize( window.innerWidth, window.innerHeight );
    //this.renderer.gammaOutput = true;
    //this.renderer.gammaFactor = 2.2;
    //this.renderer.shadowMap.enabled = true;

    this.renderer.setPixelRatio( window.devicePixelRatio );
    this.renderer.setClearColor(new THREE.Color('lightgrey'), 0)
    this.renderer.setSize( window.innerWidth, window.innerHeight );
    this.renderer.gammaOutput = true;
    this.renderer.gammaFactor = 2.2;
    this.renderer.shadowMap.enabled = true;

    this.scene = new THREE.Scene();
    //this.texturenya = new THREE.TextureLoader().load('https://assets.awwwards.com/awards/images/2011/07/gif-cinematic01.gif');
    //this.texturenya = new THREE.TextureLoader().load(this.webcamImage);
    //this.scene.background = new THREE.Color( 0xdddddd ); // BACKGROUND PUTIH
    //this.scene.background = this.texturenya;
    this.scene.background = new THREE.Color( 0xdddddd );

    //this.camera = new THREE.PerspectiveCamera( 45, window.innerWidth / window.innerHeight, 1, 10000 );
    //this.camera.position.set( 100, 200, 300 );

    this.cameraView = new THREE.PerspectiveCamera(40,window.innerWidth/window.innerHeight,1,20000);
    this.cameraView.rotation.y = 45/180*Math.PI;
    this.cameraView.position.x = 800;
    this.cameraView.position.y = 100;
    this.cameraView.position.z = 1000;
    
    this.hlight = new THREE.AmbientLight (0xffffff, 0.4);
    this.scene.add(this.hlight);

    //DIRECTIONAL LIGHT

    this.renderer.shadowMap.enabled = true;
    this.renderer.shadowMap.type = THREE.PCFSoftShadowMap;

    this.directionalLight = new THREE.DirectionalLight(0xffffff, 0.2);
    this.directionalLight.position.set(0,1,0);
    this.directionalLight.castShadow = true;
    this.scene.add(this.directionalLight);

    //ADDITIONAL LIGHT UNTUK ENHANCE GRAPHIC

    this.light = new THREE.PointLight(0xc4c4c4, 0.2);
    this.light.position.set(0,20,50);
    this.light.castShadow = true;
    this.scene.add(this.light);

    //Set up shadow properties for the light
    this.light.shadow.mapSize.width = 512; // default
    this.light.shadow.mapSize.height = 512; // default
    this.light.shadow.camera.near = 0.5; // default
    this.light.shadow.camera.far = 500; // default

    // const helper = new THREE.CameraHelper( this.light.shadow.camera );
    // this.scene.add( helper );

    //this.loader.load('./assets/threejs_models/altz.fbx', function (object3d) {
    //console.log(object3d);

    //object3d.position.set(0,0,0);

    //this.scene.add(object3d);

    

    //this.renderer.sortObjects = false;
    //this.renderer.setSize(window.innerWidth,window.innerHeight);

    //this.renderer.render( this.scene, this.camera );
    //});

    // Initialise the group
    //const markerGroup = new THREE.Group();
    //this.scene.add(markerGroup);

    // MARKER INTEGRASI
    /*this.source = new THREEAR.Source({this.renderer, this.cameraView});
    THREEAR.initialize({ source: this.source }).then((controller) => {
      // Here your code
  });*/

  this.exitModalBackButtonSubscribe = this.platform.backButton.subscribeWithPriority(666666, () =>{
    if(this.constructor.name == "RendertigadimensiPage")
    {
      this.closeModalRender3D();
    }
  });
  }

  async showAlertARView() {
    this.updateFullMarkerImage();

    const alert = await this.alertController.create({
      cssClass: 'alertClass',
      header: 'Render AR',
      //subHeader: 'Silakan arahkan kamera Anda ke lingkungan sekitar yang mirip dengan gambar marker berikut!',
      subHeader: 'Print atau jalankan gambar marker berikut ke perangkat lain kemudian scan menggunakan kamera Anda. Pastikan gambar marker di bawah ini muncul sebelum melanjutkan dan pastikan koneksi internet aman!',
      message: `<img src="${this.markerOnTutorialAlert}" style="border-radius: 2px">`,
      buttons: [
        {
          text: 'Batal',
          role: 'cancel',
          cssClass: 'secondary',
          handler: () => {
            console.log('Confirm Cancel');
          }
        },
        /*{
          text: 'Download Marker',
          cssClass: 'secondary',
          handler: () => {
            console.log('Confirm Download Marker Imagenya');

            this.downloadMarkerBlob(this.markerOnTutorialAlert);
            
            //console.log(uuidv4());
          }
        },*/
        {
          text: 'Lanjutkan',
          handler: () => {
            console.log('Confirm Ok');

            let payloadAR = {
              backHref: '/katalogdetailview/' + this.backHrefKatalogUUID,
              fbxFileUrl: this.fbxUrl,
              fbxLinkDiffuseTexture: this.texturenya,
              linkMarkerAR: this.linkMarkernya
            };

            this.router.navigate(['/arviewfix/' + window.btoa(JSON.stringify(payloadAR))]);
            this.closeModalRender3D();
          }
        }
      ]
    });

    await alert.present();
  }

  downloadMarkerBlob(dataImage)
  {
    //const blob = new Blob([dataImage], { type: 'application/octet-stream' });

    //this.fileMarkerImageToDownloadUrl = this.sanitizer.bypassSecurityTrustResourceUrl(window.URL.createObjectURL(blob));

    /*let domElement = window.document.createElement('a');
    domElement.href = window.URL.createObjectURL(new Blob([dataImage], {type: 'image/png'}));
    domElement.download = uuidv4() + ".png";
    document.body.appendChild(domElement)
    domElement.click();
    document.body.removeChild(domElement)*/

    let a = document.createElement("a"); //Create <a>
    a.href = dataImage; //Image Base64 Goes here
    a.download = uuidv4() + ".png"; //File name Here
    a.click();

    this.base64ToGallery.base64ToGallery(dataImage, {prefix: '_img', mediaScanner: true}).then(
      res => alert('Saved image to gallery '),
      err => alert('Error saving image to gallery ')
    );
  }

  hexaColor(color)
  {
    return /^#[0-9A-F]{6}$/i.test(color);
  }

  updateFullMarkerImage()
  {
    // get patternRatio
		let patternRatio = 0.5;
		let imageSize = 512;
		let borderColor = "black";

		let s = new Option().style;
		s.color = borderColor;
      if (borderColor === '' || (s.color != borderColor && !this.hexaColor(borderColor)))
      {
        // if color not valid, use black
        borderColor = 'black';
      }
      
    this.buildFullMarker(patternRatio, imageSize, borderColor);
  }

  buildFullMarker(patternRatio, imageSize, borderColor)
  {
    let innerImageURL = this.linkMarkernya;

    let whiteMargin = 0.1
	let blackMargin = (1 - 2 * whiteMargin) * ((1-patternRatio)/2)
	// var blackMargin = 0.2

	let innerMargin = whiteMargin + blackMargin

	let canvas = document.createElement('canvas');
	let context = canvas.getContext('2d')
	canvas.width = canvas.height = imageSize

	context.fillStyle = 'white';
	context.fillRect(0,0,canvas.width, canvas.height)

	// copy image on canvas
	context.fillStyle = borderColor;
	context.fillRect(
		whiteMargin * canvas.width,
		whiteMargin * canvas.height,
		canvas.width * (1-2*whiteMargin),
		canvas.height * (1-2*whiteMargin)
	);

	// clear the area for innerImage (in case of transparent image)
	context.fillStyle = 'white';
	context.fillRect(
		innerMargin * canvas.width,
		innerMargin * canvas.height,
		canvas.width * (1-2*innerMargin),
		canvas.height * (1-2*innerMargin)
	);


	// display innerImage in the middle
	let innerImage = document.createElement('img')
	innerImage.onload = (e: any) => {
		// draw innerImage
		context.drawImage(innerImage,
			innerMargin * canvas.width,
			innerMargin * canvas.height,
			canvas.width * (1-2*innerMargin),
			canvas.height * (1-2*innerMargin)
		);

    let imageUrl = canvas.toDataURL()
    
    let fullMarkerURL = imageUrl;

			let fullMarkerImage = document.createElement('img')
      fullMarkerImage.src = fullMarkerURL

      //console.log(fullMarkerImage.src);

      this.markerOnTutorialAlert = fullMarkerImage.src;
	};
    innerImage.src = innerImageURL;
    innerImage.crossOrigin = 'Anonymous';
  }

  render3D()
  {
    console.log(this.fbxUrl);
    console.log(this.texturenya);

    this.loader.load(
      // resource URL
      this.fbxUrl,
      // called when resource is loaded
      ( object ) => {
        console.log(object);
        //object.position.y -= 60;
        object.position.set(0,50,0);
        //object.traverse( function( node ) { if ( node instanceof THREE.Mesh ) { node.castShadow = true; } } );

        let tempTextureUrl = new THREE.TextureLoader().load(this.texturenya);

        object.traverse( function ( child ){
          if ( child.isMesh )
          {
            //console.log( child.geometry.attributes.uv );
            child.material.map = tempTextureUrl; // assign your diffuse texture here
          } 
        });

        /*object.traverse(function (child) {
          if (child instanceof THREE.Mesh) {
              // apply texture
              child.material.map = this.texturenya;
              child.material.needsUpdate = true;
          }
        });*/
    
       this.scene.add( object );
    
      },
      // called when loading is in progresses
      ( xhr ) => {
    
        console.log( ( xhr.loaded / xhr.total * 100 ) + '% loaded' );
        this.progressLoading = "Memuat " + Math.round( parseInt(xhr.loaded) / parseInt(xhr.total) * 100 ) + "%";

        if( Math.round( parseInt(xhr.loaded) / parseInt(xhr.total) * 100 ) >= 100)
        {
          this.progressLoading = "AR 3D View";
        }
    
      },
      // called when loading has errors
      ( error ) => {
    
        console.log( 'An error happened' );
        console.log(error);
    
      }
    );

    this.renderer.sortObjects = false;
    this.renderer.setSize(window.innerWidth,window.innerHeight);
    this.renderer.render( this.scene, this.cameraView );

    //this.camera.position.set( 0, 20, 100 );
    this.controls = new OrbitControls(this.cameraView, this.renderer.domElement);
    //this.controls.addEventListener('change', this.renderer.domElement);
    this.controls.update();

    //this.scene.add(this.mesh);
  }

  ngAfterViewInit()
  {
    this.renderer.setSize(window.innerWidth, window.innerHeight);
    this.rendererContainer.nativeElement.appendChild(this.renderer.domElement);
    this.animate();
  }

  animate()
  {
    if(this.scene != null && this.scene != undefined)
    {
      window.requestAnimationFrame(() => this.animate());

      this.controls.update();

      this.renderer.render( this.scene, this.cameraView );
    }
 }

  async closeModalRender3D()
  {
    this.renderer.renderLists.dispose(); // mencegah memory (ram) leak
    this.rendererContainer.nativeElement.removeChild(this.renderer.domElement); // mencegah memory (ram) leak
    this.scene = null;
    this.mesh = null;
    this.cameraView = null;
    this.loader = null;
    this.object3d = null;
    this.hlight = null;
    this.directionalLight = null;
    this.light = null;
    this.light2 = null;
    this.light3 = null;
    this.light4 = null;
    this.controls = null;
    this.texturenya = "";
    this.textureLoader = null;

    await this.modalController.dismiss();
  }

  ngOnInit()
  {
    this.statusBar.overlaysWebView(false);
    this.statusBar.show();

    //
    console.log(this.fbxUrl);
    console.log(this.texturenya);

    this.render3D();
    this.updateFullMarkerImage();
  }
}
