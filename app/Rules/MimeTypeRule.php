<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MimeTypeRule implements Rule
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
        $listMimeTypes = [];
        if(Schema::hasTable('attachment_types')){
           $list = DB::table('attachment_types')
               ->select('mimetype')
               ->get();
           $listMimeTypes = $list->map(function ($item){
               return $item->mimetype;
           })->toArray();

        }
        return in_array($value->getClientMimeType(), $listMimeTypes);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'El tipo de archivo no es aceptado';
    }
}
