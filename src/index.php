<?php

session_start();
ini_set('display_errors', 1);
ini_set('memory_limit', -1);
set_time_limit(0);
define('WEBROOT', str_replace('index.php', '', $_SERVER['SCRIPT_NAME']));
define('ROOT', str_replace('index.php', '', $_SERVER['SCRIPT_FILENAME']));

require(ROOT . 'PHPExcel.php');

class CatalogueController {

    public function index()
    {

        if (file_exists("output.csv"))
        {
            unlink("output.csv");
        }
        $this->convertXLStoCSV('Tableau_récap_marges_etc.xlsx', "HTNOVALEUR", 'output.csv');
    }

    private function convertXLStoCSV($infile, $feuille, $outfile)
    {
        $fileType = PHPExcel_IOFactory::identify($infile);
        $reader = PHPExcel_IOFactory::createReader($fileType);

        $reader->setReadDataOnly(true);
        $reader->setLoadSheetsOnly($feuille);

        $excel = $reader->load($infile);

        $writer = PHPExcel_IOFactory::createWriter($excel, 'CSV');
        $writer->save($outfile);
    }

    private function TraitementCsvToArray($filename)
    {
        $row = 0;
        $col = 0;
        $handle = @fopen($filename, "r");
        if ($handle)
        {
            while (($row = fgetcsv($handle, 0, ';')) !== false)
            {
                if (empty($fields))
                {
                    $fields = $row;
                    continue;
                }
                foreach ($row as $k => $value)
                {
                    $results[$col][$fields[$k]] = $value;
                }
                $col++;
                unset($row);
            }
            if (!feof($handle))
            {
                echo "Erreur: fgets() échoue";
            }
            fclose($handle);
        }
        return $results;
    }

}

$Catalogue = new CatalogueController();
$Catalogue->index();

