<?php
namespace WPOrbit\Taxonomies\Pivoter;

/**
 * Class TaxonomyPivoter
 *
 * @package WPOrbit\Taxonomies
 */
class TaxonomyPivoter
{
    /**
     * @var int The parent ID post from which we orient relationships.
     */
    protected $parentId;

    /**
     * @var string
     */
    protected $taxonomy;

    /**
     * @var string The type of pivot context-- 'post' or 'user'.
     */
    protected $pivotContext = 'post';

    /**
     * @var string
     */
    protected $postType = 'post';

    /**
     * Establish post relationships.
     * @return $this
     */
    public function pivotPosts( $postType = 'post' )
    {
        $this->pivotContext = 'post';
        $this->postType = $postType;
        return $this;
    }

    /**
     * Establish user relationships.
     * @return $this
     */
    public function pivotUsers()
    {
        $this->pivotContext = 'user';
        return $this;
    }

    /**
     * @param $id
     * @return bool
     */
    public function hasRelation( $id )
    {
        return has_term( (string) $id, $this->taxonomy, $this->parentId );
    }

    /**
     * Establish a relationship.
     * @param $id
     * @return $this
     */
    public function addRelation( $id )
    {
        if ( $this->hasRelation( $id ) )
        {
            return $this;
        }

        // Get the term we want to add.
        $term = get_term_by( 'name', $id, $this->taxonomy );

        // Invalid term.
        if ( ! $term )
        {
            // Create the term.
            $term = wp_insert_term(
                (string) $id,
                $this->taxonomy
            );

            // Get term ID.
            $termId = $term['term_id'];
        }

        else {
            $termId = $term->term_id;
        }

        // Add the term ID.
        wp_add_object_terms( $this->parentId, $termId, $this->taxonomy );

        return $this;
    }

    /**
     * Remove a relationship.
     * @param $id
     * @return $this
     */
    public function removeRelation( $id )
    {
        if ( ! $this->hasRelation( $id ) )
        {
            return $this;
        }

        // Get the term we want to add.
        $term = get_term_by( 'name', $id, $this->taxonomy );

        if ( ! $term ) {
            return $this;
        }

        wp_remove_object_terms( $this->parentId, $term->term_id, $this->taxonomy );

        return $this;
    }

    /**
     * Returns a list of related IDs.
     * @return array
     */
    public function getIds()
    {
        // Array for output.
        $relatedIds = [];

        // Get terms.
        $terms = wp_get_object_terms( $this->parentId, $this->taxonomy );

        // Error fetching terms.
        if ( is_wp_error( $this ) ) {
            return [];
        }

        // Extract IDs stored as term names.
        foreach( $terms as $term )
        {
            /** @var $term \WP_Term */
            $relatedIds[] = $term->name;
        }

        // Return array.
        return $relatedIds;
    }

    /**
     * TaxonomyPivoter constructor.
     *
     * @param $postId int Parent post ID.
     * @param $taxonomy string The taxonomy name through which relationships are established.
     * @param $context string The data type of the pivoted IDs ('post' or 'user').
     */
    public function __construct( $postId, $taxonomy, $context = 'post' )
    {
        $this->parentId = $postId;
        $this->taxonomy = $taxonomy;

        if ( 'post' == $context ) {
            $this->pivotPosts();
        }

        if ( 'user' == $context ) {
            $this->pivotUsers();
        }
    }
}