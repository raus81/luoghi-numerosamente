<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FetchIndettaglio extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:indettaglio {action}';

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

        $letter = ord('a');
        while ($letter <= ord('z')) {
            $ascii = chr($letter);
            $url = "http://italia.indettaglio.it/ita/comuni/comuni_{$ascii}.html";
            $fileCheck = md5($url);
            if (!file_exists('data/indettaglio/' . $fileCheck)) {
                echo "$url\r\n";
                $data = file_get_contents($url);
                file_put_contents('data/indettaglio/' . $ascii, $data);
                file_put_contents('data/indettaglio/' . $fileCheck, '');
            } else {
                $data = file_get_contents('data/indettaglio/' . $ascii);
            }
            $matches = [];
            preg_match_all('@<OPTION VALUE="/ita/(.*?)\.html">(.*?)</OPTION>@', $data, $matches);
            foreach ($matches[1] as $index => $cUrl) {
                $fullUrl = 'http://italia.indettaglio.it/ita/' . $cUrl . '.html';
                $filename = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $matches[2][$index])));
                echo $filename . ' ' . $fullUrl . PHP_EOL;
                if (file_exists('data/indettaglio/' . $filename)) {
                    echo "Skipping $fullUrl\r\n";
                    continue;
                }
                $cData = file_get_contents($fullUrl);
                file_put_contents('data/indettaglio/' . $filename, $cData);

            }

            $letter++;
        }


    }


    private function tableToArray($content)
    {
        $DOM = new \DOMDocument();
        $DOM->loadHTML($content);
        $items = $DOM->getElementsByTagName('tr');
        $return = array();
        foreach ($items as $node) {

             foreach( $node->childNodes as $tdNode ){
                echo $tdNode->nodeValue;
            }
        }
        return $return;
    }



    private function parse()
    {
        $stringTest = '<TABLE class="table">
<TR class="info"><td>Genere</td><td>Laurea</td><td>Diploma</td><td>Licenza Media</td><td>Licenza Elementare</td><td>Alfabeti</td><td>Analfabeti</td></tr>
<tr><td><b class="colore">Maschi</b></td><td>217</td><td>839</td><td>1063</td><td>561</td><td>164</td><td>10</td></tr>
<tr><td><b class="colore">Femmine</b></td><td>302</td><td>937</td><td>790</td><td>861</td><td>220</td><td>10</td></tr>
<tr><td><b class="colore">Totale</b></td><td>519</td><td>1776</td><td>1853</td><td>1422</td><td>384</td><td>20</td></tr>
</table>';
        $this->tableToArray($stringTest);
        return;
        $files = array_diff(scandir('data/indettaglio/'), array('.', '..'));
        $data = Collection::make($files);
        $comuni = $data->filter(function ($file) {
            return str_contains($file, '-');
        })->toArray();

        echo "Num " . count($comuni) . PHP_EOL;
        foreach ($comuni as $comune) {
            $data = file_get_contents('data/indettaglio/' . $comune);
            //echo $data . PHP_EOL;
            $match = [];
            preg_match('@Home</a> >> <a href="/ita/">Italia</a> >> <a href="/ita/regioni/.*?.html">(.*?)</a> >> <a href="/ita/province/.*">.*</a> >> <span><a href="/ita/.*?\.html" itemprop="url">(.*?)</a>@', $data, $match);

            $dbData = DB::selectOne("SELECT Codice_Comune_alfanumerico as codice FROM comuni_raw WHERE Denominazione like ? and Denominazione_Regione like ?", [$match[2], $match[1] . '%']);

            if ($dbData == null) {
                //print_r($match);
                echo "Skipping " . $match[2] . PHP_EOL;
                continue;
            }

            //print_r($dbData);
            $codice = $dbData->codice;


        }

    }
}
