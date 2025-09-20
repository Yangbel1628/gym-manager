<?php include('db_connect.php'); ?>
<div class="container-fluid">
	<div class="col-lg-12">
		<div class="row mb-4 mt-4">
			<div class="col-md-12"></div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<b>Session Schedules</b>
						<span class="float:right">
							<button class="btn btn-primary btn-sm float-right" id="new_schedule">
								<i class="fa fa-plus"></i> New Entry
							</button>
						</span>
					</div>
					<div class="card-body">
						<hr>
						<div id="calendar"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<style>
	td {
		vertical-align: middle !important;
	}

	td p {
		margin: unset;
	}

	a.fc-daygrid-event,
	a.fc-timegrid-event {
		cursor: pointer;
	}
</style>

<script>
	document.addEventListener('DOMContentLoaded', function() {
		var calendarEl = document.getElementById('calendar');

		$('#new_schedule').click(function() {
			uni_modal('New Schedule', 'manage_schedule.php');
		});

		start_load();

		// Fetch schedule from ajax.php
		$.ajax({
			url: 'ajax.php?action=get_schecdule',
			method: 'POST',
			dataType: 'json',
			success: function(resp) {
				var events = [];
				if (resp && resp.length > 0) {
					resp.forEach(function(item) {
						events.push({
							id: item.id,
							title: item.name,
							start: item.date_from + 'T' + item.time_from,
							end: item.date_to + 'T' + item.time_to
						});
					});
				}

				var calendar = new FullCalendar.Calendar(calendarEl, {
					initialView: 'dayGridMonth',
					headerToolbar: {
						left: 'prev,next today',
						center: 'title',
						right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
					},
					navLinks: true,
					editable: false,
					selectable: true,
					nowIndicator: true,
					dayMaxEvents: true,
					events: events,
					eventClick: function(info) {
						uni_modal('Manage Schedule Details', 'manage_schedule.php?id=' + info.event.id);
					}
				});

				calendar.render();
			},
			error: function(err) {
				console.error("AJAX error:", err);
			},
			complete: function() {
				end_load();
			}
		});
	});
</script>