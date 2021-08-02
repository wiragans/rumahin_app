import { Component, OnInit, ViewChild, ElementRef } from '@angular/core';
//import * as THREE from '../../../node_modules/threear/node_modules/three/src/Three';
//import { UVGenerator } from '../../../node_modules/threear/node_modules/three/src/geometries/ExtrudeGeometry';
//import { ShaderMaterialParameters } from '../../../node_modules/threear/node_modules/three/src/materials/ShaderMaterial';
import * as THREE from 'three';
import * as THREEAR from '../../../node_modules/threear/dist/THREEAR.js';
import { FBXLoader } from 'three/examples/jsm/loaders/FBXLoader.js';
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls.js';
import { Router } from '@angular/router';
import { WebcamImage } from 'ngx-webcam';
import { Subject, Observable } from 'rxjs';
import { AndroidPermissions } from '@ionic-native/android-permissions/ngx';
import { StatusBar } from '@ionic-native/status-bar/ngx';
//declare var jQuery: any;

@Component({
  selector: 'app-artestnew',
  templateUrl: './artestnew.page.html',
  styleUrls: ['./artestnew.page.scss'],
})
export class ArtestnewPage implements OnInit {
  @ViewChild('rendererContainer', null) rendererContainer: ElementRef;

    public webcamImage: WebcamImage = null;
    private trigger: Subject<void> = new Subject<void>();

    public currentHerf: string = "";

    renderer = new THREE.WebGLRenderer({antialias: true, alpha: true}); // ANTI ALIASING MENYALA
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

    clock = null;
    deltaTime : number;
    totalTime : number;

    arToolkitSource = null;
    eue = null;

    camera = null;
    markerGroup = null;
    source = null;

  constructor(private router: Router, private androidPermissions: AndroidPermissions, private statusBar: StatusBar)
  {
    
  }

  triggerSnapshot(): void
  {
   this.trigger.next();
  }

  handleImage(webcamImage: WebcamImage): void
  {
   this.webcamImage = webcamImage;
  }
   
  public get triggerObservable(): Observable<void>
  {
   return this.trigger.asObservable();
  }

  ngOnInit():void
  {
    this.statusBar.overlaysWebView(false);
    this.statusBar.show();

    this.currentHerf = this.router.url;
    console.log(this.currentHerf);

    this.androidPermissions.checkPermission(this.androidPermissions.PERMISSION.CAMERA).then(
      result => console.log('Has permission?',result.hasPermission),
      err => this.androidPermissions.requestPermission(this.androidPermissions.PERMISSION.CAMERA)
    );
    
    this.androidPermissions.requestPermissions([this.androidPermissions.PERMISSION.CAMERA, this.androidPermissions.PERMISSION.GET_ACCOUNTS]);

    if(this.currentHerf == "/artestnew")
    {
      this.renderAR();
    }

    else
    {
      //
    }
  }

  renderAR():void
  {
    var renderer = new THREE.WebGLRenderer({
      antialias	: true,
      alpha: true
    });
    renderer.setPixelRatio( window.devicePixelRatio );
    renderer.setClearColor(new THREE.Color('lightgrey'), 0)
    renderer.setSize( window.innerWidth, window.innerHeight );
    renderer.gammaOutput = true;
    renderer.gammaFactor = 2.2;
    renderer.shadowMap.enabled = true;
    //renderer.domElement.style.position = 'absolute';
		//renderer.domElement.style.top = '0px';
		//renderer.domElement.style.left = '0px';
    //document.getElementById("renderAr").appendChild( renderer.domElement );
    //this.rendererContainer2.nativeElement.appendChild(renderer.domElement);
    //document.getElementById("renderAr").appendChild(renderer.domElement);

    document.body.appendChild(renderer.domElement);
  
    // init scene and camera
    var scene = new THREE.Scene();
    var camera = new THREE.Camera();
    scene.add(camera);

    var markerGroup = new THREE.Group();
    scene.add(markerGroup);

    var source = new THREEAR.Source({ renderer, camera });
    
    var loader = new FBXLoader();

    //

    var hlight = new THREE.AmbientLight (0xffffff, 0.4);
    scene.add(hlight);

    //DIRECTIONAL LIGHT

    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFSoftShadowMap;

    var directionalLight = new THREE.DirectionalLight(0xffffff, 0.2);
    directionalLight.position.set(0,1,0);
    directionalLight.castShadow = true;
    scene.add(directionalLight);

    //ADDITIONAL LIGHT UNTUK EHANCE GRAPHIC

    var light = new THREE.PointLight(0xc4c4c4, 0.2);
    light.position.set(0,20,50);
    light.castShadow = true;
    scene.add(light);

    //Set up shadow properties for the light
    light.shadow.mapSize.width = 512; // default
    light.shadow.mapSize.height = 512; // default
    light.shadow.camera.near = 0.5; // default
    light.shadow.camera.far = 500; // default

    loader.load(
      // resource URL
      'https://api.netspeed.my.id/rumahinapi/documents/altz.fbx',
      // called when resource is loaded
      ( object ) => {
        //console.log(object);
        //object.scale.set(0.0005, 0.0005, 0.0005); // POM
        object.scale.set(0.005, 0.005, 0.005);
        //markerGroup.add(object);
        //scene.add(object);
        object.position.y = 0.5;
        //object.position.set(0,50,0);
        //object.traverse( function( node ) { if ( node instanceof THREE.Mesh ) { node.castShadow = true; console.log(node); } } );

        THREEAR.initialize({ source: source }).then((controller) => {
          // add a torus knot		
          //var geometry = new THREE.TorusKnotGeometry(0.3,0.1,64,16);
          //var material = new THREE.MeshNormalMaterial(); 
          //var torus = new THREE.Mesh( geometry, material );
          //torus.position.y = 0.5
          markerGroup.add(object);
          //console.log(torus);
  
          var patternMarker = new THREEAR.PatternMarker({
            patternUrl: 'https://jameslmilner.github.io/THREEAR/data/patt.hiro',
            markerObject: markerGroup
          });
          
          //console.log(scene);
  
          controller.trackMarker(patternMarker);
          
          //renderer.sortObjects = false;
          //renderer.setSize(window.innerWidth,window.innerHeight);
  
          // run the rendering loop
          var lastTimeMsec = 0;
          requestAnimationFrame(function animate(nowMsec){
            // keep looping
            requestAnimationFrame( animate );
            // measure time
            lastTimeMsec = lastTimeMsec || nowMsec-1000/60;
            var deltaMsec = Math.min(200, nowMsec - lastTimeMsec);
            lastTimeMsec = nowMsec;
            // call each update function
            controller.update( source.domElement );
            // cube.rotation.x += deltaMsec/10000 * Math.PI
            //torus.rotation.y += deltaMsec/1000 * Math.PI
            //torus.rotation.z += deltaMsec/1000 * Math.PI
            renderer.render( scene, camera );
          });
      });
        
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

    //

    //this.eue = new THREEAR.Source(this.renderer);
    //console.log(this.eue);

		//THREEAR.initialize({ source: this.eue }).then((controller) => {});

    //renderer.sortObjects = false;
    //renderer.setSize(window.innerWidth,window.innerHeight);
    //renderer.render( scene, camera );

    //var controls = new OrbitControls(camera, renderer.domElement);
    //this.rendererContainer.nativeElement.appendChild(renderer.domElement);
    //controls.update();

    /*(function ($) {
      $(document).ready(function(){
        console.log("Hello from jQuery!");
      });
    })(jQuery);*/
  }
}
