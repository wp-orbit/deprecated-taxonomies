#Taxonomy - Featured Image
via *[WP Orbit](https://github.com/wp-orbit)*::[Taxonomy](https://github.com/wp-orbit/taxonomy)

The WP Orbit - Taxonomy - Featured Image subpackage exposes a standalone class 
**TaxonomyFeaturedImage** which serves as a container through which *Featured Images* 
can be hooked to any taxonomy.

Example usage in your plugin or theme:

```php
<?php
use WPOrbit\Taxonomy\FeaturedImage\TaxonomyFeaturedImage;

// This is how we add featured image support to categories.
TaxonomyFeaturedImage::bind( 'category' );
```
 