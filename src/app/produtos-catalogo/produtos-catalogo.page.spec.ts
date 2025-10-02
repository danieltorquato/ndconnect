import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ProdutosCatalogoPage } from './produtos-catalogo.page';

describe('ProdutosCatalogoPage', () => {
  let component: ProdutosCatalogoPage;
  let fixture: ComponentFixture<ProdutosCatalogoPage>;

  beforeEach(() => {
    fixture = TestBed.createComponent(ProdutosCatalogoPage);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
