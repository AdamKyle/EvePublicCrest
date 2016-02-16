<?php

namespace EveOnline\Items;

use Illuminate\Support\Facades\Facade;

class DetailsFacade extends Facade {
    protected static function getFacadeAccessor() { return 'eveonline.item.details'; }
}
