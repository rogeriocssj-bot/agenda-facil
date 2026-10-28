# Manual de uso — AgendaFácil

## Acessos de demonstração

| Perfil | E-mail | Senha |
|---|---|---|
| Administrador | admin@agendafacil.com | admin123 |
| Cliente | cliente@agendafacil.com | cliente123 |

> Troque a senha do administrador antes de usar o sistema com clientes reais.

---

## Parte 1 — Cliente

### 1.1 Criar conta
1. Na página inicial, clique em **Criar conta** (canto superior direito).
2. Preencha nome, e-mail, telefone (opcional) e senha de pelo menos 6 caracteres.
3. Repita a senha em **Confirme a senha** e clique em **Criar conta**.
4. Você entra automaticamente e vai direto para a tela de agendamento.

### 1.2 Fazer login
1. Clique em **Entrar**.
2. Informe e-mail e senha. Use **Mostrar** para conferir o que digitou.
3. Se tinha clicado em "Agendar" antes de entrar, o sistema volta para essa tela.

### 1.3 Agendar um horário
1. Clique em **Agendar** no menu (ou em **Agendar** no cartão de um serviço).
2. **Passo 1**: escolha o serviço. A duração e o preço aparecem ao lado.
3. **Passo 2**: escolha o profissional. Só aparecem os que fazem aquele serviço.
4. **Passo 3**: escolha a data. Domingos não têm atendimento.
5. **Passo 4**: clique em um dos horários livres. Horários já ocupados não aparecem.
6. Se quiser, escreva uma observação.
7. Clique em **Confirmar agendamento**. A confirmação aparece em verde.

### 1.4 Ver e cancelar agendamentos
1. Clique em **Meus agendamentos**.
2. A tabela mostra data, horário, serviço, profissional, valor e status.
3. Para cancelar, clique em **Cancelar** e confirme.
4. O botão só aparece até **2 horas antes** do horário. Depois disso, fale com o estabelecimento.

### 1.5 Significado dos status

| Status | Significado |
|---|---|
| Agendado | Horário reservado, aguardando confirmação do estabelecimento |
| Confirmado | O estabelecimento confirmou |
| Concluído | O atendimento foi realizado |
| Cancelado | Cancelado pelo cliente ou pelo estabelecimento |

---

## Parte 2 — Administrador

### 2.1 Painel
Depois do login, o administrador vê:
- agendamentos ativos, concluídos e cancelados no mês;
- faturamento previsto do mês (soma dos serviços não cancelados);
- total de clientes cadastrados;
- lista dos atendimentos de hoje, com botões de ação.

### 2.2 Agenda geral
1. Menu **Agenda**.
2. Filtre por **data**, **profissional** e/ou **status** e clique em **Filtrar**. Use **Limpar** para tirar os filtros.
3. Em cada linha, use **Confirmar**, **Concluir** ou **Cancelar**. Só aparecem as ações permitidas para o status atual.

### 2.3 Serviços
1. Menu **Serviços** → **+ Novo serviço**.
2. Informe nome, descrição, duração (10 a 480 min) e preço (ex.: `45,00`).
3. Desmarque **Serviço ativo** para esconder o serviço dos clientes sem apagar o histórico.
4. **Excluir** só funciona para serviços que nunca foram agendados.

### 2.4 Profissionais
1. Menu **Profissionais** → **+ Novo profissional**.
2. Informe nome e especialidade e marque os serviços que a pessoa realiza (pelo menos um).
3. Desmarque **Profissional ativo** quando a pessoa sair de férias ou deixar o estabelecimento.

---

## Perguntas frequentes

**Não aparece nenhum horário.** O dia está lotado, é domingo ou já passou do horário de funcionamento. Tente outra data ou outro profissional.

**Esqueci minha senha.** Nesta versão a troca é feita pelo administrador direto no banco. A recuperação por e-mail está prevista para a próxima versão.

**Apareceu "Sua sessão expirou".** O formulário ficou aberto muito tempo. Recarregue a página e envie de novo.
