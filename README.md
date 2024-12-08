# IMPORTANT NOTICE

This plugin won't work anymore starting from 04/12/2024. Because of the changes on the basic display API. (more info: https://developers.facebook.com/blog/post/2024/09/04/update-on-instagram-basic-display-api/)
Keep an eye on https://plugins.andkindness.com/socialstar for a new Kirby instagram plugin.

# Kirby3 Instagram feed

Fetch instagram photos without the need for app aproval. This plugin will download the photos and/or video thumbnails to local storage. All media will be stored in a json file.

Only the 20 latest photo's will be stored.

This plugin uses the **basic user access token** which you can generate for all testusers of your instagram app. (https://developers.facebook.com/docs/instagram-basic-display-api/overview#user-token-generator)

## Options

Required are the **`client_id`** (Instagram App ID) and **`client_secret`** (Instagram App Secret).
All other options are optional.

```php
# site/config/config.php
return [
    'genxbe.instagram' => [
        'client_id' => '',
        'client_secret' => '',
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

    * Format of the url you need to add is `https://yoursite.com/axi/instagram`

* Add the instagram account you want to use to the test users and ask them to accept the invite (instructions below)

* Ask your user to enable the instagram link on the website, when this process is completed a first time fetch will already be done.

* After the user has linked his Instagram you can start fetching via `php site/plugins/kirby3-instagram/fetch.php`

* If you want to have regular updates you need to schedule this command via the cron. I would advise to do this every 30 or 60 minutes so you don't overload your API rate limit. (More info on https://developers.facebook.com/docs/graph-api/overview/rate-limiting/#platform-rate-limits)

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

Fields that can be used in the $media array based on the example above.

* `$media['id']`
* `$media['timestamp']`
* `$media['media_type']`
* `$media['media_url']`
* `$media['caption']`
* `$media['permalink']`
* `$media['username']`
* `$media['thumbnail_url']`

#### Setup cron

Every 30 minutes

```
*/30 * * * * cd /home/website/website.com/ && php site/plugins/kirby3-instagram/fetch.php
```

Every hour

```
0 * * * * cd /home/website/website.com/ && php site/plugins/kirby3-instagram/fetch.php
```

Faster is possible but do keep your rate limits in mind! (More info on https://developers.facebook.com/docs/graph-api/overview/rate-limiting/#platform-rate-limits)

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

Detailed instructions on: https://elfsight.com/blog/2016/05/how-to-get-instagram-access-token/ (step 1-2)

* Create a Facebook app
* Setup Instagram Basic display
  * Important, your website OAuth url must be added as a valid OAuth redirect URI!
  * All other url's (deauthorize & data deletion) don't really matter, you can enter your own website there.
  * Don't request an app review

## Adding test users

Detailed instructions on: https://elfsight.com/blog/2016/05/how-to-get-instagram-access-token/ (step 3)

* Add the feed you want to show as instagram test user
* Make sure your test users accepts the test invitation
  * More info on https://xdocs.notion.site/Link-instagram-with-your-website-9aa72c4961074cb4b4f9b5d1e6322e36

## Authenticate the instagram user and request User token

Follow instructions on https://xdocs.notion.site/Link-instagram-with-your-website-9aa72c4961074cb4b4f9b5d1e6322e36

## Usage on multiple websites

You can use your 1 app for multiple sites by adding multiple OAuth redirect URI, please do keep your rate limit in mind. You can always create multiple facebook apps do divide the rate limit.

## License

MIT

## Credits

- [Sam Serrien](https://github.com/samzzi) @ [GeNx](https://github.com/genxbe)
