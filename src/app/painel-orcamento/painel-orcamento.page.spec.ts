import { ComponentFixture, TestBed } from '@angular/core/testing';
import { PainelOrcamentoPage } from './painel-orcamento.page';

describe('PainelOrcamentoPage', () => {
  let component: PainelOrcamentoPage;
  let fixture: ComponentFixture<PainelOrcamentoPage>;

  beforeEach(() => {
    fixture = TestBed.createComponent(PainelOrcamentoPage);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
