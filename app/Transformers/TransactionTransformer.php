<?php

namespace App\Transformers;

use App\Transaction;
use League\Fractal\TransformerAbstract;

class TransactionTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Transaction $transaction)
    {
        return [
            'identificador' => (int)$transaction->id,
            'cantidad' => (int)$transaction->quantity,
            'comprador' => (int)$transaction->buyer_id
            'fechaCreacion' => (string)$transaction->createdAt,
            'fechaActualizacion' => (string)$transaction->updated_at,
            'fechaEliminacion' => isset($transaction->deleted_at) ? (string)$transaction->deleted_at : null,
        ];
    }
}
