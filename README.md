# AgendaFácil

Sistema web de **agendamento de serviços** para salões, barbearias e pequenos negócios de beleza.
Projeto Integrador Transdisciplinar em Engenharia de Software II.

**Estudante:** [Seu nome] · **RGM:** [seu RGM]
**Sistema online:** [link da hospedagem] · **Vídeo:** [link do YouTube]

---

## Funcionalidades

**Cliente**
- Cadastro e login
- Agendamento em 4 passos (serviço → profissional → data → horário livre)
- Só mostra horários realmente livres, respeitando a duração de cada serviço
- Consulta e cancelamento (até 2 h antes)

**Administrador**
- Painel com indicadores do mês e atendimentos do dia
- Agenda geral com filtros e mudança de status (confirmar, concluir, cancelar)
- Cadastro de serviços e profissionais (com os serviços que cada um realiza)

## Tecnologias

| Camada | Tecnologia |
|---|---|
| Front-end | HTML5, CSS3 responsivo, JavaScript (Fetch API) |
| Back-end | PHP 8.1+, padrão **MVC**, PDO |
| Banco | MySQL 8 / MariaDB |
| Testes | PHPUnit (58 testes) + Playwright (ponta a ponta) |

## Como rodar

**Mais fácil (sem MySQL):** dois cliques em `Abrir-AgendaFacil-Mac.command` (Mac) ou `Abrir-AgendaFacil-Windows.bat` (Windows). O banco local é criado sozinho.

**Com MySQL:**

```bash
# 1. Banco de dados (MySQL)
mysql -u root -p < database/schema.sql
mysql -u root -p < database/seed.sql

# 2. Configuração
cp config/config.local.example.php config/config.local.php   # ajuste usuário/senha do MySQL

# 3. Servidor
php -S localhost:8000 -t public
```
Acesse http://localhost:8000. Para usar com XAMPP ou publicar na internet, veja o [guia de instalação](docs/situacao-2/03-instalacao-e-publicacao.md).

**Acessos de teste:** admin `admin@agendafacil.com` / `admin123` · cliente `cliente@agendafacil.com` / `cliente123`

## Testes

```bash
composer install
vendor/bin/phpunit --testdox
```
Resultado atual: **OK (58 tests, 107 assertions)**. Os testes usam SQLite em memória e não precisam do MySQL.

## Documentação

| Situação | Documento |
|---|---|
| **1. Planejamento revisado** | [Escopo e requisitos](docs/situacao-1/01-escopo-e-requisitos.md) · [UML](docs/situacao-1/02-modelagem-uml.md) · [IHC e protótipo](docs/situacao-1/03-ihc-prototipo.md) · [Banco de dados e dicionário](docs/situacao-1/04-banco-de-dados.md) |
| **2. Desenvolvimento** | [Arquitetura MVC](docs/situacao-2/01-arquitetura-mvc.md) · [Manual de uso](docs/situacao-2/02-manual-de-uso.md) · [Instalação e publicação](docs/situacao-2/03-instalacao-e-publicacao.md) · [Roteiro do vídeo](docs/situacao-2/04-roteiro-video-narrado.md) · [Telas](docs/situacao-2/telas/) |
| **3. Testes e validação** | [Plano e resultado dos testes](docs/situacao-3/01-plano-e-resultado-dos-testes.md) · [Laudo de qualidade](docs/situacao-3/02-laudo-de-qualidade.md) · [Formulário para os colegas](docs/situacao-3/03-formulario-teste-usuario.md) · [Roteiro do vídeo de correções](docs/situacao-3/04-roteiro-video-correcoes.md) · [Evidências](docs/situacao-3/evidencias/) |

## Estrutura

```
app/        Controllers, Models, Views, Services, Core (MVC)
config/     configurações
database/   schema.sql, seed.sql, hospedagem.sql
docs/       documentação por situação-problema
public/     index.php + CSS/JS (pasta pública)
tests/      Unit, Integration, e2e.js
```

## Licença
MIT
