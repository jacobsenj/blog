<?php

/*
 * This file is part of the package t3g/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use T3G\AgencyPack\Blog\Constants;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

if (!defined('TYPO3')) {
    die('Access denied.');
}

// Add new page type:
\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\DataHandling\PageDoktypeRegistry::class)->add(Constants::DOKTYPE_BLOG_POST, [
    'type' => 'web',
    'allowedTables' => '*',
]);
\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\DataHandling\PageDoktypeRegistry::class)->add(Constants::DOKTYPE_BLOG_PAGE, [
    'type' => 'web',
    'allowedTables' => '*',
]);

// Allow backend users to drag and drop the new page type:
ExtensionManagementUtility::addUserTSConfig('
    options.pageTree.doktypesToShowInNewPageDragArea := addToList(' . Constants::DOKTYPE_BLOG_POST . ')
');
