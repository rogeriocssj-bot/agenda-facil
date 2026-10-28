# Situação 3 — Plano e resultado dos testes (verificação e validação)

## 1. Estratégia (Modelo V)

| Nível | O que verifica | Ferramenta | Onde está |
|---|---|---|---|
| Teste unitário | Funções isoladas: validação, cálculo de horário, conflitos, transições de status | PHPUnit | `tests/Unit/` |
| Teste de integração | Regras de negócio + Models + banco (SQLite em memória com as mesmas restrições do MySQL) | PHPUnit | `tests/Integration/` |
| Teste de sistema (ponta a ponta) | Fluxos completos no navegador, com MySQL real | Playwright | `tests/e2e.js` |
| Teste de aceitação | 5 colegas usando o sistema publicado | Formulário de teste | `03-formulario-teste-usuario.md` |

## 2. Casos de teste e resultado

### 2.1 Unitários (PHPUnit): 29 testes

| ID | Caso de teste | Resultado esperado | Resultado |
|---|---|---|---|
| CT01 | Campo obrigatório em branco | Mensagem "O campo Nome é obrigatório." | ✅ Passou |
| CT02 | E-mail `maria@` | Erro de e-mail inválido | ✅ Passou |
| CT03 | Senha com 3 caracteres | Erro de tamanho mínimo | ✅ Passou |
| CT04 | Confirmação de senha diferente | Erro "não confere" | ✅ Passou |
| CT05 | Datas `2026-02-30`, `25/12/2026`, `amanhã` | Rejeitadas | ✅ Passou |
| CT06 | Horas `24:00`, `9h`, `09:60` | Rejeitadas | ✅ Passou |
| CT07 | Telefone e preço com vírgula / negativo | Formato BR aceito, negativo rejeitado | ✅ Passou |
| CT08 | Cálculo do fim: 09:00 + 30 min, 10:00 + 120 min | 09:30 e 12:00 | ✅ Passou |
| CT09 | Serviço que passaria da meia-noite | Exceção | ✅ Passou |
| CT10 | Sobreposição de horários (6 cenários, incluindo "encostar" no fim) | Detecta só sobreposição real | ✅ Passou |
| CT11 | Transições de status | Concluído/cancelado não mudam mais | ✅ Passou |

### 2.2 Integração (PHPUnit + banco): 29 testes

| ID | Caso de teste | Regra | Resultado |
|---|---|---|---|
| CT12 | Agendamento válido gravado com hora_fim calculada | RF07 | ✅ Passou |
| CT13 | Agendar no passado | RN01 | ✅ Bloqueado |
| CT14 | Agendar no domingo | RN02 | ✅ Bloqueado |
| CT15 | Coloração (2h) às 17:00 (terminaria 19:00) | RN02 | ✅ Bloqueado |
| CT16 | Mesmo profissional, mesmo horário | RN03 | ✅ Bloqueado |
| CT17 | Mesmo horário, outro profissional | RN03 | ✅ Permitido |
| CT18 | Mesmo cliente em dois profissionais ao mesmo tempo | RN05 | ✅ Bloqueado |
| CT19 | Profissional que não faz o serviço | RN04 | ✅ Bloqueado |
| CT20 | Serviço ou profissional inativo | RF13/RF14 | ✅ Bloqueado |
| CT21 | Horário cancelado volta a ficar livre | RN03 | ✅ Passou |
| CT22 | Lista de horários livres (20 slots; some o ocupado) | RF06 | ✅ Passou |
| CT23 | Último horário respeita a duração (coloração até 16:00) | RN02 | ✅ Passou |
| CT24 | Hoje às 13:10 → primeiro horário 13:30 | RN01 | ✅ Passou |
| CT25 | Cancelar com mais de 2h | RN06 | ✅ Permitido |
| CT26 | Cancelar com menos de 2h | RN06 | ✅ Bloqueado |
| CT27 | Cancelar agendamento de outro cliente | Segurança | ✅ Bloqueado |
| CT28 | Admin confirma e conclui | RF12 | ✅ Passou |
| CT29 | Concluído não pode ser cancelado | RN07 | ✅ Bloqueado |
| CT30 | Resumo do mês e faturamento previsto (ignora cancelados) | RF10 | ✅ Passou |
| CT31 | Senha guardada como hash; e-mail normalizado | RNF01 | ✅ Passou |
| CT32 | Login com senha certa/errada/e-mail inexistente | RF03 | ✅ Passou |
| CT33 | E-mail duplicado bloqueado pelo banco | RN08 | ✅ Passou |
| CT34 | CRUD de serviço (preço com vírgula, desativar, excluir) | RF13 | ✅ Passou |
| CT35 | Nome de serviço duplicado (sem diferenciar maiúsculas) | RN09 | ✅ Passou |
| CT36 | Profissional com serviços (N:N) | RF14 | ✅ Passou |
| CT37 | Profissional inativo não aparece para o cliente | RF05 | ✅ Passou |
| CT38 | Excluir serviço com agendamentos (chave estrangeira) | RN10 | ✅ Bloqueado |

**Resultado final: 58 testes, 107 verificações, 0 falhas.** Saídas completas em [`evidencias/`](evidencias/).

```
$ vendor/bin/phpunit
OK (58 tests, 107 assertions)
```

### 2.3 Ponta a ponta (Playwright + MySQL)

| ID | Fluxo | Resultado |
|---|---|---|
| CT39 | Cadastro vazio mostra 4 erros; máscara de telefone `(11) 97777-6666` | ✅ |
| CT40 | Cadastro válido → tela de agendamento | ✅ (depois da correção do BUG-01) |
| CT41 | Serviço → profissionais filtrados → 20 horários → agendar | ✅ |
| CT42 | Horário reservado não aparece mais | ✅ |
| CT43 | Domingo mostra aviso; enviar sem horário mostra "Escolha um horário." | ✅ |
| CT44 | Cancelar agendamento | ✅ |
| CT45 | Cliente tentando abrir o painel do admin → "Acesso negado" | ✅ |
| CT46 | Login com senha errada → "E-mail ou senha incorretos." | ✅ |
| CT47 | Cadastro de serviço com nome repetido e preço inválido → corrigir e salvar | ✅ (depois da correção do BUG-03) |
| CT48 | Profissional sem serviço → "Selecione pelo menos um serviço." | ✅ |
| CT49 | Rota inexistente → página 404 | ✅ |
| CT50 | Celular (390 px): sem rolagem horizontal, menu recolhível | ✅ |

Registro da execução: [`evidencias/resultado-teste-e2e.txt`](evidencias/resultado-teste-e2e.txt). Prints: [`../situacao-2/telas/`](../situacao-2/telas/).
