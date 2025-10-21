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
} from '@ionic/angular/standalone';
import { addIcons } from 'ionicons';
import {
  home,
  calculator,
  people,
  cube,
  analytics,
  settings,
  barChart,
  addCircle,
  trophy,
  call,
  flash,
  time,
  add,
  checkmarkCircle,
  notifications,
  refresh,
  eye,
  mail,
  chatbubbles,
  create,
  documentText,
  trendingUp,
  statsChart, school, bulb } from 'ionicons/icons';

@Component({
  selector: 'app-painel-principal-tutorial',
  templateUrl: './painel-principal.page.html',
  styleUrls: ['./painel-principal.page.scss'],
  standalone: true,
  imports: [
    IonContent,
    IonCard,
    IonCardHeader,
    IonCardTitle,
    IonCardContent,
    IonIcon,
    CommonModule,
    FormsModule
  ]
})
export class PainelPrincipalTutorialPage implements OnInit {

  constructor() {
    addIcons({home,barChart,people,addCircle,trophy,call,flash,calculator,cube,analytics,time,add,refresh,school,bulb,eye,trendingUp,settings,checkmarkCircle,notifications,mail,chatbubbles,create,documentText,statsChart});
  }

  ngOnInit() {}

}
