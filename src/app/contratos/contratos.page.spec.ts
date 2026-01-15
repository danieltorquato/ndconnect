import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ReactiveFormsModule } from '@angular/forms';
import { IonicModule } from '@ionic/angular';

import { ContratosPage } from './contratos.page';

describe('ContratosPage', () => {
  let component: ContratosPage;
  let fixture: ComponentFixture<ContratosPage>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ContratosPage],
      imports: [IonicModule.forRoot(), ReactiveFormsModule]
    }).compileComponents();

    fixture = TestBed.createComponent(ContratosPage);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
