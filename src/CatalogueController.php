<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CatalogueController
 *
 * @author quentin
 */
class CatalogueController  {

    public function index()
    {
        echo getcwd();
        $this->LectureFichier("./Tableau_récap_marges_etc.xlsx", "2");
    }

    private function LectureFichier($fichier, $feuille)
    {
        $objPHPExcel = PHPExcel_IOFactory::load($fichier);
        $sheet = $objPHPExcel->getSheet($feuille);
        $this->TraitementTableau($sheet);
    }

    private function TraitementTableau($sheet)
    {
        foreach ($sheet->getRowIterator() as $ligne)
        {
            foreach ($ligne->getCellIterator() as $cell)
            {
                var_dump($cell);
            }
        }
    }

}
