# Taxonomy Pivoter
The TaxonomyPivoter class utilizes WordPress's taxonomy system to establish custom relationships between:

- posts and posts
- posts and users

### How It Works

- Each type of relationship should have its own taxonomy name; for example: **books-authors**
- The presence of an object's ID (post or user) as a taxonomy term indicates a relationship between
  the two pivoting objects. 
- The absence of the ID means there is no relationship.

### Example Use Cases

**Post to Post**

Suppose in your environment you have two custom post types representing **books** and **authors** data.
If we want to link authors to books (or the opposite), we implement make a contextually 
meaningful taxonomy name like **books-authors**, register the taxonomy name to our **book** 
post type, at which point we'd be ready to utilize the TaxonomyPivoter:

```php
<?php
// Declare the post ID for the object we'll be adding relationships.
$bookPostId = 123;
// Declare the taxonomy name through which these relationships exists. 
$taxonomy = 'books-authors';
// Create the pivoter class.
$pivoter = new \WPOrbit\Taxonomies\Pivoter\TaxonomyPivoter( $bookPostId, $taxonomy );

// Add the authors. 
$pivoter->addRelation( $authorPostId );
// Is an author already attached?
$pivoter->hasRelation( $authorPostId );
// Remove author from book.
$pivoter->removeRelation( $authorPostId );
```

**Post to User**

One implementation I've used from this is a "Post Likes" system to registered WordPress users.
A taxonomy name like: **post-likes** is registered to posts. Then we implement the TaxonomyPivoter 
in a 'user' context:

```php
<?php
// Some post...
$postId = 100;
// Some user...
$userId = 5;

// Make the pivoter, specifying 'user' context.
$pivoter = new \WPOrbit\Taxonomies\Pivoter\TaxonomyPivoter( $postId, 'post-likes', 'user' );
// Now we add a user ID, to make $userId "like" this post. 
$pivoter->addRelation( $userId );
// Does user like this post?
$likesPost = $pivoter->hasRelation( $userId );
// Unlike the post.
$pivoter->removeRelation( $userId );
```

