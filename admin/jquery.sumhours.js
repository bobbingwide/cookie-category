/**
 * Copyright Bobbing Wide 2012
 * My first jquery code
 * based on http://www.pengoworks.com/workshop/jquery/calculation/calculation.plugin.htm
 *
 */

 
(function($){
 
     // automatically update the "#totalSum" field every time
	  // the values are changes via the keyup event
         $("input[name^=';hrs']").sum("keyup", "#total_sum");

})(jQuery);
