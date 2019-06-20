<?php

namespace WpMailCatcher;

class ScreenOptions
{
    private $options = [];
    private $helpTabs = [];
    private $currentScreen = null;
    private $pageHook = null;

    public function __construct($pageHook)
    {
        $this->pageHook = add_action('load-' . $pageHook, [$this, 'addToScreen']);
    }

    public function newOption($type, $args)
    {
        $this->options[] = [
            'type' => $type,
            $args
        ];
    }

    public function addHelpTab($title, $content)
    {
        $this->helpTabs[] = [
            'id' => $this->pageHook . count($this->helpTabs),
            'title' => $title,
            'content' => $content
        ];
    }

    /**
     * Forces WP to handle dynamic column visibility tick boxes
     * inside of "Screen Options" tab. Because they're singletons
     * initialising them now won't cause any extra overhead
     */
    private function initTables()
    {
        MailAdminTable::getInstance();
    }

    public function addToScreen()
    {
        $this->currentScreen = get_current_screen();
        $this->initTables();

        foreach ($this->helpTabs as $helpTab) {
            $this->currentScreen->add_help_tab($helpTab);
        }

        foreach ($this->options as $option) {
            add_screen_option($option['type'], $option);
        }
    }
}
