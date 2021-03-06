Contao 4 Translation Fields Bundle
=======================

Translation Fields is a library for Contao developers to get nice translation fields in the Contao Open Source CMS.
Every translation field gets a language flag and can be translated by changing the flag to another language. The translations will be saved in the table __tl_translation_fields__ and a key from this table will be stored in the field self.

Installation
------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require craffft/translation-fields-bundle "dev-master"
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new Craffft\TranslationFieldsBundle\CraffftTranslationFieldsBundle(),
        );

        // ...
    }

    // ...
}
```

Documentation
-------------

Input types
-----------

There are three input types that you can use in the back end.
- __TranslationTextField__ (the same as input type __text__)
- __TranslationTextArea__ (the same as input type __textarea__)
- __TranslationInputType__ (the same as input type __inputType__)

How to define a field in the DCA
--------------------------------

To use the translation fields, you have to do the following changes in your DCA code.
- Add an index to your field
- Change the input type
- Change the sql to int(10)
- Add a relation to your field

Each field uses different settings. You can see this in the following codes.

### Examples ###
#### Text Field ####
The original field:

```php
$GLOBALS['TL_DCA']['tl_mytable']['fields']['myfield'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_mytable']['myfield'],
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => array('maxlength'=>255),
    'sql'                     => "varchar(255) NOT NULL default ''"
);
````

The field after the changes:

```php
$GLOBALS['TL_DCA']['tl_mytable']['config']['sql']['keys']['myfield'] = 'index';
$GLOBALS['TL_DCA']['tl_mytable']['fields']['myfield'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_mytable']['myfield'],
    'exclude'                 => true,
    'inputType'               => 'TranslationTextField',
    'eval'                    => array('maxlength'=>255),
    'sql'                     => "int(10) unsigned NOT NULL default '0'",
    'relation'                => array('type'=>'hasOne', 'load'=>'lazy')
);
```

#### Textarea Field ####
The original field:

```php
$GLOBALS['TL_DCA']['tl_mytable']['fields']['myfield'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_mytable']['myfield'],
    'exclude'                 => true,
    'inputType'               => 'textarea',
    'eval'                    => array('rte'=>'tinyFlash', 'tl_class'=>'long'),
    'sql'                     => "text NULL"
);
```

The field after the changes:

```php
$GLOBALS['TL_DCA']['tl_mytable']['config']['sql']['keys']['myfield'] = 'index';
$GLOBALS['TL_DCA']['tl_mytable']['fields']['myfield'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_mytable']['myfield'],
    'exclude'                 => true,
    'inputType'               => 'TranslationTextArea',
    'eval'                    => array('rte'=>'tinyFlash', 'tl_class'=>'long'),
    'sql'                     => "int(10) unsigned NOT NULL default '0'",
    'relation'                => array('type'=>'hasOne', 'load'=>'lazy')
);
```

#### Input Unit Field ####
The original field:

```php
$GLOBALS['TL_DCA']['tl_mytable']['fields']['myfield'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_mytable']['myfield'],
    'exclude'                 => true,
    'inputType'               => 'inputUnit',
    'options'                 => array('h1', 'h2', 'h3', 'h4', 'h5', 'h6'),
    'eval'                    => array('maxlength'=>200, 'tl_class'=>'w50'),
    'sql'                     => "blob NULL"
);
```

The field after the changes:

```php
$GLOBALS['TL_DCA']['tl_mytable']['config']['sql']['keys']['myfield'] = 'index';
$GLOBALS['TL_DCA']['tl_mytable']['fields']['myfield'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_mytable']['myfield'],
    'exclude'                 => true,
    'inputType'               => 'TranslationInputUnit',
    'options'                 => array('h1', 'h2', 'h3', 'h4', 'h5', 'h6'),
    'eval'                    => array('maxlength'=>200, 'tl_class'=>'w50'),
    'sql'                     => "blob NULL",
    'relation'                => array('type'=>'hasOne', 'load'=>'lazy')
);
```

How to translate the field values
---------------------------------

To translate the key from your current field, you can use the following methods

### Translate value ###
Translates the field key to the translation value in the current language.

```php
$intId = '1485'; // Example value

/* @var $objTranslator Translator */
$objTranslator = \System::getContainer()->get('craffft.translation_fields.service.translator');

$strTranslated = $objTranslator->translateValue($intId);

echo $strTranslated; // Returns e.g. "Hi there!"
```

Optional you can add a force language to the translateValue method.

```php
$intId = '1485'; // Example value
$strForceLanguage = 'de';

/* @var $objTranslator Translator */
$objTranslator = \System::getContainer()->get('craffft.translation_fields.service.translator');

$strTranslated = $objTranslator->translateValue($intId, $strForceLanguage);

echo $strTranslated; // Returns e.g. "Hallo zusammen!"
```

### Translate DataContainer object ###
Translates all translation field values in the data container object to a translated value.

```php
$objDC->exampleValue = '1485'; // Example value

/* @var $objTranslator Translator */
$objTranslator = \System::getContainer()->get('craffft.translation_fields.service.translator');

$objDC = $objTranslator->translateDCObject($objDC);

echo $objDC->exampleValue; // Returns e.g. "Hi there!"
```

### Translate DCA ###
Translates all translation field values in the data container array to a translated value.

```php
$arrDC['exampleValue'] = '1485'; // Example value

/* @var $objTranslator Translator */
$objTranslator = \System::getContainer()->get('craffft.translation_fields.service.translator');

$arrDC = $objTranslator->translateDCArray($arrDC, $strTable);

echo $arrDC['exampleValue']; // Returns e.g. "Hi there!"
```

Runonce
-------

If you already have content in your application fields, you have to ensure that translation fields doesn't remove your content data. Therefore you have to create a runonce which inserts the current values into the __tl_translation_fields__ table and associate the key with the field.

You can do this like in the following code:

```php
class MyApplicationRunconce extends \Controller
{
    public function run()
    {
        // Code ...

        \Craffft\TranslationFieldsBundle\Util\Updater::convertTranslationField('tl_my_table_name', 'my_field_name');

        // Code ...
    }

    // Code ...
}
```

E.g. you can have a look at the runconce.php from my extension Photoalbums2:
https://github.com/Craffft/contao-photoalbums2/blob/master/config/runonce.php
