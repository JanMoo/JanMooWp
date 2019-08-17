var NelioFPPostSearcher = {

	formatSearchResult: function(item, container, query, escapeMarkup) {
		var markup=[];
		window.Select2.util.markMatch(item.title, query.term, markup, escapeMarkup);
		var title = markup.join("");
		return  '<div class="result-content">'+
					'<div class="result-image">'+item.thumbnail+"</div>"+
					'<div class="result-item">'+
						'<div class="result-title">'+title+"</div>"+
						'<div class="result-author">by '+item.author+"</div>"+
						'<div class="result-date">'+item.date.toLocaleString()+"</div>"+
						'<div class="result-type">'+item.type+"</div>"+
						'<div class="result-status">'+item.status+"</div>"+
					'</div>'+
				'</div>';
	},

	formatSelectionResult: function(item, container) {
		container.data( "post-id", item.id );
		return item.title;
	},

	buildSearcher: function(elem, type, filter) {
		elem.select2({
			ajax: {
				url: ajaxurl,
				dataType: 'json',
				type:"POST",
				data: function (term) {
					var res = {
						term: term,
						action: "neliofp_search_posts",
						type: type,
					};
					if ( type == 'page-or-post-or-latest' ) {
						res.type = 'page-or-post';
						res.include_latest_posts = 'yes';
					}
					return res;
				},
				results: function (data) {
					if ( filter !== undefined )
						data = filter( elem, data );
					return { results:data };
				}
			},
			formatResult: NelioFPPostSearcher.formatSearchResult,
			formatSelection: NelioFPPostSearcher.formatSelectionResult,
			dropdownAutoWidth: true,
			dropdownCssClass: "bigdrop",
			escapeMarkup: function (m) { return m; }
		});
		NelioFPPostSearcher.setPlaceholder(elem);
	},

	getInfo: function(elem) {
		var chosen = elem.parent().find('.select2-chosen');
		if ( chosen.hasClass('select2-default') )
			return false;
		return { value:elem.attr('value'), label:chosen.html() };
	},

	setPlaceholder: function(elem) {
		var placeholder = elem.data('placeholder');
		var chosen = elem.parent().find('.select2-chosen');
		chosen.html(placeholder);
		chosen.parent().addClass('select2-default');
	},

};

