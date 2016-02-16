<?php

namespace EveOnline\Market\Orders;

use Illuminate\Support\Facades\Facade;

class OrderFacade extends Facade {
    protected static function getFacadeAccessor() { return 'eveonline.region.orders'; }
}
