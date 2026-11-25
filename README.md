# Admidio Language Switcher

A menu in the header bar with which every user chooses the language of Admidio for themselves,
independent of the language of the organization. *Default language* returns to the language
Admidio chooses by itself (that of the browser if Admidio has it, otherwise that of the
organization).

Requires Admidio 6.0 or later and PHP 8.2 or later.

## Settings

*Administration → Preferences → System → Language switcher*

* **Languages** – the languages the menu offers; none selected means all languages of Admidio.
* **Show flags** – the flag of each language instead of the translation icon.
* **Also for visitors** – show the menu to visitors who are not logged in.
* **Remember the choice** – keep the chosen language in a cookie for the next visit.

The choice applies to the user's own session (and, with *Remember the choice*, to the next
sessions in the same browser). The language of the organization and of all other users stays as it is.

## Header bar

The menu uses the filter `page_header_items` of the Admidio core. A core without that filter gets
the menu moved into the header bar in the browser.

## Flags

The flags in `assets/flags` are from [flag-icons](https://github.com/lipis/flag-icons) by Panayiotis
Lipiridis, MIT license (`assets/flags/LICENSE`).

## License

GNU General Public License v2.0 only, see `LICENSE`.
