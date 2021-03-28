# Kirby3 Instagram feed

Fetch instagram photos without the need for app aproval. This plugin will download the photos and/or video thumbnails to local storage. All media will be stored in a json file.

Only the 20 latest photo's will be stored.

This plugin uses the **basic user access token** which you can generate for all testusers of your instagram app. (https://developers.facebook.com/docs/instagram-basic-display-api/overview#user-token-generator)

## Options

```php
# site/config/config.php
return [
    'genxbe.instagram' => [
        'client_id' => '',
        'client_secret' => '',
        'redirect_uri' => u('axi/instagram'),
        'assetFolder' => 'instagram',
        'mediaFolder' => 'media',
        'db' => 'instagram.json',
    ],
];
```

## Usage

* find a place to add the `instagramLink` blueprint

* Add your client_id and client_secret to your config file, all other options are optional.

* Don't forget to add your website redirect uri to the **Valid OAuth Redirect URIs**

    * You can add multiple website and thus use 1 app for all of your websites

* Add the instagram account you want to use to the test users and ask them to accept the invite (instructions below)

* Ask your user to enable the instagram link on the website, when this process is completed a first time fetch will already be done.

### Examples

#### Add linkInstagram to blueprint

```yaml
title: Site
preset: pages
unlisted: true

fields:
  linkInstagram: linkInstagram
```

#### After the first fetch you can start parsing the feed

You can check for a count of the feed to hide the block when no media is available yet.
Since we work with collections you can also use fieldMethods like `limit`, `filterBy`, etc...

```php
<h1><?= $page->title() ?></h1>

<?php if(count(instagramFeed())): ?>

    <?php foreach(instagramFeed() as $media): ?>

        <a href="<?= $media['permalink']; ?>" target="_blank">
            <img src="assets/instagram/media/<?= $media['id']; ?>.jpg" width="100" height="100" />
        </a>

    <?php endforeach; ?>

    <?php foreach(instagramFeed()->limit(5) as $media): ?>

        <a href="<?= $media['permalink']; ?>" target="_blank">
            <img src="assets/instagram/media/<?= $media['id']; ?>.jpg" width="100" height="100" />
        </a>

    <?php endforeach; ?>

<?php endif; ?>
```

## Plugin installation

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

## Facebook app configuration

## Adding test users

## Usage on multiple websites

## License

MIT

## Credits

- [Sam Serrien](https://github.com/samzzi) @ [GeNx](https://github.com/genxbe)
