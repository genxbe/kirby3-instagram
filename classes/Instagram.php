<?php
namespace X;

class Instagram
{
    private $db;
    private $token;
    private $tokenRefreshed;

    public function __construct()
    {
        if(!file_exists(kirby()->root('assets')))
        {
            mkdir(kirby()->root('assets'));
        }

        if(!file_exists(kirby()->root('assets').DS.option('genxbe.instagram.assetFolder')))
        {
            mkdir(kirby()->root('assets').DS.option('genxbe.instagram.assetFolder'));
        }

        if(empty(option('genxbe.instagram.db')))
        {
            throw new \Exception('Database path is required.');
        }

        $this->db = kirby()->root('assets').DS.option('genxbe.instagram.assetFolder').DS.option('genxbe.instagram.db');
        $this->token = site()->instagramToken();
        $this->tokenRefreshed = site()->instagramTokenRefreshed();
    }

    public function feed()
    {
        return $this->getDb();
    }

    public function getToken()
    {
        try
        {
            if(get('code'))
            {
                $options = [
                    'headers' => [
                        'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
                    ],
                    'method' => 'POST',
                    'data' => http_build_query([
                        'client_id' => option('genxbe.instagram.client_id'),
                        'client_secret' => option('genxbe.instagram.client_secret'),
                        'grant_type' => 'authorization_code',
                        'redirect_uri' => option('genxbe.instagram.redirect_uri'),
                        'code' => get('code'),
                    ]),
                ];

                $data = \Remote::request('https://api.instagram.com/oauth/access_token', $options);

                $access_token = $data->json()['access_token'];
                $client_secret = option('genxbe.instagram.client_secret');

                $data = \Remote::get("https://graph.instagram.com/access_token?grant_type=ig_exchange_token&client_secret={$client_secret}&access_token={$access_token}");

                $access_token = $data->json()['access_token'];

                site()->update([
                    'instagramAuth' => (boolean)true,
                    'instagramToken' => $data->json()['access_token'],
                    'instagramTokenRefreshed' => date('Y-m-d'),
                ]);

                $root = kirby()->root();
                $dir = dirname(__DIR__);

                exec("cd {$root} && php {$dir}/fetch.php > /dev/null &");
            }
        }
        catch(\Exception $e)
        {
            site()->update([
                'instagramAuth' => (boolean)false,
                'instagramToken' => '',
            ]);
        }

        go(site()->panelUrl(), 303);
    }

    public function refreshToken()
    {
        $kirby = kirby();
        $kirby->impersonate('kirby');

        try
        {
            if(site()->instagramAuth()->isFalse())
            {
                exit;
            }

            $access_token = $this->token;
            $client_secret = option('genxbe.instagram.client_secret');

            $data = \Remote::get("https://graph.instagram.com/refresh_access_token?grant_type=ig_refresh_token&client_secret={$client_secret}&access_token={$access_token}");

            $access_token = $data->json()['access_token'];

            site()->update([
                'instagramAuth' => (boolean)true,
                'instagramToken' => $data->json()['access_token'],
                'instagramTokenRefreshed' => date('Y-m-d'),
            ]);
        }
        catch(\Exception $e)
        {
            site()->update([
                'instagramAuth' => (boolean)false,
                'instagramToken' => '',
            ]);
        }
    }

    public function fetch()
    {
        if(site()->instagramAuth()->isFalse())
        {
            exit;
        }

        $token = $this->token;
        $db = $this->getDb();

        $path = kirby()->root('assets').DS.option('genxbe.instagram.assetFolder').DS.option('genxbe.instagram.mediaFolder');

        if(!file_exists($path))
        {
            mkdir($path);
        }

        $mediaList = \Remote::get("https://graph.instagram.com/me?fields=media&access_token={$token}");

        if($mediaList->code() !== 200)
        {
            throw new \Exception("There's a problem accessing Instagram. ({$mediaList->json()['error']['message']})");
        }

        $data = $mediaList->json();

        foreach($data['media']['data'] as $media)
        {
            if(!file_exists($path.DS.$media['id'].'.jpg'))
            {
                $media = \Remote::get("https://graph.instagram.com/{$media['id']}?fields=caption,media_url,media_type,thumbnail_url,timestamp,permalink,username&access_token={$token}");
                $item = $media->json();

                if(in_array($item['media_type'], ['IMAGE', 'CAROUSEL_ALBUM']))
                {
                    file_put_contents($path.DS.$item['id'].'.jpg', file_get_contents($item['media_url']));
                }

                if($item['media_type'] == 'VIDEO')
                {
                    file_put_contents($path.DS.$item['id'].'.jpg', file_get_contents($item['thumbnail_url']));
                }

                if(!$db->findBy('id', $item['id']))
                {
                    $db->add([
                        'id' => $item['id'],
                        'timestamp' => $item['timestamp'] ?? '',
                        'media_type' => $item['media_type'] ?? '',
                        'media_url' => $item['media_url'] ?? '',
                        'caption' => $item['caption'] ?? '',
                        'permalink' => $item['permalink'] ?? '',
                        'username' => $item['username'] ?? '',
                        'thumbnail_url' => $item['thumbnail_url'] ?? '',
                    ]);
                }
            }
        }

        $newDb = $db->sortBy('timestamp', 'desc');

        foreach(array_diff(scandir($path), array('.', '..')) as $file)
        {
            if(!$newDb->findBy('id', basename($file, '.jpg')))
            {
                unlink($path.$file);
            }
        }

        $this->writeDb($newDb);

        if($this->dateDifference(date('Y-m-d'), $this->tokenRefreshed) > 50)
        {
            self::refreshToken();
        }
    }

    private function dateDifference($date_1 , $date_2 , $differenceFormat = '%a' )
    {
        $datetime1 = date_create($date_1);
        $datetime2 = date_create($date_2);

        $interval = date_diff($datetime1, $datetime2);

        return $interval->format($differenceFormat);

    }

    private function getDb()
    {
        if(file_exists($this->db))
        {
            if(filesize($this->db))
            {
                $data = \Data::read($this->db);
                $collection = new \Collection($data);

                return $collection;
            }
        }

        return new \Collection();
    }

    private function writeDb($newDb)
    {
        return file_put_contents($this->db, json_encode($newDb->data));
    }
}
