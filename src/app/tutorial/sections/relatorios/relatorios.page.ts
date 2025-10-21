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
  analytics,
  barChart,
  pieChart,
  trendingUp,
  trendingDown,
  people,
  calculator,
  time,
  calendar,
  documentText,
  download,
  eye,
  refresh,
  informationCircle,
  playCircle,
  videocam,
  add,
  pencil,
  chevronDown,
  chevronUp,
  construct,
  business,
  trophy,
  warning,
  bulb,
  checkmarkCircle,
  closeCircle,
  search,
  filter,
  statsChart, settings, image, list, grid } from 'ionicons/icons';

@Component({
  selector: 'app-relatorios-tutorial',
  templateUrl: './relatorios.page.html',
  styleUrls: ['./relatorios.page.scss'],
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
export class RelatoriosTutorialPage implements OnInit {
  isDev: boolean = false;

  constructor() {
    addIcons({analytics,people,videocam,add,pencil,barChart,trendingUp,calendar,trophy,construct,calculator,documentText,checkmarkCircle,time,settings,filter,download,statsChart,bulb,image,list,business,grid,pieChart,trendingDown,eye,refresh,informationCircle,playCircle,chevronDown,chevronUp,warning,closeCircle,search});
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
