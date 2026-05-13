<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bahan extends Model
{
    protected $table      = 'bahans';
    protected $primaryKey = 'id_bahanbaku';

    public    $incrementing = false;
    protected $keyType      = 'string';

    protected $fillable = [
        'id_bahanbaku',
        'nama_bahan',
        'satuan',
        'jml_stok',
        'stok_minimum',
        'tgl_exp',
    ];

    protected $casts = [
        'tgl_exp'      => 'date',
        'jml_stok'     => 'integer',
        'stok_minimum' => 'integer',
    ];

    /**
     * Auto-generate id_bahanbaku → BB-0001, BB-0002, dst.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->id_bahanbaku)) {
                $model->id_bahanbaku = static::generateId();
            }
        });
    }

    private static function generateId(): string
    {
        $last = static::orderBy('id_bahanbaku', 'desc')->first();

        if ($last) {
            // "BB-0007" → substr dari index 3 → "0007" → ltrim '0' → "7" → +1 = 8
            $num = (int) ltrim(substr($last->id_bahanbaku, 3), '0');
        } else {
            $num = 0;
        }

        return 'BB-' . str_pad($num + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Cek apakah stok di bawah minimum.
     */
    public function getIsBelowMinimumAttribute(): bool
    {
        return $this->jml_stok <= $this->stok_minimum;
    }

    /**
     * Cek apakah bahan akan kadaluarsa dalam N hari ke depan.
     */
    public function willExpireIn(int $days = 7): bool
    {
        if (!$this->tgl_exp) return false;
        return $this->tgl_exp->isPast() || $this->tgl_exp->diffInDays(now(), true) <= $days;
    }

    public function detilPembelian(): HasMany
    {
        return $this->hasMany(DetilPembelian::class, 'bahan_id', 'id_bahanbaku');
    }

    public function returPembelian(): HasMany
    {
        return $this->hasMany(ReturPembelian::class, 'bahan_id', 'id_bahanbaku');
    }
}