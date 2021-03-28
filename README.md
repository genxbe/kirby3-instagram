# Kirby3 Ray helper

Helper tool that enables ray on all extendable methods.

Ray is the fantastic dump debugger from our friends at Spatie. You can find more information about Ray on https://myray.app/.

<img src="https://static.gnx.cloud/genx/kirby/kirby3-ray-loop.gif">

## Options

By default the ray helper won’t be enabled, you can enable it by setting `debug` to `true` or by adding the `enabled` option in the config file.

```php
# site/config/config.php
return [
  'debug' => true,

  // OR //

  'genxbe.ray.enabled' => true,
];
```

## Usage

Add `->ray()` after the page, field, or other object you want to parse in ray. This helper doesn’t interrupt your flow so whatever you are doing will still work if you add the helper.
Pass a color as parameter if you want to enable color filtering in ray.

### Examples

```php
<?php
  // Parse page in ray
  $myField = $page->ray()->myField();

  // Parse page in ray with the blue color filter active
  $myTitle = $page->ray('blue')->myTitle();

  // Parse the page and the field in ray
  $projects = $page->ray()->projects()->ray();
?>

<?= $site->seoOgImage()->ray() ?>

<?= $site->footerLinks()->toStructure()->ray() ?>

<?= $site->footerLinks()->ray('red')->toStructure()->ray('blue') ?>
```

## Installation

### Download

Download and copy this repository to `/site/plugins/kirby3-ray`.

### Git submodule

```
git submodule add https://github.com/genxbe/kirby3-ray.git site/plugins/kirby3-ray
```

### Composer

```
composer require genxbe/kirby3-ray
```

## License

MIT

## Credits

- [Sam Serrien](https://github.com/samzzi) @ [GeNx](https://github.com/genxbe)
