<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');

class ModelFriendMain {
    public function collection() {
        return SwimAdmin::db('friend'); 
    }

    public function delete($filter, $options= array()) {
        return $this->collection()->remove($filter, $options);
    }
}
