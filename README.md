Customizable widget
====

Customizable widget. Made for WordPress.

### Installation
If you're using Composer to manage WordPress, add this plugin to your project's dependencies. Run:
```sh
composer require trendwerk/widget-customizable 1.0.0
```

Or manually add it to your `composer.json`:
```json
"require": {
	"trendwerk/widget-customizable": "1.0.0"
},
```

### Hooks

```php
apply_filters( 'widget-customizable-image-size', $args );
```

Change widget image size settings. `$args` contains:

- **$width**
- **$height**
- **$crop**

See [`add_image_size`](https://codex.wordpress.org/Function_Reference/add_image_size)
