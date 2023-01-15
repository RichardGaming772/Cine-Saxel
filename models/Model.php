<?php
trait JsonSerializer
{
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
class Model
{

    const DBNAME = 'none';

    public function __construct()
    {
    }

    public function __get($name)
    {

        return $this->$name;
    }

    public function __set($attr, $value)
    {
        if (property_exists(static::class, $attr)) {
            global $db;
            $className = get_called_class()::DBNAME;
            $st = $db->prepare("select data_type from information_schema.columns
                WHERE table_schema = 'film' AND 
                table_name = '" . $className . "' AND
                column_name = '" . $attr . "';");
            $st->execute();
            $row = $st->fetch();
            $attrtype = $row[0];
            if (!empty($attrtype)) {
                $class = new ReflectionClass(static::class);
                $idname = array_keys($class->getdefaultProperties())[0];
                $stringsql = "";
                if ($attrtype == "character varying") {
                    $value = strip_tags(preg_replace("#\'#", "''", $value));
                    $stringsql = "update " . $className . " set " . $attr . "='" . $value . "' where \"" . $idname . "\" = '" . $this->$idname . "'";
                } else {
                    $stringsql = "update " . $className . " set " . $attr . "=" . $value . " where \"" . $idname . "\" = '" . $this->$idname . "'";
                }
                $st = $db->prepare($stringsql);
                $st->execute();
            }
            $this->$attr = $value;
        }
    }

    public static function load($id, $s = null)
    {

        //Prérequis :
        //Le variables du constructeur/les propriétés de la classe doivent porters les mêmes noms que les champs dans la base
        //Le search_path doit être spécifié dans db.php
        //La variable correspondant à l'id de l'objet est initialiser en premier au sein de la classe.

        global $db;
        $class = new ReflectionClass(static::class);
        $idname = array_keys($class->getdefaultProperties())[0];
        $attributeList = array();
        foreach ($class->getMethod("__construct")->getParameters() as $var) {
            $attributeList[] = $var->name;
        }
        $class = static::class;
        $st = $db->prepare("select \"" . $idname . "\" from " . strtolower($class) . $s);
        $st->execute();
        $row = $st->fetch();
        $stringsql = "";
        if (gettype($row[0]) == "string") {
            $stringsql = "select * from " . strtolower($class) . $s . " where \"" . $idname . "\"='" . $id . "'";
        } else {
            $stringsql = "select * from " . strtolower($class) . $s . " where \"" . $idname . "\"=" . $id;
        }
        $st = $db->prepare($stringsql);
        $st->execute();
        $attrstring = "";
        foreach ($attributeList as $attr) {
            $attrstring .= ' $row["' . $attr . '"]';
            if (next($attributeList) == true) $attrstring .= ',';
        }
        $row = $st->fetch();
        if (!empty($attributeList)) {
            eval('$entity = new $class(' . $attrstring . ');');
            return isset($entity) ? $entity : null;
        } else {
            $entity = new $class();
            foreach ($row as $key => $value) {
                if (property_exists($class, $key)) {
                    $entity->$key = $value;
                }
            }
            return $entity;
        }
    }

    public static function all()
    {
        global $db;
        $class = new ReflectionClass(static::class);
        $attributeList = array();
        foreach ($class->getMethod("__construct")->getParameters() as $var) { //Construit une liste des attributs du constructeur
            $attributeList[] = $var->name;                                    //de la classe active
        }
        $class = static::class;
        $st = $db->prepare("select * from " . strtolower($class) . "s");
        $st->execute();
        $objectlist = array();
        $attrstring = "";
        foreach ($attributeList as $attr) {                       //Création d'une string contenant les elements du constructeur
            $attrstring .= ' $row["' . $attr . '"]';              //sous le format: $row["attribut1"], $row["attribut1"] ...
            if (next($attributeList) == true) $attrstring .= ','; //pour pouvoir récupérer les bonnes valeurs du fetch
        }
        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            if (!empty($attributeList)) { //Vérification que les attributs du constructeur existe
                eval('$entity = new $class(' . $attrstring . ');'); //Création d'une entité pour chaque row du tableau de résultat du fetch
                $objectlist[] = isset($entity) ? $entity : null; //Ajout (s'il existe) l'objet dans la liste
            } else { //Surcharge si constructeur vide
                $entity = new $class(); //Crée un objet vide
                foreach ($row as $key => $value) {          //Ajoute pour chaque colomne de la table du fetch
                    if (property_exists($class, $key)) {    //la valeur associé dans la propriété, si elle existe
                        $entity->$key = $value;
                    }
                }
                $objectlist[] = $entity; //Ajout de l'objet dans la liste
            }
        }
        return $objectlist; //Renvoi de la liste de tout les objets
    }
}
