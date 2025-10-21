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
  calculator,
  search,
  list,
  person,
  calendar,
  documentText,
  addCircle,
  checkmarkCircle,
  informationCircle,
  playCircle,
  videocam,
  add,
  pencil,
  chevronDown,
  chevronUp,
  construct,
  time,
  eye,
  refresh,
  create,
  trash,
  business,
  analytics,
  trophy,
  warning,
  bulb, swapHorizontal } from 'ionicons/icons';

@Component({
  selector: 'app-orcamentos-tutorial',
  templateUrl: './orcamentos.page.html',
  styleUrls: ['./orcamentos.page.scss'],
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
export class OrcamentosTutorialPage implements OnInit {
  isDev: boolean = false;

  constructor() {
    addIcons({calculator,addCircle,videocam,add,pencil,list,bulb,construct,analytics,documentText,time,eye,person,swapHorizontal,trophy,search,calendar,checkmarkCircle,informationCircle,playCircle,chevronDown,chevronUp,refresh,create,trash,business,warning});
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
