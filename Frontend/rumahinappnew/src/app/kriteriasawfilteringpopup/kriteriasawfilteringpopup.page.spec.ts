import { ComponentFixture, TestBed, waitForAsync } from '@angular/core/testing';
import { IonicModule } from '@ionic/angular';

import { KriteriasawfilteringpopupPage } from './kriteriasawfilteringpopup.page';

describe('KriteriasawfilteringpopupPage', () => {
  let component: KriteriasawfilteringpopupPage;
  let fixture: ComponentFixture<KriteriasawfilteringpopupPage>;

  beforeEach(waitForAsync(() => {
    TestBed.configureTestingModule({
      declarations: [ KriteriasawfilteringpopupPage ],
      imports: [IonicModule.forRoot()]
    }).compileComponents();

    fixture = TestBed.createComponent(KriteriasawfilteringpopupPage);
    component = fixture.componentInstance;
    fixture.detectChanges();
  }));

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
