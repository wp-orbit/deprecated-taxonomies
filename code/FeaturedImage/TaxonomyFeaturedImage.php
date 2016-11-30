<?php
namespace Zawntech\WordPress\Orbit\Taxonomy\FeaturedImage;

/**
 * Adds a "featured image" to taxonomy terms.
 * Class TaxonomyFeaturedImage
 * @package Zawntech\WordPress\Orbit\Taxonomy
 */
class TaxonomyFeaturedImage
{
    /**
     * @var TaxonomyFeaturedImage
     */
    protected static $instance;

    /**
     * TaxonomyFeaturedImage constructor.
     */
    protected function __construct()
    {
        // Defer initialization to 'init' hook.
        add_action('init', function () {
            $this->addFormFields();
            $this->saveFormFields();
        });
    }

    /**
     * @var TaxonomyFeaturedImageBinding[]
     */
    protected $bindings = [];

    protected function addFormFields()
    {
    }

    protected function saveFormFields()
    {

    }

    /**
     * @param string $taxonomy
     * @return TaxonomyFeaturedImageBinding[]
     */
    public function getBindingsByTaxonomy( $taxonomy )
    {
        // Declare an array of bindings to return if they match the supplied filter.
        $bindings = [];

        // Loop through bindings.
        foreach( $this->bindings as $binding )
        {
            // Match the taxonomy name against
            if ( $binding->taxonomy == $taxonomy )
            {
                // Push this binding.
                $bindings[] = $bindings;
            }
        }

        return $bindings;
    }

    /**
     * @return TaxonomyFeaturedImage
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    /**
     * Binds featured image support for a given taxonomy (and post types, if supplied).
     * @param string $taxonomy The taxonomy key we want to add featured images to.
     * @param array $postTypes Specifies which specific post types should per taxonomy should
     * get featured image support. If left empty, will support all post types.
     */
    public static function bind( $taxonomy, array $postTypes = [] )
    {
        $self = static::getInstance();

        // Get bindings by taxonomy.
        $bindings = $self->getBindingsByTaxonomy( $taxonomy );

        // No bindings found.
        if ( empty ( $bindings ) )
        {
            // Create the binding.
            $binding = new TaxonomyFeaturedImageBinding( $taxonomy, $postTypes );

            // Push to object.
            $self->bindings[] = $binding;

            // We're done.
            return;
        }

        // Loop through bindings.
        foreach( $bindings as &$binding )
        {
            // This binding matches our taxonomy term.
            if ( $taxonomy == $binding->taxonomy )
            {
                // Loop through current binding's post types.
                foreach( $postTypes as $postType )
                {
                    // Add this post type to the binding.
                    if ( ! in_array( $postType, $binding->postTypes ) )
                    {
                        $binding->postTypes[] = $postType;
                    }
                }
            }
        }
    }

    /**
     * @param string $taxonomy The taxonomy key we want to remove featured images from.
     * @param array $postTypes
     */
    public static function unbind( $taxonomy, array $postTypes = [] )
    {
        $self = static::getInstance();

        // Loop through bindings.
        foreach( $self->bindings as $key => $binding )
        {
            // Match the taxonomy.
            if ( $taxonomy === $binding->taxonomy )
            {
                // No post types were supplied, so remove any matching instances of the taxonomy.
                if ( empty( $postTypes ) )
                {
                    // Unset the item.
                    unset( $self->bindings[$key] );
                }

                // We need to check against post types in addition to the taxonomy key.
                else
                {
                    // Loop through post types.
                    foreach( $postTypes as $postType )
                    {
                        // Is the post type in the binding?
                        if ( in_array( $postType, $binding->postTypes ) )
                        {
                            // Unset the item.
                            unset( $self->bindings[$key] );
                        }
                    }
                }
            }
        }
    }
}