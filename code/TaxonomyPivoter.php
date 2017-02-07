<?php
namespace WPOrbit\Taxonomies;

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
     * Establish post relationships.
     * @return $this
     */
    public function pivotPosts()
    {
        $this->pivotContext = 'post';
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

    public function getIds()
    {
        return wp_get_object_terms( $this->parentId, $this->taxonomy );
    }

    public function __construct( $postId, $taxonomy )
    {
        $this->parentId = $postId;
        $this->taxonomy = $taxonomy;
    }
}