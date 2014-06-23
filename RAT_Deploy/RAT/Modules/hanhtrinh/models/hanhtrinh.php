<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class HanhtrinhTModel  extends TModel
{
    function addHanhtrinh($data)
    {
        $data['id'];
        $data['title'];
        $data['hanhtrinh'];
        $this->_db->insert($data,'hanhtrinh');
        $this->_db->update($data,'hanhtrinh');
        $data = $this->_db->getDataBy('hanhtrinh',array('id'=>1,'title'=>'hanhtrinhcantho'));
                return $data;
    }
}