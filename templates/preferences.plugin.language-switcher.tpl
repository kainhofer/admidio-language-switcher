<form {foreach $attributes as $attribute}
        {$attribute@key}="{$attribute}"
    {/foreach}>

    {include 'sys-template-parts/form.input.tpl' data=$elements['adm_csrf_token']}
    {include 'sys-template-parts/form.select.tpl' data=$elements['language_switcher_languages']}
    {include 'sys-template-parts/form.checkbox.tpl' data=$elements['language_switcher_flags']}
    {include 'sys-template-parts/form.checkbox.tpl' data=$elements['language_switcher_visitors']}
    {include 'sys-template-parts/form.checkbox.tpl' data=$elements['language_switcher_remember']}
    {include 'sys-template-parts/form.button.tpl' data=$elements['adm_button_save_language_switcher']}

    <div class="form-alert" style="display: none;">&nbsp;</div>
</form>
{$javascript}
