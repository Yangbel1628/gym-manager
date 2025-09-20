<?php include 'db_connect.php' ?>
<?php
// Fetch existing membership if editing
if (isset($_GET['id'])) {
	$qry = $conn->query("SELECT * FROM registration_info WHERE id=" . $_GET['id'])->fetch_array();
	foreach ($qry as $k => $v) {
		$$k = $v;
	}
}
?>

<div class="container-fluid">
	<form action="" id="manage-membership">
		<div id="msg"></div>
		<input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">

		<!-- Member Selection -->
		<div class="form-group">
			<label class="control-label">Member</label>
			<select name="member_id" required="required" class="custom-select select2">
				<option value=""></option>
				<?php
				// Exclude members with active memberships except the current one (for editing)
				$member_condition = isset($member_id) ? "OR id = '$member_id'" : "";
				$qry = $conn->query("SELECT *, CONCAT(lastname,', ',firstname,' ',middlename) AS name 
                                    FROM members 
                                    WHERE id NOT IN (SELECT member_id FROM registration_info WHERE status = 1) $member_condition
                                    ORDER BY CONCAT(lastname,', ',firstname,' ',middlename) ASC");
				while ($row = $qry->fetch_assoc()):
				?>
					<option value="<?php echo $row['id'] ?>" <?php echo isset($member_id) && $member_id == $row['id'] ? 'selected' : '' ?>>
						<?php echo ucwords($row['name']) ?>
					</option>
				<?php endwhile; ?>
			</select>
		</div>

		<!-- Plan Selection -->
		<div class="form-group">
			<label class="control-label">Plan</label>
			<select name="plan_id" required="required" class="custom-select select2">
				<option value=""></option>
				<?php
				$qry = $conn->query("SELECT * FROM plans ORDER BY plan ASC");
				while ($row = $qry->fetch_assoc()):
				?>
					<option value="<?php echo $row['id'] ?>" <?php echo isset($plan_id) && $plan_id == $row['id'] ? 'selected' : '' ?>>
						<?php echo ucwords($row['plan']) ?>
					</option>
				<?php endwhile; ?>
			</select>
		</div>

		<!-- Package Selection -->
		<div class="form-group">
			<label class="control-label">Package</label>
			<select name="package_id" required="required" class="custom-select select2">
				<option value=""></option>
				<?php
				$qry = $conn->query("SELECT * FROM packages ORDER BY package ASC");
				while ($row = $qry->fetch_assoc()):
				?>
					<option value="<?php echo $row['id'] ?>" <?php echo isset($package_id) && $package_id == $row['id'] ? 'selected' : '' ?>>
						<?php echo ucwords($row['package']) ?>
					</option>
				<?php endwhile; ?>
			</select>
		</div>

		<!-- Trainer Selection -->
		<div class="form-group">
			<label class="control-label">Trainer</label>
			<select name="trainer_id" class="custom-select select2">
				<option value=""></option>
				<?php
				$qry = $conn->query("SELECT * FROM trainers ORDER BY name ASC");
				while ($row = $qry->fetch_assoc()):
				?>
					<option value="<?php echo $row['id'] ?>" <?php echo isset($trainer_id) && $trainer_id == $row['id'] ? 'selected' : '' ?>>
						<?php echo ucwords($row['name']) ?>
					</option>
				<?php endwhile; ?>
			</select>
		</div>

		<!-- Submit Button -->
		<div class="form-group text-right">
			<button class="btn btn-primary" type="submit">Save Membership</button>
		</div>
	</form>
</div>

<script>
	$(document).ready(function() {
		$('.select2').select2({
			placeholder: 'Select Here',
			width: '100%'
		});

		$('#manage-membership').submit(function(e) {
			e.preventDefault();
			start_load();

			$.ajax({
				url: 'ajax.php?action=save_membership',
				method: 'POST',
				data: $(this).serialize(),
				success: function(resp) {
					// Ensure response is trimmed and checked
					resp = resp.trim();
					if (resp == 1) {
						alert_toast("Membership saved successfully.", 'success');
						setTimeout(function() {
							location.reload();
						}, 1000);
					} else if (resp == 2) {
						$('#msg').html('<div class="alert alert-danger">Selected member already has an active membership.</div>');
						end_load();
					} else {
						$('#msg').html('<div class="alert alert-danger">' + resp + '</div>');
						end_load();
					}
				},
				error: function(err) {
					console.log(err);
					end_load();
				}
			})
		});
	});
</script>