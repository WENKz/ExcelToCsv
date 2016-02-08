<?php

class Model {

    protected $db;

    public function __construct()
    {
        $this->db = $this->connectDB();
    }

    public function connectDB()
    {
        $db = new PDO('mysql:host=localhost;dbname=noza_board', "root", "");
        $db->exec("set names utf8");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    }

    public function update($table, $values, $clauses)
    {
        $db = $this->connectDB();
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $array = array();
        $i = 1;

        $query = "UPDATE " . $table . " SET";

        foreach ($values as $key => $value)
        {
            ($i == 1) ? $query.=" " : $query.= " , ";
            $query.= $key . " = '" . $value . "'";
            $i++;
        }
        $query.=" ";

        if ($clauses)
        {
            $i = 1;
            foreach ($clauses as $column => $value)
            {
                ($i == 1) ? $query.= "WHERE " : $query.= " AND ";
                // var_dump($column);
                $query.= $column . " = '" . $value . "'";
                $i++;
            }
        }
        //  var_dump($query);
        $query = $this->db->prepare($query);
        $query->execute();
    }

    public function select($table, $colonnes, $conditions = null, $diff = null, $rowCount = false, $group = null)
    {

        $i = 0;
        $query = "";
        //Colonnes selectionnées
        foreach ($colonnes as $colonne)
        {
            if ($i == 0)
            {
                $query = $colonne;
            }
            else
            {
                $query .= ", " . $colonne;
            }
        }
        //Selection de la table  
        $query = "SELECT " . $query . " FROM " . $table . " ";
        //Condition
        if ($conditions)
        {
            $i = 1;
            foreach ($conditions as $colonne => $valeur)
            {
                if ($i == 1)
                {
                    $query.= " WHERE ";
                }
                else
                {
                    $query.= " AND ";
                }
                if ($diff)
                {

                    $query.= $colonne . " " . $diff[$i - 1] . " '" . $valeur . "'";
                }
                else
                {
                    $query.= $colonne . " = '" . $valeur . "'";
                }
                $i++;
            }
        }
        if ($group)
        {

            $query.=" GROUP BY " . $group;
        }
        //   var_dump($query);
        $query = $this->db->prepare($query);
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        // Renvoyer le nombre de lignes
        if ($rowCount)
        {
            return $query->rowCount();
        }
        return $result;
    }

    public function count($table, $colonnes, $conditions = null)
    {
        $i = 0;

        $query = "SELECT " . $colonnes . ", COUNT(" . $colonnes . ") AS nombre FROM " . $table . " ";
        if ($conditions)
        {
            $i = 1;
            foreach ($conditions as $colonne => $valeur)
            {
                if ($i == 1)
                {
                    $query.="WHERE ";
                }
                else
                {
                    $query.=" AND ";
                }
                $query.= $colonne . " = '" . $valeur . "'";
                $i++;
            }
        }
        //  var_dump($query);
        try {
            $query = $this->db->prepare($query);
            $query->execute();
            $result = $query->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (Exception $e) {
            echo $e->getMessage();
            exit();
        }
    }

    public function join($tables, $colonnes, $where = null, $diff = null, $group = null, $limit = null)
    {
        $query = "";
        $i = 0;
        foreach ($colonnes as $valeur)
        {
            if ($i == 0)
            {
                $query .= $valeur;
            }
            else
            {
                $query .= ", " . $valeur;
            }
        }
        $query = "SELECT DISTINCT " . $query . " FROM " . $tables[0][0];
        foreach ($tables as $table)
        {
            $query .= " INNER JOIN " . $table[1] . " ON " . $table[1] . "." . $table[2] . " = " . $table[0] . "." . $table[2];
            $i++;
        }
        if ($where)
        {
            $i = 1;
            foreach ($where as $colonne => $valeur)
            {
                if ($i == 1)
                {
                    $query.= " WHERE ";
                }
                else
                {
                    $query.= " AND ";
                }
                if ($diff)
                {
                    $query.= $colonne . " " . $diff . " '" . $valeur . "'";
                }
                else
                {
                    $query.= $colonne . " = '" . $valeur . "'";
                }
                $i++;
            }
        }
        if ($group)
        {

            $query.=" GROUP BY " . $group;
        }
        if (is_array($limit))
        {
            $query.=" LIMIT $limit[0],$limit[1]";
        }
        // var_dump($query);
        $query = $this->db->prepare($query);
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function insert($table, $valeurs, $auto = false)
    {
        $db = $this->connectDB();
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $array = array();
        if ($auto)
        {
            $query = "INSERT INTO " . $table . " VALUES('', ";
        }
        else
        {
            $query = "INSERT INTO " . $table . " VALUES(";
        }
        $limit = count($valeurs);
        $i = 1;
        foreach ($valeurs as $clé => $valeur)
        {
            if ($i < $limit)
            {
                $query.=":" . $clé . ", ";
            }
            else
            {
                $query .= ":" . $clé;
            }
            $i++;
        }
        $query.=")";
        var_dump($query);
        try {
            $query = $this->db->prepare($query);
            $query->execute($valeurs);
        } catch (Exception $e) {
            echo "<div class='error'>" . $e->getMessage() . "</div>";
            exit();
        }
    }

    public function delete($table, $clause = true, $rowCount = false)
    {
        $query = "DELETE FROM " . $table;
        if ($clause)
        {
            $query .= " WHERE " . $clause[0] . " = :" . $clause[0];
        }
        try {
            $query = $this->db->prepare($query);
            var_dump($query);
            $lines = $query->execute(array($clause[0] => $clause[1]));
            // Retourner le nombre de lignes supprimées
            if ($rowCount)
            {
                return $lines;
            }
            else
            {
                return true;
            }
        } catch (Exception $e) {
            echo "<div class='error'>" . $e->getMessage() . "</div>";
            exit();
        }
    }

    public function load($query)
    {
        $query = $this->db->prepare($query);
        $query->execute();
        $result = $query->fetchAll();
        return $result;
    }

}
