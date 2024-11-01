<?php

namespace SmartFrameLib\MetaBox;
 if ( ! defined( 'ABSPATH' ) ) exit;

class SimpleMetaBox {

    const META_PREFIX = '___';

    /*
     * Add form fields to save and create view
     * Form fields will be automaticly sended to view with same name you gave 
     * them in form.
     */

    private $formFields = array(
    );
    //add template for create action 
    protected $template = '';

    public function create($post) {
        $vars = $this->collectPostMeta($post->ID);
        echo \SmartFrameLib\View\ViewRenderFactory::create(
                        PLUGIN_RESOURCE_PATH . $this->template, $vars)
                ->render();
    }

    /*
     * int $postId
     */

    public function save($postId) {
        $this->iteratePostFields(function ($field, $postData) use ($postId) {
            update_post_meta($postId, $field, strip_tags($postData));
        });
    }

    /*
     * function() callback - create callback that gets form $field and post data
     * to equal vield
     */

    protected function iteratePostFields(callable $callback) {

        foreach ($this->formFields as $field) {
            if (filter_input(INPUT_POST, $field)) {
                $callback($field, filter_input(INPUT_POST, $field));
            }
        }
    }

    /*
     * int $postID
     *  colellect all custom meta data connected with current post and return
     * 
     * retun array(); of post custom metadata 
     */

    protected function collectPostMeta($postId) {
        $postMetaVars = array();
        foreach ($this->formFields as $field) {
            $postMetaVars[$field] = get_post_meta($postId, $field, true);
        }
        return $postMetaVars;
    }

}
