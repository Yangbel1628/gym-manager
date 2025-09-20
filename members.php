<?php include('db_connect.php'); ?>

<div class="container-fluid">
	<style>
		input[type=checkbox] {
			transform: scale(1.5);
			padding: 10px;
		}

		td {
			vertical-align: middle !important;
		}

		td p {
			margin: unset;
		}

		img {
			max-width: 100px;
			max-height: 150px;
		}
	</style>

	<div class="col-lg-12">
		<div class="row mb-4 mt-4"></div>

		<div class="row">
			<div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<b>Member List</b>
						<span>
							<button class="btn btn-primary btn-block btn-sm col-sm-2 float-right" type="button" id="new_member">
								<i class="fa fa-plus"></i> New
							</button>
						</span>
					</div>
					<div class="card-body">
						<table class="table table-bordered table-condensed table-hover">
							<colgroup>
								<col width="5%">
								<col width="15%">
								<col width="20%">
								<col width="20%">
								<col width="20%">
								<col width="20%">
							</colgroup>
							<thead>
								<tr>
									<th class="text-center">#</th>
									<th>Member ID</th>
									<th>Name</th>
									<th>Email</th>
									<th>Contact</th>
									<th class="text-center">Action</th>
								</tr>
							</thead>
							<tbody>
								<?php
								$i = 1;
								$members = $conn->query("SELECT *, concat(lastname,', ',firstname,' ',middlename) as name FROM members ORDER BY concat(lastname,', ',firstname,' ',middlename) ASC");
								while ($row = $members->fetch_assoc()):
								?>
									<tr>
										<td class="text-center"><?php echo $i++ ?></td>
										<td><b><?php echo $row['member_id'] ?></b></td>
										<td><b><?php echo ucwords($row['name']) ?></b></td>
										<td><b><?php echo $row['email'] ?></b></td>
										<td><b><?php echo $row['contact'] ?></b></td>
										<td class="text-center">
											<button class="btn btn-sm btn-outline-primary view_member" data-id="<?php echo $row['id'] ?>">View</button>
											<button class="btn btn-sm btn-outline-primary edit_member" data-id="<?php echo $row['id'] ?>">Edit</button>
											<button class="btn btn-sm btn-outline-danger delete_member" data-id="<?php echo $row['id'] ?>">Delete</button>
										</td>
									</tr>
								<?php endwhile; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	$(document).ready(function() {
		$('table').DataTable();

		// New Member
		$('#new_member').click(function() {
			uni_modal("<i class='fa fa-plus'></i> New Member", "manage_member.php", 'mid-large');
		});

		// View Member
		$(document).on('click', '.view_member', function() {
			let id = $(this).data('id');
			uni_modal("<i class='fa fa-id-card'></i> Member Details", "view_member.php?id=" + id, 'large');
		});

		// Edit Member
		$(document).on('click', '.edit_member', function() {
			let id = $(this).data('id');
			uni_modal("<i class='fa fa-edit'></i> Manage Member Details", "manage_member.php?id=" + id, 'mid-large');
		});

		// Delete Member
		$(document).on('click', '.delete_member', function() {
			let id = $(this).data('id');
			_conf("Are you sure to delete this member?", "delete_member", [id]);
		});
	});

	function delete_member(id) {
		start_load();
		$.ajax({
			url: 'ajax.php?action=delete_member',
			method: 'POST',
			data: {
				id: id
			},
			success: function(resp) {
				let res = (typeof resp === 'object') ? resp : JSON.parse(resp);
				if (res.status == 1) {
					alert_toast(res.msg, 'success');
					end_load();
					setTimeout(() => location.reload(), 800);
				} else {
					alert_toast(res.msg, 'danger');
					end_load();
				}
			},
			error: function(err) {
				console.error(err);
				alert_toast("AJAX error", 'danger');
				end_load();
			}
		});
	}
</script>