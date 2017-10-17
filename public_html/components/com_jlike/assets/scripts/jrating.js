js = jQuery.noConflict();

js(document).ready(function(){
 	js(".basic_new").jRating({
	  step:true,
	  canRateAgain : true,
	  nbRates : 50
	});

	// Retrive user rating and allow to edit
	 js(".basic").jRating({
	  step:true,
	  type : 'small',
	  canRateAgain : true,
	  nbRates : 50
	});

	 js(".basic_readonly").jRating({
	  step:true,
	  type : 'small',
	  canRateAgain : true,
	  nbRates : 50,
	  isDisabled : true
	});
	 js(".basic_avg").jRating({
	  step:true,
	  type : 'small',
	  canRateAgain : true,
	  nbRates : 50,
	  isDisabled : true
	});
});

function addRatingStars()
{
	js(".basic_new").jRating({
	  step:true,
	  type : 'small',
	  canRateAgain : true,
	  nbRates : 50
	});
	 js(".basic").jRating({
	  step:true,
	  type : 'small',
	  canRateAgain : true,
	  nbRates : 50
	});

	 js(".basic_readonly").jRating({
	  step:true,
	  type : 'small',
	  canRateAgain : true,
	  nbRates : 50,
	  isDisabled : true
	});
	 js(".basic_avg").jRating({
	  step:true,
	  type : 'small',
	  canRateAgain : true,
	  nbRates : 50,
	  isDisabled : true
	});
}
/**
setDecending sort comment in decending order of date (Latest)
*/
function setDecending(likecontainerid)
{
	techjoomla.jQuery("#"+likecontainerid + " #lioldest").removeClass("active");
	techjoomla.jQuery("#"+likecontainerid + " #lilatest").addClass("active");
	techjoomla.jQuery("#"+likecontainerid +  ' #sorting').val(1);
	showAllComments(1,1);
}
/**Ascending sort comment in asending order of date (Olderst)
*/
function setAscending(likecontainerid)
{
	techjoomla.jQuery("#"+likecontainerid + " #lilatest").removeClass("active");
	techjoomla.jQuery("#"+likecontainerid + " #lioldest").addClass("active");
	techjoomla.jQuery("#"+likecontainerid + ' #sorting').val(2);
	showAllComments(2,1);
}
