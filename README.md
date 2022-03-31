<!-- statamic:hide -->

![Statamic](https://flat.badgen.net/badge/Statamic/3.3+/FF269E)
![Packagist version](https://flat.badgen.net/packagist/v/jacksleight/statamic-miniset)
![License](https://flat.badgen.net/github/license/jacksleight/statamic-miniset)

# Miniset 

<!-- /statamic:hide -->

Miniset is a collection of fieldtypes for creating compact fieldsets, including fieldsets that augment to a single string of utility classes. It includes the follwing fieldtypes:

* **Miniset**
	* A general purpose fieldtype for creating a compact set of fields.
* **Miniset Classes**
	* A fieldtype that augments to a single string of classes, including variant options. Works great with Tailwind CSS.
* **~Miniset Panel~** (coming someday)
	* ~A fieldtype for grouping multiple Minisets into a stacked or tabbed panel.~

## Installation

You can search for this addon in the `Tools > Addons` section of the Statamic control panel and click **install**, or run the following command from your project root:

```bash
composer require jacksleight/statamic-miniset
```

## Getting Started

Create a Miniset field and add your nested fields. Miniset is intended for "simple" data (single or multiple scalar values) and only supports the following nested fieldtypes:

* Select (inc. multiple)
* Button Group
* Text
* ~Textarea~ (coming soon)
* ~Toggle~ (coming soon)
* ~Checkboxes~ (coming soon)
* ~Radio~ (coming soon)

Other fieldtypes won’t look right and may cause errors or unexpected results. Feature requests and PR's are welcome if you think another fieldtype could be supported.

Fields within a Miniset field can be output by refernecing the inner keys:

```html
{{ my_field:example }}
```

## Miniset Classes

Miniset Classes will augment all contained values down to a single flat string of classes. It also allows you to specify a list of variants that will be offered as options when creating groups of fields. By default the variant keys will be prepended to the class value and seperated with a colon (the convention used by Tailwind CSS):

```
"w-32" / "lg" --> "lg:w-32"
```

If you're not using Tailwind CSS you can customize this behaviour by including an ampersand in your variant keys. The ampersand will be replaced with the class value when augmented:

```
"width-half" / "&@large" --> "width-half@large"
```

A Miniset Classes field can be output just like any field:

```html
<div class="{{ my_class }}"></div>
```

### Using with Tailwind

When using Tailwind the JIT compiler needs to know which classes should be included in your CSS. To simplify this Miniset Classes has a `jit_mode` option that will save a full list of possible variant utilities to the blueprint/fieldset file:

```yaml
jit_mode: true
```

Once enabled you can tell Tailwind to search these files for classes in your `tailwind.config.js` file:

```js
content: [
    './resources/blueprints/**/*.yaml',
    './resources/fieldsets/*.yaml',
],
```

This is necessary so that any classes a user can select in the CP are included in your CSS.