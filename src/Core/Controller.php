<?php

class Controller{
//1542benoit
//    protected $model;
    protected $data;
    protected $action;

    public function __construct() {
        $this->loadModel(str_replace("Controller", "", get_class($this)));
    }

    protected function isLogged() {
        if (isset($_SESSION['token_user']) && isset($_SESSION['id_user'])) {
            return true;
        }else if($_SERVER['SERVER_ADDR'] == "127.0.0.1"){
            return true;
        }
    }

    protected function loadModel($model) {
        $model .= "Model";
        include_once("Models/" . ucfirst($model) . ".php");
        $this->$model = new $model();
    }
    
      public function paginate($infos) {
        extract($infos);
        $items = array("<nav class='barre-navigation'><ul>");
        $i = 1;
        // Bouton précédent
        if ($here > 1) {
            $prec = $here - 1;
            array_push($items, "<li><a href='" . $link . $prec . "'>Préc.</a></li>");
        }
        while ($i <= $nb_page) {
            // Différencier la page actuelle des autres pages
            ($i == $here) ? array_push($items, '<li><span>' . $i . '</span></li>') : array_push($items, "<li><a href='" . $link . $i . "'>" . $i . "</a></li>");
            $i++;
        }
        // Bouton suivant
        if ($here < $nb_page) {
            $suiv = $here + 1;
            array_push($items, "<li><a href='" . $link . $suiv . "'>Suiv.</a></li>");
        }
        array_push($items, "</ul></nav>");
        $this->items = $items;
        return $items;
    }

    protected function render($pathview, $template = false) {
        if (!isset($pathview[1])) {
            $pathview[1] = $pathview[0];
        }
        if (is_array($template) && isset($template['titre'])) {
            $template['path'] = "Doctype";
            ob_start();
            include_once("Views/" . $pathview[1] . "/" . $pathview[0] . ".view.php");
            $this->content = ob_get_clean();

            $this->titre = $template["titre"];
            include_once("Views/Template/" . $template["path"] . ".view.php");
        } if (is_array($template) && isset($template['type'])) {
             $template['path'] = "ConnexionType";
            ob_start();
            include_once("Views/" . $pathview[1] . "/" . $pathview[0] . ".view.php");
            $this->content = ob_get_clean();

            $this->titre = "Connexion";
            include_once("Views/Template/" . $template["path"] . ".view.php");
            
        }else {
            include_once(ROOT . 'Views/' . $pathview[1] . '/' . $pathview[0] . '.view.php');
        }
    }

    protected function render_action($controlleur, $action, $param = false,$secParam = false) {
//        $controller = get_class($this);
        if (!$param) {
            $param = "";
        }
        $controlleur.="Controller";
        include_once("./Controllers/" . $controlleur . ".php");
        $controlleur = new $controlleur();
        return $controlleur->$action($param,$secParam);
    }

}
