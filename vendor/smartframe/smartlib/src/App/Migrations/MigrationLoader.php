<?php

namespace SmartFrameLib\App\Migrations;

use SmartFrameLib\Api\SmartFrameOptionProvider;
use SmartFrameLib\Api\SmartFrameOptionProviderFactory;
use SmartFrameLib\App\Migrations\VersionMigrator\ImagesIdFromMetaDataToSmartFrameTable;

if (!defined('ABSPATH')) exit;

class MigrationLoader
{

    const MIGRATION1 = 'smartframe-migration-1';
    const MIGRATION2 = 'smartframe-migration-2';
    const MIGRATION3 = 'smartframe-migration-3';
    const MIGRATION4 = 'smartframe-migration-4';

    public function __construct()
    {
        $config ['migration1'] = [
            'hash' => 'migartion-1-convet-add-table',
        ];
    }

    public function removeMigrationVariables()
    {
        delete_option(self::MIGRATION1);
        delete_option(self::MIGRATION2);
        delete_option(self::MIGRATION3);
        delete_option(self::MIGRATION4);
    }

    public function load()
    {
        if (get_option(self::MIGRATION1) === false) {
            $this->buildSqlSchema();
            (new SmartFrameOptionProvider('pixelrights_smartframe'))->setDefaultSettings();
            update_option(self::MIGRATION1, true);
        }

        if (get_option(self::MIGRATION2) === false) {
            (new ImagesIdFromMetaDataToSmartFrameTable())->migrate();
            update_option(self::MIGRATION2, true);
        }

        if (get_option(self::MIGRATION3) === false) {
            $this->alterTable();
            update_option(self::MIGRATION3, true);
        }

        if (get_option(self::MIGRATION4) === false) {
            $this->renameDatabaseFields();
            update_option(self::MIGRATION4, true);
        }
    }

    private function buildSqlSchema()
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        //* Create the Table
        $table_name = $wpdb->prefix . 'smartframe_image';
        $sql =
            "CREATE TABLE $table_name (
         id INTEGER NOT NULL AUTO_INCREMENT,
         image_id TEXT NOT NULL,
         thumb_url TEXT NOT NULL,
         original_url TEXT NOT NULL,
         path TEXT NOT NULL,
         file_name TEXT NOT NULL,
         width TEXT NOT NULL,
         height TEXT NOT NULL,
         size TEXT NOT NULL,
         hashed_id TEXT NOT NULL,
         api_key TEXT NOT NULL,
         PRIMARY KEY (id)
 ) $charset_collate;";

        dbDelta($sql);
    }

    private function alterTable()
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        //* Create the Table
        $table_name = $wpdb->prefix . 'smartframe_image';
        $sql = "ALTER TABLE $table_name ADD metadata varchar(999);";

        $wpdb->query($sql);
    }

    private function renameDatabaseFields()
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        //* Create the Table
        $table_name = $wpdb->prefix . 'options';


        $sql = "update $table_name
set option_name=REPLACE(option_name, 'pixelrights' , 'sfm')
WHERE option_name LIKE '%pixelrights%'";

        $wpdb->query($sql);

        $optionProvider = SmartFrameOptionProviderFactory::create();
        if ($optionProvider->getDisabledCssClasses() === 'on') {
            if (empty($optionProvider->getDisabledCssClassesList())) {
                $optionProvider->setDisabledCssClasses('all_images');
            } else {
                $optionProvider->setDisabledCssClasses('exclude_images');
            }
        }
    }

}