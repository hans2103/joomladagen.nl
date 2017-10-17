<?php 

$emails_config=array(
'message_body'=>"<table class=\"maintab\" >
<tbody>
<tr>
<td class=\"img barcode\" > </td>
<td >
<table class=\"info\"  cellspacing=\"1\" cellpadding=\"4\">
<tbody>
<tr >
<td class=\"jtlabel\" >Event</td>
<td class=\"val event\" >[EVENT_NAME]</td>
</tr>
<tr >
<td class=\"jtlabel\" >Date + Time</td>
<td class=\"val\" >[ST_DATE] to [EN_DATE]</td>
</tr>
<tr >
<td class=\"jtlabel\" >Booking ID</td>
<td class=\"val valbig\" >[TICKET_ID]</td>
</tr>
<tr >
<td class=\"jtlabel\" ><span>Name</span></td>
<td class=\"val valbig\" >[NAME]</td>
</tr>
<tr >
<td class=\"jtlabel\" >Location</td>
<td class=\"val\" >[EVENT_LOCATION]</td>
</tr>
<tr >
<td class=\"jtlabel\" >Order</td>
<td class=\"val valsmall\" >[BUYER_NAME]</span> on [BOOKING_DATE]</td>
</tr>
</tbody>
</table>
</td>
<td >
<table class=\"info2\"  cellspacing=\"1\" cellpadding=\"1\">
<tbody>
<tr>
<td class=\"img\" >[EVENT_IMAGE]</td>
</tr>
<tr>
<td class=\"jtlabel1\" >Ticket Type:<br /> [TICKET_TYPE]</td>
</tr>
<tr>
<td class=\"img\" >[QR_CODE]</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>"
);

?>