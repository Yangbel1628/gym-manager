<?php include 'db_connect.php' ?>
<?php
if (isset($_GET['id'])) {
	$qry = $conn->query("SELECT *, CONCAT(lastname, ', ', firstname, ' ', middlename) AS name FROM members WHERE id=" . $_GET['id']);
	if ($qry->num_rows > 0) {
		$row = $qry->fetch_assoc();
		foreach ($row as $k => $v) {
			$$k = $v;
		}
	}
}
?>

<div class="container-fluid">
	<div class="row">
		<div class="col-md-4">
			<p><b>Name:</b> <?php echo isset($name) ? ucwords($name) : '' ?></p>
			<p><b>Gender:</b> <?php echo isset($gender) ? ucwords($gender) : '' ?></p>
			<p><b>Email:</b> <?php echo isset($email) ? $email : '' ?></p>
			<p><b>Contact:</b> <?php echo isset($contact) ? $contact : '' ?></p>
			<p><b>Address:</b> <?php echo isset($address) ? $address : '' ?></p>
		</div>
		<div class="col-md-8">
			<h5><b>Membership Plan List</b></h5>
			<table class="table table-condensed table-bordered">
				<thead>
					<tr>
						<th>Plan</th>
						<th>Package</th>
						<th>Start</th>
						<th>End</th>
						<th>Status</th>
					</tr>
				</thead>
				<tbody>
					<?php
					if (isset($id)) {
						$paid = $conn->query("SELECT r.*, pl.plan, pa.package 
                                              FROM registration_info r 
                                              INNER JOIN plans pl ON pl.id = r.plan_id 
                                              INNER JOIN packages pa ON pa.id = r.package_id 
                                              WHERE r.member_id = $id");
						if ($paid->num_rows > 0) {
							while ($row = $paid->fetch_assoc()):
					?>
								<tr>
									<td><?php echo $row['plan'] . ' mo/s.' ?></td>
									<td><?php echo $row['package'] ?></td>
									<td><?php echo date("M d, Y", strtotime($row['start_date'])) ?></td>
									<td><?php echo date("M d, Y", strtotime($row['end_date'])) ?></td>
									<td>
										<?php if ($row['status'] == 1): ?>
											<?php if (strtotime(date('Y-m-d')) <= strtotime($row['end_date'])): ?>
												<span class="badge badge-success">Active</span>
											<?php else: ?>
												<span class="badge badge-danger">Expired</span>
											<?php endif; ?>
										<?php else: ?>
											<span class="badge badge-secondary">Closed</span>
										<?php endif; ?>
									</td>
								</tr>
					<?php
							endwhile;
						} else {
							echo '<tr><td colspan="5" class="text-center">No membership plans found.</td></tr>';
						}
					}
					?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<div class="modal-footer display">
	<div class="row">
		<div class="col-md-12">
			<button class="btn float-right btn-secondary" type="button" data-dismiss="modal">Close</button>
		</div>
	</div>
</div>

<style>
	p {
		margin: unset;
	}

	#uni_modal .modal-footer {
		display: none;
	}

	#uni_modal .modal-footer.display {
		display: block;
	}
</style>