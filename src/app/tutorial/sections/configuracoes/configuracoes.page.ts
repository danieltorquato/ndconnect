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
  settings,
  person,
  shield,
  key,
  notifications,
  colorPalette,
  language,
  time,
  business,
  mail,
  globe,
    eye,
  eyeOff,
  checkmarkCircle,
  closeCircle,
  warning,
  informationCircle,
  playCircle,
  videocam,
  add,
  pencil,
  chevronDown,
  chevronUp,
  construct,
  analytics,
  trophy,
  bulb,
  search,
  filter,
  refresh,
  save,
  trash,
  create,
  list,
  grid,
  documentText,
  download, people, cloud } from 'ionicons/icons';

@Component({
  selector: 'app-configuracoes-tutorial',
  templateUrl: './configuracoes.page.html',
  styleUrls: ['./configuracoes.page.scss'],
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
export class ConfiguracoesTutorialPage implements OnInit {
  isDev: boolean = false;

  constructor() {
    addIcons({settings,person,videocam,add,pencil,business,notifications,shield,construct,people,mail,time,colorPalette,language,grid,eye,globe,analytics,cloud,key,download,warning,eyeOff,checkmarkCircle,closeCircle,informationCircle,playCircle,chevronDown,chevronUp,trophy,bulb,search,filter,refresh,save,trash,create,list,documentText});
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
