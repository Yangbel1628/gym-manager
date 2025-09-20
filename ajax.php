<?php
// ajax.php
ini_set('display_errors', 0);
error_reporting(0);

include 'admin_class.php';

$crud = new Action();

// Read action from both GET and POST
$action = $_REQUEST['action'] ?? '';

/**
 * Send JSON response safely
 */
function echoResult($res)
{
	if (ob_get_length()) ob_end_clean();
	header('Content-Type: application/json');

	if (is_string($res) && (substr($res, 0, 1) == '{' || substr($res, 0, 1) == '[')) {
		$decoded = json_decode($res, true);
		if ($decoded !== null) {
			echo json_encode($decoded);
		} else {
			echo json_encode(['status' => 0, 'msg' => $res]);
		}
	} elseif (is_array($res)) {
		echo json_encode($res);
	} else {
		echo json_encode(['status' => $res ? 1 : 0, 'msg' => $res ? 'Success' : 'Failed']);
	}
	exit;
}

// ---------------- ACTION SWITCH ----------------
switch ($action) {

	// LOGIN / LOGOUT
	case 'login':
		echoResult($crud->login());
		break;
	case 'login2':
		echoResult($crud->login2());
		break;
	case 'logout':
	case 'logout2':
		session_destroy();
		session_unset();
		echoResult(['status' => 1, 'msg' => 'Logged out']);
		break;

	// USERS
	case 'save_user':
		echoResult($crud->save_user());
		break;
	case 'delete_user':
		echoResult($crud->delete_user());
		break;

	// SETTINGS
	case 'save_settings':
		echoResult($crud->save_settings());
		break;

	// PLANS
	case 'save_plan':
		echoResult($crud->save_plan());
		break;
	case 'delete_plan':
		echoResult($crud->delete_plan());
		break;

	// PACKAGES
	case 'save_package':
		echoResult($crud->save_package());
		break;
	case 'delete_package':
		echoResult($crud->delete_package());
		break;

	// TRAINERS
	case 'save_trainer':
		echoResult($crud->save_trainer());
		break;
	case 'delete_trainer':
		echoResult($crud->delete_trainer());
		break;

	// MEMBERS
	case 'save_member':
		echoResult($crud->save_member());
		break;
	case 'delete_member':
		echoResult($crud->delete_member());
		break;

	// SCHEDULE
	case 'save_schedule':
		echoResult($crud->save_schedule());
		break;
	case 'delete_schedule':
		echoResult($crud->delete_schedule());
		break;
	case 'get_schecdule':
		$schedules = $crud->get_schecdule();
		$events = [];
		if (isset($schedules['status']) && $schedules['status'] == 1 && isset($schedules['data'])) {
			foreach ($schedules['data'] as $row) {
				$events[] = [
					'id' => $row['id'],
					'title' => $row['name'] ?? 'Session',
					'start' => $row['date_from'] . 'T' . $row['time_from'],
					'end' => $row['date_to'] . 'T' . $row['time_to']
				];
			}
		}
		echoResult($events);
		break;

	// MEMBERSHIP
	case 'save_membership':
		echoResult($crud->save_membership());
		break;
	case 'delete_membership':
		echoResult($crud->delete_membership());
		break;
	case 'renew_membership':
		echoResult($crud->renew_membership());
		break;
	case 'end_membership':
		echoResult($crud->end_membership());
		break;

	// PAYMENTS
	case 'save_payment':
		echoResult($crud->save_payment());
		break;

	default:
		echoResult(['status' => 0, 'msg' => 'Invalid action']);
		break;
}
