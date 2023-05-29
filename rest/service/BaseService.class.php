<?php
abstract class BaseService{
    protected $dao;
    public function __construct($dao){
        $this->dao = $dao;
    }

    public function add_element($entity){
        return $this->dao->add($entity);
    }
}
?>
