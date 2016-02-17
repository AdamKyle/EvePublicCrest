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

// Returns you the json for this link.
```
