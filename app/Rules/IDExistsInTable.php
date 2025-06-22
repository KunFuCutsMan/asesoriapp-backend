<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class IDExistsInTable implements ValidationRule
{
    private string $tableName;
    public function __construct(string $tableName)
    {
        $this->tableName = $tableName;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $thing = DB::table($this->tableName)->find($value);
        if (!$thing) {
            $fail(":attribute does not exist.");
        }
    }
}
