<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 24.07.17
 * Time: 10:29
 */
namespace Niklas;

class DAO
{
    /**
     * @var \mysqli
     */
    private $mConn;

    public function __construct($host, $user, $pass, $db)
    {
        $this->mConn = new \mysqli($host, $user, $pass, $db);
        if($this->mConn->connect_error) {
            throw new \Exception("Connection failed: {$this->mConn->connect_error}");
        }

    }

    public function store($object) {
        if($object->id === null) {
            $this->_insert($object);
        } else {
            $this->_update($object);
        }
    }

    private function _insert($object) {
        $column = array();
        $values = array();
        $query = "INSERT INTO ";
        $tableName = get_class($object);
        $query .= $tableName;
        foreach ($object as $key => $value) {
            if ( ! preg_match("/^[a-zA-Z0-9_-]+$/", $key)) {
                throw new \Exception("Security Exception: invalid key '$key'");
            }
            $column[] = "`$key`";
            if($value === null) {
                $values[] = "NULL";
                continue;
            }
            $values[] = "'{$this->mConn->real_escape_string($value)}'";

        }
        $columnStr = implode(", ", $column);
        $valuesStr = implode(", ", $values);

        $query .= " ($columnStr) VALUES ($valuesStr);";

        if ($this->mConn->query($query) == FALSE) {
            throw new \Exception("Query failed: ($query) {$this->mConn->error}");
        }
        $object->id = $this->mConn->insert_id;
    }

    public function delete($object) {
        if($object->id === null) {
            throw new \Exception("id can not be null");
        }
        $tableName = get_class($object);
        $query = "DELETE FROM " . $tableName . " WHERE ";
        $id = $object->id;
        $query .= "id=" . $id . ";";
        if ($this->mConn->query($query) == FALSE) {
            throw new \Exception("Query failed: ($query) {$this->mConn->error}");
        }

    }

    private function _update($object) {
        if($object->id === null) {
            throw new \Exception("id can not be null");
        }
        $id = $object->id;
        $tableName = get_class($object);


        $nameStr = explode("\\", $tableName);
        if($nameStr !== "") {
            $tableName = end($nameStr);
        }
        $newName = lcfirst($tableName);
        $tableName = $newName;

        $query = "UPDATE " . $tableName . " SET ";
        $param = array();
        foreach ($object as $key=>$value) {
            if ( ! preg_match("/^[a-zA-Z0-9_-]+$/", $key)) {
                throw new \Exception("Security Exception: invalid key '$key'");
            }
            $param[] = "`$key` = '{$this->mConn->real_escape_string($value)}'";
        }
        $paramStr = implode(", ", $param);
        $query .= $paramStr . " WHERE id = " . $id;
        if ($this->mConn->query($query) == FALSE) {
            throw new \Exception("Query failed: ($query) {$this->mConn->error}");
        }

    }

    public function select($tableName, $column, $value) {
        $query = "SELECT * FROM " . $tableName . " WHERE " . $column . "=" . "'{$this->mConn->real_escape_string($value)}'" . ";";
        if (($result = $this->mConn->query($query)) === FALSE) {
            throw new \Exception("Query failed: ($query) {$this->mConn->error}");
        }
        $row = mysqli_fetch_array($result);
        return $row;
    }

    public function load($object, array $restriction) {
        $query = "SELECT * FROM " . get_class($object);
        $restrictions = [];
        foreach ($restriction as $key => $value) {
            if ( ! preg_match("/^[a-zA-Z0-9_-]+$/", $key)) {
                throw new \Exception("Security Exception: invalid key '$key'");
            }
            $restrictions[] = "`$key` = '{$this->mConn->real_escape_string($value)}'";
        }
        if (count($restrictions) > 0) {
            $query .= " WHERE " . implode(" AND ", $restrictions);
        }

        if (($result = $this->mConn->query($query)) === FALSE) {
            throw new \Exception("Query failed: ($query) {$this->mConn->error}");
        }
        if ($result->num_rows === 0) {
            throw new \Exception("No matching dataset");
        }
        $row = $result->fetch_assoc();
        foreach ($object as $key => $value) {
            if ( ! preg_match("/^[a-zA-Z0-9_-]+$/", $key)) {
                throw new \Exception("Security Exception: invalid key '$key'");
            }
            $object->$key = $row[$key];
        }

    }

    public function query($query, array $values) {
        $finalQuery = "";
        $querys = explode("?", $query);
        for ($i = 0; $i < count($querys); $i++) {
           if ($i < count($querys)-1) {
               $finalQuery .= $querys[$i] . "'{$this->mConn->real_escape_string($values[$i])}'";
           } else {
               $finalQuery .= $querys[$i];
           }
        }
        $finalQuery .= ";";

        if (($result = $this->mConn->query($finalQuery)) === FALSE) {
            throw new \Exception("Query failed: ($query) {$this->mConn->error}");
        }

        return new DAOResult($result);
    }

}