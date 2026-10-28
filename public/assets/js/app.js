/* =========================================================
   AgendaFácil - Scripts do front-end (JavaScript puro)
   ========================================================= */
(function () {
    'use strict';

    // ---------- Menu mobile ----------
    const toggle = document.querySelector('.menu-toggle');
    const menu = document.getElementById('menu');
    if (toggle && menu) {
        toggle.addEventListener('click', () => {
            const aberto = menu.classList.toggle('aberto');
            toggle.setAttribute('aria-expanded', String(aberto));
        });
    }

    // ---------- Fechar alertas ----------
    document.querySelectorAll('.alerta-fechar').forEach((btn) => {
        btn.addEventListener('click', () => btn.closest('.alerta').remove());
    });

    // ---------- Confirmação antes de ações destrutivas ----------
    document.querySelectorAll('form[data-confirmar]').forEach((form) => {
        form.addEventListener('submit', (ev) => {
            if (!window.confirm(form.dataset.confirmar)) ev.preventDefault();
        });
    });

    // ---------- Mostrar/ocultar senha ----------
    document.querySelectorAll('.ver-senha').forEach((btn) => {
        btn.addEventListener('click', () => {
            const campo = document.getElementById(btn.dataset.alvo);
            const mostrar = campo.type === 'password';
            campo.type = mostrar ? 'text' : 'password';
            btn.textContent = mostrar ? 'Ocultar' : 'Mostrar';
            btn.setAttribute('aria-label', mostrar ? 'Ocultar senha' : 'Mostrar senha');
        });
    });

    // ---------- Máscara de telefone ----------
    document.querySelectorAll('[data-mascara="telefone"]').forEach((campo) => {
        campo.addEventListener('input', () => {
            const d = campo.value.replace(/\D/g, '').slice(0, 11);
            let v = d;
            if (d.length > 2) v = '(' + d.slice(0, 2) + ') ' + d.slice(2);
            if (d.length > 7) v = '(' + d.slice(0, 2) + ') ' + d.slice(2, d.length - 4) + '-' + d.slice(-4);
            campo.value = v;
        });
    });

    // ---------- Validação no navegador (o servidor valida de novo) ----------
    function mensagemErro(campo) {
        const rotulo = (document.querySelector('label[for="' + campo.id + '"]') || {}).textContent || 'Campo';
        const nome = rotulo.replace('*', '').replace(/\(.*\)/, '').replace(/^\d\.\s*/, '').trim();
        if (campo.validity.valueMissing) return 'O campo ' + nome + ' é obrigatório.';
        if (campo.validity.typeMismatch && campo.type === 'email') return 'Informe um e-mail válido.';
        if (campo.validity.tooShort) return nome + ' deve ter pelo menos ' + campo.minLength + ' caracteres.';
        if (campo.validity.rangeUnderflow || campo.validity.rangeOverflow) return nome + ' fora do intervalo permitido.';
        if (campo.dataset.igual) {
            const outro = document.getElementById(campo.dataset.igual);
            if (outro && outro.value !== campo.value) return 'As senhas não conferem.';
        }
        return '';
    }

    function marcar(campo, msg) {
        const container = campo.closest('.campo') || campo.parentElement;
        let aviso = container.querySelector('.erro-campo');
        if (msg) {
            campo.classList.add('campo-invalido');
            campo.setAttribute('aria-invalid', 'true');
            if (!aviso) {
                aviso = document.createElement('small');
                aviso.className = 'erro-campo';
                container.appendChild(aviso);
            }
            aviso.textContent = msg;
        } else {
            campo.classList.remove('campo-invalido');
            campo.removeAttribute('aria-invalid');
            if (aviso) aviso.remove();
        }
    }

    document.querySelectorAll('form[data-validar]').forEach((form) => {
        const campos = form.querySelectorAll('input:not([type=hidden]):not([type=checkbox]):not([type=radio]), select, textarea');
        // Correção BUG-03: erros vindos do servidor também deixam a borda vermelha
        // e passam a ser limpos enquanto o usuário corrige o campo.
        campos.forEach((c) => {
            const container = c.closest('.campo') || c.parentElement;
            if (container.querySelector('.erro-campo')) {
                c.classList.add('campo-invalido');
                c.setAttribute('aria-invalid', 'true');
            }
        });
        campos.forEach((c) => {
            c.addEventListener('blur', () => marcar(c, mensagemErro(c)));
            // Correção BUG-01: revalida enquanto o usuário digita, para a mensagem sumir
            // antes do clique no botão (evita o botão "pular" de lugar e o clique se perder).
            c.addEventListener('input', () => {
                if (c.classList.contains('campo-invalido')) marcar(c, mensagemErro(c));
            });
        });
        form.addEventListener('submit', (ev) => {
            let primeiro = null;
            campos.forEach((c) => {
                if (c.disabled) return;
                const msg = mensagemErro(c);
                marcar(c, msg);
                if (msg && !primeiro) primeiro = c;
            });
            const grupoHora = form.querySelector('#horarios');
            if (grupoHora && !form.querySelector('input[name="hora"]:checked')) {
                const aviso = grupoHora.parentElement.querySelector('.erro-campo') || document.createElement('small');
                aviso.className = 'erro-campo';
                aviso.textContent = 'Escolha um horário.';
                grupoHora.parentElement.appendChild(aviso);
                if (!primeiro) primeiro = grupoHora;
            }
            if (primeiro) {
                ev.preventDefault();
                primeiro.scrollIntoView({ behavior: 'smooth', block: 'center' });
                if (primeiro.focus) primeiro.focus();
                return;
            }
            const btn = form.querySelector('button[type=submit]');
            if (btn) { btn.disabled = true; btn.textContent = 'Enviando...'; } // evita clique duplo
        });
    });

    // ---------- Tela de agendamento: carrega profissionais e horários ----------
    const formAg = document.getElementById('form-agendamento');
    if (!formAg) return;

    const selServico = document.getElementById('servico_id');
    const selProf = document.getElementById('profissional_id');
    const inpData = document.getElementById('data');
    const boxHoras = document.getElementById('horarios');
    // Correção BUG-05: o valor "0" (nenhum profissional anterior) era tratado como verdadeiro
    // e apagava a seleção automática quando o serviço tem um único profissional.
    let profOld = formAg.dataset.profissionalOld !== '0' ? formAg.dataset.profissionalOld : '';
    let horaOld = formAg.dataset.horaOld;

    function msgHorarios(texto, classe) {
        boxHoras.innerHTML = '';
        const p = document.createElement('p');
        p.className = classe || 'muted';
        p.textContent = texto;
        boxHoras.appendChild(p);
    }

    async function buscarJSON(url) {
        const resp = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
        if (resp.status === 401) { window.location.reload(); throw new Error('sessão'); }
        if (!resp.ok) throw new Error('HTTP ' + resp.status);
        return resp.json();
    }

    async function carregarProfissionais() {
        selProf.innerHTML = '<option value="">Carregando...</option>';
        selProf.disabled = true;
        if (!selServico.value) {
            selProf.innerHTML = '<option value="">Escolha um serviço primeiro</option>';
            return carregarHorarios();
        }
        try {
            const lista = await buscarJSON(formAg.dataset.apiProfissionais + '&servico=' + encodeURIComponent(selServico.value));
            selProf.innerHTML = '';
            if (!lista.length) {
                selProf.innerHTML = '<option value="">Nenhum profissional disponível para este serviço</option>';
            } else {
                selProf.appendChild(new Option('Selecione...', ''));
                lista.forEach((p) => selProf.appendChild(new Option(p.nome + (p.especialidade ? ' — ' + p.especialidade : ''), p.id)));
                selProf.disabled = false;
                if (lista.length === 1) selProf.value = lista[0].id;
                if (profOld && lista.some((p) => String(p.id) === profOld)) selProf.value = profOld;
                profOld = '';
            }
        } catch (e) {
            selProf.innerHTML = '<option value="">Erro ao carregar. Recarregue a página.</option>';
        }
        carregarHorarios();
    }

    async function carregarHorarios() {
        if (!selServico.value || !selProf.value || !inpData.value) {
            return msgHorarios('Escolha serviço, profissional e data para ver os horários livres.');
        }
        const dia = new Date(inpData.value + 'T12:00:00').getDay();
        if (dia === 0) return msgHorarios('Não atendemos aos domingos. Escolha outra data.', 'aviso');

        msgHorarios('Buscando horários...', 'carregando');
        try {
            const url = formAg.dataset.apiHorarios + '&servico=' + selServico.value + '&profissional=' + selProf.value + '&data=' + inpData.value;
            const dados = await buscarJSON(url);
            if (dados.erro) return msgHorarios(dados.erro, 'aviso');
            if (!dados.horarios.length) return msgHorarios('Não há horários livres nesta data. Tente outro dia ou outro profissional.', 'aviso');
            boxHoras.innerHTML = '';
            dados.horarios.forEach((h) => {
                const label = document.createElement('label');
                label.className = 'slot';
                label.innerHTML = '<input type="radio" name="hora" value="' + h + '" required><span>' + h + '</span>';
                boxHoras.appendChild(label);
            });
            if (horaOld) {
                const r = boxHoras.querySelector('input[value="' + horaOld + '"]');
                if (r) r.checked = true;
                horaOld = '';
            }
            boxHoras.addEventListener('change', () => {
                const aviso = boxHoras.parentElement.querySelector('.erro-campo');
                if (aviso) aviso.remove();
            }, { once: true });
        } catch (e) {
            msgHorarios('Não foi possível carregar os horários. Tente novamente.', 'aviso');
        }
    }

    selServico.addEventListener('change', carregarProfissionais);
    selProf.addEventListener('change', carregarHorarios);
    inpData.addEventListener('change', carregarHorarios);
    if (selServico.value) carregarProfissionais();
})();
