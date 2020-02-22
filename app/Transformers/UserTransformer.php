<?php

namespace App\Transformers;

use App\User;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(User $user)
    {
        return [
            'identificador' => (int)$user->id,
            'nombre' => (string)$user->name,
            'correo' => (string)$user->email,
            'verificado' => (int)$user->verified,
            'esAdministrador' => ($user->admin === 'true'),
            'fechaCreacion' => (string)$user->created_at,
            'fechaAtualizacion' => (string)$user->updated_at,
            'fechaEliminacion' => isset($user->deleted_at) ? (string)$user->deleted_at : null,
        ];
    }

    public static function originalAttribute($index)
    {
        $attributes = [
            'identificador' => 'id',
            'nombre' => 'name',
            'correo' => 'email',
            'verificado' => 'verified',
            'esAdministrador' => 'admin',
            'fechaCreacion' => 'created_at',
            'fechaAtualizacion' => 'updated_at',
            'fechaEliminacion' => 'deleted_at',
        ];

        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
