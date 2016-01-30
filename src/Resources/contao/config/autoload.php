<?php

/*
 * This file is part of the Translation Fields Bundle.
 *
 * (c) Daniel Kiesel <https://github.com/iCodr8>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
    'TranslationFields',
));

/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
    // Classes
    'TranslationFields\Purge'                          => 'vendor/craffft/translation-fields-bundle/src/Resources/contao/classes/Purge.php',
    'TranslationFields\TranslationController'          => 'vendor/craffft/translation-fields-bundle/src/Resources/contao/classes/TranslationController.php',
    'TranslationFields\TranslationFields'              => 'vendor/craffft/translation-fields-bundle/src/Resources/contao/classes/TranslationFields.php',
    'TranslationFields\TranslationFieldsBackendHelper' => 'vendor/craffft/translation-fields-bundle/src/Resources/contao/classes/TranslationFieldsBackendHelper.php',
    'TranslationFields\TranslationFieldsWidgetHelper'  => 'vendor/craffft/translation-fields-bundle/src/Resources/contao/classes/TranslationFieldsWidgetHelper.php',
    'TranslationFields\Updater'                        => 'vendor/craffft/translation-fields-bundle/src/Resources/contao/classes/Updater.php',

    // Driver
    'TranslationFields\DC_Table'                       => 'vendor/craffft/translation-fields-bundle/src/Resources/contao/driver/DC_Table.php',

    // Models
    'TranslationFields\TranslationFieldsModel'         => 'vendor/craffft/translation-fields-bundle/src/Resources/contao/models/TranslationFieldsModel.php',
    'TranslationFields\TranslationFieldsPageModel'     => 'vendor/craffft/translation-fields-bundle/src/Resources/contao/models/TranslationFieldsPageModel.php',

    // Widgets
    'TranslationFields\TranslationInputUnit'           => 'vendor/craffft/translation-fields-bundle/src/Resources/contao/widgets/TranslationInputUnit.php',
    'TranslationFields\TranslationTextArea'            => 'vendor/craffft/translation-fields-bundle/src/Resources/contao/widgets/TranslationTextArea.php',
    'TranslationFields\TranslationTextField'           => 'vendor/craffft/translation-fields-bundle/src/Resources/contao/widgets/TranslationTextField.php',
));
