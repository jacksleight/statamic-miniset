# Miniset Lab

A Statamic 6 site that exists only to develop and test `jacksleight/statamic-miniset`. The package is pulled in over a composer path repository (`../statamic-miniset`, constraint `*`), so `vendor/jacksleight/statamic-miniset` is a symlink into the package's working tree and PHP edits there are live here with no rebuild. The lab always sees whatever branch the package is on, including uncommitted work.

Herd parks `~/Packages`, so the site answers on http://statamic-miniset--lab.test/ with no vhost step. The control panel is at `/cp`.

**The control panel assets are a symlink too.** `public/vendor/statamic-miniset/build` points at `../../../../statamic-miniset/dist/build`, so `npm run build` in the package is live here on the next reload. `vendor:publish --tag=statamic-miniset` would replace it with a copy that never updates again; if that happens, put the symlink back.

## Structure

The `tests` collection is routed at `/tests/{slug}`. Each entry tests one case and has its own blueprint in `resources/blueprints/collections/tests/` and its own template in `resources/views/tests/`. The templates print the stored value (`{{ lab:raw }}`, the raw YAML as JSON) beside what the field augments to: the class string for Miniset Classes, the `to_json` output and each key for Miniset.

| Entry | Fieldtype | What it exercises |
| ----- | --------- | ----------------- |
| `/tests/values` | Miniset | Every officially supported nested type (select, select multiple, button group, checkboxes, radio, text, textarea, toggle, range) at mixed widths |
| `/tests/validation` | Miniset | A nested `required` text field: clear it and the save returns 422 with the error on `details.name` |
| `/tests/conditional` | Miniset | A Miniset behind `if: media_type: image`, the port of the old lab's `bug_test_1`. Saving with it hidden must not error, and the hidden value is dropped |
| `/tests/classes` | Miniset Classes | No variants: select, button group, checkboxes, radio and free text into one class string |
| `/tests/variants` | Miniset Classes | `md` / `lg` / `xl` with a custom `default_label` and multi-class option values, each class prefixed |
| `/tests/single-variant` | Miniset Classes | One variant, so the + button commits it directly rather than opening the picker |
| `/tests/ampersand` | Miniset Classes | `&@large` (suffix) and `hover:&` (the explicit form of the default prefix) |
| `/tests/many-variants` | Miniset Classes | Twelve variants with HTML labels; the picker wraps and the tabs overflow |
| `/tests/linked` | Miniset Classes | `field: lab_classes.spacing` with a `config` override of its variants, and a field whose nested `fields` are a linked field (`lab_classes.radius`) plus `import: lab_colours`. These are the three paths `JitSafeScanner` resolves |
| `/tests/nested` | both | Both fieldtypes in a Replicator set and a Grid row, where field and meta paths are prefixed |
| `/tests/jit-safe` | Miniset Classes | Map-format options next to the list format the CP saves, plus a text field. The page prints the generated `miniset_classes.yaml` |

The home page at `/` lists them, driven by each entry's `summary` field.

## JIT safe mode

`config/statamic/miniset.php` has `jit_safe.enable` on, and `resources/css/site.css` carries `@source "./statamic/miniset_classes.yaml"`, so any class the CP can produce compiles. **The YAML is generated, not authored**: it is not in the package's `.lab/` overlay. After a rebuild, or after editing a blueprint by hand rather than through the CP, run `php please miniset:jit-safe-scan`, then `npm run build`.

## Demos and screenshots

The `demos` collection, routed at `/demos/{slug}`, is what the marketplace screenshots are taken from. Four Brighton neighbourhoods share one blueprint, `neighbourhood`:

- **Card style** (Miniset): theme button group, size select, image button group, rounded toggle, badge text
- **Section layout** (Miniset Classes): padding select, columns and align button groups, background select, with `md: Tablet`, `lg: Desktop`, `xl: Wide` and `dark: Dark mode` variants. Map-format options only, and no text fields, so the scan covers every class

**The field order (title, section layout, card style, intro, image) and the intro being a textarea serve the shots.** Each slide shot hides the fields it does not need and keeps the calm intro textarea under the subject to run off the slide's bottom edge, rather than the image field, whose big Browse Assets button pulled the eye. The title above Card style gives the floating code panel an empty corner to sit on.

**Pavilion Gardens has a Default group and nothing else, on purpose.** The variant picker shot is taken there, so its tabs show the state before any variant group exists. Seafront's Desktop group is the state after.

`demos/show` renders the section with the `section_classes` string and the card with classes derived from `card_style`, so both visibly work, and prints both values underneath. **Demo copy is short, light and written from nobody's point of view.**

**The card style row is Theme 50, Size 25, Image 25, then Rounded 25, Badge 75 on purpose.** A button group that does not fit its column stacks its buttons vertically. The four theme buttons need about 220px, a w-25 column at a capture-width panel is about 160px, and four quarters in one row would need a stage around 950px wide, too wide to read on a slide.

The capture pipeline is the `code-capture` skill in `~/Projects/jacksleight`, whose `shots/statamic-miniset.json` hard-codes the demo entry UUIDs. They stay valid because the entries are baked into the package's `.lab/` overlay verbatim.

## Survey findings (recorded, not fixed)

Confirmed in this lab on 2026-10-30. The fixes are a separate pass.

1. **CP-saved option lists break the JIT scan.** `JitSafeScanner::processConfigs` reads `array_keys($field['field']['options'])`, but Statamic 6 saves options edited in the CP as a list of `{key, value}`. `/tests/jit-safe`'s `weight` field is in that format, and the generated YAML lists `'0'`, `'1'`, `'lg:0'`, `'lg:1'` for it instead of `font-normal` / `font-bold`. The field itself works in the CP and on the page; only the scan is wrong.
2. **`MinisetFieldtype.vue`'s `updated(handle, value)` and `updateMeta(handle, value)` are dead code.** They override the mixin's `updateMeta(value)` with the Statamic 5 signature and emit `meta-updated`, which nothing listens for: the parent binds `onUpdate:meta`. Instrumented in the CP, editing five nested fields on `/tests/values` (button group, text, textarea, radio, checkboxes) changed the value through `FieldsProvider` with zero calls to either method. The one live consequence is that a field action calling `updateMeta(value)` on a Miniset would do nothing. Statamic 6's own `GroupFieldtype.vue` carries the same two methods.
3. **The scan command ignores `enable`, and nothing prunes stale keys.** With `enable` false, `miniset:jit-safe-scan` still wrote every key; the save listeners do check it. A key planted for a blueprint that does not exist survived a full scan, because `processData` only sets or unsets the key it is processing.
4. **`mix-manifest.json` in the package root is local cruft, not a repo issue.** It is gitignored and has never been committed, so it never shipped; it points at a `dist/js/addon.js` that no longer exists. It and its `.gitignore` line can simply go.

Observed while testing, also left alone:

5. **The variant tabs never wrap.** With eleven groups on `/tests/many-variants` the tabs run past the panel's right edge, long labels squash onto two lines, and the + button ends up under the sidebar where it cannot be clicked.
6. **The variant picker lists variants that already have a group.** Clicking one selects the existing tab, which is harmless but means the picker never shrinks.
7. **A button group with more options than its column fits stacks vertically**, as the three-option theme field on `/tests/values` shows at w-25.

## Things to know

- **A field handle of `layout` breaks the page.** Statamic reads an entry's `layout` key as the layout template name, so a Miniset Classes field called `layout` 500s with "Array to string conversion". The variants test uses `arrangement` for this reason.
- **A CP save rewrites a blueprint into the `tabs:` form**, and an entry save adds `updated_by` / `updated_at`. Restore the flat authored files before baking, so the overlay stays as written.
- **Saving an entry redirects to the listing**, so a browser script that saves and then keeps clicking is on the wrong page.
- **The licensing alert opens on every CP load** in trial mode. Snooze it, or let `code-capture`'s stylesheet hide it.
- **Assets** are in `public/assets/brighton`, container handle `assets`.

## Rebuilding

This lab is disposable, and everything in it worth keeping is baked into `.lab/` in the package repo, a flat mirror of these paths copied over a fresh `statamic new` shell. To rebuild, run the `setup-lab` skill. The generated steps after the copy are the composer link, `vendor:publish --tag=statamic-miniset` plus the symlink above, `php please miniset:jit-safe-scan`, then `npm install && npm run build`.

To add something that should survive a rebuild, re-run `setup-lab` with that instruction. Do not edit `.lab/` by hand, and never sync the whole lab into it. **Entry files are baked verbatim, UUIDs included**: the shot manifest depends on the demo ones, and changing demo content means the marketplace screenshots need retaking.

## Skip (local login)

Local-only login bypass from the machine's skip package (`~/Packages/skip`), configured in `.claude/skip.json`: `GET /!/skip/{handle}` logs in as `{handle}@example.test` and lands on `?redirect=` (a path on this site) or the handle's own `redirect`. Example: `/!/skip/admin`. A listed handle with no user yet is created on its first login, with the password `password`.
