<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');

class ModelNewsComment {
    public function collection() {
        return SwimAdmin::db('news_comment');
    }

    public function delete($filter, $option=array()) {
        return $this->collection()->remove($filter, $option);
    }

}
