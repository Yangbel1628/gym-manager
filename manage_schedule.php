<?php include 'db_connect.php'; ?>
<?php
if (isset($_GET['id'])) {
	$qry = $conn->query("SELECT * FROM schedules WHERE id=" . $_GET['id']);
	foreach ($qry->fetch_array() as $k => $v) {
		$$k = $v;
	}
	$dow_arr = !empty($dow) ? explode(',', $dow) : [];
} else {
	$dow_arr = [];
}
?>
<div class="container-fluid">
	<form id="manage-schedule">
		<input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">

		<div class="form-group">
			<label>Member</label>
			<select name="member_id" class="custom-select select2" required>
				<option value=""></option>
				<?php
				$members = $conn->query("SELECT *, concat(lastname,', ',firstname,' ',middlename) as name FROM members ORDER BY name ASC");
				while ($row = $members->fetch_array()):
				?>
					<option value="<?php echo $row['id'] ?>" <?php echo isset($member_id) && $member_id == $row['id'] ? 'selected' : '' ?>>
						<?php echo ucwords($row['name']) ?>
					</option>
				<?php endwhile; ?>
			</select>
		</div>

		<div class="form-group">
			<label>Days of Week</label>
			<select name="dow[]" class="custom-select select2" multiple required>
				<?php
				$days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
				for ($i = 0; $i < 7; $i++):
				?>
					<option value="<?php echo $i ?>" <?php echo in_array($i, $dow_arr) ? 'selected' : '' ?>>
						<?php echo $days[$i] ?>
					</option>
				<?php endfor; ?>
			</select>
		</div>

		<div class="form-group">
			<label>Date From</label>
			<input type="date" name="date_from" class="form-control" value="<?php echo isset($date_from) ? $date_from : '' ?>" required>
		</div>

		<div class="form-group">
			<label>Date To</label>
			<input type="date" name="date_to" class="form-control" value="<?php echo isset($date_to) ? $date_to : '' ?>" required>
		</div>

		<div class="form-group">
			<label>Time From</label>
			<input type="time" name="time_from" class="form-control" value="<?php echo isset($time_from) ? $time_from : '' ?>" required>
		</div>

		<div class="form-group">
			<label>Time To</label>
			<input type="time" name="time_to" class="form-control" value="<?php echo isset($time_to) ? $time_to : '' ?>" required>
		</div>

		<button class="btn btn-primary">Save</button>
	</form>
</div>

<script>
	$('.select2').select2({
		placeholder: "Please Select",
		width: "100%"
	});

	$('#manage-schedule').submit(function(e) {
		e.preventDefault();
		start_load();
		$.ajax({
			url: 'ajax.php?action=save_schedule',
			method: 'POST',
			data: new FormData(this),
			cache: false,
			contentType: false,
			processData: false,
			success: function(resp) {
				try {
					resp = JSON.parse(resp);
					if (resp.status == 1) {
						alert_toast(resp.msg, 'success');
						$('#uni_modal').modal('hide');
						setTimeout(function() {
							location.reload();
						}, 1500);
					} else {
						alert_toast(resp.msg, 'danger');
					}
				} catch (err) {
					console.error(resp, err);
					alert_toast("Something went wrong", 'danger');
				}
				end_load();
			},
			error: function(err) {
				console.error(err);
				alert_toast("AJAX error", 'danger');
				end_load();
			}
		});
	});
</script>