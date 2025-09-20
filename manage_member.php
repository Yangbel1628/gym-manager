<?php
include('db_connect.php');

$id = $_GET['id'] ?? '';
$member = [];

if (!empty($id)) {
	$qry = $conn->query("SELECT * FROM members WHERE id = '" . $conn->real_escape_string($id) . "'");
	if ($qry->num_rows > 0) {
		$member = $qry->fetch_assoc();
	}
}
?>

<div class="container-fluid">
	<form id="member-form">
		<input type="hidden" name="id" value="<?= $member['id'] ?? '' ?>">

		<div class="form-group">
			<label>Member ID</label>
			<input type="text" class="form-control" name="member_id" value="<?= $member['member_id'] ?? '' ?>" readonly>
		</div>

		<div class="form-group">
			<label>First Name</label>
			<input type="text" class="form-control" name="firstname" value="<?= $member['firstname'] ?? '' ?>" required>
		</div>

		<div class="form-group">
			<label>Middle Name</label>
			<input type="text" class="form-control" name="middlename" value="<?= $member['middlename'] ?? '' ?>">
		</div>

		<div class="form-group">
			<label>Last Name</label>
			<input type="text" class="form-control" name="lastname" value="<?= $member['lastname'] ?? '' ?>" required>
		</div>

		<div class="form-group">
			<label>Email</label>
			<input type="email" class="form-control" name="email" value="<?= $member['email'] ?? '' ?>">
		</div>

		<div class="form-group">
			<label>Contact</label>
			<input type="text" class="form-control" name="contact" value="<?= $member['contact'] ?? '' ?>">
		</div>

		<div class="form-group">
			<label>Gender</label>
			<select class="form-control" name="gender">
				<option value="Male" <?= (isset($member['gender']) && $member['gender'] == 'Male') ? 'selected' : '' ?>>Male</option>
				<option value="Female" <?= (isset($member['gender']) && $member['gender'] == 'Female') ? 'selected' : '' ?>>Female</option>
			</select>
		</div>

		<div class="form-group">
			<label>Address</label>
			<textarea class="form-control" name="address"><?= $member['address'] ?? '' ?></textarea>
		</div>

		<!-- Add plan_id, package_id, trainer_id if needed -->

		<button type="submit" class="btn btn-primary btn-block">Save</button>
	</form>
</div>

<script>
	$(document).ready(function() {
		$('#member-form').submit(function(e) {
			e.preventDefault();
			start_load(); // optional: show loading spinner

			$.ajax({
				url: 'ajax.php?action=save_member',
				method: 'POST',
				data: $(this).serialize(),
				dataType: 'json', // important to parse JSON response
				success: function(resp) {
					end_load();
					if (resp.status == 1) {
						alert_toast(resp.msg, 'success');
						setTimeout(function() {
							location.reload(); // refresh table
						}, 800);
					} else {
						alert_toast(resp.msg, 'danger');
					}
				},
				error: function(err) {
					console.error(err);
					end_load();
					alert_toast("AJAX error", 'danger');
				}
			});
		});
	});
</script>