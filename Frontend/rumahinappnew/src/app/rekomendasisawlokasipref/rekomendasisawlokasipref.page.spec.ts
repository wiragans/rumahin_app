import { ComponentFixture, TestBed, waitForAsync } from '@angular/core/testing';
import { IonicModule } from '@ionic/angular';

import { RekomendasisawlokasiprefPage } from './rekomendasisawlokasipref.page';

describe('RekomendasisawlokasiprefPage', () => {
  let component: RekomendasisawlokasiprefPage;
  let fixture: ComponentFixture<RekomendasisawlokasiprefPage>;

  beforeEach(waitForAsync(() => {
    TestBed.configureTestingModule({
      declarations: [ RekomendasisawlokasiprefPage ],
      imports: [IonicModule.forRoot()]
    }).compileComponents();

    fixture = TestBed.createComponent(RekomendasisawlokasiprefPage);
    component = fixture.componentInstance;
    fixture.detectChanges();
  }));

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
