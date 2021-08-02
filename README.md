# Flagpole

[![Maintainability](https://api.codeclimate.com/v1/badges/58e979a1be8d7f7c3d6d/maintainability)](https://codeclimate.com/github/jamesrwilliams/wp-feature-flags/maintainability) [![GitHub release](https://img.shields.io/github/release-pre/jamesrwilliams/flagpole.svg)](https://github.com/jamesrwilliams/flagpole/releases) [![PRs Welcome](https://img.shields.io/badge/PRs%20-welcome-brightgreen.svg)](https://github.com/jamesrwilliams/flagpole/pulls) ![Packagist Downloads](https://img.shields.io/packagist/dm/jamesrwilliams/flagpole) [![Known Vulnerabilities](https://snyk.io/test/github/jamesrwilliams/flagpole/badge.svg?targetFile=composer.lock)](https://snyk.io/test/github/jamesrwilliams/flagpole?targetFile=composer.lock)

This plugin is for WordPress theme developers who wish to add simple feature flags to their themes. These flags can be enabled via an admin interface, previewed on a per-user, or group basis, and even enabled via QueryString for those without accounts.  For planned development work please see the [roadmap](https://github.com/jamesrwilliams/flagpole/projects) or issues labeled with [enhancement](https://github.com/jamesrwilliams/flagpole/issues?q=is%3Aopen+is%3Aissue+label%3Aenhancement).

## Contents

1. [Installation](#installation)
2. [Adding flags to themes](#checking-the-status-of-a-feature-flag)
3. [Enabling flags](#enabling-flags)
4. [Using QueryStrings](#query-strings)
5. [Shortcodes](#shortcodes)
6. [Contributing](#contributing)

## Installation

Add this project source code to your `wp-content/plugins` directory and enable it like you would any other plugin. It is also available via [Packagist](https://packagist.org/packages/jamesrwilliams/flagpole) to use with [composer](https://getcomposer.org/).

```bash
composer require jamesrwilliams/flagpole
```

This plugin is currently not available via the WordPress Plugin directory however we are working towards that for [v1.0.0](https://github.com/jamesrwilliams/flagpole/milestone/2).

### Required theme changes

As this plugin is closely coupled with your theme code it is a good idea to add the following to block to your theme to catch any errors if the Flagpole plugin is disabled for any reason.

```php
if ( ! function_exists( 'flagpole_flag_enabled' ) ) {
	function flagpole_flag_enabled() {
		return false;
	}
}
```

## Register a flag

To register a flag, simply add the following to your theme's `functions.php` file using the `flagpole_register_flag` function:

```php
if ( function_exists( 'flagpole_register_flag' ) ) {
	flagpole_register_flag([
		'title'       => 'My awesome new feature',
		'key'         => 'correct-horse-battery-staple',
		'enforced'    => false,
		'label'       => 'All',
		'description' => 'An example feature definition',
		'stable'      => false,
	]);
}
```

Wrapping the registration function call in a `function_exists` helps avoid errors if the plugin is disabled for any reason. You can also pass an array of flags to `flagpole_register_flag` to easily instantiate multiple flags at once.

### Checking the status of a flag

In your templates you can then check the feature status using the `flagpole_flag_enabled()` function in your PHP theme code to toggle features based on the status of your flags:

```php
if ( flagpole_flag_enabled( 'flag_key' ) ) {
	/* flag_key is enabled! */
}
```

Replace `flag_key` with the key used in the register function to check if it is enabled.

#### Flag options/arguments


| Parameter              | Type      | Default | Description |
|------------------------|-----------|---------|-------------|
| key                    | `string`  | -       | The unique key used in the template to check if a feature is enabled. |
| title                  | `string`  | ""      | The human readable feature name. |
| description (optional) | `string`  | ""      | A description displayed in the admin screen. |
| stable (optional)      | `boolean` | `false` | If true allows users to publish features from the admin area. |
| enforced (optional)    | `boolean` | `false` | Setting this to true will override any user specific settings and will enforce the flag to be enabled for every user. Useful for deploying a flag before removing it from the codebase. |
| label | `string` | `All` | Using labels lets you group together flags that are similar. Adding a label will separate the flag out in the admin UI.

## Enabling flags

There are three ways to have a flag enabled with Flagpole. These are:

- **[Previewing](#previewing)** - Enable a flag only for the current logged-in user.
- **[Publishing](#publishing)** - Enable a flag for every visitor on the site.
- **[Enforcing](#enforcing)** - Flags which are enabled by default in the theme.

### Previewing

A flag can be previewed for the current logged-in user by enabling the preview in the Flagpole admin screen.

Navigate to the Flagpole screen in the WP admin dashboard, located under Tools > Flagpole. Find a flag you wish to enable, and click the "enable preview" button.

This flag will now be enabled for this user until it is toggled again. Users can preview any number of flags at any one time. For previewing multiple flags at the same time check out [Flag Groups](#flag-groups).

### Publishing

Publishing a flag enables it for every user that visits your site, this includes logged-out users. Any user can publish a feature as long as it has been marked as stable by setting the `stable` property to `true` in the flag registration block. This acts as a safety net allowing theme developers mark features ready for publication.

E.g.

```php
flagpole_register_flag([
	'title'       => 'Feature ready for publication',
	'key'         => 'super-awesome-navigation-change',
	'stable'      => true,
]);
```

### Enforcing

Enforcing a flag is where a developer can force a flag to be in a published state. This allows them to enable a flag via their source code by setting the 'enforced' option to true in the flag options. The idea behind enforced flags are a stepping stone before removing the flag logic from the theme. Enforced flags are displayed in a separate list in the admin area and are not interactive to users in the admin area.

```php
flagpole_register_flag([
	'title'       => 'An enforced flag',
	'key'         => 'enforced-flag',
	'enforced'    => true,
]);
```

## Flag Groups

Flag groups are a way to manage multiple flags at a time. You can preview Flag groups like you can a single flag and by using the `group` URL parameter, and the group key you wish to enable.
A `private` group will require users to login to the site prior to activating the flags for them.

You can either preview a flag group by enabling it in the WP Admin as you would for a single flag, or you can use the query string method to enable a group of flags using the following query string format: `?group={groupKey}`

Example:

```
https://www.example.com?group=battery-horse-staple
```

## Advanced Custom Fields (ACF) Support

ACF field groups can also be set to show or hide based on feature flags.

In the 'Location' section of a field group, 'Feature flags' will be available as an option. This allows you to show a field group depending on whether a feature flag are enabled or not. This can be combined with the and/or rules to display a field group depending on the status of multiple feature flags.

## Shortcodes

This plugin adds a number of utility shortcodes to help to debug the use of Flagpole flags.

- [debugFlagpole_flags](#debugFlagpole_flags)
- [debugFlagpole_groups](#debugFlagpole_groups)
- [debugFlagpole_db](#debugFlagpole_db)

### debugFlagpole_flags

The shortcode by default shows all flags that are not enforced found in your theme. You can also specify which flags you're looking to debug specifically using the flag parameter like so with either a single key or a comma separated list:

Basic Usage:

```php
// Single Key
echo do_shortcode('[debugFlagpole_flags]');
```

This will display a table of all the non-enforced flags currently found in the active theme, including a status and, if they are enabled, a reason why.

You can specify single or multiple flag for this to output if you don't want to show everything.

```php
// Multiple keys
echo do_shortcode('[debugFlagpole_flags flag="key-1,key-2,key-3"]');
```

Passing the `enforced` value will display all `enforced` flags instead of the other flags. E.g:

```php
echo do_shortcode('[debugFlagpole_flags enforced="true"]');
```

### debugFlagpole_groups

Use the following shortcode to get a debug output for flag groups.

```php
echo do_shortcode('[debugFlagpole_groups]');
```

### debugFlagpole_db

Use the following shortcode to get a debug output for everything!

```php
echo do_shortcode('[debugFlagpole_db]');
```

## Contributing

Any PRs and suggestions are very welcome, along with ideas and discussions on issues.
