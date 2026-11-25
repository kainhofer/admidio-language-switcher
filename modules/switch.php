<?php
/**
 ***********************************************************************************************
 * Language switcher - change the language of the session and return to the page.
 *
 * Parameters:
 * language : the code of a language the switcher offers, or "default"
 *
 * @copyright Reinhold Kainhofer
 * @see https://www.admidio.org/
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0 only
 ***********************************************************************************************
 */

use Admidio\Infrastructure\Exception;
use AdmidioPlugin\LanguageSwitcher\LanguageSwitcher;

try {
    require_once(__DIR__ . '/../../../system/common.php');

    if (!$gValidLogin && !$gSettingsManager->getBool('language_switcher_visitors')) {
        throw new Exception('SYS_NO_RIGHTS');
    }

    $getLanguage = admFuncVariableIsValid($_GET, 'language', 'string', array('requireValue' => true));
    if (!LanguageSwitcher::switchTo($getLanguage)) {
        throw new Exception('SYS_INVALID_PAGE_VIEW');
    }

    // back to the page the menu was used on, if it is a page of this installation
    $referer = (string)($_SERVER['HTTP_REFERER'] ?? '');
    admRedirect(str_starts_with($referer, ADMIDIO_URL . '/') ? $referer : ADMIDIO_URL . '/');
} catch (Throwable $e) {
    handleException($e);
}
