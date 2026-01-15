import { Component } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Document, Packer, Paragraph, TextRun } from 'docx';
import { saveAs } from 'file-saver';
import { IonHeader, IonItem, IonLabel, IonInput, IonToolbar, IonContent, IonList, IonListHeader, IonCheckbox, IonTitle, IonButton, IonTextarea } from "@ionic/angular/standalone";
import { CommonModule } from '@angular/common';
import { FormsModule, ReactiveFormsModule} from '@angular/forms';

type CampoContrato =
  | 'nomeEmpresa'
  | 'enderecoEmpresa'
  | 'cnpj'
  | 'representante'
  | 'valorAcordo'
  | 'prazo'
  | 'descricaoAcordo';

type OpcoesSelecionadas = Record<CampoContrato, boolean>;

@Component({
  selector: 'app-contratos',
  templateUrl: './contratos.page.html',
  styleUrls: ['./contratos.page.scss'],
  imports: [IonTitle, IonToolbar, IonHeader, IonContent, IonList, IonListHeader, IonItem, IonCheckbox, IonButton, IonInput, IonTextarea, CommonModule, FormsModule, ReactiveFormsModule, IonLabel]
  })
export class ContratosPage {
  contratoForm: FormGroup;
  opcoesSelecionadas: OpcoesSelecionadas;

  constructor(private fb: FormBuilder) {
    this.contratoForm = this.fb.group({
      nomeEmpresa: ['', Validators.required],
      enderecoEmpresa: [''],
      cnpj: [''],
      representante: [''],
      valorAcordo: [''],
      prazo: [''],
      descricaoAcordo: ['']
    });

    // Opções para incluir no contrato
    this.opcoesSelecionadas = {
      nomeEmpresa: true,
      enderecoEmpresa: false,
      cnpj: false,
      representante: false,
      valorAcordo: false,
      prazo: false,
      descricaoAcordo: false
    };
  }

  private p(texto: string, opts?: { bold?: boolean }) {
    return new Paragraph({
      children: [
        new TextRun({
          text: texto,
          bold: opts?.bold ?? false,
        }),
      ],
    });
  }

  private vazio() {
    return new Paragraph({ children: [new TextRun('')] });
  }

  gerarContrato() {
    if (this.contratoForm.valid) {
      const dados = this.contratoForm.value;
      const inc = this.opcoesSelecionadas;

      const dadosContratante: string[] = [];
      if (inc.nomeEmpresa && dados.nomeEmpresa) dadosContratante.push(`Nome: ${dados.nomeEmpresa}`);
      if (inc.enderecoEmpresa && dados.enderecoEmpresa) dadosContratante.push(`Endereço: ${dados.enderecoEmpresa}`);
      if (inc.cnpj && dados.cnpj) dadosContratante.push(`CNPJ: ${dados.cnpj}`);
      if (inc.representante && dados.representante) dadosContratante.push(`Representante: ${dados.representante}`);

      const doc = new Document({
        sections: [{
          properties: {},
          children: [
            this.p('CONTRATO DE PRESTAÇÃO DE SERVIÇOS', { bold: true }),
            this.p(''),

            this.p('Pelo presente instrumento particular, as partes abaixo identificadas celebram o presente CONTRATO DE PRESTAÇÃO DE SERVIÇOS, que se regerá pelas cláusulas e condições seguintes.'),
            this.p(''),

            this.p('1. DAS PARTES', { bold: true }),
            this.p('1.1. CONTRATANTE:'),
            ...(dadosContratante.length > 0
              ? dadosContratante.map(linha => this.p(linha))
              : [this.p('Dados do(a) CONTRATANTE: (não informados neste documento).')]),
            this.p('1.2. CONTRATADO(A): (preencher com os dados do prestador de serviços).'),
            this.p(''),

            this.p('2. DO OBJETO', { bold: true }),
            ...(inc.descricaoAcordo && dados.descricaoAcordo
              ? [this.p(`2.1. O objeto do presente contrato consiste em: ${dados.descricaoAcordo}`)]
              : [this.p('2.1. O objeto do presente contrato consiste na prestação de serviços conforme proposta/escopo a ser acordado entre as partes.')]),
            this.p('2.2. Quaisquer ajustes de escopo deverão ser formalizados por escrito entre as partes.'),
            this.p(''),

            this.p('3. DO PRAZO', { bold: true }),
            ...(inc.prazo && dados.prazo
              ? [this.p(`3.1. O prazo de vigência deste contrato será: ${dados.prazo}.`)]
              : [this.p('3.1. O presente contrato inicia-se na data de sua assinatura e vigerá por prazo indeterminado, podendo ser rescindido conforme cláusula própria.')]),
            this.p(''),

            this.p('4. DO PREÇO, FORMA DE PAGAMENTO E REAJUSTE', { bold: true }),
            ...(inc.valorAcordo && dados.valorAcordo
              ? [this.p(`4.1. Pelos serviços, a CONTRATANTE pagará ao(à) CONTRATADO(A) o valor de: ${dados.valorAcordo}.`)]
              : [this.p('4.1. Pelos serviços, as partes acordarão o valor e a forma de pagamento em proposta/comprovante à parte, que integrará este contrato.')]),
            this.p('4.2. Salvo disposição diversa entre as partes, despesas extraordinárias dependerão de aprovação prévia da CONTRATANTE.'),
            this.p(''),

            this.p('5. DAS OBRIGAÇÕES DAS PARTES', { bold: true }),
            this.p('5.1. Obrigações do(a) CONTRATADO(A):'),
            this.p('a) executar os serviços com zelo, técnica e dentro das boas práticas aplicáveis;'),
            this.p('b) manter a CONTRATANTE informada sobre o andamento das atividades;'),
            this.p('c) cumprir prazos acordados, quando aplicáveis;'),
            this.p('5.2. Obrigações da CONTRATANTE:'),
            this.p('a) fornecer informações e materiais necessários à execução do objeto;'),
            this.p('b) realizar os pagamentos conforme ajustado;'),
            this.p('c) aprovar entregas e/ou solicitar ajustes de forma objetiva e em tempo razoável.'),
            this.p(''),

            this.p('6. CONFIDENCIALIDADE E PROTEÇÃO DE DADOS', { bold: true }),
            this.p('6.1. As partes comprometem-se a manter confidenciais todas as informações a que tiverem acesso em razão deste contrato, não as divulgando a terceiros sem autorização prévia e por escrito, exceto por obrigação legal.'),
            this.p('6.2. Quando aplicável, as partes comprometem-se a observar a legislação de proteção de dados vigente.'),
            this.p(''),

            this.p('7. PROPRIEDADE INTELECTUAL', { bold: true }),
            this.p('7.1. Salvo estipulação diversa, a titularidade de materiais/entregáveis seguirá o que estiver definido em proposta ou termo aditivo.'),
            this.p(''),

            this.p('8. RESCISÃO', { bold: true }),
            this.p('8.1. Este contrato poderá ser rescindido por qualquer das partes mediante aviso prévio por escrito, com antecedência mínima razoável, respeitadas obrigações pendentes e valores proporcionais devidos.'),
            this.p('8.2. A rescisão por descumprimento contratual poderá ocorrer de forma imediata, sem prejuízo de perdas e danos.'),
            this.p(''),

            this.p('9. DISPOSIÇÕES GERAIS', { bold: true }),
            this.p('9.1. A tolerância de uma parte quanto ao descumprimento de qualquer cláusula não implicará novação ou renúncia.'),
            this.p('9.2. Este contrato obriga as partes e seus sucessores.'),
            this.p(''),

            this.p('10. DO FORO', { bold: true }),
            this.p('10.1. Para dirimir quaisquer controvérsias oriundas deste contrato, as partes elegem o foro da comarca da CONTRATANTE, com renúncia a qualquer outro, por mais privilegiado que seja.'),
            this.p(''),

            this.p('E, por estarem assim justas e contratadas, firmam o presente instrumento em 2 (duas) vias de igual teor e forma.'),
            this.p(''),

            this.p('ASSINATURAS', { bold: true }),
            this.p('CONTRATANTE: ________________________________'),
            this.p('CONTRATADO(A): ______________________________'),
          ],
        }],
      });

      Packer.toBlob(doc).then(blob => {
        saveAs(blob, 'contrato.docx');
      });
    }
  }
}
