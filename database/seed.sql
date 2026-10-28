-- =====================================================================
-- AgendaFácil - Dados iniciais para teste
-- Usuário administrador: admin@agendafacil.com / senha: admin123
-- Usuário cliente:       cliente@agendafacil.com / senha: cliente123
-- =====================================================================
USE agendafacil;
SET NAMES utf8mb4;

INSERT INTO usuarios (nome, email, telefone, senha_hash, perfil) VALUES
('Administrador', 'admin@agendafacil.com', '(11) 99999-0000',
 '$2y$10$haHxRty21DDRg5xd4UuNXePBqEN5gKnFrGqjvSLNqZemdZOpR4A7S', 'admin'),
('Maria Cliente', 'cliente@agendafacil.com', '(11) 98888-1111',
 '$2y$10$b3lLa0DMtm3XzEk8JYn2A.s8LbCSvnhtv3h0aeMIdxEmsu5l2Oc9m', 'cliente');

INSERT INTO servicos (nome, descricao, duracao_min, preco) VALUES
('Corte de cabelo', 'Corte masculino ou feminino com lavagem', 30, 45.00),
('Barba', 'Barba completa com toalha quente', 30, 30.00),
('Manicure', 'Cutilagem e esmaltação', 60, 35.00),
('Escova', 'Escova modeladora', 60, 50.00),
('Coloração', 'Coloração completa', 120, 150.00);

INSERT INTO profissionais (nome, especialidade) VALUES
('Carlos Souza', 'Barbeiro'),
('Ana Lima', 'Cabeleireira'),
('Juliana Rocha', 'Manicure');

INSERT INTO profissional_servico (profissional_id, servico_id) VALUES
(1, 1), (1, 2),
(2, 1), (2, 4), (2, 5),
(3, 3);
