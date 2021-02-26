<?php
class Names_to_Tags extends Plugin {

   /** @var PluginHost $host */
   private $host;

   function about() {
		return array(1.0,
			"Assigns tags based on names in title",
			"fox");
	}

   function init($host) {
		$this->host = $host;

		$host->add_hook($host::HOOK_ARTICLE_FILTER, $this);
		$host->add_hook($host::HOOK_PREFS_EDIT_FEED, $this);
		$host->add_hook($host::HOOK_PREFS_SAVE_FEED, $this);
	}

   function hook_prefs_edit_feed($feed_id) {
      $enabled_feeds = $this->host->get_array($this, "enabled_feeds");
      ?>
         <header><?= __("Names to Tags") ?></header>

         <section>
            <fieldset>
               <label class="checkbox">
                  <?= \Controls\checkbox_tag("names_to_tags_enabled", in_array($feed_id, $enabled_feeds)) ?>
                  <?= __('Assign tags based on names') ?>
               </label>
            </fieldset>
         </section>
      </section>
   <?php
   }

   function hook_prefs_save_feed($feed_id) {
		$enabled_feeds = $this->host->get_array($this, "enabled_feeds");

		$enable = checkbox_to_sql_bool($_POST["names_to_tags_enabled"] ?? "");
		$key = array_search($feed_id, $enabled_feeds);

		if ($enable) {
			if ($key === false) {
				array_push($enabled_feeds, $feed_id);
			}
		} else {
			if ($key !== false) {
				unset($enabled_feeds[$key]);
			}
		}

		$this->host->set($this, "enabled_feeds", $enabled_feeds);
	}

   function hook_article_filter($article) {

      if (in_array($article["feed"]["id"], $this->host->get_array($this, "enabled_feeds"))) {
         $matches = [];

         if (preg_match("/([A-Z]\w+ [A-Z]\w+)/", $article["title"], $matches)) {
            array_push($article["tags"], mb_strtolower($matches[0]));
         }
      }

      return $article;
   }

   function api_version() {
		return 2;
	}

}