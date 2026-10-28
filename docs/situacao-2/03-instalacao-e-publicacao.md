# Instalação local e publicação

## ⭐ Jeito mais fácil: modo local (sem XAMPP e sem MySQL)

O sistema guarda os dados num arquivo (SQLite), que é criado sozinho na primeira vez. Os dados iniciais e os logins de teste já vêm prontos.

**Mac**
1. Descompacte o `agendafacil.zip`.
2. Dê dois cliques em **`Abrir-AgendaFacil-Mac.command`**.
   - Se o Mac avisar que o arquivo é de "desenvolvedor não identificado": clique com o **botão direito** no arquivo → **Abrir** → **Abrir**.
3. O arquivo procura o PHP no Mac. Se não achar, o jeito mais fácil é instalar o **XAMPP para Mac** (apachefriends.org): arraste-o para Aplicativos e abra o arquivo de novo. Não precisa ligar nada no XAMPP. (Também dá para usar o PHP do Homebrew, se já tiver.)
4. O navegador abre em `http://localhost:8000`. Para parar, feche a janela do Terminal.
5. Para apagar tudo e recomeçar do zero: dois cliques em **`Zerar-dados-Mac.command`**.

**Windows** (não precisa instalar nada)
1. Clique com o botão direito no `agendafacil.zip` → **Extrair tudo** e abra a pasta extraída.
2. Dê dois cliques em **`Abrir-AgendaFacil-Windows.bat`**.
   - Se aparecer "O Windows protegeu o computador": **Mais informações** → **Executar assim mesmo**.
3. Na primeira vez, o arquivo baixa sozinho o PHP oficial (php.net, cerca de 30 MB) para a pasta `php-portable`. Se faltar o componente Visual C++ da Microsoft, ele também é instalado (clique em **Sim** se o Windows pedir).
4. O navegador abre em `http://localhost:8000`. Para parar, feche a janela preta. Para recomeçar do zero: **`Zerar-dados-Windows.bat`**.
   - Se o XAMPP já estiver instalado em `C:\xampp`, o arquivo usa o PHP dele e não baixa nada.

> O modo local é para usar e demonstrar no próprio computador. Para publicar na internet (link da atividade), use o MySQL (seção C).

## A. Rodar no seu computador com MySQL (XAMPP)

1. Instale o **XAMPP** (vem com PHP 8 e MySQL/MariaDB): https://www.apachefriends.org
2. Abra o XAMPP Control Panel e clique em **Start** no **Apache** e no **MySQL**.
3. Copie a pasta `agendafacil` para `C:\xampp\htdocs\`.
4. Acesse `http://localhost/phpmyadmin` → aba **Importar** → importe `database/schema.sql` e depois `database/seed.sql`.
5. Copie `config/config.local.example.php` para `config/config.local.php` e confira usuário `root` com senha vazia.
6. Acesse **http://localhost/agendafacil/public/**.

**Alternativa sem Apache:** com o PHP instalado, rode na pasta do projeto:
```bash
php -S localhost:8000 -t public
```
e acesse http://localhost:8000.

## B. Rodar os testes

```bash
composer install          # instala o PHPUnit (uma vez só)
vendor/bin/phpunit --testdox
```
Os testes usam SQLite em memória, então **não precisam do MySQL ligado**.

Teste ponta a ponta (opcional, precisa do Node.js e do sistema rodando em localhost:8000):
```bash
npm i -D playwright && npx playwright install chromium
node tests/e2e.js ./prints
```

## C. Publicar na internet (InfinityFree, gratuito, PHP + MySQL)

1. Crie uma conta em https://www.infinityfree.com e clique em **Create Account** (hospedagem).
2. Escolha um subdomínio gratuito, por exemplo `agendafacil.infinityfreeapp.com`.
3. No painel (**Control Panel / VistaPanel**) → **MySQL Databases** → crie um banco. Anote **host**, **nome do banco**, **usuário** e **senha**.
4. Abra o **phpMyAdmin** do banco criado → **Importar**:
   - importe **`database/hospedagem.sql`** (já vem sem `CREATE DATABASE`/`USE`, porque na hospedagem o banco já existe com outro nome; contém tabelas e dados iniciais).
5. Edite `config/config.local.php` com os dados da hospedagem:
   ```php
   'db' => [
       'dsn'     => 'mysql:host=sqlXXX.infinityfree.com;dbname=if0_XXXXX_agenda;charset=utf8mb4',
       'usuario' => 'if0_XXXXX',
       'senha'   => 'SUA_SENHA',
   ],
   'app' => ['debug' => false],
   ```
6. No **Gerenciador de arquivos** (ou FileZilla), dentro de `htdocs/`:
   - envie **o conteúdo** de `public/` (index.php e a pasta assets) direto para `htdocs/`;
   - envie as pastas `app/` e `config/` para `htdocs/` também (o `index.php` detecta essa estrutura sozinho).
7. Acesse o seu endereço. Esse é o **link da solução em funcionamento** para o formulário da atividade.

> As pastas `app/` e `config/` já têm um `.htaccess` que bloqueia acesso direto pelo navegador. Outra opção gratuita com PHP + MySQL: AwardSpace.

## D. Subir para o GitHub

```bash
git init
git add .
git commit -m "AgendaFácil - PIT Engenharia de Software II"
git branch -M main
git remote add origin https://github.com/SEU_USUARIO/agendafacil.git
git push -u origin main
```
O arquivo `.gitignore` já impede o envio de `vendor/` e de `config/config.local.php` (que tem sua senha).
