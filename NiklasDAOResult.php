<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 25.07.17
 * Time: 09:57
 */

class NiklasDAOResult
{
    /**
     * @var mysqli_result
     */
    private $mResult;

    public function __construct(mysqli_result $result)
    {
        $this->mResult = $result;
    }

    public function numRows() {
       return $this->mResult->num_rows;
    }

    public function asArray($prototype = null) {
            if ($prototype === null) {
                while ($row[] = mysqli_fetch_assoc($this->mResult)) ;
                return $row;
            } elseif(is_object($prototype)) {
                while ($row = mysqli_fetch_assoc($this->mResult)) {
                    $object = clone $prototype;

                    foreach ($row as $key => $value) {
                        $object->$key = $value;
                    }

                    $objAr[] = $object;
                }
                return $objAr;

            } else {
            throw new Exception("prototype must be a object:");

        }

    }

    public function one($prototyp) {
        if(is_object($prototyp)) {
            if($this->numRows() === 1) {
                return $row = mysqli_fetch_assoc($this->mResult);
            } else {
                return false;
            }
        } else {
            throw new Exception("prototype must be a object:");
        }
    }

    public function first($prototyp) {
        if(is_object($prototyp)) {
            if($this->numRows() > 0) {
                return $row = mysqli_fetch_assoc($this->mResult);
            } else {
                return false;
            }
        } else {
            throw new Exception("prototype must be a object:");
        }
    }

    public function each(callable $function) {
        $ref = new ReflectionFunction($function);
        if($ref->getParameters()[0]->isArray()) {
            while ($row = mysqli_fetch_assoc($this->mResult)) {
                $function($row);

            }

        } elseif ($ref->getParameters()[0]->getClass() !== null) {
            $className = $ref->getParameters()[0]->getClass()->name;
            while ($row = mysqli_fetch_assoc($this->mResult)) {
                $object = new $className();

                foreach($row as $key => $value) {
                    $object->$key = $value;
                }

                $function($object);
            }

        }

    }

}