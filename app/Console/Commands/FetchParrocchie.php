<?php

namespace App\Console\Commands;

use App\Models\Place;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FetchParrocchie extends Command {
    const PAGES = 2599;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:parrocchie {action}';

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
        } else if ($action == 'parse') {
            $this->parse();
        }
        return Command::SUCCESS;
    }

    private function download(): void
    {
        $pages = self::PAGES;
        $baseUrl = 'https://www.chiesacattolica.it/annuario-cei/ricerca-parrocchie/';
        for ($i = 1; $i <= $pages; $i++) {
            $filename = 'data/parrocchie/' . $i;

            if (file_exists($filename)) {
                echo "Skipping page $i\r\n";
                continue;
            }
            $url = $baseUrl;
            if ($i > 1) {
                $url .= '?pagina=' . $i;
            }

            try {
                file_put_contents($filename, file_get_contents($url));
            } catch (\Exception $e) {
                echo "error downloading $url\r\n";
            }
            echo "Scaricata pagina $i\r\n";

        }
    }

    private $regExp = '@<div id="parrocchia_\d+".*?>.*?<span class="cci-singleparrocchia-diocesi">.*?<a href.*?>\s+(.*?)\s+</a>.*?"cci-singlecommep-title">(.*?)</h4>.*?"cci-singleparrocchia-indirizzo">(.*?)</span>.*?"cci-singleparrocchia-abitanti">Numero di abitanti: <b>(\d+)<@s';


    private function parse()
    {
        for ($i = 1; $i <= self::PAGES; $i++) {
            $filename = 'data/parrocchie/' . $i;
            if (file_exists($filename . '.done')) {
                echo "Skipping $i\r\n";
                continue;
            }

            $data = file_get_contents($filename);
            $matches = [];
            preg_match_all($this->regExp, $data, $matches);
            //print_r($matches);
            foreach ($matches[0] as $index => $match) {
                $data = [];
                for ($j = 1; $j <= 4; $j++) {
                    $data[] = preg_replace('@\s+@', ' ', $matches[$j][$index]);
                }
                $this->saveData($data);
                // print_r($data);
            }
            file_put_contents($filename . '.done', '');


        }

    }

    private function saveData(array $data)
    {
        echo "Saving {$data[1]}\r\n";
        print_r($data);
        $match = [];
        preg_match('@\s*([^,]+)- (\d+)\s*\((\w{2})\)@', $data[2], $match);
        if (strpos($data[2], "REPUBBLICA DI SAN MARINO") !== false) {
            return;
        }


        $name = trim($match[1]);
        $origName = $name;
        if ($name == 'SAN GIOVANNI DI FASSA') {
            $name = 'San Giovanni di Fassa-S??n Jan';
        }
        $name = str_replace("A'", "??", $name);
        $name = str_replace("I'", "??", $name);
        if (strpos($name, " DE'") === false) {
            $name = str_replace("E'", "??", $name);
        }
        if (strpos($name, "VO'") === false) {
            $name = str_replace("O'", "??", $name);
        }
        $name = str_replace("U'", "??", $name);
        $name = str_replace("MONRUPINO", "Monrupino-Repentabor", $name);
        $name = str_replace("CAVA D?? TIRRENI", "CAVA DE' TIRRENI", $name);

        if ($origName == "ANTEY-SAINT-ANDRE'") {
            $name = 'ANTEY-SAINT-ANDR??';
        }
        if ($origName == "MONTESCUDO-MONTECOLOMBO") {
            $name = 'MONTESCUDO-MONTE COLOMBO';
        }
        if ($origName == 'TERZO DI AQUILEIA') {
            $name = "Terzo d'Aquileia";
        }

        $parent = $match[3];
        if ($name == 'SASSOFELTRIO' || $name == "MONTECOPIOLO") {
            $parent = 'rn';
        }
        if (isset($this->matches[$origName])) {
            $name = $this->matches[$origName];
        }

        $query = Place::query()->whereLivello(4)
            ->where(function ($q) use ($name, $origName) {
                $q->where('nome', 'like', $name);
                $q->orWhere('nome', 'like', $origName);
            })
            ->where('parent', 'like', trim($parent));

        dump($query->getBindings());
        dump($query->toSql());

        $comune = $query
            ->first();


        //return;
        DB::insert("INSERT OR IGNORE INTO parrocchie(codice, nome, diocesi, indirizzo,fedeli)VALUES(?,?,?,?,?);", [
            $comune->codice, $data[1], $data[0], $data[2], $data[3]
        ]);
    }

    private $matches = [
        "VERRES" => 'Verr??s',
        "SAN FLORIANO DEL COLLIO" => 'San Floriano del Collio-??teverjan',
        "DUINO-AURISINA" => 'Duino Aurisina-Devin Nabre??ina',
        "HONE" => 'H??ne',
        "RHEMES-SAINT-GEORGES" => 'Rh??mes-Saint-Georges',
        'SAINT-RHEMY-EN-BOSSES' => 'Saint-Rh??my-en-Bosses',
        "PRE'-SAINT-DIDIER" => 'Pr??-Saint-Didier',
        "MALE'" => 'Mal??',
        "PONT-CANAVESE" => "Pont Canavese",
        'MONTEBELLO IONICO' => 'Montebello Jonico',
        'FENIS' => 'F??nis',
        'SGONICO' => 'Sgonico-Zgonik',
        'JOVENCAN' => 'Joven??an',
        'EMARESE' => 'Emar??se',
        'CHATILLON' => 'Ch??tillon',
        'GORNATE-OLONA' => 'Gornate Olona',
        'RHEMES-NOTRE-DAME' => 'Rh??mes-Notre-Dame'
    ];
}
