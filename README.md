# Feature flags for WordPress themes

This plugin is for developers. The aim is to simplify/speed up the process of working with feature flags.

## Features

- Adds an admin UI where users can enable/disable features for testing in a live environment.
- Can enforce flags once you are happy for them to be deployed (allows for staged removal in source).

## Planned development

- [ ] [Google Analytics A/B testing support](https://github.com/jamesrwilliams/feature-flags/issues/6)
- [ ] [User roles support](https://github.com/jamesrwilliams/feature-flags/issues/5)
- [ ] [Add "Screen Options" tab to admin screen](https://github.com/jamesrwilliams/feature-flags/issues/4)
- [ ] [Allow users to enable features for all users](https://github.com/jamesrwilliams/feature-flags/issues/2)

## Usage

### Required theme changes

Due to the nature of this plugin requiring theme changes, it is a good idea to add the following to the your theme to catch any errors that may occur if the feature-flags plugin is disabled for any reason.

```php
if ( ! function_exists( 'is_enabled' ) ) {
	function is_enabled() {
		return false;
	}
}
```

### Register a flag

```php
register_feature_flag( $args );
```
When registering a flag it is a good idea to wrap them in a function exists block to avoid any errors if the plugin is disabled for any reason. In your templates you can then check the feature status using:

```php
if ( function_exists( 'register_feature_flag' ) ) {

    register_feature_flag([
        
        'title'       => 'My awesome new feature',
        'key'         => 'correct-horse-battery-staple',
        'enforced'    => false,
        'description' => 'An example feature definition'
        'queryable'   => true,
    
    ]);
}
```

### Checking the status of a feature flag

```php
is_enabled( 'feature-key' );
```
Replace `feature-key` with the key used in the register function to check if it is enabled.

**Example**

```php
if ( is_enabled( 'correct-horse-battery-staple' ) {
    /* Flagged feature */
}
```

## Options

**key** - `string` 

The unique key used in the template to check if a feature is enabled.

**title** - `string`

The human readable feature name.

**enforced** (optional) - `boolean` - Default: `false`

Setting this to true will override any user specific settings and will enforce the flag to be true for every user. Useful for deploying a flag before removing it from the codebase.

**description** (optional) - `string` - Default: ''

A description displayed in the admin screen. Use to tell users what they are enabling and other information. 

**queryable** (optional) - `boolean` - Default: `false`

Allow users to enable this flag by using a query string in the URL.

## Standards

This project uses the [WordPress VIP](https://github.com/Automattic/VIP-Coding-Standards) coding standards.
