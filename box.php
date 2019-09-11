<?php
/*
Name: Thesis Post Box Plus
Author: WP-Social-Proof.Com
Description: Smarter Post Box that handles Read More links for Excerpts too
Version: 1.1
Requires: 2.84
Class: thesis_post_box_plus
Docs: https://wp-social-proof.com/
License: MIT

Copyright Shivanand Sharma

*/
class thesis_post_box_plus extends thesis_box {
    public $_class = 'thesis_post_box';
	public $type = 'rotator';
	public $dependents = array(
		'thesis_post_headline',
		'thesis_post_date',
		'thesis_post_author',
		'thesis_post_author_avatar',
		'thesis_post_author_description',
		'thesis_post_edit',
		'thesis_post_content',
		'thesis_post_excerpt_plus',
		'thesis_post_num_comments',
		'thesis_post_categories',
		'thesis_post_tags',
		'thesis_post_image',
		'thesis_post_thumbnail');
	public $children = array(
		'thesis_post_headline',
		'thesis_post_author',
		'thesis_post_edit',
		'thesis_post_content');

	protected function translate() {
		$this->title = $this->name = __('Post Box +', 'wpsp_post_box_plus');
	}

	protected function html_options() {
		global $thesis;
		$html = $thesis->api->html_options(array(
			'div' => 'div',
			'section' => 'section',
			'article' => 'article'), 'div');
		unset($html['id']);
		$html['class']['tooltip'] = sprintf(__('This box already contains a %1$s, <code>post_box</code>. If you wish to add an additional %1$s, you can do that here. Separate multiple %1$ses with spaces.%2$s', 'wpsp_post_box_plus'), $thesis->api->base['class'], __($thesis->api->strings['class_note'], 'wpsp_post_box_plus'));
		return array_merge($html, array(
			'wp' => array(
				'type' => 'checkbox',
				'label' => __($thesis->api->strings['auto_wp_label'], 'wpsp_post_box_plus'),
				'tooltip' => __($thesis->api->strings['auto_wp_tooltip'], 'wpsp_post_box_plus'),
				'options' => array(
					'auto' => __($thesis->api->strings['auto_wp_option'], 'wpsp_post_box_plus'))),
			'schema' => $thesis->api->schema->select()));
	}

	public function html($args = array()) {
		global $thesis, $wp_query, $post; #wp
		extract($args = is_array($args) ? $args : array());
		$classes = array();
		$tab = str_repeat("\t", $depth = !empty($depth) ? $depth : 0);
		$post_count = !empty($post_count) ? $post_count : false;
		$html = !empty($this->options['html']) ? $this->options['html'] : 'div';
		if (!empty($this->options['class']))
			$classes[] = trim($thesis->api->esc($this->options['class']));
		if (empty($post_count) || $post_count == 1)
			$classes[] = 'top';
		if (!empty($this->options['wp']['auto']))
			$classes = is_array($wp = get_post_class()) ? $classes + $wp : $classes;
		$schema = !empty($this->options['schema']) ? $this->options['schema'] : false;
		$hook = trim($thesis->api->esc(!empty($this->options['_id']) ?
			$this->options['_id'] : (!empty($this->options['hook']) ?
			$this->options['hook'] : '')));
		do_action("thesis_hook_before_post_box_$hook", $post_count);
		!empty($hook) ? $thesis->api->hook("hook_before_$hook", $post_count) : '';
		echo "$tab<$html", ($wp_query->is_404 ? '' : " id=\"post-$post->ID\""), ' class="post_box', (!empty($classes) ? ' '. trim(esc_attr(implode(' ', $classes))) : ''), '"', ($schema ? ' itemscope itemtype="'. esc_url($thesis->api->schema->types[$schema]). '"' : ''), ">\n"; #wp
		if (is_singular() && $schema)
			echo "$tab\t<meta itemscope itemprop=\"mainEntityOfPage\" itemType=\"https://schema.org/WebPage\" itemid=\"", get_permalink(), "\" />\n";
		do_action("thesis_hook_post_box_{$hook}_top", $post_count);
		!empty($hook) ? $thesis->api->hook("hook_top_$hook", $post_count) : '';
		$this->rotator(array_merge($args, array('depth' => $depth + 1, 'schema' => $schema)));
		do_action("thesis_hook_post_box_{$hook}_bottom", $post_count);
		!empty($hook) ? $thesis->api->hook("hook_bottom_$hook", $post_count) : '';
		echo "$tab</$html>\n";
		do_action("thesis_hook_after_post_box_$hook", $post_count);
		!empty($hook) ? $thesis->api->hook("hook_after_$hook", $post_count) : '';
	}
}

class thesis_post_excerpt_plus extends thesis_box {
    public $_class = 'thesis_post_excerpt';
	protected function translate() {
		$this->title = __('Excerpt', 'wpsp_post_box_plus');
		$this->read_more = __('Read more', 'wpsp_post_box_plus');
	}

protected function html_options() {
	global $thesis;
	$html = $thesis->api->html_options();
	unset($html['id']);
	return $html;
}

protected function options() {
	return array(
		'style' => array(
			'type' => 'radio',
			'label' => __('Excerpt Type', 'wpsp_post_box_plus'),
			'tooltip' => __('The Thesis enhanced excerpt strips <code>h1</code>-<code>h4</code> tags and images, in addition to the typical items removed by WordPress.', 'wpsp_post_box_plus'),
			'options' => array(
				'wpsp_post_box_plus' => __('Thesis enhanced (recommended)', 'wpsp_post_box_plus'),
				'wp' => __('WordPress default', 'wpsp_post_box_plus')),
			'default' => 'wpsp_post_box_plus'),
		'ellipsis' => array(
			'type' => 'radio',
			'label' => __('Excerpt Ellipsis', 'wpsp_post_box_plus'),
			'options' => array(
				'bracket' => __('Show ellipsis with a bracket at the end of the excerpt', 'wpsp_post_box_plus'),
				'no_bracket' => __('Show ellipsis without a bracket at the end of the excerpt', 'wpsp_post_box_plus'),
				'none' => __('Do not show an ellipsis', 'wpsp_post_box_plus')),
			'default' => 'bracket'),
		'read_more_show' => array(
			'type' => 'checkbox',
			'label' => __('Read More Link', 'wpsp_post_box_plus'),
			'options' => array(
				'show' => __('Show &ldquo;Read more&rdquo; link at the end of an excerpt', 'wpsp_post_box_plus'))));
}

public function html($args = array()) {
	global $thesis, $post;
	extract($args = is_array($args) ? $args : array());
	$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
	// !isset['first'] or empty['first']['second']
	if (empty($this->options['read_more_show']) || empty($this->options['read_more_show']['show']))
		$thesis->wp->filter($this->_class, array('wpautop' => false));
	elseif (!empty($this->options['read_more_show']) && !empty($this->options['read_more_show']['show'])) {
        if(empty($this->options['style'])) { // [Post Box (Archive) → Excerpt → Options → Thesis enhanced (recommended)]
            add_filter('thesis_trim_excerpt', array($this, 'more')); 
        }
        else { // [Post Box (Archive) → Excerpt → Options → WordPress default]
            add_filter('get_the_excerpt', array($this, 'more'));
        }
    }
    $content = empty($this->options['style']) ? ( has_excerpt() ? $thesis->api->trim_excerpt($thesis->api->efa(get_the_excerpt())) : $thesis->api->trim_excerpt($thesis->api->efa($post->post_content)) ) : get_the_excerpt();
	echo
		"$tab<div class=\"post_content post_excerpt", (!empty($this->options['class']) ? ' '. trim(esc_attr($this->options['class'])) : ''), '"', (!empty($schema) ? ' itemprop="description"' : ''), ">\n",
		apply_filters($this->_class,
			!empty($this->options['read_more_show']) && !empty($this->options['read_more_show']['show']) ?
				wpautop($content, false) :
				$content),
		"$tab</div>\n";
	if (!empty($this->options['read_more_show']) && !empty($this->options['read_more_show']['show'])) {
        if(empty($this->options['style'])) { // [Post Box (Archive) → Excerpt → Options → Thesis enhanced (recommended)]
            remove_filter('thesis_trim_excerpt', array($this, 'more')); 
        }
        else { // [Post Box (Archive) → Excerpt → Options → WordPress default]
            remove_filter('get_the_excerpt', array($this, 'more'));
        }
	}
}

public function more($in = '', $read_more = false) {
	global $thesis, $post;
	$out = '';
	$in = str_replace(array('[...]', '[…]', '[&hellip;]'), '', preg_replace('/&hellip;*$/', '', trim($in)));
	if (!$read_more) {
		if (!isset($this->options['ellipsis']))
			$out .= ' [&hellip;]';
		elseif (isset($this->options['ellipsis'])) {
			if ($this->options['ellipsis'] == 'no_bracket')
				$out .= '&hellip;';
			elseif ($this->options['ellipsis'] == 'none')
				$out .= '';
		}
	}
	// When in the Thesis enhanced mode, this method will be called twice:
	// Once for the excerpt filter and again for the trim_excerpt API method.
    static $track = 1;
    
    if ( ! empty($this->options['read_more_show']) && ! empty($this->options['read_more_show']['show']) ) {
		$read_more = is_array($post_meta = get_post_meta($post->ID, '_thesis_post_content', true)) && !empty($post_meta['read_more']) ?
			$post_meta['read_more'] :
			apply_filters("{$this->_class}_read_more", $this->read_more);
		$out .= "\n<a class=\"excerpt_read_more\" href=\"". get_permalink(). "\">". trim($thesis->api->efh($read_more)). "</a>";
	}
	$track++;
	return (!empty($in) ? rtrim($in, ',.?!:;') : ''). $out;
	}
}