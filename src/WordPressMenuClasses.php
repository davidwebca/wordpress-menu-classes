<?php

namespace davidwebca\WordPress;

class WordPressMenuClasses
{
    /**
     * Creates the class instance and attaches WordPress hooks
     *
     * @return void
     */
    public function __construct()
    {
        add_filter('nav_menu_link_attributes', [$this, 'navMenuLinkAttributes'], 10, 4);
        add_filter('nav_menu_item_attributes', [$this, 'navMenuItemAttributes'], 10, 4);
        add_filter('nav_menu_submenu_attributes', [$this, 'navSubmenuAttributes'], 10, 3);
    }

    /**
     * Add custom attributes or classes to links in wp_nav_menu
     *
     * @param  object   $atts   wp_nav_menu attributes object
     * @param  object   $item   wp_nav_menu item object
     * @param  object   $args   wp_nav_menu args object
     * @param  int      $depth  Depth of the current menu item being parsed.
     *                          his is an index, thus starts with 0 for the root level.
     * @return object           Modified attributes for the current link
     */
    public function navMenuLinkAttributes($atts, $item, $args, $depth)
    {
        $index = $item->menu_order;

        $atts = $this->buildAttributes('a', $atts, $args, $depth, $index);
        $atts = $this->buildClasses('a', $atts, $args, $depth, $index);

        return $atts;
    }

    /**
     * Add custom classes to lis in wp_nav_menu
     *
     * @param  array    $classes    CSS classes added to the li of our menu.
     * @param  object   $item       wp_nav_menu item object
     * @param  object   $args       wp_nav_menu args object
     * @param  int      $depth      Depth of the current menu item being parsed.
     *                              This is an index, thus starts with 0 for the root level.
     * @return array                Modified classes for the current li element
     */
    public function navMenuItemAttributes($atts, $item, $args, $depth)
    {
        $index = $item->menu_order;

        $atts = $this->buildAttributes('li', $atts, $args, $depth, $index);
        $atts = $this->buildClasses('li', $atts, $args, $depth, $index);

        return $atts;
    }

    /**
     * Add custom classes and attributes to ul.submenu in wp_nav_menu
     *
     * @param  object   $atts   wp_nav_menu attributes object
     * @param  object   $args   wp_nav_menu args object
     * @param  int      $depth      Depth of the current submenu being parsed.
     *                              This is an index, thus starts with 0 for the root level.
     * @return object               Modified attributes for the current ul submenu
     */
    public function navSubmenuAttributes($atts, $args, $depth)
    {
        $atts = $this->buildAttributes('submenu', $atts, $args, $depth);
        $atts = $this->buildClasses('submenu', $atts, $args, $depth);

        return $atts;
    }

    /**
     * Utility function to build the attributes
     *
     * @param  String   $prefix  The prefix (a, li, submenu)
     * @param  object   $atts    wp_nav_menu attributes object
     * @param  object   $args    wp_nav_menu args object
     * @param  int      $depth   Depth of the current submenu being parsed.
     * @param  int      $index   The index of menu order, -1 is considered absent
     *
     * @return object            Modified attributes for the current element
     */
    public function buildAttributes($prefix, $atts, $args, $depth, $index = -1) {
        if (property_exists($args, "{$prefix}_atts")) {
            $atts = array_merge($atts, $args->{"{$prefix}_atts"});
        }
        if (property_exists($args, "{$prefix}_atts_{$depth}")) {
            $atts = array_merge($atts, $args->{"{$prefix}_atts_{$depth}"});
        }
        if ($index !== -1 && property_exists($args, "{$prefix}_atts_order_{$index}")) {
            $atts = array_merge($atts, $args->{"{$prefix}_atts_order_{$index}"});
        }

        if (empty($atts['class'])) {
            $atts['class'] = '';
        }
        return $atts;
    }



    /**
     * Utility function to build the classes
     *
     * @param  String   $prefix  The prefix (a, li, submenu)
     * @param  object   $atts    wp_nav_menu attributes object
     * @param  object   $args    wp_nav_menu args object
     * @param  int      $depth   Depth of the current submenu being parsed.
     * @param  int      $index   The index of menu order, -1 is considered absent
     *
     * @return object            Modified attributes for the current element
     */
    public function buildClasses($prefix, $atts, $args, $depth, $index = -1) {
        $classes = explode(' ', $atts['class']);

        $classes = array_merge($classes, $this->arrayOrStringClasses("{$prefix}_class", $args));
        $classes = array_merge($classes, $this->arrayOrStringClasses("{$prefix}_class_$depth", $args));
        $classes = array_merge($classes, $this->arrayOrStringClasses("{$prefix}_class_order_$depth", $args));

        // Applying this fix everywhere even though there's only
        // a user interface to add classes to links so far
        $classes = $this->fixWordPressClasses($classes);

        $atts['class'] = implode(' ', $classes);

        return $atts;
    }

    /**
     * Utility function to accept array or string classes
     *
     * @param  String  $prop     The property to check on our custom arguments (ex.: ul_class, li_class_order_1)
     * @param  object  $args     wp_nav_menu args object
     *
     * @return object            Modified attributes for the current element
     */
    public function arrayOrStringClasses($prop, $args) {
        $classes = [];
        if (property_exists($args, $prop)) {
            $temp_classes = $args->{$prop};
            if(is_string($temp_classes)) {
                $temp_classes = explode(' ', $temp_classes);
            }
            $classes = array_merge($classes, $temp_classes);
        }

        return $classes;
    }

    /**
     * Fix for tailwindcss classes that include ":" (colon)
     * Enter triple underscore hover___text-primary instaed of hover:text-primary
     *
     * Some filters provided so that you can customize your own replacements,
     * passed directly to preg_replace so supports array replacements as well.
     *
     * WordPress trac following the issue of escaping CSS classes:
     * @link https://core.trac.wordpress.org/ticket/33924
     */
    public function fixWordPressClasses($classes)
    {
        $patterns = apply_filters('nav_menu_css_class_unescape_patterns', '/___/');
        $replacements = apply_filters('nav_menu_css_class_unescape_replacements', ':');
        $classes = array_map(function ($cssclass) use ($patterns, $replacements) {
            return preg_replace($patterns, $replacements, $cssclass);
        }, $classes);

        return $classes;
    }
}

// phpcs:disable
if (function_exists('add_action')) {
    new WordPressMenuClasses();
}
