<?php

/*
 * This file is part of the Translation Fields Bundle.
 *
 * (c) Daniel Kiesel <https://github.com/iCodr8>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Craffft\TranslationFieldsBundle\Util;

use Contao\Database;
use Craffft\TranslationFieldsBundle\Service\Languages;
use TranslationFields\TranslationFieldsWidgetHelper;

class Updater
{
    /**
     * @param $table
     * @param $field
     */
    public static function convertTranslationField($table, $field)
    {
        $backup = $field . '_backup';
        $objDatabase = Database::getInstance();

        /* @var $objLanguages Languages */
        $objLanguages = \System::getContainer()->get('craffft.translation_fields.service.languages');

        // Backup the original column and then change the column type
        if (!$objDatabase->fieldExists($backup, $table, true)) {
            $objDatabase->query("ALTER TABLE `$table` ADD `$backup` text NULL");
            $objDatabase->query("UPDATE `$table` SET `$backup`=`$field`");
            $objDatabase->query("ALTER TABLE `$table` CHANGE `$field` `$field` int(10) unsigned NOT NULL default '0'");
            $objDatabase->query("UPDATE `$table` SET `$field`='0'");
        }

        $objRow = $objDatabase->query("SELECT id, $backup FROM $table WHERE $backup!=''");

        while ($objRow->next()) {
            if (is_numeric($objRow->$backup)) {
                $intFid = $objRow->$backup;
            } else {
                if (strlen($objRow->$backup) > 0) {
                    $intFid = TranslationFieldsWidgetHelper::saveValuesAndReturnFid(
                        $objLanguages->getLanguagesWithValue($objRow->$backup)
                    );
                } else {
                    $intFid = 0;
                }
            }

            $objDatabase
                ->prepare("UPDATE $table SET $field=? WHERE id=?")
                ->execute($intFid, $objRow->id);
        }
    }
}
