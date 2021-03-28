# Kirby3 Instagram feed

Fetch instagram photos without the need for app aproval.

## Options


```php
# site/config/config.php
return [
    'genxbe.instagram' => [
        'assetFolder' => 'instagram',
        'db' => 'instagram.json',
        'media' => 'media/',
        'client_id' => '',
        'client_secret' => '',
        'redirect_uri' => u('axi/instagram'),
    ],
];
```

## Usage

### Examples

## Installation

### Download

Download and copy this repository to `/site/plugins/kirby3-instagram`.

### Git submodule

```
git submodule add https://github.com/genxbe/kirby3-instagram.git site/plugins/kirby3-instagram
```

### Composer

```
composer require genxbe/kirby3-instagram
```

## License

MIT

## Credits

- [Sam Serrien](https://github.com/samzzi) @ [GeNx](https://github.com/genxbe)
