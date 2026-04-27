<?php

namespace App\Http\Requests;

use App\Enums\AgeGroup;
use App\Enums\Gender;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ListProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            //

            'gender'        => ['nullable', 'string'],
            'age_group'     => ['nullable', 'string'],
            'country_id'    => ['nullable', 'string'],
            'min_age'       => ['nullable', 'integer'],
            'max_age'       => ['nullable', 'integer'],
            'min_gender_probability'    => ['nullable', 'numeric'],
            'max_gender_probability'    => ['nullable', 'numeric'],
            'min_country_probability'   => ['nullable', 'numeric'],
            'max_country_probability'   => ['nullable', 'numeric'],
            'sort_by'       => ['nullable', 'string', 'in:age,created_at,gender_probability'],
            'order'         => ['nullable', 'string', 'in:asc,desc'],
            'page'          => ['nullable', 'integer', 'min:1'],
            'limit'         => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
}
