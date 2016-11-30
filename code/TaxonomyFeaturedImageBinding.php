<?php
namespace Zawntech\WordPress\Orbit\Taxonomy;

/**
 * Class TaxonomyFeaturedImageBinding
 * @package Zawntech\WordPress\Orbit\Taxonomy
 */
class TaxonomyFeaturedImageBinding
{
    /**
     * @var string The taxonomy key.
     */
    public $taxonomy;

    /**
     * @var array An array of post types the taxonomy should be applied to.
     */
    public $postTypes = [];

    /**
     * TaxonomyFeaturedImageBinding constructor.
     * @param string $taxonomy
     * @param array $postTypes
     */
    public function __construct( $taxonomy, $postTypes = [] )
    {
        $this->taxonomy = $taxonomy;
        $this->postTypes = $postTypes;
    }
}