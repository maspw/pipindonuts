<?php

namespace App\Filament\Resources\PenjualanProdukResource\Widgets;

use Filament\Widgets\Widget;

class PenjualanInsightWidget extends Widget
{
  
    protected int | string | array $columnSpan = 'full';

    protected static string $view = 'filament.widgets.penjualan-insight';

    protected $listeners = [
        'refreshWidget' => '$refresh',
    ];
}