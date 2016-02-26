<style>
    <!--
 @page {
   size: 7in 9.25in;
   margin: 27mm 16mm 27mm 16mm;
}
    .ACBG{background-color:rgb(92 , 121 , 187)}
    .ACBR{background-color:rgb(221 , 34 , 133)}
    .ACCE{background-color:rgb(134 , 194 , 235)}
    .ACFU{background-color:rgb(255 , 205 , 28)}
    .ACNA{background-color:rgb(167 , 53 , 139)}
    .BADG{background-color:rgb(224 , 9 , 40)}
    .BANG{background-color:rgb(92 , 121 , 187)}
    .BIET{background-color:rgb(142 , 103 , 63)}
    .BLTA{background-color:rgb(96 , 76 , 35)}
    .BOCI{background-color:rgb(211 , 39 , 29)}
    .BOX{background-color:rgb(120 , 179 , 43)}
    .BRIQ{background-color:rgb(221 , 34 , 133)}
    .CEND{background-color:rgb(254 , 201 , 23)}
    .CIEL{background-color:rgb(134 , 194 , 235)}
    .CONF{background-color:rgb(224 , 9 , 40)}
    .COUT{background-color:rgb(224 , 9 , 40)}
    .DRAP{background-color:rgb(224 , 9 , 40)}
    .ECUS{background-color:rgb(224 , 9 , 40)}
    .ENC{background-color:rgb(142 , 103 , 63)}
    .FERO{background-color:rgb(0 , 156 , 119)}
    .FILT{background-color:rgb(0 , 149 , 212)}
    .GRIN{background-color:rgb(238 , 114 , 3)}
    .MATU{background-color:rgb(40 , 53 , 131)}
    .NARG{background-color:rgb(167 , 53 , 139)}
    .PIPE{background-color:rgb(0 , 102 , 57)}
    .POCL{background-color:rgb(224 , 9 , 40)}
    .POEN{background-color:rgb(142 , 103 , 63)}
    .ROUL{background-color:rgb(40 , 53 , 131)}
    .SAZI{background-color:rgb(120 , 179 , 43)}
    .SMAR{background-color:rgb(229 , 121 , 174)}
    .STIC{background-color:rgb(224 , 9 , 40)}
    .TUBE{background-color:rgb(0 , 156 , 119)}

    table{background:gray;width: 277.45mm}





    -->
</style>
<?php
session_start();
ini_set('display_errors', 1);
set_time_limit(0);
define('WEBROOT', str_replace('index.php', '', $_SERVER['SCRIPT_NAME']));
define('ROOT', str_replace('index.php', '', $_SERVER['SCRIPT_FILENAME']));

require(ROOT . 'PHPExcel.php');

class CatalogueController {

    public function index()
    {
        echo "<pre>";
        if (file_exists("output.csv"))
        {
            unlink("output.csv");
        }
        $this->convertXLStoCSV('Tableau_récap_marges_etc.xlsx', "HTNOVALEUR", 'output.csv');
        $tableauCsv = $this->TraitementCsvToArray("output.csv", ',');
        $DesignationReference = $this->TraitementCsvToArray("designation_ref.csv", ';');
        $ArrayTab = $this->traitementTableau($tableauCsv);
        $ArraySku = $this->DistinctSku($ArrayTab);
        $tableau = $this->RechercheDegressif($ArrayTab, $ArraySku);
        $final = $this->TraitementPdf($DesignationReference, $tableau);

        foreach ($final as $fin)
        {
            $lignes = 0;
            echo "<table>";
            //var_dump($fin);
            foreach ($fin as $page)
            {

                if (array_key_exists("Nom", $page))
                {
                    echo "<tr class='" . $page["ref"] . "'>";
                    $array = array();
                    echo"<td colspan='2' WIDTH='146.9mm'>" . $page["Nom"] . "</td>";
                    $nb = count($page) - 2;
                    for ($i = 1; $i <= $nb; $i++)
                    {
                        if (!empty($page[$i]))
                        {
                            echo "<td>Prix par " . $page[$i] . "</td>";
                            $array[] = "Prix par " . $page[$i];
                        }
                    }
                    for($a = $nb ; $a <= 7;$a++){
                        echo "<td WIDTH='18.65mm'></td>";
                    }
                    echo "</tr>";
                }
                else
                {
                    echo "<tr>";

                    echo "<td WIDTH='34.75mm'>" . $page["REFERENCE"] . "</td>";
                    echo "<td WIDTH='112.15mm'>" . $page["DESIGNATION"] . "</td>";
                    foreach ($array as $prixpar)
                    {

                        if (array_key_exists($prixpar, $page))
                        {
                            $p = explode(".", $page[$prixpar]);
                            // var_dump($p);
                            echo "<td WIDTH='18.65mm'>" . $p[0];
                            if (array_key_exists("1", $p))
                            {
                                $o = substr($p[1], 0, 2);
                                echo (strlen($o) == 1) ? "." . $o . "0" : "." . $o;
                            }
                            echo "&euro;</td>";
                        }else{
                            echo "<td WIDTH='18.65mm'></td>";
                        }
                    }
                  
                    echo "</tr>";
                }
                $lignes++;
            }
            echo "</table>";
        }
    }

    private function TraitementPdf($titres, $produits)
    {
        $lignes = 0;
        $tabs = 0;
        $pages = array();
        foreach ($titres as $titre)
        {
            $DesignationTraité = array_filter($titre);

            if ($lignes < 20)
            {
                $pages[$tabs][] = $DesignationTraité;
                $lignes++;
            }
            else
            {
                $tabs++;
                $pages[$tabs][] = $DesignationTraité;
                $lignes = 2;
            }
            foreach ($produits as $produit)
            {
       
                if (strrpos ($produit["REFERENCE"],$DesignationTraité["ref"]) !== false && $produit["REFERENCE"] != "TUBE-0023")
                {
                    if ($lignes >= 30)
                    {
                        $tabs++;
                        $lignes = 2;
                        $pages[$tabs][] = $DesignationTraité;
                    }
                    $pages[$tabs][] = $produit;
                    $lignes++;
                }
                if ($DesignationTraité["ref"] == $produit["REFERENCE"])
                {
                    if ($lignes >= 30)
                    {
                        $tabs++;
                        $lignes = 2;
                        $pages[$tabs][] = $DesignationTraité;
                    }
                    $pages[$tabs][] = $produit;
                    $lignes++;
                }
            }
        }
        return $pages;
    }

    private function temp()
    {
        foreach ($final as $fin)
        {
            $lignes = 0;
            echo "<table>";
            foreach ($fin as $page)
            {
                echo "<tr>";
                if ($lignes == 0 || array_key_exists("Nom", $page))
                {
                    echo"<td colspan = '2'>" . $page["Nom"] . "</td>";
                    $nb = count($page) - 2;
                    for ($i = 1; $i <= $nb; $i++)
                    {
                        if (!empty($page[$i]))
                        {
                            echo "<td>Prix par " . $page[$i] . "</td>";
                        }
                    }
                }
                else
                {


                    foreach ($page as $produit)
                    {
                        echo "<td>" . $produit . "</td>";
                    }
                }
                $lignes++;
                echo "</tr>";
            }
            echo "</table>";
        }
    }

    private function tempotest()
    {
        echo "<table>";
        foreach ($DesignationReference as $DesignationRef)
        {
            echo "<tr>";
            $DesignationTraité = array_filter($DesignationRef);

            $nb = count($DesignationTraité) - 2;
            echo"<td colspan = '2'>" . $DesignationTraité["Nom"] . "</td>";

            for ($i = 1; $i <= $nb; $i++)
            {
                if (!empty($DesignationTraité[$i]))
                {
                    echo "<td>Prix par " . $DesignationTraité[$i] . "</td>";
                }
            }
            echo "</tr>";
            foreach ($tableau as $val)
            {

                if (!empty(preg_grep("/" . $DesignationTraité["ref"] . "+/", $val)) && $val["REFERENCE"] != "TUBE-0023")
                {
                    echo "<tr>";
                    echo "<td>" . $val["REFERENCE"] . "</td><td>" . $val["DESIGNATION"] . "</td>";
                    for ($i = 1; $i <= $nb; $i++)
                    {
                        if (!empty($DesignationTraité[$i]))
                        {
                            if (!empty($val["Prix par " . $DesignationTraité[$i]]))
                            {
                                $p = explode(".", $val["Prix par " . $DesignationTraité[$i]]);
                                echo "<td style='text-align:right'>" . $p[0];
                                if (array_key_exists("1", $p))
                                {
                                    if (strlen($p[1]) > 1)
                                    {
                                        echo "." . $p[1][0] . $p[1][1] . " &euro;";
                                    }
                                    else
                                    {
                                        echo "." . $p[1][0] . "0 &euro;";
                                    }
                                }
                                else
                                {
                                    echo ".00 &euro;";
                                }
                                echo "  </td>";
                            }
                        }
                    }
                    echo "</tr>";
                }
                if ($DesignationTraité["ref"] == $val["REFERENCE"])
                {
                    echo "<tr>";
                    echo "<td>" . $val["REFERENCE"] . "</td><td>" . $val["DESIGNATION"] . "</td>";
                    for ($i = 1; $i <= $nb; $i++)
                    {
                        if (!empty($DesignationTraité[$i]))
                        {
                            if (!empty($val["Prix par " . $DesignationTraité[$i]]))
                            {
                                $p = explode(".", $val["Prix par " . $DesignationTraité[$i]]);
                                echo "<td style='text-align:right'>" . $p[0];
                                if (array_key_exists("1", $p))
                                {
                                    if (strlen($p[1]) > 1)
                                    {
                                        echo "." . $p[1][0] . $p[1][1] . " &euro;";
                                    }
                                    else
                                    {
                                        echo "." . $p[1][0] . "0 &euro;";
                                    }
                                }
                                else
                                {
                                    echo ".00 &euro;";
                                }
                                echo "  </td>";
                            }
                        }
                    }
                    echo "</tr>";
                }
            }
        }
        echo "</table>";
    }

    private function CreateCsv()
    {
        $chemin = "fichier.csv";
        $delimiteur = ",";
        $fichier_csv = fopen($chemin, 'w+');
        fprintf($fichier_csv, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($fichier_csv, array("", "", "Prix par 1", "Prix par 3", "Prix par 6", "Prix par 12", "Prix par 24", "Prix par 48", "Prix par 96"), $delimiteur);
        fclose($fichier_csv);
        $fichier_csv = fopen($chemin, 'a+');
        foreach ($tableau as $ligne)
        {
            fputcsv($fichier_csv, $ligne, $delimiteur);
        }
        fclose($fichier_csv);
    }

    private function RechercheDegressif($ArrayTab, $ArraySku)
    {
        $TableauFinal = Array();
        $tab = 1;
        foreach ($ArraySku as $ArraySk)
        {
            $i = 0;

            foreach ($ArrayTab as $Array)
            {
                if (in_Array($ArraySk, $Array))
                {
                    $i++;
                    if ($i > 1)
                    {
                        $TableauFinal[$tab]["Prix par " . $Array["QTE_TARIF_VENTE"]] = $Array["PRIXUNITAIRE"];
                    }
                    else
                    {
                        $TableauFinal[$tab]["REFERENCE"] = $ArraySk;
                        $TableauFinal[$tab]["DESIGNATION"] = $Array["DESIGNATION"];
                        $TableauFinal[$tab]["Prix par 1"] = $Array["PRIXUNITAIRE"];
                    }
                }
            }
            $tab ++;
        }
        return $TableauFinal;
    }

    private function DistinctSku($ArrayTab)
    {
        foreach ($ArrayTab as $Array)
        {
            $sku[] = $Array["REFERENCE"];
        }
        return array_unique($sku);
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

    private function TraitementCsvToArray($filename, $separateur)
    {
        $row = 0;
        $col = 0;
        $handle = @fopen($filename, "r");
        if ($handle)
        {
            while (($row = fgetcsv($handle, 0, $separateur)) !== false)
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

    private function traitementTableau($tableau)
    {
        $i = 0;
        $Feuilles = array();
        foreach ($tableau as $valeur)
        {
            $i++;

            extract($valeur);
            $Feuilles[$i]["REFERENCE"] = $REFERENCE;
            $Feuilles[$i]["DESIGNATION"] = $DESIGNATION;
            $Feuilles[$i]["PRIXUNITAIRE"] = $PRIXUNITAIRE;
            $Feuilles[$i]["QTE_TARIF_VENTE"] = $QTE_TARIF_VENTE;
        }
        return $Feuilles;
    }

}

$Catalogue = new CatalogueController();
$Catalogue->index();

