<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FetchCognomi extends Command {


    private $command = "curl 'https://www.cognomix.it/json/classificacognomi'   -H 'authority: www.cognomix.it'   -H 'sec-ch-ua: \"Chromium\";v=\"94\", \"Google Chrome\";v=\"94\", \";Not A Brand\";v=\"99\"'   -H 'accept: application/json, text/javascript, */*; q=0.01'   -H 'content-type: application/x-www-form-urlencoded; charset=UTF-8'   -H 'x-requested-with: XMLHttpRequest'   -H 'sec-ch-ua-mobile: ?0'   -H 'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.81 Safari/537.36'   -H 'sec-ch-ua-platform: \"Windows\"'   -H 'origin: https://www.cognomix.it'   -H 'sec-fetch-site: same-origin'   -H 'sec-fetch-mode: cors'   -H 'sec-fetch-dest: empty'   -H 'referer: https://www.cognomix.it/classifiche-cognomi-regioni-province-comuni.php'   -H 'accept-language: it-IT,it;q=0.9,en-US;q=0.8,en;q=0.7'   -H 'cookie: cognomix_session=7nntjrisgnhfq1hidacgi4uvh3ngi6cr; cognomix_cookiepolicy=Accetto; cognomix_csrf=9eeac069697b664af9f8e6072480dcba'   --data-raw 'csrf_token=NzU0ZjQ4N2VkOTk3MzA3Zjc2MjEzMWUwMDExZjlkNTI%3D&dove=4&reg=&pro=&istat=108046'   --compressed";
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:cognomi {action}';

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

    private function getCmd($codice)
    {
        return preg_replace("@istat=\d+@", 'istat=' . $codice, $this->command);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public
    function handle()
    {
        $action = $this->argument('action');

        if ($action == "download") {

            $this->downloadData();
        } elseif ($action == 'parse') {

            $this->parseData();
        }
        return Command::SUCCESS;
    }

    private function downloadData(): void
    {
        $users = $this->getComuni();

        foreach ($users as $user) {
            $codiceComuneAlfanumerico = $user->Codice_Comune_alfanumerico;
            $comune = $user->Denominazione;
            $filename = "data/cognomi/" . $codiceComuneAlfanumerico;
            if (file_exists($filename)) {
                echo "Skipping $comune\r\n";
                continue;
            }
            $cmd = $this->getCmd($codiceComuneAlfanumerico);
            $data = system($cmd);
            if (strlen($data) > 200) {
                file_put_contents($filename, $data);
            }

            echo PHP_EOL . $comune . ": " . strlen($data) . PHP_EOL;

            sleep(0.1);
        }
    }

    private function parseData()
    {
        $comuni = $this->getComuni();
        foreach ($comuni as $comune) {
            $codiceComuneAlfanumerico = $comune->Codice_Comune_alfanumerico;
            $nome = $comune->Denominazione;
            $filename = "data/cognomi/" . $codiceComuneAlfanumerico;
            if (!file_exists($filename)) {
                continue;
            }
            if (file_exists($filename . '.done')) {
                echo "Skipping: $nome\r\n";
                continue;
            }
            $data = file_get_contents($filename);
            echo $data;
            $match = [];
            preg_match_all("@<tr><td>\d+<\\\/td><td><a.*?>(.*?)<\\\/a><\\\/td><td>(\d+)<\\\/td><\\\/tr>@is", $data, $match);
            if (count($match[0])) {
                for ($i = 0, $iMax = count($match[0]); $i < $iMax; $i++) {
                    echo $match[1][$i] . " " . $match[2][$i] . PHP_EOL;
                    DB::insert("INSERT INTO cognomi(codice, cognome , quanti)VALUES(?, ?, ?)", [
                        $codiceComuneAlfanumerico,
                        $match[1][$i],
                        $match[2][$i]
                    ]);
                }
                file_put_contents($filename . '.done', '');
            }

        }
    }

    /**
     * @return array
     */
    private function getComuni(): array
    {
        $users = DB::select('select Denominazione,Codice_Comune_alfanumerico from comuni_raw cr  ');
        return $users;
    }
}
