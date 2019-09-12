<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');

class ModelInterviewTag {
    public function collection() {
        return SwimAdmin::db('interview_tags');
    }

    public function find($filter=array(), $projection=array()) {
        return $this->collection()->find($filter, $projection);
    }

    public function findOne($filter) {
        return $this->collection()->findOne($filter); 
    }

    public function insert($data) {
        return $this->collection()->insert($data);
    }

    public function update($filter, $data) {
        $data = array('$set' => $data);
        return $this->collection()->update($filter, $data);
    }

    public function remove($filter) {
        return $this->collection()->remove($filter);
    }

}
