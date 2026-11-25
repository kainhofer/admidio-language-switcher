<?php
/**
 ***********************************************************************************************
 * Language switcher - entry file.
 *
 * A menu in the header bar with which every user chooses the language of Admidio for themselves,
 * independent of the language of the organization.
 *
 * @copyright Reinhold Kainhofer
 * @see https://www.admidio.org/
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0 only
 ***********************************************************************************************
 */

use AdmidioPlugin\LanguageSwitcher\LanguageSwitcher;

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    exit('This page may not be called directly!');
}

LanguageSwitcher::register();
