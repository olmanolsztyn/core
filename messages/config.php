<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

return [

    // string, required, root directory of all source files
    'sourcePath'   => __DIR__ . DIRECTORY_SEPARATOR . '..',

    // array, required, list of language codes that the extracted messages
    // should be translated to. For example, ['zh-CN', 'de'].
    'languages'    => ['ru-RU'],

    // string, the name of the function for translating messages.
    // Defaults to 'Yii::t'. This is used as a mark to find the messages to be
    // translated. You may use a string for single function name or an array for
    // multiple function names.
    'translator'   => 'Yii::t',

    // boolean, whether to sort messages by keys when merging new messages
    // with the existing ones. Defaults to false, which means the new (untranslated)
    // messages will be separated from the old (translated) ones.
    'sort'         => true,

    // boolean, whether to remove messages that no longer appear in the source code.
    // Defaults to false, which means these messages will NOT be removed.
    'removeUnused' => false,

    // boolean, whether to mark messages that no longer appear in the source code.
    // Defaults to true, which means each of these messages will be enclosed with a pair of '@@' marks.
    'markUnused'   => true,

    // array, list of patterns that specify which files (not directories) should be processed.
    // If empty or not set, all files will be processed.
    'only'   => ['*.php'],

    // array, list of patterns that specify which files/directories should NOT be processed.
    // If empty or not set, all files/directories will be processed.
    'except' => [
        '.idea',
        '.git',
        '.gitignore',
        '.gitkeep',
        '/messages',
        '/vendor',
        '/web',
        '/runtime',
    ],

    // 'php' output format is for saving messages to php files.
    'format' => 'php',

    // Root directory containing message translations.
    'messagePath' => __DIR__,

    // boolean, whether the message file should be overwritten with the merged messages
    'overwrite' => true,

    // Message categories to ignore
    'ignoreCategories' => [
        'yii',
    ],

];
