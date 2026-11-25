<?php
namespace AdmidioPlugin\LanguageSwitcher\Presenter;

use Admidio\Infrastructure\Exception;
use Admidio\Infrastructure\Language;
use Admidio\Infrastructure\Plugins\PluginRegistry;
use Admidio\Infrastructure\Utils\SecurityUtils;
use Admidio\UI\Presenter\FormPresenter;
use Admidio\UI\Presenter\PreferencesPresenter;
use AdmidioPlugin\LanguageSwitcher\LanguageSwitcher;

/**
 ***********************************************************************************************
 * The preferences panel of the plugin. The manifest cannot offer the languages as a list to choose
 * from, so the panel is built here.
 *
 * @copyright Reinhold Kainhofer
 * @see https://www.admidio.org/
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0 only
 ***********************************************************************************************
 */
final class LanguageSwitcherPreferencesPresenter
{
    private const TEMPLATE = 'preferences.plugin.language-switcher.tpl';

    private function __construct()
    {
    }

    /**
     * @throws Exception|\Smarty\Exception
     */
    public static function createForm(PreferencesPresenter $page): string
    {
        global $gL10n, $gCurrentSession;

        $plugin = PluginRegistry::requireEnabled(LanguageSwitcher::ID);
        $settings = $plugin->settings;
        $values = $plugin->getSettingValues();

        $form = new FormPresenter(
            'adm_preferences_form_language_switcher',
            self::TEMPLATE,
            SecurityUtils::encodeUrl(ADMIDIO_URL . FOLDER_MODULES . '/preferences.php', array('mode' => 'save', 'panel' => 'language_switcher')),
            null,
            array('class' => 'form-preferences')
        );

        $languages = $gL10n->getAvailableLanguages();
        asort($languages, SORT_NATURAL | SORT_FLAG_CASE);
        $form->addSelectBox(
            'language_switcher_languages',
            Language::translateIfTranslationStrId($settings['language_switcher_languages']['label']),
            $languages,
            array(
                'defaultValue' => array_values((array)$values['language_switcher_languages']),
                'showContextDependentFirstEntry' => false,
                'helpTextId' => $settings['language_switcher_languages']['description'],
                'multiselect' => true,
                'maximumSelectionNumber' => count($languages)
            )
        );
        foreach (array('language_switcher_flags', 'language_switcher_visitors', 'language_switcher_remember') as $name) {
            $form->addCheckbox(
                $name,
                Language::translateIfTranslationStrId($settings[$name]['label']),
                (bool)$values[$name],
                array('helpTextId' => $settings[$name]['description'])
            );
        }
        $form->addSubmitButton('adm_button_save_language_switcher', $gL10n->get('SYS_SAVE'), array('icon' => 'bi-check-lg', 'class' => 'offset-sm-3'));

        $form->addToSmarty($page->getSmartyTemplate());
        $gCurrentSession->addFormObject($form);

        return $plugin->renderTemplate($page, self::TEMPLATE);
    }
}
