<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 10.08.17
 * Time: 11:57
 */

namespace Niklas;


trait Entity
{
    public function __construct(array $data)
    {
        $this->load($data);
    }

    public function load(array $data) {
        foreach($data as $key => $value) {
            if($key === 'id') {
                continue;
            }
            if(property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}