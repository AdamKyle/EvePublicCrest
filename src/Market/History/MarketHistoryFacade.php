<?php

namespace EveOnline\Market\History;

use Illuminate\Support\Facades\Facade;

class MarketHistoryFacade extends Facade {
    protected static function getFacadeAccessor() { return 'eveonline.market.history'; }
}
