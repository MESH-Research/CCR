<?php
declare(strict_types=1);

namespace App\GraphQL\Validators;

use Nuwave\Lighthouse\Validation\Validator;

class ProfileMetadataAcademicProfilesValidator extends Validator
{
    /**
     * Return the validation rules.
     *
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'orchid_id' => [
                'max:128',
            ],
            'humanities_commons' => [
                'max:128',
            ],
            'academia_edu_id' => [
                'max:128',
            ],
        ];
    }
}