<?php

namespace EveOnline\Market\Prices;

use Illuminate\Support\Facades\Facade;

class PricesFacade extends Facade {
    protected static function getFacadeAccessor() { return 'eveonline.market.prices'; }
}
