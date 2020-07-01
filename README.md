# Installation
```
composer require inweb/admin-field-models
```

# Usage

```php
public function detailFields(AdminRequest $request)
{
    return [
        // Other fields ...
        
        ModelsField::make(__('Товары'))->resource('product')->size('full'),
        
        // Other fields ...
    ];
}
```

# TODO
- Specify relation name
- Validate data on adding relation
