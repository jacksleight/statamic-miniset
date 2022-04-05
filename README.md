<!-- statamic:hide -->

![Statamic](https://flat.badgen.net/badge/Statamic/3.3+/FF269E)
![Packagist version](https://flat.badgen.net/packagist/v/jacksleight/statamic-miniset)
![License](https://flat.badgen.net/github/license/jacksleight/statamic-miniset)

# Miniset 

<!-- /statamic:hide -->

Miniset allows you to create compact sets of fields that either combine into a string of classes, or store an array of simple values.

Miniset includes the following components:

* **Miniset**  
  A general purpose fieldtype for creating a compact set of fields that store an array of simple values.
* **Miniset Classes**  
  A class specific fieldtype for creating a compact set of fields that combine into a string of classes, including variant options. Works great with Tailwind CSS.

## Installation

You can search for this addon in the `Tools > Addons` section of the Statamic control panel and click **install**, or run the following command from your project root:

```bash
composer require jacksleight/statamic-miniset
```

## Creating a Miniset Field

Create a Miniset field and add your nested fields. Miniset is intended for simple data (single or multiple scalar values) and only officially supports these nested fieldtypes:

* Select (inc. multiple)
* Button Group
* Checkboxes
* Radio
* Text
* Textarea

Other fieldtypes can be added but will not have a compact style. 

Fields within a Miniset field can be output by [plucking the values](https://statamic.dev/new-antlers-parser#plucking):

```html
{{ my_options.theme }}
```


## Creating a Miniset Classes Field

Create a Miniset Classes field and add your nested fields and variants. Miniset Classes only supports the following nested fieldtypes:

* Select (inc. multiple)
* Button Group
* Checkboxes
* Radio
* Text (not `jit_safe` compatible)
* Textarea (not `jit_safe` compatible)

Other fieldtypes should not be added and may cause errors or unexpected results.

Variants will be offered as options when adding groups of fields. By default the variant keys will be prepended to the class value and seperated with a colon (the convention used by Tailwind CSS):

```text
"lg" / "w-32" --> "lg:w-32"
```

If you're not using Tailwind CSS you can customize this behaviour by including an ampersand in your variant keys. The ampersand will be replaced with the class value when augmented:

```text
"&@large" / "width-half" --> "width-half@large"
```

Miniset Classes will combine all field values down to a single flat string of classes which can be output just like any field:

```html
<div class="{{ my_classes }}">
```

### Using `jit_safe` mode with Tailwind

When using Tailwind the JIT compiler scans your content for classes, and this will need to include any classes that could be selected by users in the control panel. To simplify this process Miniset has a `jit_safe` mode. To enable this publish the config:

```bash
php please vendor:publish --tag=statamic-miniset-config
```

Then open `config/statamic/miniset.php` and set the `jit_safe.enable` option to `true`:

```php
'jit_safe' => [
    'enable' => true,
    'file'   => resource_path('css/statamic/miniset_classes.yaml'),
],
```

Once enabled Miniset will scan your Miniset Classes fields and save a full list of possible classes to `resources/css/statamic/miniset_classes.yaml` whenever you update a blueprint or fieldset.

You can then tell Tailwind to search this file in your `tailwind.config.js`:

```js
content: [
    './resources/css/statamic/miniset_classes.yaml',
],
```
