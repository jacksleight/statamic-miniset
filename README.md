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

Create a Tailset field and add the fields, classes and variants you need. I recommend doing this in a fieldset so it can easily be reused.

The following fieldtypes are the only ones supported within Tailset:

* Select (inc. multiple)
* Button Group
* Checkboxes
* Radio
* Text

Other fieldtypes probably won’t look right and may cause errors or unexpected results.

### Using with Tailwind CSS

When using Tailwind’s JIT mode you’ll need to tell it to search your blueprint and fieldset files for classes:

Tailset automatically embeds a full list of variant classes in the YAML file, so they can be discovered and included by the compiler.

### Using with other CSS frameworks / custom classes

While this was built to be used with Tailwind CSS you don’t have to use Tailwind classes or naming conventions.

* Use the `variant_separator` option to change the character used separate variants from classes.
* Use the `variant_position` option to change whether variants are placed before or after the class.