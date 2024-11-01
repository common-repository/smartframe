<?php

namespace SmartFrameLib\View;
if (!defined('ABSPATH')) exit;

class FormFieldsRenderer
{
    /**
     * @param $field
     * @param $post
     * @return bool
     */
    public function canBeDisplayed($field, $post)
    {
        if (isset($field['show_for']) && 1 === preg_match("/" . implode('|', $field['show_for']) . "/", $post->post_mime_type)) {
            return true;
        }

        return false;
    }

    public function renderInOneRow($fields)
    {
        $template = SMARTFRAME_PLUGIN_DIR . '/admin/partials/form/standard-wordpress-template-edit-attachment.php';
        $renderFields = '';
        foreach ($fields as $field) {
            $renderFields .= ViewRenderFactory::create($template, ['name' => $field['label'], 'input' => $field['html']])->render();
        }
        return $renderFields;
    }

    public function render(&$field, $post)
    {
        $meta = get_post_meta($post->ID, $field['option_name'], true);
        switch ($field['input']) {
            case 'text':
                $field['input'] = 'html';
                if (isset($field['disable_input']) && 0 !== preg_match("/" . implode('|', $field['disable_input']) . "/", $post->post_mime_type)) {
                    $disabled = 'disabled="disabled"';
                }
                $field['html'] = '<input ' . $disabled . ' maxlength="1000" type="text" class="' . $field['class'] . '" value="' . $meta . '" name="attachments[' . $post->ID . '][' . $field['option_name'] . ']">';
                break;

            case 'textarea':
                $field['input'] = 'textarea';
                break;

            case 'select':

                // Select type doesn't exist, so we will create the html manually
                // For this, we have to set the input type to 'html'
                $field['input'] = 'html';
                if (isset($field['disable_input']) && 0 !== preg_match("/" . implode('|', $field['disable_input']) . "/", $post->post_mime_type)) {
                    $disabled = 'disabled="disabled"';
                }

                // Create the select element with the right name (matches the one that wordpress creates for custom fields)
                $html = '<select ' . $disabled . ' class="' . $field['class'] . '" style=\'text-overflow: ellipsis;width:100%;\' name="attachments[' . $post->ID . '][' . $field['option_name'] . ']">';

                // If options array is passed

                if (isset($field['break_line_options'])) {
                    // Browse and add the options
                    foreach ($field['break_line_options'] as $k => $v) {
                        if ($meta == $k)
                            $selected = ' selected="selected"';
                        else
                            $selected = '';

                        $html .= '<option' . $selected . ' value="' . $k . '">' . $v . '</option>';
                    }
                    $html .= '<option value="" disabled="disabled">─────</option>';
                }
                if (isset($field['options'])) {
                    // Browse and add the options
                    foreach ($field['options'] as $k => $v) {
                        if ($meta == $k)
                            $selected = ' selected="selected"';
                        else
                            $selected = '';

                        $html .= '<option' . $selected . ' value="' . $k . '">' . $v . '</option>';
                    }
                }

                $html .= '</select>';

                // Set the html content
                $field['html'] = $html;

                break;

            case 'checkbox':

                // Checkbox type doesn't exist either
                $field['input'] = 'html';
                $disabled = '';
                if (isset($field['disable_input_bool']) && $field['disable_input_bool'] === true) {
                    $disabled = 'disabled="disabled"';
                }
                if (isset($field['disable_input']) && 0 !== preg_match("/" . implode('|', $field['disable_input']) . "/", $post->post_mime_type)) {
                    $disabled = 'disabled="disabled"';
                }
                // Set the checkbox checked or not
                if ($meta == 'yes')
                    $checked = ' checked="checked"';
                else
                    $checked = '';

                $html = '<input ' . $disabled . ' class="' . $field['class'] . '" value ="yes" ' . $checked . ' type="checkbox" name="attachments[' . $post->ID . '][' . $field['option_name'] . ']" id="attachments-' . $post->ID . '-' . $field['option_name'] . '" />';

                $field['html'] = $html;

                break;

            case 'radio':

                // radio type doesn't exist either
                $field['input'] = 'html';

                $html = '';

                if (!empty($field['options'])) {
                    $i = 0;

                    foreach ($field['options'] as $k => $v) {
                        if ($meta == $k)
                            $checked = ' checked="checked"';
                        else
                            $checked = '';

                        $html .= '<input' . $checked . ' value="' . $k . '" type="radio" name="attachments[' . $post->ID . '][' . $field['option_name'] . ']" id="' . sanitize_key($field['option_name'] . '_' . $post->ID . '_' . $i) . '" /> <label for="' . sanitize_key($field['option_name'] . '_' . $post->ID . '_' . $i) . '">' . $v . '</label><br />';
                        $i++;
                    }
                }

                $field['html'] = $html;

                break;
        }

        return $field;
    }
}