<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class SvgMimeType implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $mimes = ['image/svg', 'image/svg+xml'];
        return in_array($value->getClientMimeType(), $mimes);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'El archivo :attribute debe ser de tipo image/svg o image/svg+xml';
    }
}
