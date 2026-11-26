<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Warehouse extends Model
{
    protected $table = 'warehouses';

    protected $primaryKey = 'warehouse_id';

    public const ACTIVE = 'Ativo';
    public const REQUESTED = 'Solicitado';
    public const INACTIVE = 'Inativo';
    public const REJECTED = 'Recusado';

    public const SITUATIONS = [
        self::ACTIVE,
        self::REQUESTED,
        self::INACTIVE,
        self::REJECTED,
    ];

    protected $fillable = [
        'warehouse_name',
        'warehouse_description',
        'warehouse_situation',
        'warehouse_request_reason',
        'warehouse_cancelation_reason',
        'requested_by',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}

