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
        if ($this->hasAttribute($args, "{$prefix}_atts")) {
            $atts = $this->mergeAttributes($atts, $this->getAttribute($args, "{$prefix}_atts"));
        }
        if ($this->hasAttribute($args, "{$prefix}_atts_{$depth}")) {
            $atts = $this->mergeAttributes($atts, $this->getAttribute($args, "{$prefix}_atts_{$depth}"));
        }
        if ($index !== -1 && $this->hasAttribute($args, "{$prefix}_atts_order_{$index}")) {
            $atts = $this->mergeAttributes($atts, $this->getAttribute($args, "{$prefix}_atts_order_{$index}"));
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
        $classes = explode(' ', $this->getAttribute($atts, 'class'));

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
        if ($this->hasAttribute($args, $prop)) {
            $temp_classes = $this->getAttribute($args, $prop);
            if(is_string($temp_classes)) {
                $temp_classes = explode(' ', $temp_classes);
            }
            $classes = array_merge($classes, $temp_classes);
        }

        return $classes;
    }

    /**
     * Utility function to merge attributes with an exception on handling the classes to avoid overriding
     * 
     * @param  String  $atts        Original attributes
     * @param  object  $new_atts    New attributes
     *
     * @return array                Modified attributes array
     */
    public function mergeAttributes($atts, $new_atts) {
        $new_classes = $this->arrayOrStringClasses('class', $new_atts);

        if(!empty($new_classes)) {
            $original_classes = $this->arrayOrStringClasses('class', $atts);
            $new_classes = array_merge($original_classes, $new_classes);

            // Applying this fix everywhere even though there's only
            // a user interface to add classes to links so far
            $classes = $this->fixWordPressClasses($new_classes);

            $new_atts['class'] = implode(' ', $classes);
        }

        $atts = array_merge($atts, $new_atts);

        return $atts;
    }

    /**
     * Checks if an attribute exists on a variable regardless of whether it's an object or array
     *
     * @param   object|array    $variable The variable to check (object or array)
     * @param   string          $attribute The attribute/key name to check for
     * 
     * @return  bool            True if the attribute exists, false otherwise
     */
    function hasAttribute($variable, $attribute) {
        return (is_object($variable) && property_exists($variable, $attribute)) || 
               (is_array($variable) && array_key_exists($attribute, $variable));
    }

    /**
     * Gets an attribute from a variable regardless of whether it's an object or array
     * 
     * @param   object|array    $variable The variable to get the attribute from (object or array)
     * @param   string          $attribute The attribute/key name to retrieve
     * @param   mixed           $default The default value to return if attribute doesn't exist
     *  
     * @return  mixed           The value of the attribute or the default value if not found
     */
    function getAttribute($variable, $attribute, $default = null) {
        if (is_object($variable)) {
            return property_exists($variable, $attribute) ? $variable->$attribute : $default;
        } elseif (is_array($variable)) {
            return array_key_exists($attribute, $variable) ? $variable[$attribute] : $default;
        }
        
        return $default;
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
