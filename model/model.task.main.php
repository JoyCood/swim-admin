<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');

class ModelTaskMain
{
    public function collection() {
        return SwimAdmin::db('task');
    }    

    public function insert($data) {
        return $this->collection()->insert($data); 
    }

    public function findOne($filter, $projection=array()) {
        return $this->collection()->findOne($filter);
    }

    public function find($filter, $projection=array()) {
        return $this->collection()->find($filter, $projection);
    }

    public function findAndModify($filter, $update, $fields=array(), $options=array()) {
        return $this->collection()->findAndModify($filter, $update, $fields, $options);
    
    }

    public function update($filter, $data) {
        return $this->collection()->update($filter, $data);
    }
}
