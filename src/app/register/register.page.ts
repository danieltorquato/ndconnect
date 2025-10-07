import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators, AbstractControl, FormsModule, ReactiveFormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { AuthService, RegisterData } from '../services/auth.service';
import { LoadingController, ToastController } from '@ionic/angular';
import { CommonModule } from '@angular/common';
import { IonContent, IonInput, IonLabel, IonItem, IonButton, IonIcon, IonSpinner } from '@ionic/angular/standalone';

@Component({
  selector: 'app-register',
  templateUrl: './register.page.html',
  styleUrls: ['./register.page.scss'],
  standalone: true,
  imports: [IonSpinner, IonIcon,
    IonContent,
    IonInput,
    IonLabel,
    IonItem,
    IonButton,
    CommonModule,
    FormsModule,
    ReactiveFormsModule
  ]
})
export class RegisterPage implements OnInit {
  registerForm: FormGroup;
  showPassword = false;
  showConfirmPassword = false;
  isLoading = false;
  errorMessage = '';

  constructor(
    private formBuilder: FormBuilder,
    private authService: AuthService,
    private router: Router,
    private loadingController: LoadingController,
    private toastController: ToastController
  ) {
    this.registerForm = this.formBuilder.group({
      nome: ['', [Validators.required, Validators.minLength(3)]],
      email: ['', [Validators.required, Validators.email]],
      senha: ['', [Validators.required, Validators.minLength(6)]],
      confirmarSenha: ['', [Validators.required]]
    }, { validators: this.passwordMatchValidator });
  }

  ngOnInit() {
    // Verificar se já está logado
    if (this.authService.isLoggedIn()) {
      this.router.navigate(['/painel']);
    }
  }

  passwordMatchValidator(control: AbstractControl): {[key: string]: any} | null {
    const senha = control.get('senha');
    const confirmarSenha = control.get('confirmarSenha');

    if (senha && confirmarSenha && senha.value !== confirmarSenha.value) {
      return { mismatch: true };
    }

    return null;
  }

  async onRegister() {
    if (this.registerForm.invalid) {
      this.markFormGroupTouched();
      return;
    }

    this.isLoading = true;
    this.errorMessage = '';

    const formData = this.registerForm.value;
    const registerData: RegisterData = {
      nome: formData.nome,
      email: formData.email,
      senha: formData.senha,
      nivel_acesso: 'cliente' // Por padrão, novos usuários são clientes
    };

    try {
      const response = await this.authService.register(registerData).toPromise();

      if (response?.success) {
        await this.showToast('Conta criada com sucesso! Faça login para continuar.', 'success');
        this.router.navigate(['/login']);
      } else {
        this.errorMessage = response?.message || 'Erro ao criar conta';
      }
    } catch (error) {
      console.error('Erro no registro:', error);
      this.errorMessage = 'Erro de conexão. Tente novamente.';
    } finally {
      this.isLoading = false;
    }
  }

  togglePassword() {
    this.showPassword = !this.showPassword;
  }

  toggleConfirmPassword() {
    this.showConfirmPassword = !this.showConfirmPassword;
  }

  goToLogin() {
    this.router.navigate(['/login']);
  }

  goToHome() {
    this.router.navigate(['/home']);
  }

  private markFormGroupTouched() {
    Object.keys(this.registerForm.controls).forEach(key => {
      const control = this.registerForm.get(key);
      control?.markAsTouched();
    });
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
