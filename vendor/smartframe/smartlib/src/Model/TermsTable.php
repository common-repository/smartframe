<?php

namespace SmartFrameLib\Model;
 if ( ! defined( 'ABSPATH' ) ) exit;

class TermsTable {

    public function fetchUnusedTags() {
        global $wpdb;
        $sql = "SELECT terms.term_id, terms.name FROM
{$wpdb->terms} terms
INNER JOIN {$wpdb->term_taxonomy} taxo
ON terms.term_id=taxo.term_id
WHERE taxo.taxonomy = 'post_tag'
AND taxo.count=0";
        return $wpdb->get_results($sql);
    }

}
