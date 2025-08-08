<?php

namespace App\Rules;

use Closure;
use DateTimeImmutable;
use Illuminate\Contracts\Validation\ValidationRule;

class OnlyHasHours implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $valor = DateTimeImmutable::createFromFormat("H:i", $value);
        if (!$valor) {
            $fail(':attribute debe ser formato de hora.');
            return;
        }

        $minuto = intval($valor->format('i'));
        if ($minuto != 0) {
            $fail(':attribute no debe de tener minutos');
        }
    }
}
