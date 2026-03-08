<?php
date_default_timezone_set('Asia/Phnom_Penh');
// echo date('Y-m-d H:i:s');

// CORS Headers for cross-origin requests (works on InfinityFree)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once(__DIR__ . '/config/db.php');
require_once(__DIR__ . '/controllers/AuthController.php');

require_once(__DIR__ . '/controllers/backend/CategoryController.php');
require_once(__DIR__ . '/controllers/backend/CourseController.php');
require_once(__DIR__ . '/controllers/backend/RoadmapController.php');
require_once(__DIR__ . '/controllers/backend/ClassTypesController.php');
require_once(__DIR__ . '/controllers/backend/TermAndTimeController.php');
require_once(__DIR__ . '/controllers/backend/ScheduleController.php');
require_once(__DIR__ . '/controllers/backend/BuildingController.php');
require_once(__DIR__ . '/controllers/backend/InstructorController.php');
require_once(__DIR__ . '/controllers/backend/ClassAndStuController.php');
require_once(__DIR__ . '/controllers/backend/AttendanceRule.php');
require_once(__DIR__ . '/controllers/backend/DiscountController.php');
require_once(__DIR__ . '/controllers/backend/BackupController.php');


require_once(__DIR__ . '/controllers/frontend/ClassController.php');
require_once(__DIR__ . '/controllers/frontend/StudentController.php');
require_once(__DIR__ . '/controllers/frontend/StudentPermission.php');
require_once(__DIR__ . '/controllers/frontend/BlacklistController.php');
require_once(__DIR__ . '/controllers/frontend/ReqCertificateteController.php');


header('Content-Type: application/json');
session_start();

function response($status, $message = "", $data = []) {
    echo json_encode([
        "status" => $status,
        "message" => $message,
        "data" => $data
    ]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$endpoint = $_GET['endpoint'] ?? '';

switch ($endpoint) {

    // --- Auth ---
    case 'register':
        if ($method !== 'POST') response(false, "Method not allowed");
        AuthController::register($conn);
    break;

    case 'login':
        if ($method !== 'POST') response(false, "Method not allowed");
        AuthController::login($conn);
    break;

    case 'profile':
        if ($method !== 'GET') response(false, "Method not allowed");
        AuthController::profile($conn);
    break;

    // Get all pending users (for admin)
    case 'getPendingUsers':
        if ($method !== 'GET') response(false, "Method not allowed");
        AuthController::getPendingUsers($conn);
    break;

    // Approve or reject a user (for admin)
    case 'updateApproval':
        if ($method !== 'POST') response(false, "Method not allowed");
        AuthController::updateApproval($conn);
    break;

    case 'checkApproval':
        if ($method !== 'GET') response(false, "Method not allowed");
        AuthController::checkApproval($conn);
    break;


    case 'logout':
        if ($method !== 'POST') response(false, "Method not allowed");
        AuthController::logout();
    break;

    // --- Category CRUD ---
    case 'category_get_all':
        if ($method !== 'GET') response(false, "Method not allowed");
        CategoryController::getAll($conn);
    break;

    case 'category_get':
        if ($method !== 'GET') response(false, "Method not allowed");
        $id = intval($_GET['id'] ?? 0);
        CategoryController::get($conn, $id);
    break;

    case 'category_getsome':
        if ($method !== 'GET') response(false, "Method not allowed");
        CategoryController::getSomeCategory($conn);
    break;

    case 'category_create':
        if ($method !== 'POST') response(false, "Method not allowed");
        $category = $_POST['category'] ?? '';
        $created_by = $_SESSION['user']['id'] ?? null;
        CategoryController::create($conn, $category, $created_by);
    break;

    case 'category_update':
        if ($method !== 'POST') response(false, "Method not allowed");
        $id = intval($_POST['id'] ?? 0);
        $category = $_POST['category'] ?? '';
        CategoryController::update($conn, $id, $category);
    break;

    case 'category_delete':
        if ($method !== 'POST') response(false, "Method not allowed");
        $id = intval($_POST['id'] ?? 0);
        CategoryController::delete($conn, $id);
    break;


    // --- Courses ---
    case 'course_getall':
        if ($method !== 'GET') response(false, "Method not allowed");
        CourseController::getAll($conn);
    break;

    case 'course_get':
        if ($method !== 'GET') response(false, "Method not allowed");
        $id = $_GET['id'] ?? 0;
        CourseController::get($conn, $id);
    break;

    case 'course_create':
        if ($method !== 'POST') response(false, "Method not allowed");
        $course = $_POST['course'] ?? null;
        $category_id = $_POST['category_id'] ?? null;
        $created_by = $_SESSION['user']['id'] ?? null;
        
        CourseController::create($conn, $course, $category_id, $created_by);
    break;

    case 'course_update':
        if ($method !== 'POST') response(false, "Method not allowed");

        // Get data from POST
        $id = intval($_POST['id'] ?? 0);
        $course = $_POST['course'] ?? '';
        $category_id = intval($_POST['category_id'] ?? 0);

        // Call the update function
        CourseController::update($conn, $id, $course, $category_id);
    break;

    case 'course_delete':
        if($method !== 'POST') response(false, "Method not allowed");
        $id = intval($_POST['id'] ?? 0);
        CourseController::delete($conn, $id);
    break;

    // --- Roadmaps ---
    case 'roadmap_getall':
        if ($method !== 'GET') response(false, "Method not allowed");
        RoadmapController::getAll($conn);
    break;

    case 'roadmap_get':
        if ($method !== 'GET') response(false, "Method not allowed");
        $id = $_GET['id'] ?? 0;
        RoadmapController::get($conn, $id);
    break;

    case 'roadmap_create':
        if ($method !== 'POST') response(false, "Method not allowed");
        $course_id = $_POST['course_id'] ?? null;
        $lesson = $_POST['lessons'] ?? null;
        $created_by = $_SESSION['user']['id'] ?? 1; // fallback to 1 if session missing
        RoadmapController::create($conn, $course_id, $lesson, $created_by);
    break;

    case 'roadmap_update':
        if ($method !== 'POST') response(false, "Method not allowed"); // changed from PUT
        $id = $_POST['id'] ?? null;
        $course_id = $_POST['course_id'] ?? null;
        $lesson = $_POST['lessons'] ?? null;
        RoadmapController::update($conn, $id, $course_id, $lesson);
    break;

    case 'roadmap_delete':
        if ($method !== 'POST') response(false, "Method not allowed"); // changed from DELETE
        $id = $_POST['id'] ?? 0;
        RoadmapController::delete($conn, $id);
    break;


    // --- CLASS TYPES CRUD ---
    case 'class_type_create':
        if ($method !== 'POST') response(false, "Method not allowed");
        $name = $_POST['name'] ?? null;
        $created_by = $_SESSION['user']['id'] ?? 1; // fallback to 1 if session missing
        ClassTypesController::create($conn, $name, $created_by);
    break;

    case 'class_type_update':
        if ($method !== 'POST') response(false, "Method not allowed"); // using POST for simplicity
        $id = $_POST['id'] ?? null;
        $name = $_POST['name'] ?? null;
        ClassTypesController::update($conn, $id, $name);
    break;

    case 'class_type_delete':
        if ($method !== 'POST') response(false, "Method not allowed"); // using POST for simplicity
        $id = $_POST['id'] ?? 0;
        ClassTypesController::delete($conn, $id);
    break;

    case 'class_type_get_all':
        if ($method !== 'GET') response(false, "Method not allowed");
        ClassTypesController::getAll($conn);
    break;
    
     // --- TERMS CRUD ---
    case 'term_get_all':
        if($method !== 'GET') response(false, "Method not allowed");
        TermAndTimeController::getAllTerms($conn);
    break;

    case 'term_create':
        if($method !== 'POST') response(false, "Method not allowed");
        $term = $_POST['term'] ?? '';
        $created_by = $_SESSION['user']['id'] ?? 1;
        TermAndTimeController::createTerm($conn, $term, $created_by);
    break;

    case 'term_update':
        if($method !== 'POST') response(false, "Method not allowed");
        $id = intval($_POST['id'] ?? 0);
        $term = $_POST['term'] ?? '';
        TermAndTimeController::updateTerm($conn, $id, $term);
    break;

    case 'term_delete':
        if($method !== 'POST') response(false, "Method not allowed");
        $id = intval($_POST['id'] ?? 0);
        TermAndTimeController::deleteTerm($conn, $id);
    break;

    // --- TIMES CRUD ---
    case 'time_get_all':
        if($method !== 'GET') response(false, "Method not allowed");
        TermAndTimeController::getAllTimes($conn);
    break;

    case 'time_create':
        if($method !== 'POST') response(false, "Method not allowed");
        $time_slot = $_POST['time'] ?? '';
        $created_by = $_SESSION['user']['id'] ?? 1; // keep created_by
        TermAndTimeController::createTime($conn, $time_slot, $created_by);
    break;

    case 'time_update':
        if($method !== 'POST') response(false, "Method not allowed");
        $id = intval($_POST['id'] ?? 0);
        $time_slot = $_POST['time'] ?? '';
        TermAndTimeController::updateTime($conn, $id, $time_slot);
    break;

    case 'time_delete':
        if($method !== 'POST') response(false, "Method not allowed");
        $id = intval($_POST['id'] ?? 0);
        TermAndTimeController::deleteTime($conn, $id);
    break;


    // --- SCHEDULES CRUD ---
    case 'schedule_getall':
        if ($method !== 'GET') response(false, "Method not allowed");
        ScheduleController::getAll($conn);
    break;

    case 'schedule_get':
        if ($method !== 'GET') response(false, "Method not allowed");
        $id = intval($_GET['id'] ?? 0);
        ScheduleController::get($conn, $id);
    break;

    case 'schedule_create':
        if ($method !== 'POST') response(false, "Method not allowed");

        $class_type_id = intval($_POST['class_type_id'] ?? 0);
        $term_id = intval($_POST['term_id'] ?? 0);
        $time_ids = $_POST['time_ids'] ?? [];
        $created_by = $_SESSION['user']['id'] ?? 1;

        if (empty($time_ids)) {
            response(false, "No time slots selected");
        }

        // Build bulk insert
        $values = [];
        $params = [];
        $types = '';
        foreach ($time_ids as $time_id) {
            $time_id = intval($time_id);
            $values[] = "(?, ?, ?, ?)";
            $params[] = $class_type_id;
            $params[] = $term_id;
            $params[] = $time_id;
            $params[] = $created_by;
            $types .= "iiii";
        }

        $sql = "INSERT INTO schedules (class_type_id, term_id, time_id, created_by) VALUES " . implode(',', $values);
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            response(true, "Schedules created successfully");
        } else {
            response(false, "Create failed: " . $conn->error);
        }

    break;

    case 'schedule_delete':
        if ($method !== 'POST') response(false, "Method not allowed");
        $id = intval($_POST['id'] ?? 0);
        ScheduleController::deleteByClassType($conn, $id);
    break;

    case 'schedule_term_delete':
        if ($method !== 'POST') response(false, "Method not allowed");

        $classTypeId = intval($_POST['class_type_id'] ?? 0);
        $termId = intval($_POST['term_id'] ?? 0);

        if ($classTypeId && $termId) {
            ScheduleController::deleteByClassTypeAndTerm($conn, $classTypeId, $termId);
        } else {
            response(false, "Both class_type_id and term_id are required for deletion.");
        }
    break;
    
    // --- BUILDING ---
    case 'insert_building':
        if ($method !== 'POST') response(false, "Method not allowed");

        $buildingName = $_POST['building_name'] ?? '';
        $userId = $_SESSION['user']['id'] ?? 1;

        BuildingController::addBuilding($conn, $buildingName, $userId);
    break;

    case 'getAllBuildings':
        if ($method !== 'GET') response(false, "Method not allowed");

        BuildingController::getAllBuildings($conn);
    break;

    case 'update_building':
        if ($method !== 'POST') response(false, "Method not allowed");
        $buildingId = $_POST['building_id'] ?? 0;
        $buildingName = $_POST['building_name'] ?? '';
        BuildingController::updateBuilding($conn, $buildingId, $buildingName);
    break;

    case 'delete_building':
        if ($method !== 'POST') response(false, "Method not allowed");
        $buildingId = $_POST['building_id'] ?? 0;
        BuildingController::deleteBuilding($conn, $buildingId);
    break;


    // --- FLOOR ---
    case 'insert_floor':
        if ($method !== 'POST') response(false, "Method not allowed");

        $buildingId = $_POST['building_id'] ?? 0;
        $floorName = $_POST['floor_name'] ?? '';
        $userId = $_SESSION['user']['id'] ?? 1;

        BuildingController::addFloor($conn, $buildingId, $floorName, $userId);
    break;

    case 'getFloors':
        if ($method !== 'GET') response(false, "Method not allowed");

        $buildingId = $_GET['building_id'] ?? 0;
        BuildingController::getFloors($conn, $buildingId);
    break;

    // --- Update Floor ---
    case 'update_floor':
        $floor_id = intval($_POST['floor_id'] ?? 0);
        $floor_name = $_POST['floor_name'] ?? '';
        BuildingController::updateFloor($conn, $floor_id, $floor_name);
    break;

    // --- Delete Floor ---
    case 'delete_floor':
        if ($method !== 'POST') response(false, "Method not allowed");

        $floor_id = intval($_POST['floor_id'] ?? 0);
        BuildingController::deleteFloor($conn, $floor_id);
    break;

    
    // --- ROOM ---
    case 'insert_room':
        if ($method !== 'POST') response(false, "Method not allowed");

        $buildingId = $_POST['building_id'] ?? 0;
        $floorId = $_POST['floor_id'] ?? 0; // optional if room not tied to floor
        $roomName = $_POST['room_name'] ?? '';
        $userId = $_SESSION['user']['id'] ?? 1;

        BuildingController::addRoom($conn, $buildingId, $floorId, $roomName, $userId);
    break;

    case 'getAllBuildingFloorsRooms':
        if ($method !== 'GET') response(false, "Method not allowed");

        BuildingController::getAllBuildingFloorsRooms($conn);
    break;

    // --- Update Room ---
    case 'update_room':
        $room_id = intval($_POST['room_id'] ?? 0);
        $room_name = $_POST['room_name'] ?? '';
        BuildingController::updateRoom($conn, $room_id, $room_name);
    break;
    
    // --- Delete Room ---
    case 'delete_room':
        if ($method !== 'POST') response(false, "Method not allowed");

        $room_id = intval($_POST['room_id'] ?? 0);
        BuildingController::deleteRoom($conn, $room_id);
    break;

    case 'getRooms':
        if ($method !== 'GET') response(false, "Method not allowed");
        $buildingId = $_GET['building_id'] ?? 0;
        $floorId = $_GET['floor_id'] ?? 0;
        BuildingController::getRooms($conn, $buildingId, $floorId);
    break;



    // --- INSTRUCTOR CRUD ---
    case 'instructor_getall':
        if ($method !== 'GET') response(false, "Method not allowed");
        InstructorController::getAll($conn);
    break;

    case 'getsingleinstructor':
        $inst_id = isset($_POST['inst_id']) ? intval($_POST['inst_id']) : 0;
        InstructorController::getsingleinstructor($conn,$inst_id);
    break;

    case 'instructor_create':
        if ($method !== 'POST') response(false, "Method not allowed");
        $name = $_POST['name'] ?? '';
        $gender = $_POST['gender'] ?? '';
        $tel = $_POST['tel'] ?? '';
        $email = $_POST['email'] ?? '';
        $pass = $_POST['pass'] ?? '';
        $image = $_POST['image'] ?? '';
        $created_by = $_SESSION['user']['id'] ?? 1;

        InstructorController::create($conn, $name, $gender, $tel, $email, $pass, $image, $created_by);
    break;

    case 'instructor_update':
        if ($method !== 'POST') response(false, "Method not allowed");
        $id = intval($_POST['id'] ?? 0);
        $name = $_POST['name'] ?? '';
        $gender = $_POST['gender'] ?? '';
        $tel = $_POST['tel'] ?? '';
        $email = $_POST['email'] ?? '';
        $approval = $_POST['approval'] ?? 'pending';
        $image = $_POST['image'] ?? '';

        InstructorController::update($conn, $id, $name, $gender, $tel, $email, $approval, $image);
    break;

    case 'instructor_delete':
        $id = $_POST['id'] ?? null;

        if (!$id) {
            echo json_encode(['status' => false, 'message' => 'No ID received']);
            exit;
        }
        InstructorController::delete($conn, $id);
    break;

    // --- CLASSES CRUD ---
    case 'class_create':
        if ($method !== 'POST') response(false, "Method not allowed");

        // Get POST data
        $lesson = $_POST['lesson'] ?? '';
        $class_status = $_POST['class_status'] ?? '';
        $course_id = intval($_POST['course_id'] ?? 0);
        $instructor_id = intval($_SESSION['user']['id'] ?? 1);
        $building_id = intval($_POST['building_id'] ?? 0);
        $floor_id = intval($_POST['floor_id'] ?? 0);
        $room_id = intval($_POST['room_id'] ?? 0);
        $status_id = intval($_POST['status_id'] ?? 0);
        $class_type_id = intval($_POST['class_type_id'] ?? 0);
        $time_id = intval($_POST['time_id'] ?? 0);
        $term_id = intval($_POST['term_id'] ?? 0);

        ClassController::create(
            $conn, 
            $lesson, 
            $class_status, 
            $course_id, 
            $instructor_id, 
            $building_id, 
            $floor_id, 
            $room_id, 
            $class_type_id, // correct
            $time_id, 
            $term_id
        );

    break;
    
    case 'class_get_by_instructor':
        if($method !== 'GET') response(false, "Method not allowed");

        $instructor_id = intval($_SESSION['user']['id'] ?? 0);
        ClassController::getByInstructor($conn, $instructor_id);
    break;

    case 'class_get_by_instructor_id':
        if ($method !== 'GET') response(false, "Method not allowed");

        $instructor_id = intval($_GET['instructor_id'] ?? 0);

        if (!$instructor_id) {
            response(false, "Instructor ID is required");
        }

        ClassController::getByInstructor($conn, $instructor_id);
    break;

    case 'class_update':
        if ($method !== 'POST') response(false, "Method not allowed");

        // Get POST data
        $id = intval($_POST['id'] ?? 0);
        $lesson = $_POST['lesson'] ?? '';
        $class_status = $_POST['class_status'] ?? 'progress';
        $course_id = intval($_POST['course_id'] ?? 0);
        $instructor_id = intval($_SESSION['user']['id'] ?? 1);
        $building_id = intval($_POST['building_id'] ?? 0);
        $floor_id = intval($_POST['floor_id'] ?? 0);
        $room_id = intval($_POST['room_id'] ?? 0);
        $class_type_id = intval($_POST['class_type_id'] ?? 0);
        $time_id = intval($_POST['time_id'] ?? 0);
        $term_id = intval($_POST['term_id'] ?? 0);

        ClassController::update(
            $conn,
            $id,
            $lesson,
            $class_status,
            $course_id,
            $instructor_id,
            $building_id,
            $floor_id,
            $room_id,
            $class_type_id,
            $time_id,
            $term_id
        );
    break;

    case 'class_update_admin':
        if ($method !== 'POST') response(false, "Method not allowed");

        // Get POST data
        $id = intval($_POST['id'] ?? 0);
        $lesson = $_POST['lesson'] ?? '';
        $class_status = $_POST['class_status'] ?? 'progress';
        $course_id = intval($_POST['course_id'] ?? 0);
        $instructor_id = intval($_POST['instructor_id'] ?? 1);
        $building_id = intval($_POST['building_id'] ?? 0);
        $floor_id = intval($_POST['floor_id'] ?? 0);
        $room_id = intval($_POST['room_id'] ?? 0);
        $class_type_id = intval($_POST['class_type_id'] ?? 0);
        $time_id = intval($_POST['time_id'] ?? 0);
        $term_id = intval($_POST['term_id'] ?? 0);

        ClassController::update(
            $conn,
            $id,
            $lesson,
            $class_status,
            $course_id,
            $instructor_id,
            $building_id,
            $floor_id,
            $room_id,
            $class_type_id,
            $time_id,
            $term_id
        );
    break;

    case 'create_stu':
        if ($method !== 'POST') response(false, "Method not allowed");

        // Get POST data
        $fullname = $_POST['fullname'] ?? '';
        $gender = $_POST['gender'] ?? '';
        $tel = $_POST['tel'] ?? '';
        $instructor_id = intval($_SESSION['user']['id'] ?? 1);
        $class_id = intval($_POST['class_id'] ?? null);
        $transferTo = isset($_POST['transferTo']) && $_POST['transferTo'] !== '' 
            ? intval($_POST['transferTo']) 
            : null; // ✅ new line

        // Call createStu function
        StudentController::createStudent($conn, $fullname, $gender, $tel, $instructor_id, $class_id, $transferTo);
    break;

    case 'create_stu_forstu':
        if ($method !== 'POST') response(false, "Method not allowed");

        // Get POST data
        $fullname = $_POST['fullname'] ?? '';
        $gender = $_POST['gender'] ?? '';
        $tel = $_POST['tel'] ?? '';
        $instructor_id = intval($_POST['instructor_id'] ?? 1);
        $class_id = intval($_POST['class_id'] ?? null);
        $transferTo = isset($_POST['transferTo']) && $_POST['transferTo'] !== '' 
            ? intval($_POST['transferTo']) 
            : null; // ✅ new line

        // Call createStu function
        StudentController::createStudent($conn, $fullname, $gender, $tel, $instructor_id, $class_id, $transferTo);
    break;

    case 'submittedStudent':
        // Make sure required fields exist
        $fullname = $_POST['fullname'] ?? null;
        $gender   = $_POST['gender'] ?? null;
        $tel      = $_POST['tel'] ?? null;
        $instructor_id = intval($_POST['instructor_id'] ?? 1);
        $class_id = intval($_POST['class_id'] ?? null);

        if (empty($fullname) || empty($gender) || empty($tel)) {
            echo json_encode([
                'status' => false,
                'message' => 'Full name, gender, and phone are required.'
            ]);
            exit;
        }

        // Call the submitStudent function
        StudentController::submitStudent($conn, $fullname, $gender, $tel,$instructor_id,$class_id);
    break;


    case 'getClassById':
        $class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
        ClassController::getClassById($conn, $class_id);
    break;

    case 'getClassWithStudent':
        $class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
        ClassController::getClassWithStudents($conn, $class_id);
    break;


    case 'transferClass':
        if ($method !== 'POST') response(false, "Method not allowed");

        $instructor_id = isset($_POST['instructor_id']) ? intval($_POST['instructor_id']) : 1;
        $class_id = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;

        if ($class_id <= 0) response(false, "Class ID is required");

        ClassController::transferClass($conn, $class_id, $instructor_id);
    break;

    case 'get_students_by_class':
        
        $class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
        // $instructor_id = $_SESSION['user']['id'] ?? 0;

        StudentController::getStudentsByClass($conn, $class_id);
    break;

    case 'record_attendance':
        if ($method !== 'POST') response(false, "Method not allowed");

        $students = $_POST['students'] ?? '';
        $class_id = $_POST['class_id'] ?? 0;
        // $att_record_date = $_POST['att_record_date'] ?? null;

        if(empty($students)) {
            response(false, "No student data provided");
        }

        // Decode JSON string to PHP array
        $studentsArray = json_decode($students, true);
        if(!$studentsArray || !is_array($studentsArray)) {
            response(false, "Invalid student data format");
        }

        StudentController::recordsAttBatch($conn, $studentsArray,$class_id);
    break;

    case 'get_students_attendance_summary':
        $class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
        if (empty($class_id)) response(false, "Class ID is required");
        StudentController::getStudentsAttendanceSummary($conn, $class_id);
    break;

     case 'approvedStudent':
        // Get POST data
        $studentId = isset($_POST['studentId']) ? intval($_POST['studentId']) : 0;
        $classId = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;

        if ($studentId <= 0 || $classId <= 0) {
            echo json_encode([
                'status' => false,
                'message' => 'Student ID and Class ID are required.'
            ]);
            exit;
        }

        // Call the approveStudent method
        StudentController::approveStudent($conn, $studentId, $classId);
    break;


    case 'update_student':
        $stu_id = isset($_POST['stu_id']) ? intval($_POST['stu_id']) : 0;
        $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
        $gender = isset($_POST['gender']) ? trim($_POST['gender']) : '';
        $tel = isset($_POST['tel']) ? trim($_POST['tel']) : '';

        StudentController::updateStudent($conn, $stu_id, $full_name, $gender, $tel);
    break;

    
    case 'save_scores':
        // decode JSON from AJAX
        $scores = isset($_POST['scores']) ? json_decode(json_encode($_POST['scores']), true) : [];

        if (empty($scores)) {
            echo json_encode(["status" => false, "message" => "No scores to save"]);
            exit;
        }

        StudentController::saveScoresFast($conn, $scores);
    break;
    
    case 'get_totals_by_instructor':
        $instructor_id = intval($_SESSION['user']['id'] ?? 1);
        ClassController::getTotalsByInstructor($conn, $instructor_id);
    break;
    
    case 'update_class_status':

        $user_id = $_SESSION['user']['id'] ?? null;
        $class_id = $_POST['class_id'] ?? null;
        $class_status = $_POST['class_status'] ?? null;

        ClassController::updateClassStatus(
            $conn,
            $class_id,
            $class_status,
            $user_id
        );

    break;

    case 'switch_instrutor':
        $class_id = $_POST['class_id'] ?? null;
        $instructor_id = $_POST['instructor_id'] ?? null;

        ClassController::switchInstructor($conn,$class_id,$instructor_id);
    break;
    
    case 'count_attendance_by_students':
        // Get stu_ids from frontend (sent as JSON)
        $stu_ids = json_decode($_POST['stu_ids'] ?? '[]', true);

        StudentController::countAttendanceByStudents($conn, $stu_ids);
    break;    
    // case 'is_attendance_recorded_today':
    //     $class_id = $_GET['class_id'] ?? null;
    //     $date = $_GET['date'] ?? date('Y-m-d'); // default to today
    //     StudentController::isAttendanceRecordedToday($conn, $class_id, $date);
    // break;

    case 'is_attendance_recorded_today':
        $class_id = $_GET['class_id'] ?? null;
        $date = $_GET['date'] ?? date('Y-m-d');

        $recorded = StudentController::isAttendanceRecordedToday($conn, $class_id, $date);

        response(true, "Checked", [
            "recorded" => $recorded
        ]);
    break;

    
    case "transferStudentAndRemove":
        StudentController::transferStudentAndRemove($conn, $_POST['stu_id'], $_POST['transferTo']);
    break;

    case "transferStudentNotRemove":
        StudentController::transferStudentWithoutRemove($conn, $_POST['stu_id'], $_POST['transferTo']);
    break;

    case 'showStudentData':
        $id = $_POST['id'] ?? 0;
        $data = StudentController::getStudentAttendanceById($conn, $id);
 
    break;

    case 'getAllClasses':
        $page   = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $limit  = isset($_GET['limit']) ? intval($_GET['limit']) : 7;
        $search = $_GET['search'] ?? "";
        $course = $_GET['course'] ?? "";
        $term   = $_GET['term'] ?? "";
        $time   = $_GET['time'] ?? "";
        $class_status   = $_GET['class_status'] ?? "";

        ClassAndStuController::getAllClasses($conn, $page, $limit, $search, $course, $term, $time, $class_status);
    break;

    case 'deleteClass':
        $class_id = $_POST['class_id'] ?? 0; // or $_GET depending on your request
        ClassAndStuController::deleteClass($conn, $class_id);
    break;

    case 'getAllStudents':
        ClassAndStuController::getAllStudents(
            $conn,
            $_GET['page']   ?? 1,
            $_GET['limit']  ?? 10,
            $_GET['search'] ?? '',
            $_GET['course'] ?? '',
            $_GET['gender'] ?? ''
        );
    break;

    case 'delete_student':
        $stu_id = isset($_POST['studentId']) ? intval($_POST['studentId']) : 0;
        $class_id = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;

        if (!$stu_id) response(false, "Student ID is required");

        ClassAndStuController::deleteStudent($conn, $stu_id, $class_id);
    break;
    
    case 'filterKruAvailable':
        // Make sure the time_id is passed in the request (GET or POST)
        if (isset($_GET['time_id'])) {
            $time_id = intval($_GET['time_id']); // sanitize input
            InstructorController::filterInstructorAvailble($conn, $time_id);
        } else {
            echo json_encode([
                "status" => false,
                "message" => "time_id parameter is required"
            ]);
        }
    break;

    case 'insert_group':
        $groupName    = $_POST['groupName'] ?? '';
        $groupTopic   = $_POST['groupTopic'] ?? '';
        $instructorId = intval($_SESSION['user']['id'] ?? 1);
        $classId      = intval($_POST['class_id'] ?? 0);
        $students     = $_POST['students'] ?? [];

        // Ensure students is an array
        if (!is_array($students)) {
            $students = json_decode($students, true) ?: [];
        }

        // Validate
        if (!$groupName || !$groupTopic || !$instructorId || !$classId || empty($students)) {
            echo json_encode(['status' => false, 'message' => 'Missing required fields']);
            exit;
        }

        // Insert group
        $result = StudentController::insertGroup($conn, $groupName, $groupTopic, $instructorId, $classId, $students);
        echo json_encode($result);
    break;


    case 'getGroups':
        $classId = isset($_GET['class_id']) ? intval($_GET['class_id']) : null;

        $result = StudentController::getGroups($conn, $classId);

        echo json_encode($result);
    break;

    case 'getGroupById':
        $groupId = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if (!$groupId) {
            echo json_encode(['status' => false, 'message' => 'Group ID required']);
            break;
        }

        $result = StudentController::getGroupById($conn, $groupId);
        echo json_encode($result);
    break;

    case 'update_group':
        $groupId = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $groupName = $_POST['groupName'] ?? '';
        $groupTopic = $_POST['groupTopic'] ?? '';
        $classId = $_POST['class_id'] ?? 0;
        $students = isset($_POST['students']) ? json_decode($_POST['students'], true) : [];

        if (!$groupId) {
            echo json_encode(['status'=>false,'message'=>'Group ID required']);
            break;
        }

        $result = StudentController::updateGroup($conn, $groupId, $groupName, $groupTopic, $classId, $students);
        echo json_encode($result);
    break;
    case 'delete_group':
        if (!isset($_POST['id']) || empty($_POST['id'])) {
            response(false, "Group ID is required");
            break;
        }

        $groupId = intval($_POST['id']);
        $result = StudentController::deleteGroup($conn, $groupId); // call the function we defined

        if ($result['status']) {
            response(true, $result['message']);
        } else {
            response(false, $result['message']);
        }
    break;
    case 'countClass':
        // Get parameters from request (GET or POST)
        $course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : "";
        $time_id = isset($_GET['time_id']) ? intval($_GET['time_id']) : "";

        // Call the function
        ClassAndStuController::countClasses($conn, $course_id, $time_id);
    break;
	
        
    case 'instructors':
        if ($method !== 'GET') response(false, "Method not allowed");
        InstructorController::gets($conn);
    break;
	case 'class_by_teacher':
        StudentPermission::getClassesByTeacher(
            $conn,
            $_GET['teacher_id'] ?? null
        );
    break;

    case 'get_students_by_class_permission':
        if ($method !== 'GET') response(false, "Method not allowed");
        StudentPermission::getStudentsByClass(
            $conn,
            $_GET['class_id'] ?? null
        );
    break;


    case 'student_permission_create':
        StudentPermission::submitPermission($conn);
    break;
    
    case 'fetch_permissions_admin':
        StudentPermission::fetchPermissionsForAdmin($conn);
        break;
        
    case 'approve_permission':
        StudentPermission::approvePermission(
            $conn,
            $_POST['permission_id'] ?? null
        );
    break;
        
    case 'getTodayPermissions':
        StudentPermission::getTodayPermissions(
            $conn,
            $_GET['class_id'],
            $_GET['date']
        );
    break;

    case 'update_permission':
        if ($method !== 'POST') response(false, "Method not allowed");
        StudentPermission::updatePermission($conn);
    break;

    case 'delete_permission':
        if ($method !== 'POST') response(false, "Method not allowed");
        StudentPermission::deletePermission($conn);
    break;

    case 'saveRule':
        AttendanceRule::saveRule($conn);
    break;

    // case 'getActiveRules':
    //     AttendanceRule::getActiveRules($conn);
    // break;

    case 'getAllRules':
        AttendanceRule::getAllRules($conn);
    break;

    case 'getActiveRule':
        AttendanceRule::getAllRules(
            $conn,
            $_GET['type'] ?? null
        );
    break;

    case 'toggleRule':
        AttendanceRule::toggleRule($conn);
    break;

    case 'updateRule':
        AttendanceRule::updateRule($conn);
    break;

    case 'deleteRule':
        AttendanceRule::deleteRule($conn);
    break;

    case 'beforeTrackAttendance':
        StudentController::beforeTrackAttendance(
            $conn,
            $_GET['class_id'],
            $_GET['date']
        );
    break;

    case 'approve_absence_block':
        if ($method !== 'POST') response(false, "Method not allowed");
        StudentPermission::approveAbsenceBlock($conn);
    break;
    
    case 'fetch_absence_permission_admin':
        if ($method !== 'GET') response(false, "Method not allowed");
        StudentPermission::fetchAbsenceAndPermissionForAdmin($conn);
    break;

    case 'fetch_blacklist_students':
        if ($method !== 'GET') response(false, "Method not allowed");
        BlacklistController::getHardLockStudents($conn);
    break;

    case 'unblock_blacklist_student':
        if ($method !== 'POST') response(false, "Method not allowed");
        BlacklistController::unblockHardLockStudent($conn);
    break;

    case 'get_discounts':
        DiscountController::getAll($conn);
        break;

    case 'create_discount':
        if ($method !== 'POST') response(false, "Method not allowed");
        DiscountController::create($conn, $_POST);
        break;

    case 'update_discount':
        if ($method !== 'POST') response(false, "Method not allowed");
        DiscountController::update($conn, $_POST);
        break;

    case 'delete_discount':
        if ($method !== 'POST') response(false, "Method not allowed");
        DiscountController::delete($conn, $_POST['id'] ?? 0);
        break;

    case 'get_discount_rule':
        DiscountController::getRuleForScore($conn, $_GET['score'] ?? 0);
    break;
    
    case 'get_discount_rules':
        DiscountController::getActiveRules($conn);
    break;

    case "updateAttendanceRecord":
        StudentController::updateAttendanceRecord(
            $conn,
            $_POST['record_id'] ?? null,
            $_POST['present'] ?? 0,
            $_POST['absent'] ?? 0,
            $_POST['permission'] ?? 0,
            $_POST['reason'] ?? ""
        );
    break;

    case "backupDatabase":
        if ($method !== 'POST') response(false, "Method not allowed");
        
        // Get JSON data
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? 'download';
        
        switch ($action) {
            case 'download':
                $result = BackupController::backupDownload($conn);
                break;
            case 'telegram':
                $result = BackupController::backupTelegram($conn);
                break;
            case 'both':
                $result = BackupController::backupBoth($conn);
                break;
            default:
                response(false, "Invalid action");
        }
        
        response($result['status'], $result['message'], $result['data']);
    break;

    case "downloadBackupFile":
        if ($method !== 'GET') response(false, "Method not allowed");
        
        $filename = $_GET['filename'] ?? '';
        
        if (empty($filename)) {
            response(false, "Filename is required");
        }
        
        // Prevent path traversal attack
        if (strpos($filename, '..') !== false || strpos($filename, '/') !== false) {
            response(false, "Invalid filename");
        }
        
        BackupController::downloadBackup($filename);
    break;

    case "deleteBackupFile":
        if ($method !== 'POST') response(false, "Method not allowed");
        
        $input = json_decode(file_get_contents('php://input'), true);
        $filename = $input['filename'] ?? '';
        
        if (empty($filename)) {
            response(false, "Filename is required");
        }
        
        // Prevent path traversal attack - BackupController also checks this
        if (strpos($filename, '..') !== false || strpos($filename, '/') !== false) {
            response(false, "Invalid filename");
        }
        
        $result = BackupController::deleteBackup($filename);
        response($result['status'], $result['message'], $result['data']);
    break;

    case "getBackupHistory":
        if ($method !== 'GET') response(false, "Method not allowed");
        
        $result = BackupController::getBackupHistory($conn);
        response($result['status'], $result['message'], $result['data']);
    break;

    case "checkBackupRequirements":
        if ($method !== 'GET') response(false, "Method not allowed");
        
        // Check if mysqldump exists
        $mysqldumpPaths = array(
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
            'C:\\Program Files (x86)\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
            'mysqldump'
        );
        
        $mysqldumpFound = false;
        $foundPath = null;
        foreach ($mysqldumpPaths as $path) {
            if (file_exists($path)) {
                $mysqldumpFound = true;
                $foundPath = $path;
                break;
            }
        }
        
        // Check if uploads folder exists and is writable
        $uploadsPath = __DIR__ . '/assets/uploads';
        $uploadsExist = is_dir($uploadsPath);
        $uploadsWritable = is_writable($uploadsPath) || is_writable(__DIR__ . '/assets');
        
        response(true, "Backup requirements check", [
            'mysqldump_found' => $mysqldumpFound,
            'mysqldump_path' => $foundPath,
            'uploads_folder_exists' => $uploadsExist,
            'uploads_writable' => $uploadsWritable,
            'php_version' => phpversion(),
            'os' => PHP_OS
        ]);
    break;

    case "cleanupBackups":
        if ($method !== 'GET') response(false, "Method not allowed");
        
        $result = BackupController::manualCleanup();
        response($result['status'], $result['message'], $result['data']);
    break;

    case "mark_student_late":

        if($method !== 'POST') response(false, "Method not allowed");

        $studentId = $_POST['studentId'] ?? null;
        $classId = $_POST['class_id'] ?? null;
        $attDate = $_POST['att_date'] ?? null;

        StudentController::markStudentLate($conn, $studentId, $classId, $attDate);

    break;

    case "get_student_for_certificate":

        if($method !== 'POST') response(false, "Method not allowed");

        $class_id = $_POST['class_id'] ?? 0;

        $instructorId = intval($_SESSION['user']['id'] ?? 1);

        if(!$instructorId){
            response(false, "User not authenticated");
        }

        ReqCertificateteController::getStudentRequests($conn, $class_id, $instructorId);

    break;

    case "update_student_name":
        $class_id = $_POST['class_id'] ?? 0;
        ReqCertificateteController::saveUpdatedName($conn, $_POST['student_id'] ?? 0, $_POST['full_name'] ?? '');
    break;

    case "approve_student_request":

        if($method !== 'POST') response(false, "Method not allowed");

        $end_class_id = intval($_POST['end_class_id'] ?? 0);
        $student_id   = intval($_POST['student_id'] ?? 0);

        if(!$end_class_id || !$student_id){
            response(false, "Invalid parameters");
        }

        ReqCertificateteController::approveStudentRequest($conn, $end_class_id, $student_id);

    break;
    default:
        response(false, "Invalid endpoint");
}