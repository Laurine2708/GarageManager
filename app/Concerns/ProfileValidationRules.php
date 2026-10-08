<?php

namespace App\Concerns;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

/**
 * Centralise les règles de validation communes aux profils.
 */
trait ProfileValidationRules
{
    /**
     * Regroupe les règles de validation communes aux champs de profil.
     *
     * @param int|null $userId Identifiant à ignorer pour l'unicité de l'adresse e-mail.
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function profileRules(?int $userId = null): array
    {
        return [
            'name' => $this->nameRules(),
            'email' => $this->emailRules($userId),
        ];
    }

    /**
     * Fournit les règles de validation du nom affiché.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function nameRules(): array
    {
        return ['required', 'string', 'max:255'];
    }

    /**
     * Fournit les règles de validation de l'adresse e-mail et de son unicité.
     *
     * @param int|null $userId Identifiant de l'utilisateur à exclure du contrôle d'unicité.
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function emailRules(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'email',
            'max:255',
            $userId === null
                ? Rule::unique(User::class)
                : Rule::unique(User::class)->ignore($userId),
        ];
    }
}
