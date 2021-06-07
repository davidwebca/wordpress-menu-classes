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
        add_filter("nav_menu_link_attributes", [$this, "navMenuLinkAttributes"], 10, 4);
        add_filter("nav_menu_css_class", [$this, "navMenuCSSClass"], 10, 4);
        add_filter("nav_menu_submenu_css_class", [$this, "navMenuSubmenuCSSClass"], 10, 3);
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
        if (property_exists($args, "link_atts")) {
            $atts = array_merge($atts, $args->link_atts);
        }
        if (property_exists($args, "link_atts_$depth")) {
            $atts = array_merge($atts, $args->{"link_atts_$depth"});
        }

        if (empty($atts["class"])) {
            $atts["class"] = "";
        }

        $classes = explode(" ", $atts["class"]);


        if (property_exists($args, "a_class")) {
            $arr_classes = explode(" ", $args->a_class);
            $classes = array_merge($classes, $arr_classes);
        }
        if (property_exists($args, "a_class_$depth")) {
            $arr_classes = explode(" ", $args->{"a_class_$depth"});
            $classes = array_merge($classes, $arr_classes);
        }

        // Applying this here too just in case, but there's
        // no default user interface to add a class directly to a link in the menu
        // (classes are applied to li elements by default)
        $classes = $this->fixWordPressClasses($classes);

        $atts["class"] = implode(" ", $classes);

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
    public function navMenuCSSClass($classes, $item, $args, $depth)
    {
        if (property_exists($args, "li_class")) {
            $arr_classes = explode(" ", $args->li_class);
            $classes = array_merge($classes, $arr_classes);
        }
        if (property_exists($args, "li_class_$depth")) {
            $arr_classes = explode(" ", $args->{"li_class_$depth"});
            $classes = array_merge($classes, $arr_classes);
        }

        $classes = $this->fixWordPressClasses($classes);

        return $classes;
    }

    /**
     * Add custom classes to ul.sub-menu in wp_nav_menu
     *
     * @param  array    $classes    CSS classes added to all ul submenu of our menu.
     * @param  object   $args       wp_nav_menu args object
     * @param  int      $depth      Depth of the current menu item being parsed.
     *                              This is an index, thus starts with 0 for the root level.
     * @return object               Modified attributes for the current ul submenu
     */
    public function navMenuSubmenuCSSClass($classes, $args, $depth)
    {
        if (property_exists($args, "submenu_class")) {
            $arr_classes = explode(" ", $args->submenu_class);
            $classes = array_merge($classes, $arr_classes);
        }

        if (property_exists($args, "submenu_class_$depth")) {
            $arr_classes = explode(" ", $args->{"submenu_class_$depth"});
            $classes = array_merge($classes, $arr_classes);
        }

        // Applying this here too just in case, but there's
        // no default user interface to add a class to a submenu
        $classes = $this->fixWordPressClasses($classes);

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
    public function fixWordPressClasses($classes) {
        $patterns = apply_filters(
            "nav_menu_css_class_unescape_patterns",
            "/___/"
        );
        $replacements = apply_filters(
            "nav_menu_css_class_unescape_replacements",
            ":"
        );
        $classes = array_map(function ($cssclass) use (
            $patterns,
            $replacements
        ) {
            return preg_replace($patterns, $replacements, $cssclass);
        },
        $classes);

        return $classes;
    }
}

// phpcs:disable
if (function_exists("add_action")) {
    new WordPressMenuClasses();
}
