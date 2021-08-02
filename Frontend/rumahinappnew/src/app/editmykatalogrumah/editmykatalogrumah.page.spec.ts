import { ComponentFixture, TestBed, waitForAsync } from '@angular/core/testing';
import { IonicModule } from '@ionic/angular';

import { EditmykatalogrumahPage } from './editmykatalogrumah.page';

describe('EditmykatalogrumahPage', () => {
  let component: EditmykatalogrumahPage;
  let fixture: ComponentFixture<EditmykatalogrumahPage>;

  beforeEach(waitForAsync(() => {
    TestBed.configureTestingModule({
      declarations: [ EditmykatalogrumahPage ],
      imports: [IonicModule.forRoot()]
    }).compileComponents();

    fixture = TestBed.createComponent(EditmykatalogrumahPage);
    component = fixture.componentInstance;
    fixture.detectChanges();
  }));

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
