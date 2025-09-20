<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

class Action
{
	private $db;

	public function __construct()
	{
		include 'db_connect.php'; // database connection
		$this->db = $conn;
	}

	public function __destruct()
	{
		$this->db->close();
	}

	private function json($data)
	{
		if (ob_get_length()) ob_end_clean();
		header('Content-Type: application/json');
		echo json_encode($data);
		exit;
	}

	// ---------------- LOGIN ----------------
	function login()
	{
		$username = $_POST['username'] ?? '';
		$password = $_POST['password'] ?? '';
		$qry = $this->db->query("SELECT * FROM users WHERE username='" . $this->db->real_escape_string($username) . "' AND password='" . md5($password) . "'");
		if ($qry->num_rows > 0) {
			foreach ($qry->fetch_assoc() as $key => $value) {
				if ($key != 'password') $_SESSION['login_' . $key] = $value;
			}
			if ($_SESSION['login_type'] != 1) {
				session_unset();
				$this->json(['status' => 2, 'msg' => 'Not admin']);
			}
			$this->json(['status' => 1, 'msg' => 'Login successful']);
		} else {
			$this->json(['status' => 0, 'msg' => 'Invalid credentials']);
		}
	}

	function login2()
	{
		$username = $_POST['username'] ?? $_POST['email'] ?? '';
		$password = $_POST['password'] ?? '';
		$qry = $this->db->query("SELECT * FROM users WHERE username='" . $this->db->real_escape_string($username) . "' AND password='" . md5($password) . "'");
		if ($qry->num_rows > 0) {
			foreach ($qry->fetch_assoc() as $key => $value) {
				if ($key != 'password') $_SESSION['login_' . $key] = $value;
			}
			$this->json(['status' => 1, 'msg' => 'Login successful']);
		} else {
			$this->json(['status' => 0, 'msg' => 'Invalid credentials']);
		}
	}

	// ---------------- LOGOUT ----------------
	function logout()
	{
		session_destroy();
		session_unset();
		$this->json(['status' => 1, 'msg' => 'Logged out']);
	}

	function logout2()
	{
		session_destroy();
		session_unset();
		$this->json(['status' => 1, 'msg' => 'Logged out']);
	}

	// ---------------- USERS ----------------
	function save_user()
	{
		$id = $_POST['id'] ?? '';
		$name = $_POST['name'] ?? '';
		$username = $_POST['username'] ?? '';
		$type = $_POST['type'] ?? '';
		$password = $_POST['password'] ?? '';

		$data = "name='" . $this->db->real_escape_string($name) . "',
                 username='" . $this->db->real_escape_string($username) . "',
                 type='" . $this->db->real_escape_string($type) . "'";
		if (!empty($password)) $data .= ", password='" . md5($password) . "'";
		if (empty($id)) {
			$save = $this->db->query("INSERT INTO users SET $data");
		} else {
			$save = $this->db->query("UPDATE users SET $data WHERE id='" . $this->db->real_escape_string($id) . "'");
		}
		$this->json($save ? ['status' => 1, 'msg' => 'User saved'] : ['status' => 0, 'msg' => $this->db->error]);
	}

	function delete_user()
	{
		$id = $_POST['id'] ?? '';
		if (!$id) $this->json(['status' => 0, 'msg' => 'ID missing']);
		$delete = $this->db->query("DELETE FROM users WHERE id='" . $this->db->real_escape_string($id) . "'");
		$this->json($delete ? ['status' => 1, 'msg' => 'User deleted'] : ['status' => 0, 'msg' => $this->db->error]);
	}

	// ---------------- SETTINGS ----------------
	function save_settings()
	{
		$name = $_POST['name'] ?? '';
		$email = $_POST['email'] ?? '';
		$contact = $_POST['contact'] ?? '';
		$about = $_POST['about'] ?? '';

		$data = "name='" . $this->db->real_escape_string($name) . "',
                 email='" . $this->db->real_escape_string($email) . "',
                 contact='" . $this->db->real_escape_string($contact) . "',
                 about_content='" . htmlentities($about) . "'";

		if (!empty($_FILES['img']['tmp_name'])) {
			$fname = strtotime(date('Y-m-d H:i')) . '_' . $_FILES['img']['name'];
			move_uploaded_file($_FILES['img']['tmp_name'], 'assets/uploads/' . $fname);
			$data .= ", cover_img='" . $this->db->real_escape_string($fname) . "'";
		}

		$chk = $this->db->query("SELECT * FROM system_settings");
		if ($chk->num_rows > 0) {
			$save = $this->db->query("UPDATE system_settings SET $data");
		} else {
			$save = $this->db->query("INSERT INTO system_settings SET $data");
		}

		if ($save) {
			$query = $this->db->query("SELECT * FROM system_settings LIMIT 1")->fetch_assoc();
			foreach ($query as $key => $value) {
				if (!is_numeric($key)) $_SESSION['settings'][$key] = $value;
			}
			$this->json(['status' => 1, 'msg' => 'Settings saved']);
		} else {
			$this->json(['status' => 0, 'msg' => $this->db->error]);
		}
	}

	// ---------------- PLANS ----------------
	function save_plan()
	{
		$id = $_POST['id'] ?? '';
		$name = $_POST['name'] ?? '';
		$price = $_POST['price'] ?? 0;

		$data = "name='" . $this->db->real_escape_string($name) . "',
                 price='" . $this->db->real_escape_string($price) . "'";

		if (empty($id)) {
			$save = $this->db->query("INSERT INTO plans SET $data");
		} else {
			$save = $this->db->query("UPDATE plans SET $data WHERE id='" . $this->db->real_escape_string($id) . "'");
		}

		$this->json($save ? ['status' => 1, 'msg' => 'Plan saved'] : ['status' => 0, 'msg' => $this->db->error]);
	}

	function delete_plan()
	{
		$id = $_POST['id'] ?? '';
		if (!$id) $this->json(['status' => 0, 'msg' => 'ID missing']);
		$delete = $this->db->query("DELETE FROM plans WHERE id='" . $this->db->real_escape_string($id) . "'");
		$this->json($delete ? ['status' => 1, 'msg' => 'Plan deleted'] : ['status' => 0, 'msg' => $this->db->error]);
	}

	// ---------------- PACKAGES ----------------
	function save_package()
	{
		$id = $_POST['id'] ?? '';
		$name = $_POST['name'] ?? '';
		$details = $_POST['details'] ?? '';

		$data = "name='" . $this->db->real_escape_string($name) . "',
                 details='" . $this->db->real_escape_string($details) . "'";

		if (empty($id)) {
			$save = $this->db->query("INSERT INTO packages SET $data");
		} else {
			$save = $this->db->query("UPDATE packages SET $data WHERE id='" . $this->db->real_escape_string($id) . "'");
		}

		$this->json($save ? ['status' => 1, 'msg' => 'Package saved'] : ['status' => 0, 'msg' => $this->db->error]);
	}

	function delete_package()
	{
		$id = $_POST['id'] ?? '';
		if (!$id) $this->json(['status' => 0, 'msg' => 'ID missing']);
		$delete = $this->db->query("DELETE FROM packages WHERE id='" . $this->db->real_escape_string($id) . "'");
		$this->json($delete ? ['status' => 1, 'msg' => 'Package deleted'] : ['status' => 0, 'msg' => $this->db->error]);
	}

	// ---------------- TRAINERS ----------------
	function save_trainer()
	{
		$id = $_POST['id'] ?? '';
		$name = $_POST['name'] ?? '';
		$specialty = $_POST['specialty'] ?? '';

		$data = "name='" . $this->db->real_escape_string($name) . "',
                 specialty='" . $this->db->real_escape_string($specialty) . "'";

		if (empty($id)) {
			$save = $this->db->query("INSERT INTO trainers SET $data");
		} else {
			$save = $this->db->query("UPDATE trainers SET $data WHERE id='" . $this->db->real_escape_string($id) . "'");
		}

		$this->json($save ? ['status' => 1, 'msg' => 'Trainer saved'] : ['status' => 0, 'msg' => $this->db->error]);
	}

	function delete_trainer()
	{
		$id = $_POST['id'] ?? '';
		if (!$id) $this->json(['status' => 0, 'msg' => 'ID missing']);
		$delete = $this->db->query("DELETE FROM trainers WHERE id='" . $this->db->real_escape_string($id) . "'");
		$this->json($delete ? ['status' => 1, 'msg' => 'Trainer deleted'] : ['status' => 0, 'msg' => $this->db->error]);
	}

	// ---------------- MEMBERS ----------------
	function save_member()
	{
		$id = $_POST['id'] ?? '';
		$member_id = $_POST['member_id'] ?? 'M' . date('Ymd') . rand(1000, 9999);
		$lastname = $_POST['lastname'] ?? '';
		$firstname = $_POST['firstname'] ?? '';
		$middlename = $_POST['middlename'] ?? '';
		$email = $_POST['email'] ?? '';
		$contact = $_POST['contact'] ?? '';
		$gender = $_POST['gender'] ?? '';
		$address = $_POST['address'] ?? '';
		$plan_id = $_POST['plan_id'] ?? '';
		$package_id = $_POST['package_id'] ?? '';
		$trainer_id = $_POST['trainer_id'] ?? '';

		$data = "member_id='" . $this->db->real_escape_string($member_id) . "',
                 lastname='" . $this->db->real_escape_string($lastname) . "',
                 firstname='" . $this->db->real_escape_string($firstname) . "',
                 middlename='" . $this->db->real_escape_string($middlename) . "',
                 email='" . $this->db->real_escape_string($email) . "',
                 contact='" . $this->db->real_escape_string($contact) . "',
                 gender='" . $this->db->real_escape_string($gender) . "',
                 address='" . $this->db->real_escape_string($address) . "',
                 plan_id='" . $this->db->real_escape_string($plan_id) . "',
                 package_id='" . $this->db->real_escape_string($package_id) . "',
                 trainer_id='" . $this->db->real_escape_string($trainer_id) . "'";

		if (empty($id)) {
			$save = $this->db->query("INSERT INTO members SET $data");
		} else {
			$save = $this->db->query("UPDATE members SET $data WHERE id='" . $this->db->real_escape_string($id) . "'");
		}

		$this->json($save ? ['status' => 1, 'msg' => 'Member saved', 'member_id' => $member_id] : ['status' => 0, 'msg' => $this->db->error]);
	}

	function delete_member()
	{
		$id = $_POST['id'] ?? '';
		if (!$id) $this->json(['status' => 0, 'msg' => 'ID missing']);
		$delete = $this->db->query("DELETE FROM members WHERE id='" . $this->db->real_escape_string($id) . "'");
		$this->json($delete ? ['status' => 1, 'msg' => 'Member deleted'] : ['status' => 0, 'msg' => $this->db->error]);
	}

	// ---------------- SCHEDULE ----------------
	function save_schedule()
	{
		$id = $_POST['id'] ?? '';
		$member_id = $_POST['member_id'] ?? '';
		$dow = $_POST['dow'] ?? [];
		$date_from = $_POST['date_from'] ?? '';
		$date_to = $_POST['date_to'] ?? '';
		$time_from = $_POST['time_from'] ?? '';
		$time_to = $_POST['time_to'] ?? '';

		if (!$member_id || empty($dow) || !$date_from || !$date_to || !$time_from || !$time_to) {
			$this->json(['status' => 0, 'msg' => 'All fields are required']);
		}

		$dow_str = implode(',', $dow);

		if ($id) {
			$stmt = $this->db->prepare("UPDATE schedules SET member_id=?, dow=?, date_from=?, date_to=?, time_from=?, time_to=? WHERE id=?");
			$stmt->bind_param("isssssi", $member_id, $dow_str, $date_from, $date_to, $time_from, $time_to, $id);
		} else {
			$stmt = $this->db->prepare("INSERT INTO schedules (member_id,dow,date_from,date_to,time_from,time_to) VALUES (?,?,?,?,?,?)");
			$stmt->bind_param("isssss", $member_id, $dow_str, $date_from, $date_to, $time_from, $time_to);
		}

		$this->json($stmt->execute() ? ['status' => 1, 'msg' => 'Schedule saved'] : ['status' => 0, 'msg' => $stmt->error]);
	}

	function delete_schedule()
	{
		$id = $_POST['id'] ?? '';
		if (!$id) $this->json(['status' => 0, 'msg' => 'ID missing']);
		$delete = $this->db->query("DELETE FROM schedules WHERE id='" . $this->db->real_escape_string($id) . "'");
		$this->json($delete ? ['status' => 1, 'msg' => 'Schedule deleted'] : ['status' => 0, 'msg' => $this->db->error]);
	}

	function get_schecdule()
	{
		$qry = $this->db->query("SELECT s.*, CONCAT(m.firstname,' ',m.lastname) as name FROM schedules s LEFT JOIN members m ON m.id = s.member_id ORDER BY date_from ASC, time_from ASC");
		$data = [];
		while ($row = $qry->fetch_assoc()) {
			$data[] = [
				'id' => $row['id'],
				'name' => $row['name'],
				'start' => $row['date_from'] . 'T' . $row['time_from'],
				'end' => $row['date_to'] . 'T' . $row['time_to']
			];
		}
		return ['status' => 1, 'data' => $data];
	}

	// ---------------- MEMBERSHIP & PAYMENTS ----------------
	function save_membership()
	{
		$id = $_POST['id'] ?? '';
		$member_id = $_POST['member_id'] ?? '';
		$plan_id = $_POST['plan_id'] ?? '';
		$start_date = $_POST['start_date'] ?? '';
		$end_date = $_POST['end_date'] ?? '';

		if (!$member_id || !$plan_id || !$start_date || !$end_date) {
			$this->json(['status' => 0, 'msg' => 'All fields are required']);
		}

		if ($id) {
			$stmt = $this->db->prepare("UPDATE memberships SET member_id=?, plan_id=?, start_date=?, end_date=? WHERE id=?");
			$stmt->bind_param("iissi", $member_id, $plan_id, $start_date, $end_date, $id);
		} else {
			$stmt = $this->db->prepare("INSERT INTO memberships (member_id, plan_id, start_date, end_date) VALUES (?,?,?,?)");
			$stmt->bind_param("iiss", $member_id, $plan_id, $start_date, $end_date);
		}

		$this->json($stmt->execute() ? ['status' => 1, 'msg' => 'Membership saved'] : ['status' => 0, 'msg' => $stmt->error]);
	}

	function delete_membership()
	{
		$id = $_POST['id'] ?? '';
		if (!$id) $this->json(['status' => 0, 'msg' => 'ID missing']);
		$delete = $this->db->query("DELETE FROM memberships WHERE id='" . $this->db->real_escape_string($id) . "'");
		$this->json($delete ? ['status' => 1, 'msg' => 'Membership deleted'] : ['status' => 0, 'msg' => $this->db->error]);
	}

	function renew_membership()
	{
		$id = $_POST['id'] ?? '';
		$end_date = $_POST['end_date'] ?? '';
		if (!$id || !$end_date) $this->json(['status' => 0, 'msg' => 'ID and end date required']);
		$stmt = $this->db->prepare("UPDATE memberships SET end_date=? WHERE id=?");
		$stmt->bind_param("si", $end_date, $id);
		$this->json($stmt->execute() ? ['status' => 1, 'msg' => 'Membership renewed'] : ['status' => 0, 'msg' => $stmt->error]);
	}

	function end_membership()
	{
		$id = $_POST['id'] ?? '';
		if (!$id) $this->json(['status' => 0, 'msg' => 'ID required']);
		$stmt = $this->db->prepare("UPDATE memberships SET end_date=CURRENT_DATE() WHERE id=?");
		$stmt->bind_param("i", $id);
		$this->json($stmt->execute() ? ['status' => 1, 'msg' => 'Membership ended'] : ['status' => 0, 'msg' => $stmt->error]);
	}

	function save_payment()
	{
		$id = $_POST['id'] ?? '';
		$member_id = $_POST['member_id'] ?? '';
		$amount = $_POST['amount'] ?? '';
		$date = $_POST['date'] ?? '';

		if (!$member_id || !$amount || !$date) $this->json(['status' => 0, 'msg' => 'All fields required']);

		if ($id) {
			$stmt = $this->db->prepare("UPDATE payments SET member_id=?, amount=?, date=? WHERE id=?");
			$stmt->bind_param("iisi", $member_id, $amount, $date, $id);
		} else {
			$stmt = $this->db->prepare("INSERT INTO payments (member_id, amount, date) VALUES (?,?,?)");
			$stmt->bind_param("iis", $member_id, $amount, $date);
		}

		$this->json($stmt->execute() ? ['status' => 1, 'msg' => 'Payment saved'] : ['status' => 0, 'msg' => $stmt->error]);
	}
}
