import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import {
  IonContent,
  IonCard,
  IonCardHeader,
  IonCardTitle,
  IonCardContent,
  IonIcon,
  IonButton,
  IonItem,
  IonLabel,
  IonAccordion,
  IonAccordionGroup,
  IonBadge,
  IonChip
} from '@ionic/angular/standalone';
import { addIcons } from 'ionicons';
import {
  cube,
  search,
  list,
  addCircle,
  create,
  trash,
  eye,
  construct,
  informationCircle,
  playCircle,
  videocam,
  add,
  pencil,
  chevronDown,
  chevronUp,
  time,
  refresh,
  business,
  analytics,
  trophy,
  warning,
  bulb,
  checkmarkCircle,
  closeCircle,
  documentText,
  settings, calculator, swapHorizontal } from 'ionicons/icons';

@Component({
  selector: 'app-produtos-tutorial',
  templateUrl: './produtos.page.html',
  styleUrls: ['./produtos.page.scss'],
  standalone: true,
  imports: [
    IonContent,
    IonCard,
    IonCardHeader,
    IonCardTitle,
    IonCardContent,
    IonIcon,
    IonButton,
    IonItem,
    IonLabel,
    IonAccordion,
    IonAccordionGroup,
    IonBadge,
    IonChip,
    CommonModule,
    FormsModule
  ]
})
export class ProdutosTutorialPage implements OnInit {
  isDev: boolean = false;

  constructor() {
    addIcons({cube,addCircle,videocam,add,pencil,list,bulb,construct,documentText,business,search,create,informationCircle,analytics,eye,warning,refresh,checkmarkCircle,calculator,swapHorizontal,trophy,trash,playCircle,chevronDown,chevronUp,time,closeCircle,settings});
  }

  ngOnInit() {
    // Verificar se o usuário é dev
    const usuario = JSON.parse(localStorage.getItem('usuario') || '{}');
    this.isDev = usuario.nivel_acesso === 'dev';
  }

  adicionarVideo(topicId: string) {
    console.log('Adicionar vídeo para tópico:', topicId);
    // Implementar lógica para adicionar vídeo
  }

  editarVideo(topicId: string) {
    console.log('Editar vídeo do tópico:', topicId);
    // Implementar lógica para editar vídeo
  }

}
