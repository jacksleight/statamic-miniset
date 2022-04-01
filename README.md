<!-- statamic:hide -->

![Statamic](https://flat.badgen.net/badge/Statamic/3.3+/FF269E)
![Packagist version](https://flat.badgen.net/packagist/v/jacksleight/statamic-miniset)
![License](https://flat.badgen.net/github/license/jacksleight/statamic-miniset)

# Miniset 

<!-- /statamic:hide -->

Miniset is a suite of fieldtypes for creating compact fieldsets, including fieldsets that combine into a single string of utility classes. It includes the follwing fieldtypes:

* **Miniset**  
  A general purpose fieldtype for creating a compact set of fields.
* **Miniset Classes**  
  A fieldtype that combines all fields into a single string of classes, including variant options. Works great with Tailwind CSS.
* **~Miniset Panel~** (coming someday)  
  ~A fieldtype for grouping multiple Minisets into a stacked or tabbed panel.~

## Installation

You can search for this addon in the `Tools > Addons` section of the Statamic control panel and click **install**, or run the following command from your project root:

```bash
composer require jacksleight/statamic-miniset
```

## Creating a Miniset Fieldtype

Create a Miniset field and add your nested fields. Miniset is intended for "simple" data (single or multiple scalar values) and only supports the following nested fieldtypes:

* Select (inc. multiple)
* Button Group
* Text
* ~Textarea~ (coming soon)
* ~Toggle~ (coming soon)
* ~Checkboxes~ (coming soon)
* ~Radio~ (coming soon)

Other fieldtypes won’t look right. Feature requests and PR's are welcome if you think another fieldtype could be supported.

Fields within a Miniset field can be output by referencing the inner keys:

```html
{{ my_field:example }}
```

## Creating a Miniset Classes Fieldtype

Create a Miniset field and add your nested fields and variants. Miniset Classes only supports the following nested fieldtypes:

* Select (inc. multiple)
* Button Group
* Text (not `jit_safe` compatible)
* ~Checkboxes~ (coming soon)
* ~Radio~ (coming soon)

Other fieldtypes won’t look right and may cause errors or unexpected results. Feature requests and PR's are welcome if you think another fieldtype could be supported.

Variants will be offered as options when creating groups of fields. By default the variant keys will be prepended to the class value and seperated with a colon (the convention used by Tailwind CSS):

```
"lg" / "w-32" --> "lg:w-32"
```

If you're not using Tailwind CSS you can customize this behaviour by including an ampersand in your variant keys. The ampersand will be replaced with the class value when augmented:

```
"&@large" / "width-half" --> "width-half@large"
```

Miniset Classes will combine all contained values down to a single flat string of classes which can be output just like any field:

```html
<div class="{{ my_class }}">
	<!--- --->
</div>
```

### Using `jit_safe` mode with Tailwind

When using Tailwind the JIT compiler needs to know which classes should be included in your CSS, and this needs to include any classes that could possibly be selected by users in the CP. To simplify this process Miniset has a `jit_safe` mode. To enable this publish the config:

```bash
php please vendor:publish --tag=statamic-miniset-config
```

Then open `config/statamic/miniset.php` and set the `jit_safe` option to `true`:

```php
`jit_safe` => true,
```

Once enabled Miniset will scan your Miniset Classes fields and save a full list of possible classes to `resources/css/statamic/miniset_classes.yaml` whenever you update a blueprint or fieldset. If you would like to save this file to a different location you can change the `jit_file` option.

You can then tell Tailwind to search this file in your `tailwind.config.js`:

```js
content: [
    './resources/css/statamic/miniset_classes.yaml',
],
```