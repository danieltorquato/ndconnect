import { ComponentFixture, TestBed } from '@angular/core/testing';
import { OrcamentoClientePage } from './orcamento-cliente.page';

describe('OrcamentoClientePage', () => {
  let component: OrcamentoClientePage;
  let fixture: ComponentFixture<OrcamentoClientePage>;

  beforeEach(() => {
    fixture = TestBed.createComponent(OrcamentoClientePage);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
