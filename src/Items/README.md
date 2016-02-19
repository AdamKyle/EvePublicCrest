# Eve Online Market Item Details

Super simple class that fetches the item details for a specific item.

An example of the json returned is [https://public-crest.eveonline.com/types/32772/](https://public-crest.eveonline.com/types/32772/)

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

        EveOnline\Items\DetailsProvider::class,

    ],

    'aliases' => [

        ...

        'EveItemDetails' => EveOnline\Items\DetailsFacade::class,
    ],
];
```

This class generates the following log: `eveonline_item_details.log` Which is then stored in the `storage/logs`.

## Quick Use

Super simple:

```php
EveItemDetails::details('https://public-crest.eveonline.com/types/32772/');
```

You can see the JSON (which we decode for you) for this particular example link [here](https://public-crest.eveonline.com/types/32772/);

This link can be fetched by you using the [Eve Online Market Types Facade](https://github.com/AdamKyle/EvePublicCrest/blob/master/src/Market/Types/README.md)

We suggest you store the information you fetch in a database of some sort.
