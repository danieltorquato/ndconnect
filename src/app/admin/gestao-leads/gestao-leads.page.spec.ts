import { ComponentFixture, TestBed } from '@angular/core/testing';
import { GestaoLeadsPage } from './gestao-leads.page';

describe('GestaoLeadsPage', () => {
  let component: GestaoLeadsPage;
  let fixture: ComponentFixture<GestaoLeadsPage>;

  beforeEach(() => {
    fixture = TestBed.createComponent(GestaoLeadsPage);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
