#!/bin/bash
# =============================================================
#  AgendaFácil - abrir no Mac (modo local, sem XAMPP e sem MySQL)
#  Dê dois cliques neste arquivo. Para parar, feche a janela.
# =============================================================
cd "$(dirname "$0")" || exit 1

# Procura o PHP: Homebrew, XAMPP ou MAMP (qualquer um serve)
for p in /opt/homebrew/bin /usr/local/bin /Applications/XAMPP/xamppfiles/bin /Applications/MAMP/bin/php/php8*/bin; do
  if [ -x "$p/php" ] && "$p/php" -m 2>/dev/null | grep -qi pdo_sqlite; then
    export PATH="$p:$PATH"; break
  fi
done

if ! command -v php >/dev/null 2>&1; then
  echo ""
  echo "O PHP não foi encontrado neste Mac."
  echo ""
  echo "JEITO MAIS FÁCIL (sem Homebrew):"
  echo "  1. Baixe o XAMPP para Mac em https://www.apachefriends.org (versão 'for OS X')."
  echo "  2. Abra o arquivo baixado e arraste o XAMPP para a pasta Aplicativos."
  echo "  3. Abra este arquivo (Abrir-AgendaFacil-Mac) de novo. Não precisa ligar nada no XAMPP."
  echo ""
  read -r -p "Quer tentar instalar pelo Homebrew mesmo assim? (s/N) " resp
  if [ "$resp" != "s" ] && [ "$resp" != "S" ]; then
    open "https://www.apachefriends.org/download.html"
    exit 1
  fi
  if ! command -v brew >/dev/null 2>&1 && [ ! -x /opt/homebrew/bin/brew ] && [ ! -x /usr/local/bin/brew ]; then
    /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)" || {
      echo ""; echo "Não foi possível instalar o Homebrew. Use o XAMPP (passos acima)."
      open "https://www.apachefriends.org/download.html"; read -r -p "Enter para sair"; exit 1; }
  fi
  if [ -x /opt/homebrew/bin/brew ]; then eval "$(/opt/homebrew/bin/brew shellenv)"; fi
  if [ -x /usr/local/bin/brew ]; then eval "$(/usr/local/bin/brew shellenv)"; fi
  brew install php || { echo "Falha ao instalar o PHP. Use o XAMPP (passos acima)."; read -r -p "Enter para sair"; exit 1; }
fi

# Banco local em arquivo: é criado sozinho na primeira vez
export DB_DSN="sqlite:$(pwd)/database/agendafacil-local.sqlite"
export APP_DEBUG=0

PORTA=8000
while lsof -i :"$PORTA" >/dev/null 2>&1; do PORTA=$((PORTA+1)); done

clear
echo "============================================================"
echo "  AgendaFácil está rodando em:  http://localhost:$PORTA"
echo ""
echo "  Administrador: admin@agendafacil.com   / admin123"
echo "  Cliente:       cliente@agendafacil.com / cliente123"
echo ""
echo "  Para PARAR o sistema, feche esta janela."
echo "============================================================"
(sleep 2; open "http://localhost:$PORTA") &
php -S "localhost:$PORTA" -t public 2>/dev/null
