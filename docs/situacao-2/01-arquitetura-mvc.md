# Situação 2 — Arquitetura e codificação

## 1. Tecnologias

| Camada | Tecnologia |
|---|---|
| Front-end | HTML5, CSS3 (responsivo, sem framework), JavaScript puro (Fetch API) |
| Back-end | PHP 8.1+ com orientação a objetos, padrão **MVC** |
| Banco de dados | MySQL 8 via PDO (consultas preparadas) |
| Testes | PHPUnit 9.6/10 (unitários e de integração) + Playwright (teste ponta a ponta) |
| Versionamento | Git + GitHub |
| Modo de codificação | **Tradicional** (código escrito à mão, sem low-code/no-code) |

## 2. Padrão MVC

```
Navegador ──► public/index.php (Front Controller) ──► Router
                                                        │
                       ┌────────────────────────────────┘
                       ▼
               Controller (C)  ──usa──►  Service (regras de negócio)
                   │                          │
                   │                          ▼
                   │                     Model (M) ──PDO──► MySQL
                   ▼
               View (V) ──► HTML + CSS + JS ──► Navegador
```

- **Model** (`app/Models`): conversa com o banco. Uma classe por tabela principal.
- **View** (`app/Views`): só exibe dados. Todo texto passa por `e()` (proteção XSS).
- **Controller** (`app/Controllers`): recebe a requisição, valida os dados, chama o Service/Model e escolhe a View.
- **Service** (`app/Services/AgendamentoService.php`): concentra as regras de negócio RN01–RN07. Fica separado para ser testado sem navegador.
- **Core** (`app/Core`): peças reutilizáveis (Router, Database, Session, Auth, Csrf, Validator, View).

## 3. Estrutura de pastas

```
agendafacil/
├── app/
│   ├── Controllers/   AuthController, HomeController, AgendamentoController,
│   │                  AdminController, ServicoController, ProfissionalController
│   ├── Core/          Router, Controller, Database, Config, Session, Auth, Csrf,
│   │                  Validator, View, helpers.php
│   ├── Models/        Model, Usuario, Servico, Profissional, Agendamento
│   ├── Services/      AgendamentoService, RegraNegocioException
│   └── Views/         layout, auth, agendamentos, admin, servicos, profissionais, erros
├── config/            config.php (+ config.local.example.php)
├── database/          schema.sql, seed.sql
├── docs/              situacao-1, situacao-2, situacao-3
├── public/            index.php, assets/css, assets/js   ← pasta pública do site
├── tests/             Unit, Integration, e2e.js
├── composer.json
└── phpunit.xml
```

## 4. Rotas

| Método | Rota (`?r=`) | Controller@ação | Acesso |
|---|---|---|---|
| GET | home | HomeController@index | Público |
| GET/POST | login | AuthController@formLogin / login | Público |
| GET/POST | cadastro | AuthController@formCadastro / cadastro | Público |
| POST | sair | AuthController@sair | Logado |
| GET | agendamentos | AgendamentoController@index | Cliente |
| GET/POST | agendamentos/novo | AgendamentoController@form / salvar | Cliente |
| POST | agendamentos/cancelar | AgendamentoController@cancelar | Cliente |
| GET | api/profissionais | AgendamentoController@apiProfissionais (JSON) | Logado |
| GET | api/horarios | AgendamentoController@apiHorarios (JSON) | Logado |
| GET | admin | AdminController@dashboard | Admin |
| GET | admin/agenda | AdminController@agenda | Admin |
| POST | admin/status | AdminController@status | Admin |
| GET | servicos, servicos/form | ServicoController@index / form | Admin |
| POST | servicos/salvar, servicos/excluir | ServicoController@salvar / excluir | Admin |
| GET | profissionais, profissionais/form | ProfissionalController@index / form | Admin |
| POST | profissionais/salvar, profissionais/excluir | ProfissionalController@salvar / excluir | Admin |

As rotas usam `index.php?r=...` para funcionar em qualquer hospedagem compartilhada sem precisar configurar `.htaccess`.

## 5. Tratamento de erros

| Tipo de erro | Tratamento |
|---|---|
| Campo inválido | `Validator` no servidor + validação em JavaScript; o formulário volta preenchido com a mensagem embaixo do campo |
| Regra de negócio violada | `RegraNegocioException` → mensagem amigável em alerta vermelho |
| Acesso sem login | Redireciona para o login e, depois de entrar, volta à página pedida |
| Cliente acessando área do admin | Página 403 "Acesso negado" |
| Rota inexistente | Página 404 |
| Formulário forjado ou sessão expirada | Token CSRF inválido → 419 + mensagem "Sua sessão expirou" |
| Exceção inesperada (ex.: banco fora do ar) | `set_exception_handler` grava no log e mostra a página 500 sem detalhes técnicos (com `APP_DEBUG=0`) |
| Falha na chamada AJAX | Mensagem "Não foi possível carregar os horários. Tente novamente." |
| Clique duplo no botão | Botão é desativado após o primeiro envio |

## 6. Segurança

- Senhas com `password_hash()` / `password_verify()` (bcrypt).
- PDO com `prepare()`: sem concatenação de dados do usuário em SQL.
- `htmlspecialchars` em toda saída (função `e()`).
- Token CSRF em todos os formulários POST.
- `session_regenerate_id()` no login e cookie `HttpOnly` + `SameSite=Lax`.
- Controle de acesso por perfil em cada ação (`exigirLogin()` / `exigirAdmin()`).
- O cliente só cancela os próprios agendamentos (verificação de dono no Service).
- Credenciais do banco fora do Git (`config.local.php` e variáveis de ambiente).
