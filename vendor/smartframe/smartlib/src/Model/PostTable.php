<?php

namespace SmartFrameLib\Model;
 if ( ! defined( 'ABSPATH' ) ) exit;

class PostTable {

    public function insertPost(array $data) {
        global $user_ID;
        $new_post = array(
            'post_title' => 'test_post',
            'post_content' => 'test madafaka ',
            'post_status' => 'publish',
            'post_date' => date('Y-m-d H:i:s'),
            'post_author' => $user_ID,
            'post_type' => 'post',
            'tags_input' => array('tag1,tag2'),
            'post_category' => array(3)
        );
        return $post_id = wp_insert_post($new_post);
    }

}
