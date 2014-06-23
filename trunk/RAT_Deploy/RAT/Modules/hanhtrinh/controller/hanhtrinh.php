<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class HanhtrinhTController extends TController
{
    function acctionAdd()
    {
        $title = TRequest::getVar('title');
        $lichtrinh = TRequest::getVar('lichtrinh');
        $model = $this->getModel();
        $data = $model->addHanhtrinh();
        $this->setView(array('data'=>$data));
    }
}