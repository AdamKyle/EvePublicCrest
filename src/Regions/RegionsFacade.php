<?php

namespace EveOnline\Regions;

use Illuminate\Support\Facades\Facade;

class RegionsFacade extends Facade {
    protected static function getFacadeAccessor() { return 'eveonline.regions'; }
}
