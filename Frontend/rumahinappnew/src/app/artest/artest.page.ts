import { Component, OnInit, ViewChild, ElementRef } from '@angular/core';
import { Camera, CameraOptions } from '@ionic-native/camera/ngx';
import { DomSanitizer } from '@angular/platform-browser';
import { CameraPreview, CameraPreviewOptions, CameraPreviewPictureOptions } from '@ionic-native/camera-preview/ngx';
import * as THREE from 'three';
import { FBXLoader } from 'three/examples/jsm/loaders/FBXLoader.js';
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls';
import { Router } from '@angular/router';
//import { interval, Subscription } from 'rxjs';
import { WebcamImage } from 'ngx-webcam';
import { Subject, Observable } from 'rxjs';
import { WebcamModule } from 'ngx-webcam';
import { NgModule } from '@angular/core';
import { AndroidPermissions } from '@ionic-native/android-permissions/ngx';

@Component({
  selector: 'app-artest',
  templateUrl: './artest.page.html',
  styleUrls: ['./artest.page.scss'],
})
export class ArtestPage implements OnInit {
  @ViewChild('rendererContainer', null) rendererContainer: ElementRef;
  base64Image = "";
  base64:string = "data:image/png;base64,";
  //subscription: Subscription;

  public webcamImage: WebcamImage = null;
  private trigger: Subject<void> = new Subject<void>();

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

    // MARKER DETECTION VARIABLENYA
    source = null;


  constructor(private camera: Camera, private domSanitizer: DomSanitizer, private cameraPreview: CameraPreview, private router: Router, private androidPermissions: AndroidPermissions)
  {
    //this.renderer.shadowMap.enabled = true;
    //this.renderer.shadowMap.type = THREE.PCFSoftShadowMap;

    this.renderer.setPixelRatio( window.devicePixelRatio );
    this.renderer.setSize( window.innerWidth, window.innerHeight );
    this.renderer.gammaOutput = true;
    this.renderer.gammaFactor = 2.2;
    this.renderer.shadowMap.enabled = true;
    //this.renderer.setClearColor(0x000000, 0);

    // TEXTURE LOADER

    //const loader = new THREE.TextureLoader();
    //this.scene.background = loader.load('https://threejs.org/examples/textures/uv_grid_opengl.jpg');

    //

    this.scene = new THREE.Scene();
    //this.texturenya = new THREE.TextureLoader().load('https://assets.awwwards.com/awards/images/2011/07/gif-cinematic01.gif');
    this.texturenya = new THREE.TextureLoader().load(this.webcamImage);
    //this.scene.background = new THREE.Color( 0xdddddd ); // BACKGROUND PUTIH
    this.scene.background = this.texturenya;

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

    //ADDITIONAL LIGHT UNTUK EHANCE GRAPHIC

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

    this.loader.load(
      // resource URL
      'https://api.netspeed.my.id/rumahinapi/documents/building_SPBU.fbx',
      // called when resource is loaded
      ( object ) => {
        console.log(object);
        //object.position.y -= 60;
        object.position.set(0,50,0);
        //object.traverse( function( node ) { if ( node instanceof THREE.Mesh ) { node.castShadow = true; } } );
    
       this.scene.add( object );
    
      },
      // called when loading is in progresses
      ( xhr ) => {
    
        console.log( ( xhr.loaded / xhr.total * 100 ) + '% loaded' );
    
      },
      // called when loading has errors
      ( error ) => {
    
        console.log( 'An error happened' );
    
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
    //window.requestAnimationFrame(() => this.animate());
    //this.renderer.render(this.scene, this.camera);

    window.requestAnimationFrame(() => this.animate());

	  this.controls.update();

	  this.renderer.render( this.scene, this.cameraView );
 }

  scanAR():void
  {
    const options: CameraOptions = {
      quality: 100,
      destinationType: this.camera.DestinationType.FILE_URI,
      encodingType: this.camera.EncodingType.JPEG,
      mediaType: this.camera.MediaType.PICTURE
      //sourceType: this.camera.PictureSourceType.PHOTOLIBRARY, // UNTUK LOAD GAMBAR DARI PHOTO LIBRARY
    }
    
    this.camera.getPicture(options).then((imageData) => {
     // imageData is either a base64 encoded string or a file URI
     // If it's base64 (DATA_URL):
     //this.base64Image = 'data:image/jpeg;base64,' + imageData;
     this.base64Image = imageData;
    }, (err) => {
     // Handle error
    });
  }

  takePicture():void
  {
    const cameraPreviewOpts: CameraPreviewOptions = {
      x: 0,
      y: 0,
      width: window.screen.width,
      height: window.screen.height,
      camera: 'rear',
      tapPhoto: true,
      previewDrag: true,
      toBack: true,
      alpha: 1
    };

    const pictureOpts: CameraPreviewPictureOptions = {
      width: 300,
      height: 300,
      quality: 100
    }

    this.cameraPreview.startCamera(cameraPreviewOpts).then((val) =>{
      //alert("Berhasil discan");
      this.cameraPreview.takePicture(pictureOpts).then((base64) => {
        this.base64 = this.base64 + base64;
        this.cameraPreview.stopCamera();
        //alert(this.base64);
      })
    }, (err)=>{
      //alert(JSON.stringify(err));
    });
  }

  ngOnInit()
  {
    /*setInterval(() => {
      this.takePicture();
  }, 1000);*/

  this.androidPermissions.checkPermission(this.androidPermissions.PERMISSION.CAMERA).then(
    result => console.log('Has permission?',result.hasPermission),
    err => this.androidPermissions.requestPermission(this.androidPermissions.PERMISSION.CAMERA)
  );
  
  this.androidPermissions.requestPermissions([this.androidPermissions.PERMISSION.CAMERA, this.androidPermissions.PERMISSION.GET_ACCOUNTS]);

  setInterval(() => {
    this.triggerSnapshot();
  }, 100);
  }

  triggerSnapshot(): void
  {
   this.trigger.next();
  }

  handleImage(webcamImage: WebcamImage): void
  {
   //console.info('Saved webcam image', webcamImage);
   this.webcamImage = webcamImage;
   this.texturenya = new THREE.TextureLoader().load(this.webcamImage.imageAsDataUrl);
   this.scene.background = null;
   this.scene.background = this.texturenya;
  }
   
  public get triggerObservable(): Observable<void>
  {
   return this.trigger.asObservable();
  }
}