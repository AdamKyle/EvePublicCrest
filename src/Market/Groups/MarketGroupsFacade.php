<?php

namespace EveOnline\Market\Groups;

use Illuminate\Support\Facades\Facade;

class MarketGroupsFacade extends Facade {
    protected static function getFacadeAccessor() { return 'eveonline.market.groups'; }
}
