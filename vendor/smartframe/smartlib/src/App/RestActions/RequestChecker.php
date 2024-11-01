<?php

namespace SmartFrameLib\App\RestActions;

class RequestChecker
{
    public function is_rest()
    {
        $prefix = rest_get_url_prefix();
        if (defined('REST_REQUEST') && REST_REQUEST // (#1)
            || isset($_GET['rest_route']) // (#2)
            && strpos(trim($_GET['rest_route'], '\\/'), $prefix, 0) === 0) {
            return true;
        }

        if (isset($_GET['fl_builder'])) { //Divi Builder
            return true;
        }

        if (isset($_GET['et_fb'])) { //Divi Builder
            return true;
        }

//        if (isset($_GET['rest_route']) && is_admin()) {
//            return true;
//        }

        // (#3)
        $rest_url = wp_parse_url(site_url($prefix));
        $current_url = wp_parse_url(add_query_arg([]));
        return strpos($current_url['path'], $rest_url['path'], 0) === 0;
    }

}