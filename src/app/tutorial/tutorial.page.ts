import { Component, OnInit } from '@angular/core';
import { Router, RouterModule } from '@angular/router';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import {
  IonContent,
  IonButton,
  IonIcon,
  IonCard,
  IonCardHeader,
  IonCardTitle,
  IonCardContent,
  IonGrid,
  IonRow,
  IonCol
} from '@ionic/angular/standalone';
import { addIcons } from 'ionicons';
import {
  arrowBack,
  home,
  calculator,
  people,
  cube,
  analytics,
  settings,
  documentText as docText,
  list,
  trophy,
  call,
  mail,
  chatbubbles,
  eye,
  create,
  trash,
  checkmarkCircle,
  addCircle,
  time,
  notifications,
  refresh,
  cog,
  statsChart,
  trendingUp,
  add,
  logOut,
  warningOutline,
  informationCircle,
  playCircle,
  school,
  book,
  helpCircle, barChart, flash, filter, closeCircle, search, construct, swapHorizontal, person, calendar, grid, bulb,
  documentText} from 'ionicons/icons';

@Component({
  selector: 'app-tutorial',
  templateUrl: './tutorial.page.html',
  styleUrls: ['./tutorial.page.scss'],
  standalone: true,
  imports: [
    IonContent,
    IonButton,
    IonIcon,
    IonCard,
    IonCardHeader,
    IonCardTitle,
    IonCardContent,
    IonGrid,
    IonRow,
    IonCol,
    CommonModule,
    FormsModule,
    RouterModule
  ]
})
export class TutorialPage implements OnInit {

  constructor(private router: Router) {
    // Removed 'document' from the addIcons parameter to fix type error and duplicate keys.
    addIcons({list,home,people,calculator,cube,analytics,settings,barChart,addCircle,trophy,call,flash,time,add,filter,checkmarkCircle,closeCircle,search,construct,chatbubbles,create,documentText,swapHorizontal,informationCircle,person,calendar,grid,statsChart,cog,bulb,refresh,notifications,arrowBack,school,mail,eye,trash,trendingUp,logOut,warningOutline,playCircle,book,helpCircle});
  }

  ngOnInit() {}

  voltarPainel() {
    this.router.navigate(['/painel-orcamento']);
  }

  irParaSecao(secao: string) {
    const element = document.getElementById(secao);
    if (element) {
      element.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }
}
