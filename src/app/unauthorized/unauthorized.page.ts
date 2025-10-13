import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService, Usuario } from '../services/auth.service';
import { IonIcon, IonButton } from "@ionic/angular/standalone";
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-unauthorized',
  templateUrl: './unauthorized.page.html',
  styleUrls: ['./unauthorized.page.scss'],
  standalone: true,
  imports: [IonButton, IonIcon, CommonModule],
})
export class UnauthorizedPage implements OnInit {
  usuario: Usuario | null = null;

  constructor(
    private authService: AuthService,
    private router: Router
  ) {}

  ngOnInit() {
    
    this.usuario = this.authService.getUsuarioAtual();

  }

  getNivelDisplay(nivel: string): string {
    const niveis: { [key: string]: string } = {
      'admin': 'Administrador',
      'gerente': 'Gerente',
      'vendedor': 'Vendedor',
      'cliente': 'Cliente'
    };
    return niveis[nivel] || nivel;
  }

  goToPainel() {
    this.redirectToPage('/painel');
  }
goToPainelOrcamento() {
  this.redirectToPage('/painel-orcamento');
}
    goToHome() {
      this.redirectToPage('/home');
  }
redirectToPage(page: string) {
  this.router.navigate([page]);
}
  async logout() {
    await this.authService.logout().toPromise();
    this.redirectToPage('/login');
  }
}
