<?php
	defined ('_JEXEC' ) or die ( 'Restricted access' );
$params = JComponentHelper::getParams('com_jlike');
switch ($params->get('jlike_rating_image')) {
    case "stars":
       $rate_image = 'stars.png';
        break;
    case "heart":
       $rate_image = 'heart.png';
        break;
    default:
         $rate_image = 'stars.png';
}


?>
<script type="text/javascript">
/************************************************************************
*************************************************************************
@Name :       	jRating - jQuery Plugin
@Revison :    	3.1
@Date : 		13/08/2013
@Author:     	 ALPIXEL - (www.myjqueryplugins.com - www.alpixel.fr)
@License :		 Open Source - MIT License : http://www.opensource.org/licenses/mit-license.php

**************************************************************************
*************************************************************************/
var user_id =<?php echo $loged_user; ?>;

(function($) {
	$.fn.jRating = function(op) {
		var defaults = {
			/** String vars **/
			bigStarsPath : '<?php echo JUri::root();?>components/com_jlike/assets/css/icons/<?php echo $rate_image;?>', // path of the icon stars.png
			smallStarsPath : '<?php echo JUri::root();?>components/com_jlike/assets/css/icons/small/<?php echo $rate_image;?>', // path of the icon small.png
			phpPath : '<?php echo JUri::root();?>index.php?option=com_jlike&task=SaveNewRating&tmpl=component&format=row', // path of the php file jRating.php
			type : 'big', // can be set to 'small' or 'big'
			//count_By order: '<?php echo $this->allowRating;?>',

			/** Boolean vars **/
			step:false, // if true,  mouseover binded star by star,
			isDisabled:false, // if true, user could not rate
			showRateInfo: true, // show rates informations when cursor moves onto the plugin
			canRateAgain : false, // if true, the user could rates {nbRates} times with jRating.. Default, 1 time
			sendRequest: true, // send values to server

			/** Integer vars **/
			length:<?php echo $params->get('rating_length');?>, // number of star to display
			decimalLength : 0, // number of decimals.
			rateMax : <?php echo $params->get('rating_length');?>, // maximal rate - integer from 0 to 9999 (or more)
			rateInfosX : 0, //-45, // relative position in X axis of the info box when mouseover
			rateInfosY : 0, // 5, // relative position in Y axis of the info box when mouseover
			nbRates : 1, // 1,
			param_allowRating: <?php echo $this->urldata->jlike_allow_rating;?>,

			/** Functions **/
			onSuccess : null, // Fires when the server response is ok
			onError : null, // Fires when the server response is not ok
			onClick: null // Fires when clicking on a star
		};

		if(this.length>0)
		return this.each(function() {
			/*vars*/
			var opts = $.extend(defaults, op),
			newWidth = 0,
			starWidth = 0,
			starHeight = 0,
			bgPath = '',
			hasRated = false,
			globalWidth = 0,
			nbOfRates = opts.nbRates;

			if($(this).hasClass('jDisabled') || opts.isDisabled)
				var jDisabled = true;
			else
				var jDisabled = false;

			getStarWidth();
			$(this).height(starHeight);

			var average = parseFloat($(this).attr('data-average')), // get the average of all rates
			idBox = parseInt($(this).attr('data-id')), // get the id of the box
			widthRatingContainer = starWidth*opts.length, // Width of the Container
			widthColor = average/opts.rateMax*widthRatingContainer, // Width of the color Container

			quotient =
			$('<div>',
			{
				'class' : 'jRatingColor',
				css:{
					width:widthColor
				}
			}).appendTo($(this)),

			average =
			$('<div>',
			{
				'class' : 'jRatingAverage',
				css:{
					width:0,
					top:- starHeight
				}
			}).appendTo($(this)),

			 jstar =
			$('<div>',
			{
				'class' : 'jStar',
				css:{
					width:widthRatingContainer,
					height:starHeight,
					top:- (starHeight*2),
					background: 'url('+bgPath+') repeat-x'
				}
			}).appendTo($(this));


			$(this).css({width: widthRatingContainer,overflow:'hidden',zIndex:1,position:'relative'});

			if(!jDisabled)
			$(this).unbind().bind({
				mouseenter : function(e){
					var realOffsetLeft = findRealLeft(this);
					var relativeX = e.pageX - realOffsetLeft;
					if (opts.showRateInfo)
					var tooltip =
					$('<p>',{
						'class' : 'jRatingInfos',
						html : getNote(relativeX)+' <span class="maxRate">/ '+opts.rateMax+'</span>',
						css : {
							top: (e.pageY + opts.rateInfosY),
							left: (e.pageX + opts.rateInfosX)
						}
					}).appendTo('body').show();
				},
				mouseover : function(e){
					$(this).css('cursor','pointer');
				},
				mouseout : function(){
					$(this).css('cursor','default');
					if(hasRated) average.width(globalWidth);
					else average.width(0);
				},
				mousemove : function(e){
					var realOffsetLeft = findRealLeft(this);
					var relativeX = e.pageX - realOffsetLeft;
					if(opts.step) newWidth = Math.floor(relativeX/starWidth)*starWidth + starWidth;
					else newWidth = relativeX;
					average.width(newWidth);
					if (opts.showRateInfo)
					$("p.jRatingInfos")
					.css({
						left: (e.pageX + opts.rateInfosX)
					})
					.html(getNote(newWidth) +' <span class="maxRate">/ '+opts.rateMax+'</span>');
				},
				mouseleave : function(){
					$("p.jRatingInfos").remove();
				},
				click : function(e){
                    var element = this;

					/*set vars*/
					hasRated = true;
					globalWidth = newWidth;
					nbOfRates--;

					if(!opts.canRateAgain || parseInt(nbOfRates) <= 0) $(this).unbind().css('cursor','default').addClass('jDisabled');

					if (opts.showRateInfo) $("p.jRatingInfos").fadeOut('fast',function(){$(this).remove();});
					e.preventDefault();
					var rate = getNote(newWidth);
					average.width(newWidth);


					/** ONLY FOR THE DEMO, YOU CAN REMOVE THIS CODE **/
						/*$('.datasSent p').html('<strong>idBox : </strong>'+idBox+'<br /><strong>rate : </strong>'+rate+'<br /><strong>action :</strong> rating');
						$('.serverResponse p').html('<strong>Loading...</strong>');*/
					/** END ONLY FOR THE DEMO **/

					if(opts.onClick) opts.onClick( element, rate );

					if(opts.sendRequest) {
						if(!parseInt(user_id))
						{
							alert('<?php echo JText::_('COM_JLIKE_LOGIN_TO_COMMENT'); ?>');
							return false;
						}

						<?php if($this->urldata->jlike_allow_rating == 1) { ?>
							<?php if(!$this->allowRating) { ?>
								alert('<?php echo JText::_('COM_JLIKE_LOGIN_TO_RATING_REVIEWS'); ?>');
								return false;
							<?php } ?>
						<?php } ?>

						$(".reviewButton").removeAttr('disabled');
						$.post(opts.phpPath,{
								idBox : idBox,
								user_rating : rate,
								action : 'rating',
								element_id : <?php echo $this->urldata->cont_id ;?>,
								element : '<?php echo $this->urldata->element; ?>',
								url : '<?php echo $this->urldata->url; ?>',
								title : '<?php echo $this->urldata->title; ?>',
								rating_upto:<?php echo $params->get('rating_length');?>,
								plg_name:'<?php echo $this->urldata->plg_name;?>'
							},
							function(data) {
								if(!data.error)
								{
									/** ONLY FOR THE DEMO, YOU CAN REMOVE THIS CODE **/
										//$('.serverResponse p').html(data.server);
									/** END ONLY FOR THE DEMO **/


									/** Here you can display an alert box,
										or use the jNotify Plugin :) http://www.myqjqueryplugins.com/jNotify
										exemple :	*/
									if(opts.onSuccess) opts.onSuccess( element, rate );
								}
								else
								{

									/** ONLY FOR THE DEMO, YOU CAN REMOVE THIS CODE **/
										//$('.serverResponse p').html(data.server);
									/** END ONLY FOR THE DEMO **/

									/** Here you can display an alert box,
										or use the jNotify Plugin :) http://www.myqjqueryplugins.com/jNotify
										exemple :	*/
									if(opts.onError) opts.onError( element, rate );
								}
							},
							'json'
						);
					}

				}
			});

			function getNote(relativeX) {
				var noteBrut = parseFloat((relativeX*100/widthRatingContainer)*parseInt(opts.rateMax)/100);
				var dec=Math.pow(10,parseInt(opts.decimalLength));
				var note = Math.round(noteBrut*dec)/dec;
				return note;
			};

			function getStarWidth(){
				switch(opts.type) {
					case 'small' :
						starWidth = 12; // width of the picture small.png
						starHeight = 10; // height of the picture small.png
						bgPath = opts.smallStarsPath;
					break;
					default :
						starWidth = 23; // width of the picture stars.png
						starHeight = 20; // height of the picture stars.png
						bgPath = opts.bigStarsPath;
				}
			};

			function findRealLeft(obj) {
			  if( !obj ) return 0;
			  return obj.offsetLeft + findRealLeft( obj.offsetParent );
			};
		});

	}
})(jQuery);

</script>
