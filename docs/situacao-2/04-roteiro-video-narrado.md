# Roteiro do vídeo narrado: solução em funcionamento (mínimo 5 minutos)

**Ferramentas sugeridas:** OBS Studio (gratuito) ou a gravação de tela do Windows (Win + Alt + R). Publique no YouTube como **Não listado** e cole o link no formulário.

**Antes de gravar:** rode `database/seed.sql` de novo para deixar os dados limpos e abra duas abas, uma no sistema e outra no GitHub.

| Tempo | Tela | O que falar |
|---|---|---|
| 0:00–0:30 | Página inicial | "Olá, sou [nome], RGM [número]. Este é o AgendaFácil, um sistema web de agendamento para salões e barbearias, desenvolvido no Projeto Integrador II em PHP com padrão MVC e banco MySQL." |
| 0:30–1:00 | Página inicial | O problema: agendamento por WhatsApp gera horário duplicado e retrabalho. Mostre os cartões de serviço com duração e preço. |
| 1:00–1:45 | Criar conta | Clique em **Criar conta** sem preencher e mostre as mensagens de erro. Mostre a máscara do telefone e o botão "Mostrar senha". Crie a conta. |
| 1:45–3:00 | Novo agendamento | Escolha "Coloração" e mostre que só aparecem profissionais que fazem o serviço. Escolha um domingo e mostre o aviso. Escolha uma segunda e mostre que os horários respeitam a duração (último horário 16:00). Confirme. |
| 3:00–3:30 | Novo agendamento | Volte e mostre que o horário reservado sumiu da lista (sem conflito). |
| 3:30–4:00 | Meus agendamentos | Mostre o status e o botão Cancelar, explicando a regra das 2 horas. |
| 4:00–5:00 | Painel admin | Saia e entre como admin. Mostre os indicadores, a agenda com filtros, confirme e conclua um atendimento. Mostre que um agendamento concluído não oferece mais ações. |
| 5:00–5:45 | Serviços / Profissionais | Cadastre um serviço com nome repetido para mostrar a validação. Mostre a desativação em vez de exclusão. |
| 5:45–6:30 | Celular | Pressione F12 → modo celular e mostre o menu recolhível e as tabelas em cartões. |
| 6:30–7:30 | VS Code + terminal | Mostre a estrutura MVC (Models, Views, Controllers, Services). Rode `vendor/bin/phpunit --testdox` e mostre os 58 testes passando. |
| 7:30–8:00 | GitHub | Mostre o repositório, a pasta `docs/` organizada por situação e encerre. |
