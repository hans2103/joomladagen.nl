<?php

$emails_config=array(
'message_body'=>"<!DOCTYPE html>
<html >
<head>
	<title></title>
</head>
<body>
	<table cellpadding=\"10\" class=\"demoTable\" style=
	\"background-color: #f7f7f7;\" width=\"80%\">
		<tbody>
			<tr>
				<td>[QR_CODE]<br><br>
				<span style=\"color: #999999; font-size: 14px;\">Booking ID: </span> [TICKET_ID]<br>
				</td>

				<td colspan=\"2\"><span style=\"font-size: 20px;\">Hi <br>
				Welcome to [EVENT_NAME]</span><br>
				<br>
				[EVENT_DESCRIPTION]
<br/><br/>
<span style=\"color: #999999; font-size: 14px;\">Name: </span> [NAME]
<br/><br/><span style=\"color: #999999; font-size: 14px;\">Ticket Type:  </span>[TICKET_TYPE]
.</td>
			</tr>
			<tr>
				<td><img alt=\"editors\" src=
				\"https://cdn0.iconfinder.com/data/icons/business-economics/64/calendar_date_icon_organizer_plan_planning_schedule_approved_event_month-128.png\"
				style=
				\"max-width: 58px; display: block; height: auto !important;\"><br>
<span style=\"color: #999999; font-size: 14px;\">Event Start Date</span><br>
				[ST_DATE]</td>

				<td><img alt=\"cleaning\" src=
				\"https://cdn0.iconfinder.com/data/icons/business-economics/64/calendar_date_icon_organizer_plan_planning_schedule_approved_event_month-128.png\"
				style=
				\"max-width: 58px; display: block; height: auto !important;\"><br>
<span style=\"color: #999999; font-size: 14px;\">Event END Date</span>
<br>
				[EN_DATE]</td>

				<td><img alt=\"editors\" src=
				\"https://cdn0.iconfinder.com/data/icons/hotel-services-3/64/hotel_icon_signboard_five_star_service_luxury_board-128.png\"
				style=
				\"max-width: 58px; display: block; height: auto !important;\"><br>
<span style=\"color: #999999; font-size: 14px;\">Event Location</span>
<br>

				[EVENT_LOCATION]</td>
			</tr>

			<tr>
				<td colspan=\"3\"><strong>**Note:- Show this email at the time of
				event</strong></td>
			</tr>
		</tbody>
	</table>
</body>
</html>"
);

?>
