<!-- statamic:hide -->

![Statamic](https://flat.badgen.net/badge/Statamic/3.3+/FF269E)
![Packagist version](https://flat.badgen.net/packagist/v/jacksleight/statamic-tailset)
![License](https://flat.badgen.net/github/license/jacksleight/statamic-tailset)

# Tailset 

<!-- /statamic:hide -->

This Statamic addon allows you to create a compact set of fields that all augment to a single list of utility classes. It can be used in page builders when you need to give users easy control over the classes added to a component.

## Installation

You can search for this addon in the `Tools > Addons` section of the Statamic control panel and click **install**, or run the following command from your project root:

```bash
composer require jacksleight/statamic-tailset
```

## Getting Started

Create a Tailset field and add the fields, options and variants you need. I recommend doing this in a fieldset so it can easily be reused.

The following fieldtypes are the only ones supported within Tailset:

* Select (inc. multiple)
* Button Group
* Text
* ~Checkboxes~ (planned)
* ~Radio~ (planned)

Other fieldtypes probably won’t look right and may cause errors or unexpected results.

### Using with Tailwind CSS

When using Tailwind you’ll need to tell the JIT compiler to search your blueprint and fieldset files for classes:

```js
content: [
    './resources/blueprints/**/*.yaml',
    './resources/fieldsets/**/*.yaml',
],
```

Tailset embeds a full list of possible variant utilities in the YAML file. This is necessary so they can be discovered by the compiler and included in your CSS files, so they work when a user selects them in the CP.

### Using with other CSS frameworks / custom classes

While this was built to be used with Tailwind CSS you don’t have to use Tailwind classes or naming conventions.

* Use the `variant_separator` option to change the character used separate variants from utilities.
* Use the `variant_position` option to change whether variants are placed before or after the utilitiy.