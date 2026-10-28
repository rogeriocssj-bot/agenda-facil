# Situação 1 — Escopo e Requisitos (revisão Kaizen da PIT I)

## 1. Identificação do projeto

| Item | Descrição |
|---|---|
| Nome | **AgendaFácil** — Sistema web de agendamento de serviços |
| Tipo de aplicação | Web responsiva (funciona no computador e no celular) |
| Público-alvo | Pequenos negócios de beleza e bem-estar (salões, barbearias, manicures) e seus clientes |
| Linguagem / padrão | PHP 8 com padrão de projeto **MVC** |
| Banco de dados | MySQL 8 (SGBD popular e de fácil manutenção) |

## 2. Problema

Pequenos salões e barbearias costumam marcar horários por telefone, WhatsApp ou caderno. Isso causa:

- **horários duplicados** (dois clientes marcados no mesmo horário com o mesmo profissional);
- **tempo perdido** do profissional respondendo mensagens durante o atendimento;
- **faltas sem aviso**, porque o cliente não tem um lugar fácil para cancelar;
- **falta de controle**: o dono não sabe quantos atendimentos teve no mês nem o faturamento previsto.

## 3. Objetivo

Permitir que o **cliente agende sozinho**, vendo apenas os horários realmente livres, e que o **administrador** controle a agenda, os serviços e os profissionais em um só lugar.

## 4. Escopo

### 4.1 Dentro do escopo (o que o sistema faz)

- Cadastro e login de clientes; login de administrador.
- Catálogo público de serviços (nome, descrição, duração e preço).
- Agendamento em 4 passos: serviço → profissional → data → horário livre.
- Cálculo automático do horário de término pela duração do serviço.
- Bloqueio de conflitos de horário (profissional e cliente).
- Consulta e cancelamento dos próprios agendamentos pelo cliente.
- Painel do administrador com indicadores do mês.
- Agenda geral com filtros (data, profissional, status) e mudança de status.
- Cadastro, edição, desativação e exclusão de serviços e profissionais.

### 4.2 Fora do escopo (versões futuras)

- Pagamento online.
- Envio automático de lembretes por WhatsApp/e-mail.
- Horários diferentes por profissional (folgas individuais).
- Aplicativo nativo para celular.

## 5. Revisão em relação à PIT I (melhoria contínua — Kaizen)

| # | Como estava no planejamento | O que foi melhorado | Motivo |
|---|---|---|---|
| 1 | Cliente digitava o horário livremente | Sistema mostra **somente os horários livres** em botões | Evita erro de digitação e tentativa de horário ocupado |
| 2 | Duração única para todos os serviços | Cada serviço tem **duração própria** e o término é calculado | Coloração (2h) não pode ocupar só 30 min da agenda |
| 3 | Qualquer profissional fazia qualquer serviço | Tabela **profissional_servico** (relação N:N) | Manicure não faz corte; evita agendamento impossível |
| 4 | Exclusão física de serviços | Serviço/profissional com histórico só pode ser **desativado** | Preserva o histórico de agendamentos (integridade referencial) |
| 5 | Cancelamento a qualquer momento | Cliente cancela até **2 horas antes** | Regra pedida pelos colegas que trabalham em salão |
| 6 | Sem indicadores | Painel com atendimentos, cancelamentos e faturamento previsto | Dono precisa acompanhar o negócio |
| 7 | Layout só para computador | Layout **responsivo** (tabelas viram cartões no celular) | A maioria dos clientes agenda pelo celular |

## 6. Atores

| Ator | Descrição |
|---|---|
| Visitante | Pessoa não logada: vê os serviços, cria conta e faz login |
| Cliente | Agenda, consulta e cancela os próprios horários |
| Administrador | Dono/gerente: gerencia agenda, serviços e profissionais |

## 7. Requisitos funcionais

| Código | Requisito | Ator |
|---|---|---|
| RF01 | Exibir a lista de serviços ativos com duração e preço | Visitante |
| RF02 | Cadastrar conta de cliente (nome, e-mail, telefone, senha) | Visitante |
| RF03 | Autenticar usuário por e-mail e senha | Visitante |
| RF04 | Encerrar a sessão (sair) | Cliente/Admin |
| RF05 | Listar os profissionais que realizam o serviço escolhido | Cliente |
| RF06 | Listar os horários livres de um profissional em uma data | Cliente |
| RF07 | Registrar um agendamento | Cliente |
| RF08 | Listar os agendamentos do cliente | Cliente |
| RF09 | Cancelar agendamento respeitando a antecedência mínima | Cliente |
| RF10 | Exibir painel com indicadores do mês e atendimentos do dia | Admin |
| RF11 | Listar a agenda geral com filtros por data, profissional e status | Admin |
| RF12 | Alterar status do agendamento (confirmar, concluir, cancelar) | Admin |
| RF13 | Manter serviços (incluir, alterar, desativar, excluir) | Admin |
| RF14 | Manter profissionais e os serviços que cada um realiza | Admin |

## 8. Requisitos não funcionais

| Código | Requisito |
|---|---|
| RNF01 | Senhas armazenadas com hash seguro (`password_hash` / bcrypt) |
| RNF02 | Proteção contra SQL Injection (consultas preparadas com PDO) |
| RNF03 | Proteção contra XSS (todo texto exibido passa por `htmlspecialchars`) |
| RNF04 | Proteção contra CSRF (token em todos os formulários POST) |
| RNF05 | Interface responsiva a partir de 360 px de largura |
| RNF06 | Acessibilidade: rótulos em todos os campos, foco visível, navegação por teclado, mensagens com `role="alert"` |
| RNF07 | Mensagens de erro claras, em português, sem detalhes técnicos para o usuário |
| RNF08 | Validação no navegador (JavaScript) **e** no servidor (PHP) |
| RNF09 | Código organizado no padrão MVC e versionado no GitHub |
| RNF10 | Testes automatizados com PHPUnit |

## 9. Regras de negócio

| Código | Regra |
|---|---|
| RN01 | Não é permitido agendar em data/horário que já passou |
| RN02 | O atendimento deve começar e terminar dentro do expediente (seg. a sáb., 08:00 às 18:00) |
| RN03 | Um profissional não pode ter dois agendamentos sobrepostos |
| RN04 | O profissional precisa realizar o serviço escolhido |
| RN05 | O cliente não pode ter dois agendamentos sobrepostos |
| RN06 | O cliente só pode cancelar com pelo menos 2 horas de antecedência |
| RN07 | Agendamentos concluídos ou cancelados não mudam mais de status |
| RN08 | O e-mail do usuário é único no sistema |
| RN09 | O nome do serviço é único; a duração fica entre 10 e 480 minutos |
| RN10 | Serviço ou profissional com agendamentos não pode ser excluído, apenas desativado |
