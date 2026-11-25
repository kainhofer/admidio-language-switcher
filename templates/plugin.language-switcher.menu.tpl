<span id="adm_plg_language_switcher" class="dropdown me-2 d-none">
    <a class="btn btn-sm dropdown-toggle admidio-language-switcher-button" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="{$l10n->get('PLG_LANGUAGE_SWITCHER_TOOLTIP')}">
        {if $languageSwitcherFlag != ''}<img class="admidio-language-switcher-flag" src="{$languageSwitcherFlag|escape}" alt="">{else}<i class="bi bi-translate"></i>{/if} <span class="d-none d-sm-inline">{$languageSwitcherLabel|escape}</span>
    </a>
    <ul class="dropdown-menu dropdown-menu-end admidio-language-switcher-menu">
        {foreach $languageSwitcherEntries as $entry}
            {if isset($entry.divider) && $entry.divider}
                <li><hr class="dropdown-divider"></li>
            {else}
                <li><a class="dropdown-item{if isset($entry.active) && $entry.active} active{/if}" href="{$entry.url|escape}">{if !empty($entry.image)}<img class="admidio-language-switcher-flag me-1" src="{$entry.image|escape}" alt=""> {elseif isset($entry.icon)}<i class="bi {$entry.icon}"></i> {/if}{$entry.label|escape}</a></li>
            {/if}
        {/foreach}
    </ul>
</span>
