import { ComponentFixture, TestBed, waitForAsync } from '@angular/core/testing';
import { IonicModule } from '@ionic/angular';

import { RekomendasisawjenisrumahprefPage } from './rekomendasisawjenisrumahpref.page';

describe('RekomendasisawjenisrumahprefPage', () => {
  let component: RekomendasisawjenisrumahprefPage;
  let fixture: ComponentFixture<RekomendasisawjenisrumahprefPage>;

  beforeEach(waitForAsync(() => {
    TestBed.configureTestingModule({
      declarations: [ RekomendasisawjenisrumahprefPage ],
      imports: [IonicModule.forRoot()]
    }).compileComponents();

    fixture = TestBed.createComponent(RekomendasisawjenisrumahprefPage);
    component = fixture.componentInstance;
    fixture.detectChanges();
  }));

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
