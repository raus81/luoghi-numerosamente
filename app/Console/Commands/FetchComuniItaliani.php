<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FetchComuniItaliani extends Command {
    const HTTP_WWW_COMUNI_ITALIANI_IT = "http://www.comuni-italiani.it/";
    const DOWNLOAD_PATH = 'data/ci/';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:comuni-italiani {action}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $action = $this->argument('action');
        if ($action == 'download') {
            $this->download();
        } elseif ($action == 'parse') {
            $this->parse();
        }
        return Command::SUCCESS;
    }

    private function download()
    {
        $baseUrl = self::HTTP_WWW_COMUNI_ITALIANI_IT;
        if (!file_exists("data/ci/home.html")) {

            $htmlHome = file_get_contents("http://www.comuni-italiani.it/index.html");
            file_put_contents("data/ci/home.html", $htmlHome);
        } else {
            $htmlHome = file_get_contents("data/ci/home.html");

        }


        $matches = [];
        preg_match_all("@(\d{3})\/index.html@", $htmlHome, $matches);
        print_r($matches);
        foreach ($matches[1] as $provinciaNum) {
            $urlProvincia = $baseUrl . $provinciaNum . '/index.html';
            echo $urlProvincia . ":";
            $fileProvincia = self::DOWNLOAD_PATH . $provinciaNum;
            if (!file_exists($fileProvincia)) {
                echo 'downloading';
                $htmlProvincia = file_get_contents($urlProvincia);
                file_put_contents($fileProvincia, $htmlProvincia);
            } else {
                echo 'found';
                $htmlProvincia = file_get_contents($fileProvincia);
            }
            echo PHP_EOL;
            $cityMatches = [];
            preg_match_all("@(\d{3})\/index.html@", $htmlProvincia, $cityMatches);

            foreach ($cityMatches[1] as $cityNum) {
                $fileCity = self::DOWNLOAD_PATH . $provinciaNum . '_' . $cityNum;
                $urlCity = $baseUrl . $provinciaNum . '/' . $cityNum . '/index.html';
                echo $urlCity . ":";

                if (!file_exists($fileCity)) {
                    echo 'downloading';
                    $htmlCity = file_get_contents($urlCity);
                    file_put_contents($fileCity, $htmlProvincia);
                } else {
                    echo 'found';
                    $htmlCity = file_get_contents($fileCity);
                }
                echo PHP_EOL;

                //Downloading stemma
                if (preg_match("@Stemma Comune@", $htmlCity)) {
                    $this->subDownload($provinciaNum, $cityNum, "stemma");
                }
                //Downloading banche
                if (preg_match("@Lista Banche@", $htmlCity)) {
                    $this->subDownload($provinciaNum, $cityNum, "banche", '/');
                }

                //Downloading farmacie
                if (preg_match("@href=\"farmacie@", $htmlCity)) {
                    $this->subDownload($provinciaNum, $cityNum, "farmacie", '/index.html');
                }

            }

        }
    }

    private function parse()
    {
        $files = array_diff(scandir(self::DOWNLOAD_PATH), array('.', '..'));
        $data = Collection::make($files);

        $filesFiltered = $data->filter(function ($name) {
            return preg_match("@^\d+_\d+$@", $name);
        });

        foreach ($filesFiltered as $file) {
            $htmlCity = file_get_contents(self::DOWNLOAD_PATH . $file);

            $codiceIstat = str_replace("_", "", $file);
            echo "Codice Istat: " . $codiceIstat . PHP_EOL;

            if (file_exists(self::DOWNLOAD_PATH . $file . '_farmacie')) {
                $this->parseFarmacie($file);

            }
        }
    }

    /**
     * @param $provinciaNum
     * @param $cityNum
     * @return void
     */
    private function subDownload($provinciaNum, $cityNum, $path, $suffix = '.html'): void
    {
        $urlStemma = self::HTTP_WWW_COMUNI_ITALIANI_IT . $provinciaNum . '/' . $cityNum . '/' . $path . $suffix;
        $fileStemma = self::DOWNLOAD_PATH . $provinciaNum . '_' . $cityNum . '_' . $path;
        echo $urlStemma . ":";
        if (!file_exists($fileStemma)) {
            echo 'downloading';
            $htmlStemma = file_get_contents($urlStemma);
            file_put_contents($fileStemma, $htmlStemma);
        } else {
            echo 'found';
        }
        echo PHP_EOL;
    }

    private function parseFarmacie($file)
    {

        $filename = self::DOWNLOAD_PATH . $file . '_farmacie';

        $htmlFarmacie = utf8_encode(file_get_contents($filename));

        $farmacieRaw = explode("Parafarmacie - vendita farmaci senza ricetta medica", $htmlFarmacie);

        $data = [];
        $farmacie = $this->parseFarmacieHtml($farmacieRaw[0]);
        if( count($farmacie)){
            $data['farmacie'] = $farmacie;

        }
        if (isset($farmacieRaw[1])) {
            $parafarmacie = $this->parseFarmacieHtml($farmacieRaw[1]);
            if (count($parafarmacie)) {
                $data['parafarmacie'] = $parafarmacie;

            }
        }

        if( count($data )){
            $json = json_encode($data);

            DB::insert("INSERT OR IGNORE INTO info(codice, chiave, valore)VALUES(?, ?, ?)",
                [str_replace('_','',$file), 'farmacie', $json]);
        }



    }


    function parseFarmacieHtml($html)
    {
        $matchesFarmacie = [];
        preg_match_all('@<b>(.*?)</b><br>(.*?)<br>Telefono:\s+(.*?)<br>Codice:\s*(.*?); Partita Iva:\s*(.*?)<br>@',
            $html, $matchesFarmacie);

        $farmacie = [];
        foreach ($matchesFarmacie[0] as $key => $value) {
            $farmacia = [
                'nome' => $matchesFarmacie[1][$key],
                'indirizzo' => strip_tags($matchesFarmacie[2][$key]),
                'telefono' => $matchesFarmacie[3][$key],
                'codice' => $matchesFarmacie[4][$key],
                'piva' => $matchesFarmacie[5][$key]
            ];
            $farmacie[] = $farmacia;
        }
        return $farmacie;
    }
}
