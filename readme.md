# Feature flags for WordPress themes

This plugin is for developers. The aim is to simplify/speed up the process of working with feature flags.

## Features

- Adds an admin UI where users can enable/disable features for testing.
- Can enforce flags once you are happy for them to be deployed (allows for staged removal in source).

## Usage

Register a flag in functions.php

```php
register_featureFlag($options);
```

In template you can then check the feature status using:

```php
is_enabled('feature-key');
```
Replace `feature-key` with the key used in the register function to check if it is enabled.

**Example**

```php
register_feature_flag([

  'title' => 'My awesome new feature',
  'key' => 'correct-horse-battery-staple',
  'enforced' => false,
  'description' => 'An example feature definition'

]);
```

### Options

**key** (required) - `string` 

The unique key used in the template to check if a feature is enabled.

**title** (required) - `string`

The human readable feature name.

**enforced** - `boolean` - Default: `false`

Setting this to true will override any user specific settings and will enforce the flag to be true for every user. Useful for deploying a flag before removing it from the codebase.

**description** - `string` - Default: ``

A description displayed in the admin screen. Use to tell users what they are enabling and other information. 

