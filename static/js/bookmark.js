(function(){

	var spe_iframe = function(){
		that = this;
		this.config = {
			lang : {
				M00001 : 'SinaPic-Ext by <a href="http://inn-studio.com" target="_blank" title="Professional WordPress developer">INN STUDIO</a>'
			}
			
		}
		var cache = {}
		this.init = function(){
			var DQ = function(s){return document.querySelector(s)},
				DC = function(n){return document.createElement(n)},
				DG = function(n){return document.getElementById(n)};
			if(DQ('#spe-container')) return false;
			
			cache.$iframe = DC('iframe');
			cache.$iframe.setAttribute('id','spe-iframe');
			cache.$iframe.setAttribute('src',DG('spe-js').getAttribute('data-home-url'));
			
			cache.$container = DC('div');
			cache.$container.setAttribute('id','spe-container');
				
			cache.$title = DC('h3');
			cache.$title.setAttribute('id','spe-title');
			cache.$title.innerHTML = that.config.lang.M00001;
			
			cache.$container.appendChild(cache.$title);
			cache.$container.appendChild(cache.$iframe);
			
			document.body.appendChild(cache.$container);
		}

	}
	var o_spe = new spe_iframe();
	o_spe.init();
})();