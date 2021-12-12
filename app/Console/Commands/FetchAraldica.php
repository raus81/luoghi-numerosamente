<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FetchAraldica extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:araldica {action} {letter?}';

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
        $argument = $this->argument('action');
        if ($argument == 'download') {
            $this->download();
        } else if ($argument == 'parse') {
            $this->parse();
        }
        return Command::SUCCESS;
    }

    private function download()
    {

        for ($i = 1; $i <= 234; $i++) {

            $data = $this->downloadPage($i);
            $matches = [];
            preg_match_all('@<a href="(https://www.araldicacivica.it/comune/([^/]+)/)">@', $data, $matches);
            foreach ($matches[1] as $index => $url) {
                echo "$url\r\n";
                $name = $matches[2][$index];
                $this->downloadComune($url, $name);
            }
        }

    }

    /**
     * @param int $i
     * @return string
     */
    private function downloadPage(int $i): string
    {
        $baseUrl = "https://www.araldicacivica.it/comuni/";
        if (file_exists('data/araldica/list/' . $i)) {
            echo "Skipping page $i\r\n";
            return file_get_contents('data/araldica/list/' . $i);
        }

        $url = $baseUrl;
        if ($i > 1) {
            $url .= 'page/' . $i;
        }

        echo "Downloading: $url\r\n";
        $data = file_get_contents($url);
        file_put_contents('data/araldica/list/' . $i, $data);
        return $data;
    }

    private function downloadComune($url, $name)
    {
        if (file_exists('data/araldica/' . $name)) {
            echo "+ Skipping url $name\r\n";
            return file_get_contents('data/araldica/' . $name);
        }


        echo "+ Downloading: $url\r\n";
        $data = file_get_contents($url);
        file_put_contents('data/araldica/' . $name, $data);
        return $data;
    }

    private function parse()
    {
        $letter = $this->argument('letter');

        $dir = 'data/araldica/';
        if ($letter != null) {
            $dir .= $letter . '*';

        }
        $files = array_diff(scandir($dir), array('.', '..', 'list'));
        $data = Collection::make($files);
        $comuni = $data->filter(function ($file) {
            return !str_contains($file, '.done');
        })->toArray();
        print_r($comuni);
        foreach ($comuni as $comune) {
            echo "Parsing $comune\r\n";
            $match = [];
            $data = file_get_contents('data/araldica/' . $comune);
            preg_match('@>Stemma Ridisegnato</h4>.*?<img.*?src="(https.*?)\?resize@s', $data, $match);
            if (!isset($match[1])) {
                echo "Skipping \r\n";
                continue;
            }
            $imageUrl = $match[1];

            preg_match('@<li><strong>Codice Catastale:</strong>\s*(.*?)</li>@', $data, $match);
            if (!isset($match[1])) {
                echo "Skipping \r\n";
                continue;
            }
            $codiceCatastale = $match[1];

            $comuneObj = DB::selectOne("SELECT Codice_Comune_alfanumerico as codice FROM comuni_raw WHERE Codice_Catastale_comune like ?", [$codiceCatastale]);

            if ($comuneObj == null) {
                continue;
            }
            $codice = $comuneObj->codice;

            $filename = 'public/storage/stemmi/' . $codice . '.jpg';
            if (file_exists($filename)) {
                continue;
            }
            try {
                file_put_contents($filename, file_get_contents($imageUrl));
            } catch (\Exception $e) {
                print_r($e);
            }


            echo $match[1] . PHP_EOL;
        }
    }
}
