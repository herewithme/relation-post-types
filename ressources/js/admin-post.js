jQuery(function() {
	// Relation post type
	var rptApi = {
		init : function() {
			// Init the function for the boxes
			this.setupInputWithDefaultTitle();
			this.attachQuickSearchListeners();
			this.postTypeDivs();
		},
		postTypeDivs : function() {
			jQuery('.categorydivrpt').each(function() {
				var this_id = jQuery(this).attr('id'), taxonomyParts, taxonomy, settingName;

				// Setup vars for the tabs changing
				taxonomyParts = this_id.split('-');
				taxonomyParts.shift();
				taxonomy = taxonomyParts.join('-');
				settingName = taxonomy + '_tab';

				// Tab changing
				jQuery('a', '#posttype-' + taxonomy + '-tabs').click(function(e) {
					e.preventDefault();
					var t = jQuery(this).attr('href');
					jQuery(this).parent().addClass('tabs').siblings('li').removeClass('tabs');
					jQuery('#posttype-' + taxonomy + '-tabs').siblings('.tabs-panel').hide();
					jQuery(t).show();

					if ('#posttype-' + taxonomy + '-all' == t)
						deleteUserSetting(settingName);
					else
						setUserSetting(settingName, 'pop');
				});

				// Check and uncheck the boxes in the tabs
				jQuery('#tabs-panel-posttype-' + taxonomy + '-most-recent :checkbox, #tabs-panel-posttype-' + taxonomy + '-search :checkbox').live('click', function(e) {
					var t = jQuery(this), c = t.is(':checked'), id = t.val();
					if (id && t.parents('#relationsdiv-' + taxonomy).length)
						jQuery('#in-' + taxonomy + '-' + id).prop('checked', c);
				});

				// Hide the spinner
				jQuery('img.waiting', jQuery(this)).hide();

			});
			// end postdiv
		},
		attachQuickSearchListeners : function() {
			var searchTimer;

			// Get the search fields
			jQuery('.quick-search').keypress(function(e) {
				// Current object
				var t = jQuery(this);

				// If the user press enter key make the serach
				if (13 == e.which) {
					rptApi.updateQuickSearchResults(t);
					return false;
				}

				// Clear the time out
				if (searchTimer)
					clearTimeout(searchTimer);

				// Make a timer and search every 400 milliseconds
				searchTimer = setTimeout(function() {
					rptApi.updateQuickSearchResults(t);
				}, 400);
			}).attr('autocomplete', 'off');
		},
		updateQuickSearchResults : function(input) {
			// Setup vars
			var panel, params, minSearchLength = 2, q = input.val();

			// check if minimum letter requirement
			if (q.length < minSearchLength)
				return;

			// Get current panel
			panel = input.parents('.tabs-panel');

			// Make the params for the ajax query
			params = {
				'action' : 'posttype-quick-search',
				'response-format' : 'markup',
				'q' : q,
				'type' : input.attr('name'),
				'post_id' : jQuery('#post_ID').val()
			};

			// Show the waiting spinner
			jQuery('img.waiting', panel).show();

			// Make the query
			jQuery.post(ajaxurl, params, function(menuMarkup) {
				// Call the displayer a the end of the query
				rptApi.processQuickSearchQueryResponse(menuMarkup, params, panel);
			});
		},
		processQuickSearchQueryResponse : function(resp, req, panel) {
			// Get the results
			var $items = jQuery('<div>').html(resp).find('li');

			// Check if items
			if (!$items.length) {
				// Display error message
				jQuery('.categorychecklist', panel).html('<li><p>' + rpt.noItems + '</p></li>');
			} else {
				jQuery('.categorychecklist', panel).html($items);
			}
			jQuery('img.waiting', panel).hide();
		},
		setupInputWithDefaultTitle : function() {
			var name = 'input-with-default-title';

			jQuery('.' + name).each(function() {
				var $t = jQuery(this), title = $t.attr('title'), val = $t.val();
				$t.data(name, title);

				if ('' == val)
					$t.val(title);
				else if (title == val)
					return;
				else
					$t.removeClass(name);
			}).focus(function() {
				var $t = jQuery(this);
				if ($t.val() == $t.data(name))
					$t.val('').removeClass(name);
			}).blur(function() {
				var $t = jQuery(this);
				if ('' == $t.val())
					$t.addClass(name).val($t.data(name));
			});
		}
	}
	rptApi.init();
}); 