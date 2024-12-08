<?php if(!defined('WPINC')) { die; }

/*  UTILITIES FOR ALL POST TYPES
	----------------------------- */

// Author & date/time meta
function util_postsAuthorDateTime($timePre = 'Posted', $authorPre = 'by') {
	printf(__($timePre, 'exonym').' %1$s %2$s',
		/* the time the post was published */
		'<time class="entry-time" datetime="' . get_the_time('Y-m-d') . '" itemprop="datePublished">' . get_the_time(get_option('date_format')) . '</time>',
		/* the author of the post */
		'<span class="entry-byline">' . __($authorPre, 'exonym') . '</span> <span class="entry-author" itemprop="author" itemscope itemptype="http://schema.org/Person">' . get_the_author_link( get_the_author_meta( 'ID' ) ) . '</span>'
	);
}

// List out categories
function util_postsCats($catPre = 'Filed under') {
	printf(__($catPre, 'exonym' ) . ': %1$s' , get_the_category_list(', '));
}

// List out tags
function util_postsTags($tagsPre = 'Tags:') {
	the_tags(__($tagsPre, 'exonym' ) . '</span> ', ', ', '');
}

// List comment counts
function util_postsCommentCount(
	$noComms = '<span>No</span> Comments',
	$oneComm = '<span>One</span> Comment',
	$manyComms = '<span>%</span> Comments'
) {
	comments_number(__($noComms, 'exonym'), __($oneComm, 'exonym'), __($manyComms, 'exonym'));
}

// Integrate wp_page_navi
function util_postsPageNav() {
	global $wp_query;
	$bignum = 999999999;
	if ($wp_query->max_num_pages <= 1)
		return;
	echo '<nav class="nnavigation nav-pages" role="navigation">';
	echo paginate_links(array(
		'base'         => str_replace( $bignum, '%#%', esc_url( get_pagenum_link($bignum) ) ),
		'format'       => '',
		'current'      => max( 1, get_query_var('paged') ),
		'total'        => $wp_query->max_num_pages,
		'prev_text'    => '&lt;',
		'next_text'    => '&gt;',
		'type'         => 'list',
		'end_size'     => 3,
		'mid_size'     => 3
	) );
	echo '</nav>';
}

// Format for the Blog Navigation
function util_postsPostNav() {
	// Don't print empty markup if there's nowhere to navigate.
	$previous = (is_attachment()) ? get_post(get_post()->post_parent) : get_adjacent_post(false, '', true);
	$next     = get_adjacent_post(false, '', false);

	if (!$next && ! $previous) {
		return;
	}
	?>
	<nav class="navigation nav-post" role="navigation">
		<div class="nav-links">
			<?php
			previous_post_link('<div class="nav-previous">%link</div>', _x( '<span class="meta-nav">&larr;</span>&nbsp;%title', 'Previous post link', 'exonym' ) );
			next_post_link('<div class="nav-next">%link</div>', _x( '%title&nbsp;<span class="meta-nav">&rarr;</span>', 'Next post link', 'exonym' ) );
			?>
		</div>
	</nav>
	<?php
}