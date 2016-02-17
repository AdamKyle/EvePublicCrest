# Eve Online Prices

This is a rather simple and easy to use class that can return you
all of the market prices for all items. These items can be seen via
visiting [this api](https://public-crest.eveonline.com/market/prices/).

Each item in this returning list contains a type with an href, you can also get the items
details should you want them, which we recommend to do on demand.

## Install and Dependencies

This is class is used in conjunction with the rest of the library. It is designed to be used with Laravel 5.2 and Higher.

### Laravel 5.2 Setup

in the `config/app.php`:

```php
<?php

return [

    ...

    'providers' => [

        ...

        EveOnline\Market\PricesProvider::class,

    ],

    'aliases' => [

        ...

        'EvePrices' => EveOnline\Market\PricesFacade::class,
    ],
];
```

## Quick Use

To get started all you have to do to get a list of all prices is:

```php
EvePrices::prices();
```

The above will return json [looking like this](https://public-crest.eveonline.com/market/prices/). This only takes
a few seconds. As you can see each one has a `type->href` you can use that on demand to do something like:

```php
$allItemPrices = EvePrices::prices();
$itemDetails   = EvePrices::ItemType(allItemPrices[0]->type->href);
```

Which will then give you json for that item, which in this case is [this item](https://public-crest.eveonline.com/types/32772/).
