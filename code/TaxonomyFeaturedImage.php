<?php
namespace Zawntech\WordPress\Orbit\Taxonomy;

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

    /**
     *
     */
    protected function addFormFields()
    {
    }

    protected function saveFormFields()
    {

    }

    /**
     * @param $taxonomy
     * @return TaxonomyFeaturedImageBinding[]
     */
    public function getBindingsByTaxonomy( $taxonomy )
    {
        // Declare an array of bindings to return if they match the supplied filter.
        $bindings = [];

        // Loop through bindings.
        foreach( $this->bindings as $binding )
        {
            // Is this
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
     * @param string $taxonomy The taxonomy key we want to add featured images to.
     * @param array $postTypes Specifies which specific post types should per taxonomy should
     * get featured image support. If left empty, will support all post types.
     */
    public static function addFeaturedImageSupport( $taxonomy, array $postTypes = [] )
    {

    }

    /**
     * @param string $taxonomy The taxonomy key we want to remove featured images from.
     * @param array $postTypes
     */
    public static function removeFeaturedImageSupport( $taxonomy, array $postTypes = [] )
    {

    }
}