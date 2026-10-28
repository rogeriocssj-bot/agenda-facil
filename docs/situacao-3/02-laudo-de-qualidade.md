# Laudo de Qualidade — AgendaFácil

| Item | Informação |
|---|---|
| Sistema | AgendaFácil v1.1 |
| Responsável | [Nome do estudante] — RGM [número] |
| Período de testes | [data inicial] a [data final] |
| Ambiente | PHP 8.3, MySQL/MariaDB, Chrome/Chromium (desktop 1280 px e celular 390 px) |
| Testes automatizados | 58 testes PHPUnit (107 verificações) + 12 fluxos ponta a ponta |
| Testes com usuários | 5 colegas (ver seção 4) |

## 1. Resumo

O sistema atende a todos os requisitos funcionais RF01–RF14 e às regras de negócio RN01–RN10. Durante os testes foram encontrados **5 defeitos na fase interna**, todos **corrigidos e retestados**. Nenhum defeito crítico ficou aberto.

| Severidade | Encontrados | Corrigidos | Abertos |
|---|---|---|---|
| Alta | 2 | 2 | 0 |
| Média | 2 | 2 | 0 |
| Baixa | 1 | 1 | 0 |

## 2. Defeitos encontrados e correções (testes internos)

### BUG-01: Primeiro clique em "Criar conta" não funcionava depois de um erro (Alta)

- **Como reproduzir:** abrir *Criar conta*, clicar em **Criar conta** com tudo vazio, preencher todos os campos e clicar em **Criar conta** de novo.
- **Resultado obtido:** nada acontecia. Era preciso clicar uma segunda vez.
- **Resultado esperado:** enviar o cadastro no primeiro clique.
- **Causa:** a mensagem de erro do último campo só sumia no evento *blur*, que acontece no momento do clique. Ao sumir, o formulário diminuía de altura, o botão subia e o clique caía fora dele.
- **Correção:** o JavaScript passou a revalidar o campo **enquanto o usuário digita** (evento `input`), então a mensagem some antes do clique (`public/assets/js/app.js`).
- **Evidência:** teste automatizado antes da correção terminou na mesma URL (`?r=cadastro`); depois da correção redirecionou para `?r=agendamentos/novo` (CT40). Print: `02-cadastro-validacao.png`.
- **Status:** ✅ Corrigido e retestado.

### BUG-02: Mensagens com erro de concordância (Baixa)

- **Obtido:** "Senha é obrigatório." e "Confirme a senha é obrigatório."
- **Esperado:** frase correta para qualquer campo.
- **Correção:** padrão "O campo *X* é obrigatório." no PHP (`Validator.php`) e no JavaScript. O teste unitário CT01 foi atualizado.
- **Status:** ✅ Corrigido.

### BUG-03: Erro vindo do servidor não destacava o campo e "travava" o clique (Alta)

- **Como reproduzir:** como admin, cadastrar serviço com nome já existente e preço `abc`, corrigir os dois campos e clicar em **Salvar**.
- **Obtido:** o campo com erro não ficava com borda vermelha e o clique em Salvar se perdia (mesmo efeito do BUG-01).
- **Causa:** as mensagens geradas pelo PHP não eram reconhecidas pelo JavaScript.
- **Correção:** ao carregar a página, o JavaScript marca os campos que já vêm com erro do servidor e limpa a mensagem quando o usuário corrige.
- **Evidência:** print `09-servico-validacao.png` (bordas vermelhas) e CT47 passando.
- **Status:** ✅ Corrigido e retestado.

### BUG-04: Depois do login, o cliente perdia o serviço escolhido (Média)

- **Como reproduzir:** sem estar logado, clicar em **Agendar** no cartão "Manicure", fazer login.
- **Obtido:** ia para *Meus agendamentos* e o cliente tinha que procurar o serviço de novo.
- **Esperado:** voltar para *Novo agendamento* com "Manicure" já selecionado.
- **Correção:** a página pedida fica guardada na sessão e o login redireciona para ela (`Controller::exigirLogin`, `AuthController::voltarOuIr`).
- **Evidência:** teste automatizado retornou `?r=agendamentos/novo&servico=3` com "Manicure" selecionado.
- **Status:** ✅ Corrigido e retestado.

### BUG-05: Profissional não ficava selecionado ao abrir o agendamento pelo cartão do serviço (Média)

- **Como reproduzir:** logado, clicar em **Agendar** no cartão "Coloração" (serviço com um só profissional) e escolher uma data.
- **Obtido:** o campo Profissional ficava em "Selecione..." e os horários não apareciam.
- **Esperado:** com um único profissional, ele já vem selecionado.
- **Causa:** o JavaScript tratava o valor "0" (nenhum profissional escolhido antes) como se fosse um profissional e apagava a seleção automática.
- **Correção:** o valor "0" é ignorado e o profissional anterior só é aplicado se existir na lista (`app.js`).
- **Evidência:** encontrado durante a gravação do vídeo; depois da correção, o profissional "Ana Lima" vem selecionado e os horários aparecem.
- **Status:** ✅ Corrigido e retestado.

## 3. Verificações de segurança e robustez

| Verificação | Resultado |
|---|---|
| SQL Injection (`' OR 1=1 --` no login) | ✅ Bloqueado: consultas preparadas |
| XSS (`<script>` no nome/observação) | ✅ Exibido como texto, não executa |
| Envio de formulário sem token CSRF | ✅ Recusado com mensagem "Sua sessão expirou" |
| Cliente acessando `?r=admin` | ✅ Página 403 |
| Cliente cancelando agendamento de outro (alterando o id) | ✅ Recusado (CT27) |
| Horário ocupado por outro cliente entre abrir a tela e confirmar | ✅ Recebe "Este horário acabou de ser ocupado" (CT16) |
| Clique duplo em "Confirmar" | ✅ Botão desativado após o primeiro clique |
| Senhas no banco | ✅ Hash bcrypt |

## 4. Testes com usuários (colegas)

> Preencher com o resultado real dos formulários recebidos (`03-formulario-teste-usuario.md`). Cada problema relatado vira uma linha abaixo, com o que foi feito.

| ID | Testador | Problema relatado | Severidade | Ação tomada | Status |
|---|---|---|---|---|---|
| BUG-06 | | | | | |
| BUG-07 | | | | | |
| SUG-01 | | | | | |

## 5. Limitações conhecidas (próxima versão)

- Recuperação de senha por e-mail.
- Lembrete automático por WhatsApp/e-mail.
- Folgas e horários individuais por profissional.

## 6. Conclusão

Os testes automatizados cobrem todas as regras de negócio e os fluxos principais. Os defeitos encontrados eram de usabilidade no formulário e de navegação, não de regra de negócio, e foram corrigidos com teste de regressão. O sistema está **apto para uso**, com as limitações listadas na seção 5.
