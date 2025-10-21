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
  people,
  filter,
  search,
  construct,
  chatbubbles,
  create,
  documentText,
  swapHorizontal,
  informationCircle,
  call,
  mail,
  checkmarkCircle,
  closeCircle,
  addCircle,
  refresh,
  personAdd,
  eye,
  trash,
  business,
  time,
  trophy,
  playCircle,
  videocam,
  add,
  pencil,
  chevronDown,
  chevronUp, list, bulb, analytics } from 'ionicons/icons';

@Component({
  selector: 'app-gestao-leads-tutorial',
  templateUrl: './gestao-leads.page.html',
  styleUrls: ['./gestao-leads.page.scss'],
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
export class GestaoLeadsTutorialPage implements OnInit {
  isDev: boolean = false;

  constructor() {
    addIcons({people,personAdd,videocam,add,pencil,list,bulb,filter,swapHorizontal,addCircle,call,checkmarkCircle,trophy,closeCircle,construct,search,time,documentText,informationCircle,analytics,chatbubbles,create,mail,refresh,eye,trash,business,playCircle,chevronDown,chevronUp});
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
