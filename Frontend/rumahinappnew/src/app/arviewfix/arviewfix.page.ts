import { Component, OnInit, ViewChild, ElementRef } from '@angular/core';
//import * as THREE from '../../../node_modules/threear/node_modules/three/src/Three';
//import { UVGenerator } from '../../../node_modules/threear/node_modules/three/src/geometries/ExtrudeGeometry';
//import { ShaderMaterialParameters } from '../../../node_modules/threear/node_modules/three/src/materials/ShaderMaterial';
import * as THREE from 'three';
import * as THREEAR from '../../../node_modules/threear/dist/THREEAR.js';
import { FBXLoader } from 'three/examples/jsm/loaders/FBXLoader.js';
//import { THREEx } from 'threear/dist/THREEX/threex-arpatternfile.js';
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls.js';
import { WebcamImage } from 'ngx-webcam';
import { Subject, Observable } from 'rxjs';
import { AndroidPermissions } from '@ionic-native/android-permissions/ngx';
import { StatusBar } from '@ionic-native/status-bar/ngx';
import { Router, ActivatedRoute } from '@angular/router';
import { Platform } from '@ionic/angular';
//import { File } from '@ionic-native/file/ngx';
//import { FileOpener } from '@ionic-native/file-opener';
import { Pipe } from '@angular/core';
import { DomSanitizer } from '@angular/platform-browser';

@Component({
  selector: 'app-arviewfix',
  templateUrl: './arviewfix.page.html',
  styleUrls: ['./arviewfix.page.scss'],
})

@Pipe({ name: 'safe' })
export class ArviewfixPage implements OnInit {
  @ViewChild('rendererContainer', null) rendererContainer: ElementRef;
  //@ViewChild('canvas') canvas: ElementRef<HTMLCanvasElement>;
  //@ViewChild('myCanvas', {static: false}) myCanvas: ElementRef;

  //public context: CanvasRenderingContext2D;

  public webcamImage: WebcamImage = null;
  private trigger: Subject<void> = new Subject<void>();

  getDataFromParams = "";
  decodeParams = "";
  patternMarkerPattData: any;

  exitModalBackButtonSubscribe: any;

  progressLoading = "";

  aku:any;

  constructor(private router: Router, private androidPermissions: AndroidPermissions, private statusBar: StatusBar, private activatedRoute: ActivatedRoute, public platform: Platform, private sanitizer: DomSanitizer)
  {
    this.exitModalBackButtonSubscribe = this.platform.backButton.subscribeWithPriority(666666, () =>{
      if(this.constructor.name == "ArviewfixPage")
      {
        this.backHrefBtn();
      }
    });
  }

  transform(url)
  {
    return this.sanitizer.bypassSecurityTrustResourceUrl(url);
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

  ngOnInit()
  {
    this.statusBar.overlaysWebView(false);
    this.statusBar.show();
    
    this.getDataFromParams = this.activatedRoute.snapshot.paramMap.get('data');
    this.decodeParams = JSON.parse(window.atob(this.getDataFromParams));

    this.androidPermissions.checkPermission(this.androidPermissions.PERMISSION.CAMERA).then(
      result => console.log('Has permission?',result.hasPermission),
      err => this.androidPermissions.requestPermission(this.androidPermissions.PERMISSION.CAMERA)
    );
    
    this.androidPermissions.requestPermissions([this.androidPermissions.PERMISSION.CAMERA, this.androidPermissions.PERMISSION.GET_ACCOUNTS]);

    //this.renderAR();
    this.patternMarkerMake();

    //this.patternOrientationMake(image);
    
    //this.renderARWithPatterMarker();

    /*image.onload = async () => {
      this.patternOrientationMake(image);
      this.renderARWithPatterMarker();
    };*/

    /*image.onload = () => {
      this.patternOrientationMake(image);
      this.renderARWithPatterMarker();
    };*/

    //console.log(image);

    //this.patternOrientationMake(image);
    //this.renderARWithPatterMarker();

    /*image.onload = (e: any) => {
      alert("a");
      this.patternOrientationMake(image);
      this.renderARWithPatterMarker();
    };*/

    //this.patternOrientationMake(image);
    //this.renderARWithPatterMarker();
  }

  backHrefBtn()
  {
    window.location.href = this.decodeParams['backHref'];
    //console.log(this.decodeParams['backHref']);
  }

  patternMarkerMake()
  {
    let image = new Image();

    image.src = this.decodeParams['linkMarkerAR'];
    image.crossOrigin = 'Anonymous';

    image.onload = (e: any) => {
      this.patternOrientationMake(image);
    };

    this.renderARWithPatterMarker();
  }

  patternOrientationMake(image)
  {
    //let canvas = document.createElement('canvas');
    let canvas = <HTMLCanvasElement> document.getElementById('myCanvas');
    let context = canvas.getContext('2d');
    //this.context = this.myCanvas.nativeElement.getContext('2d');
    canvas.width = 16;
    canvas.height = 16;

	// document.body.appendChild(canvas)
	// canvas.style.width = '200px'

	let patternFileString = '';
  for(let orientation = 0; orientation > -2 * Math.PI; orientation -= Math.PI/2)
  {
		// draw on canvas - honor orientation
		context.save();
    context.clearRect(0,0,canvas.width,canvas.height);
		context.translate(canvas.width/2,canvas.height/2);
		context.rotate(orientation);
		context.drawImage(image, -canvas.width/2,-canvas.height/2, canvas.width, canvas.height);
    context.restore();
    
    /*this.context.save();
    this.context.clearRect(0,0,16,16);
		this.context.translate(16/2,16/2);
		this.context.rotate(orientation);
		this.context.drawImage(image, -16/2,-16/2, 16, 16);
		this.context.restore();*/

		// get imageData
    let imageData = context.getImageData(0, 0, canvas.width, canvas.height);

		// generate the patternFileString for this orientation
    if( orientation !== 0 )
    {
      patternFileString += '\n';
    }
		// NOTE bgr order and not rgb!!! so from 2 to 0
		for(let channelOffset = 2; channelOffset >= 0; channelOffset--){
			// console.log('channelOffset', channelOffset)
			for(let y = 0; y < imageData.height; y++){
				for(let x = 0; x < imageData.width; x++){

          if( x !== 0 )
          {
            patternFileString += ' ';
          }

					let offset = (y*imageData.width*4) + (x * 4) + channelOffset;
					let value = imageData.data[offset];

					patternFileString += String(value).padStart(3);
				}
				patternFileString += '\n';
			}
		}
  }

  this.patternMarkerPattData = patternFileString;
  //console.log(context.getImageData(0, 0, canvas.width, canvas.height)['data']);
  //alert(context.getImageData(0, 0, canvas.width, canvas.height)['data']);
  //console.log(this.patternMarkerPattData);

  //console.log(patternFileString);
  }

  renderARWithPatterMarker()
  {
    let renderer = new THREE.WebGLRenderer({
      antialias	: true,
      alpha: true
    });
    renderer.setPixelRatio( window.devicePixelRatio );
    renderer.setClearColor(new THREE.Color('lightgrey'), 0)
    renderer.setSize( window.innerWidth, window.innerHeight );
    renderer.gammaOutput = true;
    renderer.gammaFactor = 2.2;
    renderer.shadowMap.enabled = true;
  
    document.body.appendChild(renderer.domElement);
  
    // init scene and camera
    let scene = new THREE.Scene();
    let camera = new THREE.Camera();
    scene.add(camera);
  
    let markerGroup = new THREE.Group();
    scene.add(markerGroup);
  
    let source = new THREEAR.Source({ renderer, camera });
    
    let loader = new FBXLoader();
  
    //
  
    let hlight = new THREE.AmbientLight (0xffffff, 0.4);
    scene.add(hlight);
  
    //DIRECTIONAL LIGHT
  
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFSoftShadowMap;
  
    let directionalLight = new THREE.DirectionalLight(0xffffff, 0.2);
    directionalLight.position.set(0,1,0);
    directionalLight.castShadow = true;
    scene.add(directionalLight);
  
    //ADDITIONAL LIGHT UNTUK EHANCE GRAPHIC
  
    let light = new THREE.PointLight(0xc4c4c4, 0.2);
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
      this.decodeParams['fbxFileUrl'],
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
  
        let tempTextureUrl = new THREE.TextureLoader().load(this.decodeParams['fbxLinkDiffuseTexture']);
  
          object.traverse( function ( child ){
            if ( child.isMesh )
            {
              //console.log( child.geometry.attributes.uv );
              child.material.map = tempTextureUrl; // assign your diffuse texture here
            } 
          });
  
        THREEAR.initialize({ source: source }).then((controller) => {
          // add a torus knot		
          //var geometry = new THREE.TorusKnotGeometry(0.3,0.1,64,16);
          //var material = new THREE.MeshNormalMaterial(); 
          //var torus = new THREE.Mesh( geometry, material );
          //torus.position.y = 0.5
          markerGroup.add(object);
          //console.log(torus);
  
          let fileName = "wiraok";
  
          //var domElement = window.document.createElement('a');
          //domElement.href = window.URL.createObjectURL(new Blob([patternFileString], {type: 'text/plain'}));
          //domElement.download = fileName;
          //document.body.appendChild(domElement)
          //domElement.click();
          //document.body.removeChild(domElement)
  
          //var domElement = window.document.createElement('a');
          let blobFile = null;
          blobFile = window.URL.createObjectURL(new Blob([this.patternMarkerPattData], {type: 'text/plain'}));
          //blobFile = new Blob([this.patternMarkerPattData], {type: 'text/plain'});
  
          //console.log(blobFile);
  
          //console.log(domElement.innerHTML);
  
          /*var xhttp = new XMLHttpRequest();
          xhttp.onreadystatechange = function() {
              if (this.readyState == 4 && this.status == 200)
              {
                
              }
          };
          xhttp.open("GET", "", true);
          xhttp.send();*/
  
          /*let patternMarker = new THREEAR.PatternMarker({
            patternUrl: 'https://jameslmilner.github.io/THREEAR/data/patt.hiro',
            markerObject: markerGroup,
            //minConfidence: 0.10
          });*/

          let oklah = window.btoa(this.patternMarkerPattData);

          let patternMarker = new THREEAR.PatternMarker({
            patternUrl: 'https://api.netspeed.my.id/rumahinapi/cdn/patternMarkerTHREEARPass.php?data=' + oklah,
            markerObject: markerGroup
          });
          
          console.log(patternMarker);
          
          //console.log(scene);
  
          controller.trackMarker(patternMarker);

          //console.log(blobFile);
          //alert("Blobnya: " + blobFile);

          //this.file.writeFile(this.file.externalRootDirectory + "/Download", "siaplah1", new Blob([this.patternMarkerPattData]), {replace: true});

          //this.file.checkDir(this.file.dataDirectory, 'mydir').then(_ => console.log('Directory exists')).catch(err => console.log('Directory doesnt exist'));

          //console.log(window.URL.createObjectURL(this.patternMarkerPattData));
          
          //renderer.sortObjects = false;
          //renderer.setSize(window.innerWidth,window.innerHeight);
  
          // run the rendering loop
          let lastTimeMsec = 0;
          requestAnimationFrame(function animate(nowMsec){
            // keep looping
            requestAnimationFrame( animate );
            // measure time
            lastTimeMsec = lastTimeMsec || nowMsec-1000/60;
            let deltaMsec = Math.min(200, nowMsec - lastTimeMsec);
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

        this.progressLoading = "Memuat " + Math.round( parseInt(xhr.loaded) / parseInt(xhr.total) * 100 ) + "%";

        if( Math.round( parseInt(xhr.loaded) / parseInt(xhr.total) * 100 ) >= 100)
        {
          this.progressLoading = "Kembali";
        }
    
      },
      // called when loading has errors
      ( error ) => {
    
        console.log( 'An error happened' );
    
      }
    );
  }

  renderAR():void
  {
    /*let renderer = new THREE.WebGLRenderer({
      antialias	: true,
      alpha: true
    });
    renderer.setPixelRatio( window.devicePixelRatio );
    renderer.setClearColor(new THREE.Color('lightgrey'), 0)
    renderer.setSize( window.innerWidth, window.innerHeight );
    renderer.gammaOutput = true;
    renderer.gammaFactor = 2.2;
    renderer.shadowMap.enabled = true;

    document.body.appendChild(renderer.domElement);
  
    // init scene and camera
    let scene = new THREE.Scene();
    let camera = new THREE.Camera();
    scene.add(camera);

    let markerGroup = new THREE.Group();
    scene.add(markerGroup);

    let source = new THREEAR.Source({ renderer, camera });
    
    let loader = new FBXLoader();

    //

    let hlight = new THREE.AmbientLight (0xffffff, 0.4);
    scene.add(hlight);

    //DIRECTIONAL LIGHT

    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFSoftShadowMap;

    let directionalLight = new THREE.DirectionalLight(0xffffff, 0.2);
    directionalLight.position.set(0,1,0);
    directionalLight.castShadow = true;
    scene.add(directionalLight);

    //ADDITIONAL LIGHT UNTUK EHANCE GRAPHIC

    let light = new THREE.PointLight(0xc4c4c4, 0.2);
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
      this.decodeParams['fbxFileUrl'],
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
  
          let patternMarker = new THREEAR.PatternMarker({
            patternUrl: 'https://jameslmilner.github.io/THREEAR/data/patt.hiro',
            markerObject: markerGroup
          });
          
          //console.log(scene);
  
          controller.trackMarker(patternMarker);
          
          //renderer.sortObjects = false;
          //renderer.setSize(window.innerWidth,window.innerHeight);
  
          // run the rendering loop
          let lastTimeMsec = 0;
          requestAnimationFrame(function animate(nowMsec){
            // keep looping
            requestAnimationFrame( animate );
            // measure time
            lastTimeMsec = lastTimeMsec || nowMsec-1000/60;
            let deltaMsec = Math.min(200, nowMsec - lastTimeMsec);
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

    */

  }
}