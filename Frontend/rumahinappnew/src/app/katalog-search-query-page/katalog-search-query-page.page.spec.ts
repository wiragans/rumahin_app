import { ComponentFixture, TestBed, waitForAsync } from '@angular/core/testing';
import { IonicModule } from '@ionic/angular';

import { KatalogSearchQueryPagePage } from './katalog-search-query-page.page';

describe('KatalogSearchQueryPagePage', () => {
  let component: KatalogSearchQueryPagePage;
  let fixture: ComponentFixture<KatalogSearchQueryPagePage>;

  beforeEach(waitForAsync(() => {
    TestBed.configureTestingModule({
      declarations: [ KatalogSearchQueryPagePage ],
      imports: [IonicModule.forRoot()]
    }).compileComponents();

    fixture = TestBed.createComponent(KatalogSearchQueryPagePage);
    component = fixture.componentInstance;
    fixture.detectChanges();
  }));

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
