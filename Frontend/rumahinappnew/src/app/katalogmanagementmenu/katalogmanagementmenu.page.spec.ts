import { ComponentFixture, TestBed, waitForAsync } from '@angular/core/testing';
import { IonicModule } from '@ionic/angular';

import { KatalogmanagementmenuPage } from './katalogmanagementmenu.page';

describe('KatalogmanagementmenuPage', () => {
  let component: KatalogmanagementmenuPage;
  let fixture: ComponentFixture<KatalogmanagementmenuPage>;

  beforeEach(waitForAsync(() => {
    TestBed.configureTestingModule({
      declarations: [ KatalogmanagementmenuPage ],
      imports: [IonicModule.forRoot()]
    }).compileComponents();

    fixture = TestBed.createComponent(KatalogmanagementmenuPage);
    component = fixture.componentInstance;
    fixture.detectChanges();
  }));

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
