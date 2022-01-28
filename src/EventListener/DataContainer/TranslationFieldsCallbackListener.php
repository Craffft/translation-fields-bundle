<?php

/*
 * This file is part of the Translation Fields Bundle.
 *
 * (c) Daniel Kiesel <https://github.com/iCodr8>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Craffft\TranslationFieldsBundle\EventListener\DataContainer;

use Contao\Controller;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\Database;
use Contao\DataContainer;
use Contao\Model;
use TranslationFields\TranslationFieldsModel;

class TranslationFieldsCallbackListener
{
    /**
     * @Callback(table="tl_article", target="config.oncopy")
     * @Callback(table="tl_content", target="config.oncopy")
     * @Callback(table="tl_form", target="config.oncopy")
     * @Callback(table="tl_form_field", target="config.oncopy")
     * @Callback(table="tl_image_size", target="config.oncopy")
     * @Callback(table="tl_image_size_item", target="config.oncopy")
     * @Callback(table="tl_layout", target="config.oncopy")
     * @Callback(table="tl_member", target="config.oncopy")
     * @Callback(table="tl_member_group", target="config.oncopy")
     * @Callback(table="tl_module", target="config.oncopy")
     * @Callback(table="tl_page", target="config.oncopy")
     * @Callback(table="tl_templates", target="config.oncopy")
     * @Callback(table="tl_theme", target="config.oncopy")
     * @Callback(table="tl_user", target="config.oncopy")
     * @Callback(table="tl_user_group", target="config.oncopy")
     */
    public function onCopyCallback($intId, DataContainer $dc)
    {
        // If this is not the backend than return
        if (TL_MODE != 'BE') {
            return;
        }

        $strTable = $dc->table;
        $strModel = '\\'.Model::getClassFromTable($strTable);

        // Return if the class does not exist (#9 thanks to tsarma)
        if (!class_exists($strModel)) {
            return;
        }

        // Get object from model
        $objModel = $strModel::findByPk($intId);

        if ($objModel !== null) {
            $arrData = $objModel->row();

            if (is_array($arrData) && count($arrData) > 0) {
                // Load current data container
                Controller::loadDataContainer($strTable);

                foreach ($arrData as $strField => $varValue) {
                    switch ($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['inputType']) {
                        case 'TranslationInputUnit':
                        case 'TranslationTextArea':
                        case 'TranslationTextField':
                            // Get translation values
                            $objTranslation = TranslationFieldsModel::findByFid($varValue);

                            if ($objTranslation !== null) {
                                // Get next fid
                                $intFid = TranslationFieldsModel::getNextFid();

                                // Set copy fid by field
                                $objModel->$strField = $intFid;

                                while ($objTranslation->next()) {
                                    // Generate new translation fields object to copy the current
                                    $objCopy = clone $objTranslation->current();
                                    $objCopy->fid = $intFid;
                                    $objCopy->save();
                                }
                            }
                            break;
                    }
                }
            }

            // Save model object
            $objModel->save();
        }
    }

    /**
     * @Callback(table="tl_article", target="config.ondelete")
     * @Callback(table="tl_content", target="config.ondelete")
     * @Callback(table="tl_form", target="config.ondelete")
     * @Callback(table="tl_form_field", target="config.ondelete")
     * @Callback(table="tl_image_size", target="config.ondelete")
     * @Callback(table="tl_image_size_item", target="config.ondelete")
     * @Callback(table="tl_layout", target="config.ondelete")
     * @Callback(table="tl_member", target="config.ondelete")
     * @Callback(table="tl_member_group", target="config.ondelete")
     * @Callback(table="tl_module", target="config.ondelete")
     * @Callback(table="tl_page", target="config.ondelete")
     * @Callback(table="tl_templates", target="config.ondelete")
     * @Callback(table="tl_theme", target="config.ondelete")
     * @Callback(table="tl_user", target="config.ondelete")
     * @Callback(table="tl_user_group", target="config.ondelete")
     */
    public function onDeleteCallback($dc)
    {
        // If this is not the backend than return
        if (TL_MODE != 'BE') {
            return;
        }

        // Check if there is an active record
        if ($dc instanceof DataContainer && $dc->activeRecord) {
            $intId = $dc->activeRecord->id;

            $strTable = $dc->table;
            $strModel = '\\'.Model::getClassFromTable($strTable);

            // Return if the class does not exist (#9 thanks to tsarma)
            if (!class_exists($strModel)) {
                return;
            }

            // Get object from model
            $objModel = $strModel::findByPk($intId);

            if ($objModel !== null) {
                $arrData = $objModel->row();

                if (is_array($arrData) && count($arrData) > 0) {
                    // Load current data container
                    Controller::loadDataContainer($strTable);

                    // Get tl_undo data
                    $objUndo = Database::getInstance()->prepare(
                        "SELECT * FROM tl_undo WHERE fromTable=? ORDER BY id DESC"
                    )->limit(1)->execute($dc->table);
                    $arrSet = $objUndo->row();

                    // Deserialize tl_undo data
                    $arrSet['data'] = deserialize($arrSet['data']);

                    foreach ($arrData as $strField => $varValue) {
                        $strInputType = $GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['inputType'];

                        switch ($strInputType) {
                            case 'TranslationInputUnit':
                            case 'TranslationTextArea':
                            case 'TranslationTextField':
                                $intFid = $varValue;

                                if ($strInputType == 'TranslationInputUnit') {
                                    $arrDeserialized = deserialize($varValue);
                                    $intFid = $arrDeserialized['value'];
                                }

                                // Get translation values
                                $objTranslation = TranslationFieldsModel::findByFid($intFid);

                                if ($objTranslation !== null) {
                                    while ($objTranslation->next()) {
                                        $t = TranslationFieldsModel::getTable();

                                        // Add cross table record to undo data
                                        $arrSet['data'][$t][] = $objTranslation->row();

                                        // Delete translation
                                        $objTranslation->delete();
                                    }
                                }
                                break;
                        }
                    }

                    // Serialize tl_undo data
                    $arrSet['data'] = serialize($arrSet['data']);

                    // Update tl_undo
                    Database::getInstance()->prepare("UPDATE tl_undo %s WHERE id=?")->set($arrSet)->execute(
                        $objUndo->id
                    );
                }
            }
        }
    }
}
