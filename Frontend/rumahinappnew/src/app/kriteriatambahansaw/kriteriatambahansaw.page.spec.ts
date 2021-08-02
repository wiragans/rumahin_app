import { ComponentFixture, TestBed, waitForAsync } from '@angular/core/testing';
import { IonicModule } from '@ionic/angular';

import { KriteriatambahansawPage } from './kriteriatambahansaw.page';

describe('KriteriatambahansawPage', () => {
  let component: KriteriatambahansawPage;
  let fixture: ComponentFixture<KriteriatambahansawPage>;

  beforeEach(waitForAsync(() => {
    TestBed.configureTestingModule({
      declarations: [ KriteriatambahansawPage ],
      imports: [IonicModule.forRoot()]
    }).compileComponents();

    fixture = TestBed.createComponent(KriteriatambahansawPage);
    component = fixture.componentInstance;
    fixture.detectChanges();
  }));

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
