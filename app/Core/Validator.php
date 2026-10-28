<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Validação de dados de entrada.
 * Regras aceitas: obrigatorio, email, min:N, max:N, numero, inteiro, data, hora, igual:campo, em:a,b,c
 */
final class Validator
{
    /** @var array<string,string> */
    private array $erros = [];

    public function __construct(private array $dados)
    {
    }

    /**
     * @param array<string,string> $regras  campo => "regra1|regra2"
     * @param array<string,string> $nomes   campo => nome amigável
     */
    public function validar(array $regras, array $nomes = []): bool
    {
        foreach ($regras as $campo => $lista) {
            $valor = isset($this->dados[$campo]) ? trim((string) $this->dados[$campo]) : '';
            $rotulo = $nomes[$campo] ?? ucfirst(str_replace('_', ' ', $campo));

            foreach (explode('|', $lista) as $regra) {
                [$nome, $param] = array_pad(explode(':', $regra, 2), 2, null);
                $msg = $this->aplicar($nome, $param, $valor, $rotulo);
                if ($msg !== null) {
                    $this->erros[$campo] = $msg;
                    break; // um erro por campo
                }
            }
        }
        return $this->erros === [];
    }

    private function aplicar(string $regra, ?string $param, string $valor, string $rotulo): ?string
    {
        if ($regra !== 'obrigatorio' && $valor === '') {
            return null; // campos opcionais vazios não são validados
        }

        return match ($regra) {
            'obrigatorio' => $valor === '' ? "O campo {$rotulo} é obrigatório." : null,
            'email'       => filter_var($valor, FILTER_VALIDATE_EMAIL) ? null : "{$rotulo} deve ser um e-mail válido.",
            'min'         => mb_strlen($valor) < (int) $param ? "{$rotulo} deve ter pelo menos {$param} caracteres." : null,
            'max'         => mb_strlen($valor) > (int) $param ? "{$rotulo} deve ter no máximo {$param} caracteres." : null,
            'numero'      => is_numeric(str_replace(',', '.', $valor)) && (float) str_replace(',', '.', $valor) >= 0
                                ? null : "{$rotulo} deve ser um número positivo.",
            'inteiro'     => ctype_digit($valor) && (int) $valor > 0 ? null : "{$rotulo} deve ser um número inteiro maior que zero.",
            'data'        => self::dataValida($valor) ? null : "{$rotulo} deve ser uma data válida.",
            'hora'        => preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $valor) ? null : "{$rotulo} deve estar no formato HH:MM.",
            'igual'       => $valor === trim((string) ($this->dados[$param] ?? '')) ? null : "{$rotulo} não confere.",
            'em'          => in_array($valor, explode(',', (string) $param), true) ? null : "{$rotulo} possui um valor inválido.",
            'telefone'    => preg_match('/^\(?\d{2}\)?\s?9?\d{4}-?\d{4}$/', $valor) ? null : "{$rotulo} deve estar no formato (99) 99999-9999.",
            default       => throw new \InvalidArgumentException("Regra desconhecida: {$regra}"),
        };
    }

    public static function dataValida(string $data): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $data);
        return $d !== false && $d->format('Y-m-d') === $data;
    }

    /** @return array<string,string> */
    public function erros(): array
    {
        return $this->erros;
    }
}
