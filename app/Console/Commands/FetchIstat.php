<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FetchIstat extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:istat {action}';

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
        if ($argument == 'split') {
            $this->split();
        } else if ($argument == 'parse') {
            $this->parse();
        }
        return Command::SUCCESS;
    }

//[0] => 050037
//[1] => Vecchiano
//[2] => JAN
//[3] => popolazione al 1º gennaio
//[4] => 9
//[5] => totale
//[6] => Y5
//[7] => 5 anni
//[8] => 99
//[9] => totale
//[10] => 2021
//[11] => 2021
//[12] => 88
//[13] => p
//[14] => dato provvisorio


    /* cat
    1:nubile
    2:coniug
    3:divorziato
    4:vedovo
    5:
    6:
    16: precedentemente in unione civile
    99:totale
    */
    private function parseIndicatore(string $indicatore)
    {
        if ($indicatore == '1') {
            return 'C';
        } elseif ($indicatore == '2') {
            return 'S';
        } elseif ($indicatore == '3') {
            return 'D';

        } elseif ($indicatore == '4') {
            return 'V';
        } elseif ($indicatore == '99') {
            return 'T';
        } elseif ($indicatore == '15') {
            return 'c';
        } elseif ($indicatore == '16') {
            return 'v';
        } elseif ($indicatore == '17') {
            return 'd';
        }

        die($indicatore . ' indicatore sconosciuto');
    }

    private function parseSesso($key)
    {
        if ($key == '1') {
            return 'M';
        } elseif ($key == '2') {
            return 'F';
        } elseif ($key == '9') {
            return 'T';
        }
        die($key . ' chiave sesso sconosciuta');
    }

    private function getAgeRange($age)
    {
        if ($age == '_GE100') {
            return '100+';
        }
        if( $age == 'TOTAL'){
            return 'TOTAL';
        }
         $start = floor($age / 5) * 5;
        $end = $start + 4;
        return $start . '-' . $end;
    }

    private function parse()
    {
        $comuni = DB::select('select Denominazione as nome, Codice_Comune_alfanumerico as codice from comuni_raw');
        foreach ($comuni as $comune) {
            $codice = $comune->codice;
            $nome = $comune->nome;
            $fileIstat = 'data/istat/' . $codice . '.csv';
            $fileJson = 'info/istat/' . $codice . '.json';
            if (!file_exists($fileIstat)) {
                echo "Comune $nome non trovato\r\n";
                continue;
            }

            echo "Parsing $nome \r\n";
            $lines = file($fileIstat);

            $parsed = [];
            foreach ($lines as $line) {
                if ($line == PHP_EOL) {
                    continue;
                }
                $data = explode('|', $line);
                $valore = $data[12];
                if (!is_numeric($valore)) {
                    continue;
                }

                $anni = str_replace('Y', '', $data[6]);
                $anniRange = $this->getAgeRange($anni);
                $indicatore = $this->parseIndicatore($data[8]);
                $sesso = $this->parseSesso($data[4]);
                if (isset($parsed[$data[11]][$anniRange][$sesso][$indicatore])) {
                    $parsed[$data[11]][$anniRange][$sesso][$indicatore] += $valore;
                } else {
                    $parsed[$data[11]][$anniRange][$sesso][$indicatore] = $valore;
                }
            }
            if (!isset($parsed['2020'])) {
                continue;
            }
            $json = json_encode($parsed['2020']);
            Storage::put($fileJson, $json);
//            file_put_contents($fileJson,$json);
        }
    }

    private function split(): void
    {
        $fp = fopen('data/istat.csv', 'r');
        $headers = array_map(function ($string) {
            return preg_replace('/[^[:print:]]/', '', $string);
        }, explode('|', str_replace('"', '', fgets($fp))));

        $files = [];


        $prevKey = '';
        $tempArr = [];
        $count = 0;
        while ($line = str_replace('"', '', fgets($fp))) {
            $values = explode('|', $line);
            $data = array_combine($headers, $values);
            $filename = 'data/istat/' . $data['ITTER107'] . '.csv';

            if (!isset($files[$filename])) {
                $files[$filename] = fopen($filename, 'a');
                echo "opening file: " . count($files) . PHP_EOL;
            }

            fwrite($files[$filename], $line . PHP_EOL);

            // file_put_contents($filename,$line . PHP_EOL,FILE_APPEND);


            if ($count++ % 1000 == 0) {
                echo "Line: $count\r\n";
            }

        }
        foreach ($files as $fp) {
            echo "closing files \r\n";
            fclose($fp);
        }
    }


}
