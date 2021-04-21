<?php
namespace App\Command;

use App\Entity\Logs;
use App\Entity\News;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors','on');
class ParseCommand extends Command
{
    protected static $defaultName = 'app:parse:start';
    /**
     * @var EntityManagerInterface
     */
    private $em;
    private $date;
    private $news_repo;
    private $logs;
    private $error_file = __DIR__.'/../../var/log/error.log';

    /**
     * ParseCommand constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em        = $em;
        $this->date      = $date = new DateTime();
        $this->news_repo = $this->em->getRepository(News::class);
        $this->logs      = $this->em->getRepository(Logs::class);
        if(!file_exists($this->error_file)){
            file_put_contents($this->error_file,'');
        }
        parent::__construct();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool|int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $url      = 'http://static.feed.rbc.ru/rbc/logical/footer/news.rss';
        $method   = 'GET';
        $response = $this->getContent($url,$method);
        $log      = $this->setLog($response);
        $news     = $this->getDataFromXML($log->getResponseBody());
        return $this->writeDataInDB($news);
    }

    /**
     * @param $url
     * @param $method
     * @return array
     */
    private function getContent($url, $method){
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER => [
                'DNT: 1',
                'Upgrade-Insecure-Requests: 1',
                'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.72 Safari/537.36',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9'
            ],
        ]);
        $response = curl_exec($curl);
        $info     = curl_getinfo($curl);
        curl_close($curl);
        return [
            'request_url'    => $info['url'],
            'request_method' => $method,
            'response_body'  => $response ?? 'No body',
            'created_at'     => new DateTime(),
            'response_code'  => $info['http_code']
        ];
    }

    /**
     * @param $data
     * @return Logs
     */
    private function setLog($data){
        $log = new Logs($data);
        $this->em->persist($log);
        return $log;
    }

    /**
     * @param $xml
     * @return array
     * @throws \Exception
     */
    private function getDataFromXML($xml){
        $data   = (object) simplexml_load_string($xml);
        $items  = (array) $data->channel;
        $items  = (array) $items['item'];
        $result = [];
        foreach ($items as $item){
            try{
                $data         = [];
                $data['img']  = '';
                $data['name']              = (string)$item->title;
                $data['link']              = (string)$item->link;
                $data['short_description'] = (string)$item->description;
                $data['guid']              = (string)$item->guid;
                $data['author']            = (string)$item->author;
                $data['publications_date'] = new DateTime($item->pubDate);

                $attr = isset($item->enclosure) ? (array)$item->enclosure->attributes() : false;
                if ($attr){
                    $attr = current($attr);
                    if (isset($attr['type']) && $attr['type'] == 'image/jpeg'){
                        $data['img']  = $attr['url'] ?? '';
                    }
                }
                $new_item = $this->news_repo->findOneBy(['guid' => $data['guid']]);
                if (!$new_item){
                    $result[] = new News($data);
                }
            } catch (\Exception $e) {
                error_log($this->date->format('Y-m-d H:i:s') . ' : ' .$e->getMessage() . PHP_EOL, 3, $this->error_file);
                continue;
            }

        }
        return $result;
    }

    private function writeDataInDB($news){
        try {
            foreach ($news as $news_item){
                $this->em->persist($news_item);
            }
            $this->em->flush();
            return true;
        } catch (\Exception $e){
            error_log($this->date->format('Y-m-d H:i:s') . ' : ' .$e->getMessage() . PHP_EOL, 3, $this->error_file);
            return false;
        }

    }

}