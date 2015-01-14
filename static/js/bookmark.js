(function(){

	var spe_iframe = function(){
		that = this;
		this.config = {
			lang : {
				M00001 : 'SinaPic-Ext by <a href="http://inn-studio.com" target="_blank" title="Professional WordPress developer">INN STUDIO</a>'
			}
			
		}
		var cache = {
			moving : false,
			moving_left : 0,
			moving_top : 0
		}
		this.init = function(){
			var DQ = function(s){return document.querySelector(s)},
				DC = function(n){return document.createElement(n)},
				DG = function(n){return document.getElementById(n)};
			if(DQ('#spe-container')) return false;
			
			/** 
			 * @todo comet
			 */
			var fragment = Math.random();

			$iframe = DC('iframe');
			$iframe.setAttribute('id','spe-iframe');
			$iframe.setAttribute('src',DG('spe-js').getAttribute('data-home-url') + '#' + fragment);
			
			$container = DC('div');
			$container.setAttribute('id','spe-container');
				
			$title = DC('h3');
			$title.setAttribute('id','spe-title');
			$title.innerHTML = that.config.lang.M00001;
			
			/** FUck ie678 */
			$title.onmousedown 			= mousedown;
			document.onmousemove 		= mousemove;
			$title.onmouseup 			= mouseup;
			
			$container.appendChild($title);
			$container.appendChild($iframe);
			
			document.body.appendChild($container);
			/** 
			 * mousedown
			 */
			function mousedown(e){
				e.preventDefault();
				cache.curr_left = $container.offsetLeft;
				$container.style.left = cache.curr_left + 'px';
				$container.style.right = '';
				$container.style.opacity = 0.7;
				
				cache.clientX = e.clientX;
				
				cache.moving =  true;
			}
			/** 
			 * mousemove
			 */
			function mousemove(e){
				e.preventDefault();
				if(!cache.moving) return false;
				$container.style.left = $container.offsetLeft - cache.moving_left + 'px';
				
				cache.moving_left = cache.last_clientX - e.clientX;
				
				cache.last_clientX = e.clientX;
			}
			/** 
			 * mouseup
			 */
			function mouseup(e){
				e.preventDefault();
				cache.moving = false;
				
				$container.style.opacity = '';
			}
		}

	}
	var o_spe = new spe_iframe();
	o_spe.init();
})();