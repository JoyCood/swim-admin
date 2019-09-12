<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');

class ModelTechnicalComment {
    public function collection() {
        return SwimAdmin::db('technical_comment');
    }

    public function delete($filter, $option=array()) {
        return $this->collection()->remove($filter, $option);
    }


}
