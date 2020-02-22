<?php

namespace App\Transformers;

use App\Buyer;
use League\Fractal\TransformerAbstract;

class BuyerTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Buyer $buyer)
    {
        return [
            'identificador' => (int)$buyer->id,
            'nombre' => (string)$buyer->name,
            'correo' => (string)$buyer->email,
            'verificado' >= (int)$buyer->verified,
            'fechaCreacion' => (string)$buyer->created_at,
            'fechaAtualizacion' => (string)$buyer->updated_at,
            'fechaEliminacion' => isset($buyer->deleted_at) ? (string)$buyer->deleted_at : null,
        ];
    }
}
