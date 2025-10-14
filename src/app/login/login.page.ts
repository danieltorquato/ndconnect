import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, FormsModule, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { AuthService, LoginResponse } from '../services/auth.service';
import { LoadingController, ToastController } from '@ionic/angular';
import { IonContent, IonInput, IonIcon, IonSpinner } from "@ionic/angular/standalone";
import { addIcons } from 'ionicons';
import { eye, eyeOff, logIn, arrowBack, alertCircle, person, lockClosed, logoFacebook, logoTwitter, logoGoogle } from 'ionicons/icons';
import { CommonModule } from '@angular/common';
import { trigger, state, style, transition, animate, keyframes } from '@angular/animations';

@Component({
  selector: 'app-login',
  templateUrl: './login.page.html',
  styleUrls: ['./login.page.scss'],
  standalone: true,
  imports: [IonSpinner, IonIcon, IonContent, IonInput, CommonModule, FormsModule, ReactiveFormsModule],
  animations: [
    trigger('slideInUp', [
      transition(':enter', [
        style({ transform: 'translateY(50px)', opacity: 0 }),
        animate('0.6s ease-out', style({ transform: 'translateY(0)', opacity: 1 }))
      ])
    ]),
    trigger('fadeInUp', [
      transition(':enter', [
        style({ transform: 'translateY(20px)', opacity: 0 }),
        animate('0.4s ease-out', style({ transform: 'translateY(0)', opacity: 1 }))
      ])
    ]),
    trigger('fadeIn', [
      transition(':enter', [
        style({ opacity: 0 }),
        animate('0.3s ease-out', style({ opacity: 1 }))
      ])
    ]),
    trigger('bounceIn', [
      transition(':enter', [
        animate('0.6s ease-out', keyframes([
          style({ transform: 'scale(0.3)', opacity: 0, offset: 0 }),
          style({ transform: 'scale(1.05)', offset: 0.6 }),
          style({ transform: 'scale(0.95)', offset: 0.8 }),
          style({ transform: 'scale(1)', opacity: 1, offset: 1 })
        ]))
      ])
    ]),
    trigger('shake', [
      transition(':enter', [
        animate('0.5s ease-in-out', keyframes([
          style({ transform: 'translateX(0)', offset: 0 }),
          style({ transform: 'translateX(-10px)', offset: 0.1 }),
          style({ transform: 'translateX(10px)', offset: 0.2 }),
          style({ transform: 'translateX(-10px)', offset: 0.3 }),
          style({ transform: 'translateX(10px)', offset: 0.4 }),
          style({ transform: 'translateX(-5px)', offset: 0.5 }),
          style({ transform: 'translateX(5px)', offset: 0.6 }),
          style({ transform: 'translateX(0)', offset: 1 })
        ]))
      ])
    ]),
    trigger('pulse', [
      state('true', style({ transform: 'scale(1)' })),
      state('false', style({ transform: 'scale(1)' })),
      transition('false => true', [
        animate('2s ease-in-out', keyframes([
          style({ transform: 'scale(1)', offset: 0 }),
          style({ transform: 'scale(1.02)', offset: 0.5 }),
          style({ transform: 'scale(1)', offset: 1 })
        ]))
      ])
    ])
  ]
})
export class LoginPage implements OnInit {
  loginForm: FormGroup;
  showPassword = false;
  isLoading = false;
  errorMessage = '';
  isUsuarioFocused = false;
  isPasswordFocused = false;
  buttonRipple = false;

  constructor(
    private formBuilder: FormBuilder,
    private authService: AuthService,
    private router: Router,
    private loadingController: LoadingController,
    private toastController: ToastController
  ) {
    addIcons({person, alertCircle, lockClosed, logIn, logoFacebook, logoTwitter, logoGoogle, arrowBack, eye, eyeOff});

            this.loginForm = this.formBuilder.group({
              usuario: ['', [Validators.required, Validators.minLength(3)]],
              senha: ['', [Validators.required, Validators.minLength(6)]]
            });
  }

  ngOnInit() {
    // Verificar se já está logado
    if (this.authService.isLoggedIn()) {
      this.router.navigate(['/painel-orcamento']);
    }
  }

  async onLogin() {
    if (this.loginForm.invalid) {
      this.markFormGroupTouched();
      return;
    }

    this.isLoading = true;
    this.errorMessage = '';

    const { usuario, senha } = this.loginForm.value;

    try {
      const response = await this.authService.login(usuario, senha).toPromise();

      if (response?.success) {
        await this.showToast('Login realizado com sucesso!', 'success');

        // Redirecionar baseado no nível de acesso
        this.redirecionarPorNivel(response.usuario?.nivel_acesso);
      } else {
        this.errorMessage = response?.message || 'Erro ao fazer login';
      }
    } catch (error) {
      console.error('Erro no login:', error);
      this.errorMessage = 'Erro de conexão. Tente novamente.';
    } finally {
      this.isLoading = false;
    }
  }

  private redirecionarPorNivel(nivel?: string) {
    switch (nivel) {
      case 'admin':
      case 'gerente':
        this.router.navigate(['/painel-orcamento']);
        break;
      case 'vendedor':
        this.router.navigate(['/painel-orcamento']);
        break;
      case 'cliente':
        this.router.navigate(['/']);
        break;
      default:
        this.router.navigate(['/']);
    }
  }

  togglePassword() {
    this.showPassword = !this.showPassword;
  }


  goToHome() {
    this.router.navigate(['/home']);
  }

  private markFormGroupTouched() {
    Object.keys(this.loginForm.controls).forEach(key => {
      const control = this.loginForm.get(key);
      control?.markAsTouched();
    });
  }

  // Métodos para animações
  onInputFocus(event: any) {
    const input = event.target;
    if (input.name === 'usuario') {
      this.isUsuarioFocused = true;
    } else if (input.name === 'senha') {
      this.isPasswordFocused = true;
    }
  }

  onInputBlur(event: any) {
    const input = event.target;
    if (input.name === 'usuario') {
      this.isUsuarioFocused = false;
    } else if (input.name === 'senha') {
      this.isPasswordFocused = false;
    }
  }

  onButtonHover(event: any) {
    this.buttonRipple = true;
    setTimeout(() => {
      this.buttonRipple = false;
    }, 600);
  }

  onButtonLeave(event: any) {
    this.buttonRipple = false;
  }

  // Métodos para login social
  loginWithFacebook() {
    this.showToast('Login com Facebook em desenvolvimento', 'warning');
  }

  loginWithTwitter() {
    this.showToast('Login com Twitter em desenvolvimento', 'warning');
  }

  loginWithGoogle() {
    this.showToast('Login com Google em desenvolvimento', 'warning');
  }

  private async showToast(message: string, color: 'success' | 'danger' | 'warning' = 'success') {
    const toast = await this.toastController.create({
      message,
      duration: 3000,
      color,
      position: 'top'
    });
    await toast.present();
  }
}
