<?php
namespace AdmidioPlugin\LanguageSwitcher;

use Admidio\Hooks\Hooks;
use Admidio\Infrastructure\Language;
use Admidio\Infrastructure\Plugins\PluginPanel;
use Admidio\Infrastructure\Plugins\PluginRegistry;
use Admidio\Infrastructure\Utils\SecurityUtils;
use Admidio\Session\Entity\Session;
use Admidio\UI\Presenter\PagePresenter;
use AdmidioPlugin\LanguageSwitcher\Presenter\LanguageSwitcherPreferencesPresenter;

/**
 ***********************************************************************************************
 * The language switcher: the menu in the header bar, the choice of the language and the cookie that
 * remembers it.
 *
 * The chosen language applies to the session of the user only; the language of the organization
 * and of every other user stays as it is. With the setting "remember" a cookie keeps the choice
 * for the next visit. "Default" returns to the language Admidio would choose by itself: the
 * language of the browser if Admidio has it, otherwise that of the organization.
 *
 * The menu uses the filter page_header_items of the core; a core without it gets the menu put into
 * the header bar in the browser.
 *
 * @copyright Reinhold Kainhofer
 * @see https://www.admidio.org/
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0 only
 ***********************************************************************************************
 */
final class LanguageSwitcher
{
    public const ID = 'language-switcher';
    public const DEFAULT = 'default';
    private const CORE_HOOKS = 'Admidio\\UI\\Component\\PageExtensions';

    /**
     * The flag of each language Admidio supports: language code => country code of the file in
     * assets/flags (from the flag-icons project, MIT license). A language is not a country - English
     * gets the Union Jack, Tamil the flag of India - so a language without a fitting flag keeps the icon.
     */
    public const FLAGS = array(
        'bg' => 'bg', 'cs' => 'cz', 'da' => 'dk', 'de' => 'de', 'de-DE' => 'de', 'el' => 'gr', 'en' => 'gb',
        'es' => 'es', 'et' => 'ee', 'fi' => 'fi', 'fr' => 'fr', 'hu' => 'hu', 'id' => 'id', 'it' => 'it',
        'nb' => 'no', 'nl' => 'nl', 'pl' => 'pl', 'pt-BR' => 'br', 'pt-PT' => 'pt', 'ro' => 'ro', 'ru' => 'ru',
        'sv' => 'se', 'ta' => 'in', 'th' => 'th', 'uk' => 'ua', 'zh' => 'cn'
    );

    private function __construct()
    {
    }

    public static function register(): void
    {
        $plugin = PluginRegistry::get(self::ID);
        if ($plugin === null) {
            return;
        }

        self::applyRememberedLanguage();

        Hooks::addFilter(PluginPanel::HOOK, static function (array $panels) use ($plugin): array {
            global $gL10n;

            $panels[] = array(
                'id' => PluginPanel::normalizeId($plugin->id),
                'title' => $gL10n->get($plugin->name),
                'icon' => $plugin->icon,
                'create' => array(LanguageSwitcherPreferencesPresenter::class, 'createForm')
            );
            return $panels;
        }, PluginPanel::DEFAULT_SEQUENCE, 1, self::ID . '-panel');

        if (class_exists(self::CORE_HOOKS)) {
            Hooks::addFilter('page_header_items', array(self::class, 'addHeaderItem'), Hooks::DEFAULT_PRIORITY, 1, self::ID);
        } else {
            Hooks::addAction('page_before_render', array(self::class, 'injectMenu'), Hooks::DEFAULT_PRIORITY, 1, self::ID);
        }
    }

    /**
     * The languages the menu offers: code => name. Those the administrator chose, otherwise all.
     * @return array<string,string>
     */
    public static function getLanguages(): array
    {
        global $gL10n, $gSettingsManager;

        $available = $gL10n->getAvailableLanguages();
        $chosen = array_filter(explode(',', $gSettingsManager->getString('language_switcher_languages')));
        $languages = $chosen === array() ? $available : array_intersect_key($available, array_flip($chosen));
        asort($languages, SORT_NATURAL | SORT_FLAG_CASE);
        return $languages;
    }

    /**
     * Whether the menu is shown to the current user.
     */
    public static function isShown(): bool
    {
        global $gValidLogin, $gSettingsManager, $gLayoutReduced;

        return empty($gLayoutReduced)
            && ($gValidLogin || $gSettingsManager->getBool('language_switcher_visitors'))
            && count(self::getLanguages()) > 1;
    }

    /**
     * The language Admidio would use without the switcher.
     */
    public static function getDefaultLanguage(): string
    {
        global $gSettingsManager;

        return (new Language($gSettingsManager->getString('system_language'), true))->getLanguage();
    }

    /**
     * Change the language of the session. "default" returns to the language Admidio would choose.
     * @return bool Whether the language was valid.
     */
    public static function switchTo(string $language): bool
    {
        global $gL10n, $gSettingsManager;

        if ($language === self::DEFAULT) {
            $gL10n->setLanguage(self::getDefaultLanguage());
            Session::setCookie(self::getCookieName(), '', time() - 3600);
            return true;
        }
        if (!array_key_exists($language, self::getLanguages()) || !$gL10n->setLanguage($language)) {
            return false;
        }
        if ($gSettingsManager->getBool('language_switcher_remember')) {
            Session::setCookie(self::getCookieName(), $language, time() + 365 * 86400);
        }
        return true;
    }

    /**
     * The URL of the flag of a language, or an empty string if flags are switched off or there is none.
     */
    public static function getFlagUrl(string $language): string
    {
        global $gSettingsManager;

        // the setting has no row before the plugin update that added it has run
        if (!isset(self::FLAGS[$language]) || !$gSettingsManager->has('language_switcher_flags')
            || !$gSettingsManager->getBool('language_switcher_flags')) {
            return '';
        }
        return PluginRegistry::get(self::ID)->getAssetUrl('flags/' . self::FLAGS[$language] . '.svg');
    }

    public static function getCookieName(): string
    {
        return COOKIE_PREFIX . '_LANGUAGE';
    }

    /**
     * A new session starts in the language Admidio chooses; the remembered choice replaces it.
     */
    private static function applyRememberedLanguage(): void
    {
        global $gL10n, $gSettingsManager;

        $remembered = (string)($_COOKIE[self::getCookieName()] ?? '');
        if ($remembered === '' || PHP_SAPI === 'cli' || !$gSettingsManager->getBool('language_switcher_remember')
            || $remembered === $gL10n->getLanguage() || !array_key_exists($remembered, self::getLanguages())) {
            return;
        }
        $gL10n->setLanguage($remembered);
    }

    /**
     * The entries of the menu.
     * @return array<int,array<string,mixed>>
     */
    private static function getMenuEntries(): array
    {
        global $gL10n;

        $plugin = PluginRegistry::get(self::ID);
        $entries = array();
        foreach (self::getLanguages() as $code => $name) {
            $entries[] = array(
                'id' => strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $code)),
                'label' => $name,
                'url' => SecurityUtils::encodeUrl($plugin->getUrl('switch.php'), array('language' => $code)),
                'image' => self::getFlagUrl($code),
                'active' => $code === $gL10n->getLanguage()
            );
        }
        $entries[] = array('divider' => true, 'label' => '-');
        $entries[] = array(
            'id' => self::DEFAULT,
            'label' => $gL10n->get('PLG_LANGUAGE_SWITCHER_DEFAULT'),
            'icon' => 'bi-arrow-counterclockwise',
            'url' => SecurityUtils::encodeUrl($plugin->getUrl('switch.php'), array('language' => self::DEFAULT))
        );
        return $entries;
    }

    /**
     * Callback of **page_header_items**.
     * @param array<int,array<string,mixed>> $items
     * @return array<int,array<string,mixed>>
     */
    public static function addHeaderItem(array $items): array
    {
        global $gL10n;

        if (self::isShown()) {
            $items[] = array(
                'id' => self::ID,
                'label' => $gL10n->getLanguageIsoCode(),
                'icon' => 'bi-translate',
                'image' => self::getFlagUrl($gL10n->getLanguage()),
                'tooltip' => $gL10n->get('PLG_LANGUAGE_SWITCHER_TOOLTIP'),
                'sequence' => 50,
                'items' => self::getMenuEntries()
            );
        }
        return $items;
    }

    /**
     * Callback of **page_before_render** for a core without page_header_items: the same menu,
     * moved into the header bar in the browser.
     */
    public static function injectMenu(PagePresenter $page): void
    {
        global $gL10n;

        if (!self::isShown() || strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest') {
            return;
        }

        $plugin = PluginRegistry::get(self::ID);
        $page->addCssFile($plugin->getAssetUrl('language-switcher.css'));
        $page->addHtml($plugin->renderTemplate($page, 'plugin.language-switcher.menu.tpl', array(
            'languageSwitcherLabel' => $gL10n->getLanguageIsoCode(),
            'languageSwitcherFlag' => self::getFlagUrl($gL10n->getLanguage()),
            'languageSwitcherEntries' => self::getMenuEntries()
        )));
        $page->addJavascript('
            var languageSwitcher = $("#adm_plg_language_switcher");
            var languageSwitcherTarget = $("#adm_main_navbar .ms-auto").first();
            if (languageSwitcherTarget.length) { languageSwitcher.prependTo(languageSwitcherTarget); }
            languageSwitcher.removeClass("d-none");', true);
    }
}
