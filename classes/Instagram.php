<?php
namespace X;

class Instagram
{
    private $db;

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
    }

    public function feed()
    {
        return $this->getDb();
    }

    public function getToken()
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

            site()->update([
                'instagramAuth' => (boolean)true,
                'instagramToken' => $data->json()['access_token'],
            ]);

            exec("php {__DIR__} fetch.php > /dev/null &");

            go(site()->panelUrl(), 303);
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

        $path = kirby()->root('assets').DS.option('genxbe.instagram.assetFolder').DS.option('genxbe.instagram.media');

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

        $newDb = $db->limit(20);

        foreach(array_diff(scandir($path), array('.', '..')) as $file)
        {
            if(!$newDb->findBy('id', basename($file, '.jpg')))
            {
                unlink($path.$file);
            }
        }

        $this->writeDb($newDb);
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
