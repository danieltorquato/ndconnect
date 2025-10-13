import { Component, OnInit, OnDestroy } from '@angular/core';
import { Router } from '@angular/router';
import { addIcons } from 'ionicons';
import {
  menu, close, mailOutline, logoWhatsapp, home, informationCircle,
  cube, images, call, logoInstagram, logoFacebook, logoYoutube, logoLinkedin,
  personCircle, chevronDown, documentText, people, business, cash, calendar, barChart, logOut } from 'ionicons/icons';
import {
  IonHeader, IonToolbar, IonButton, IonIcon
} from '@ionic/angular/standalone';
import { CommonModule } from '@angular/common';
import { AuthService, Usuario } from '../../services/auth.service';
import { Subscription } from 'rxjs';

@Component({
  selector: 'app-navbar',
  templateUrl: './navbar.component.html',
  styleUrls: ['./navbar.component.scss'],
  standalone: true,
  imports: [IonHeader, IonToolbar, IonButton, IonIcon, CommonModule]
})
export class NavbarComponent implements OnInit, OnDestroy {
  menuOpen = false;
  userMenuOpen = false;
  isLoggedIn = false;
  usuario: Usuario | null = null;
  private authSubscription?: Subscription;

  private linksUteis = {
    instagram: 'https://instagram.com/ndconnect_oficial',
    facebook: 'https://facebook.com/ndconnect',
    youtube: 'https://youtube.com/@ndconnect',
    linkedin: 'https://linkedin.com/company/ndconnect'
  };

  constructor(
    public router: Router,
    private authService: AuthService
  ) {
    addIcons({personCircle,chevronDown,home,documentText,cube,people,business,cash,calendar,barChart,menu,close,logOut,mailOutline,logoWhatsapp,informationCircle,images,call,logoInstagram,logoFacebook,logoYoutube,logoLinkedin});
  }

  ngOnInit() {
    this.authSubscription = this.authService.usuario$.subscribe(usuario => {
      this.usuario = usuario;
      this.isLoggedIn = !!usuario;
    });
  }

  ngOnDestroy() {
    this.authSubscription?.unsubscribe();
  }

  toggleMenu() {
    this.menuOpen = !this.menuOpen;
    this.userMenuOpen = false;
  }

  toggleUserMenu() {
    this.userMenuOpen = !this.userMenuOpen;
    this.menuOpen = false;
  }

  irPara(rota: string) {
    this.router.navigate([rota]);
    this.menuOpen = false;
    this.userMenuOpen = false;
  }

  async logout() {
    await this.authService.logout().toPromise();
    this.router.navigate(['/home']);
    this.userMenuOpen = false;
    this.menuOpen = false;
  }

  canAccess(pagina: string): boolean {
    if (!this.usuario) return false;

    // DEV tem acesso total a tudo
    if (this.usuario.nivel_acesso === 'dev') {
      return true;
    }

    // Verificação básica de níveis
    const niveis = ['cliente', 'vendedor', 'gerente', 'admin'];
    const nivelUsuario = niveis.indexOf(this.usuario.nivel_acesso);

    // Páginas administrativas
    if (pagina.startsWith('admin/')) {
      return nivelUsuario >= 1; // vendedor ou superior
    }

    // Páginas específicas
    switch (pagina) {
      case 'orcamento':
        return nivelUsuario >= 1; // vendedor ou superior
      case 'produtos':
        return nivelUsuario >= 1; // vendedor ou superior
      case 'painel':
        return true; // todos podem acessar
      default:
        return true;
    }
  }

  getNivelDisplay(nivel?: string): string {
    const niveis: { [key: string]: string } = {
      'dev': 'Desenvolvedor',
      'admin': 'Administrador',
      'gerente': 'Gerente',
      'vendedor': 'Vendedor',
      'cliente': 'Cliente'
    };
    return niveis[nivel || ''] || 'Cliente';
  }

  abrirWhatsApp() {
    const numero = '5511953898557';
    const mensagem = encodeURIComponent('Olá! Gostaria de saber mais sobre os equipamentos da N.D Connect.');
    window.open(`https://wa.me/${numero}?text=${mensagem}`, '_blank');
  }

  abrirLink(tipo: string) {
    const url = this.linksUteis[tipo as keyof typeof this.linksUteis];
    if (url) {
      window.open(url, '_blank');
    }
  }
}
