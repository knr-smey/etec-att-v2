<?php
    session_start();

   // Check if user session exists
    if (isset($_SESSION['user']['id'])) {
        $instructor_id = $_SESSION['user']['id'];
        // echo $instructor_id;
    } else {
        echo "No instructor logged in!";
        // Optionally, redirect to login page
        // header("Location: login.php");
        // exit;
    }
?>
<div>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3 class="mb-0">Attendence System</h3>
            <p class="text-secondary mb-3">Manage your class like manage your oun heart</p>
        </div>
        <!-- <button>
            Reload
        </button> -->
    </div>

    <?php require __DIR__.'../../../utils/card_overview.php' ?>
    <div class="p-0 mt-3">
        <div class="d-flex justify-content-between align-items-center">
            
            <div class="col-4 pb-3 d-flex align-items-center">
                <form action="" class="d-flex border rounded bg-white">
                    <input id="search-class" type="text" placeholder="Search Class..." class="form-control shadow-none border-0 bg-transparent">
                    <button class="btn ">
                        <i class="bi bi-search"></i>
                    </button>
                </form>

                <button class="btn ms-2 btn-secondary" id="refreshBtn">
                    Refresh
                </button>
            </div>

            <button class="btn rounded btn-light border" data-bs-toggle="modal" data-bs-target="#addClassModal">Add Class +</button>                 
        </div>
        <!-- Success Alert -->
        <div id="successAlert" class="alert alert-success alert-dismissible fade show mt-3 " style="display:none;" role="alert">
            <span id="successMessage"></span>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <div class="row g-4" id="classes-container">
            <?php require __DIR__.'../../../utils/classes_skelaton.php' ?>
            
        </div>
    </div>


    <!-- Add Class Modal -->
    <div class="modal fade" id="addClassModal" tabindex="-1" aria-labelledby="addClassModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
            <form>
                <div class="modal-header">
                    <h5 class="modal-title" id="addClassModalLabel">Add New Class</h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex mb-3 border-bottom pb-3">
                        <div class="col-4 pe-2">
                            <label class="form-label text-etec-color">Course</label>
                            <select name="" id="courseSelect" class="form-select shadow-none border" >
                                <option value="" selected disabled>Select Course</option>
                            </select>
                        </div>               
                        <div class="col-4 pe-2">
                            <label class="form-label text-etec-color">Lessons</label>
                            <select name="" id="lessonSelect" class="form-select shadow-none border" >
                                <option value="" selected disabled>Select Lesson</option>
                            </select>
                        </div>
                        <div class="col-4 ">
                            <label class="form-label text-etec-color">Status</label>
                            <select name="" id="statusSelect" class="form-select shadow-none border" >
                                <option value="" selected disabled>Select Status</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex mb-3">
                        <div class="col-4 pe-2">
                            <label class="form-label text-etec-color">Building</label>
                            <select name="" id="buildingSelect" class="form-select shadow-none border" >
                                <option value="" selected disabled>Select Building</option>
                            </select>
                        </div>
                        <div class="col-4 pe-2">
                            <label class="form-label text-etec-color">Floor</label>
                            <select name="" id="floorSelect" class="form-select shadow-none border" >
                                <option value="" selected disabled>Select Floor</option>
                            </select>
                        </div>
                        <div class="col-4">
                            <label class="form-label text-etec-color">Room</label>
                            <select name="" id="roomSelect" class="form-select shadow-none border" >
                                <option value="" selected disabled>Select Room</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex mb-3">
                        <div class="col-6 pe-2">
                            <label class="form-label text-etec-color">Term</label>
                            <select name="" id="termSelect" class="form-select shadow-none border" >
                                <option value="" selected disabled>Select Term</option>
                            </select>
                        </div>
                        <div class="col-6 ">
                            <label class="form-label text-etec-color">Time</label>
                            <select name="" id="timeSelect" class="form-select shadow-none border" >
                                <option value="" selected disabled>Select Time</option>
                            </select>
                        </div>
                    </div>
                    <p class="text-secondary">Please fill all the form before submit</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        Add Class
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
            </div>
        </div>
    </div>

    <!-- Update Class Modal -->
    <div class="modal fade" id="updateClassModal" tabindex="-1" aria-labelledby="updateClassModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form id="updateClassForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateClassModalLabel">Update Class</h5>
                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="classid">
                        <div class="d-flex mb-3 border-bottom pb-3">
                            <div class="col-4 pe-2">
                                <label class="form-label text-etec-color">Course</label>
                                <select id="updateCourseSelect" class="form-select shadow-none border">
                                    <option value="" selected disabled>Select Course</option>
                                </select>
                            </div>
                            <div class="col-4 pe-2">
                                <label class="form-label text-etec-color">Lesson</label>
                                <select id="updateLessonSelect" class="form-select shadow-none border">
                                    <option value="" selected disabled>Select Lesson</option>
                                </select>
                            </div>
                            <div class="col-4">
                                <label class="form-label text-etec-color">Status</label>
                                <select id="updateStatusSelect" class="form-select shadow-none border">
                                    <option value="" selected disabled>Select Status</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex mb-3">
                            <div class="col-4 pe-2">
                                <label class="form-label text-etec-color">Building</label>
                                <select id="updateBuildingSelect" class="form-select shadow-none border">
                                    <option value="" selected disabled>Select Building</option>
                                </select>
                            </div>
                            <div class="col-4 pe-2">
                                <label class="form-label text-etec-color">Floor</label>
                                <select id="updateFloorSelect" class="form-select shadow-none border">
                                    <option value="" selected disabled>Select Floor</option>
                                </select>
                            </div>
                            <div class="col-4">
                                <label class="form-label text-etec-color">Room</label>
                                <select id="updateRoomSelect" class="form-select shadow-none border">
                                    <option value="" selected disabled>Select Room</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex mb-3">
                            <div class="col-6 pe-2">
                                <label class="form-label text-etec-color">Term</label>
                                <select id="updateTermSelect" class="form-select shadow-none border">
                                    <option value="" selected disabled>Select Term</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-etec-color">Time</label>
                                <select id="updateTimeSelect" class="form-select shadow-none border">
                                    <option value="" selected disabled>Select Time</option>
                                </select>
                            </div>
                        </div>

                        <p class="text-secondary">Update the details as needed before saving.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Student Modal -->
    <div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border"> <!-- border + no shadow -->
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="addStudentModalLabel">Add Student</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <input type="hidden" name="" id="course_ID">
            <form id="addStudentForm">
                <div class="modal-body">
                    <!-- Full Name -->
                    <div class="mb-3">
                        <label for="fullname" class="form-label">Full Name</label>
                        <input type="text" class="form-control shadow-none border" id="fullname" name="fullname" placeholder="Enter full name" required>
                    </div>

                    <!-- Gender -->
                    <div class="mb-3">
                        <label for="gender" class="form-label">Gender</label>
                        <select class="form-select shadow-none border" id="gender" name="gender" required>
                        <option value="" selected disabled>Select gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        </select>
                    </div>

                    <!-- Telephone -->
                    <div class="mb-3">
                        <label for="tel" class="form-label">Telephone</label>
                        <input type="tel" class="form-control shadow-none border" id="tel" name="tel" placeholder="Enter telephone number" required>
                    </div>

                    <!-- transfer option -->
                    <div class="mb-3 d-none border-top pt-3" id="transferOption">
                        <label for="tel" class="form-label text-primary">Transfer to Other Class</label>
                        <input type="tel" class="form-control shadow-none border-primary" id="transferto" name="transferto" placeholder="This transfer is only for Basic IT course (Enter Class ID)">
                        <div id="transfertoError" class="text-danger mt-1 small"></div>
                    </div>

                </div>

                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary shadow-none">
                        Add Student
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
            </div>
        </div>
    </div>

    <!-- Transfer Class Modal -->
    <div class="modal fade" id="transferClassModal" tabindex="-1" aria-labelledby="transferClassModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border"> <!-- border + no shadow -->
                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="transferClassModalLabel">Transfer Class</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="transferClassForm">
                    <div class="modal-body">
                        
                        <!-- Instructor ID -->
                        <div class="mb-3">
                            <label for="instructor_id" class="">Instructor ID</label>
                            <!-- Error message -->
                            <div id="transferClassError" class="text-danger small mt-2 d-none"></div>
                            <input type="number" min="0" class="form-control shadow-none border" id="instructor_id" name="instructor_id" placeholder="Enter Instructor ID" required>
                        </div>

                        
                    </div>

                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            Transfer
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Pre-End -->
    <div class="modal fade" id="modalPreEnd" tabindex="-1" aria-labelledby="modalPreEndLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form id="preEndForm">
                    <div class="modal-header bg-secondary text-white">
                        <h5 class="modal-title" id="modalPreEndLabel">Confirm Pre-End</h5>
                        <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="" id="classIdconfirm">
                        <p class="mb-0">
                            Are you sure you want to <strong>pre-end</strong> this class?  
                            This action will mark the class as ending soon.
                        </p>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-secondary" id="confirmPreEndBtn">Confirm Pre-End</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: End Class -->
    <div class="modal fade" id="modalEnd" tabindex="-1" aria-labelledby="modalEndLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form id="endclassform">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="modalEndLabel">Confirm End Class</h5>
                        <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" id="classIdEnd">

                        <div class="mb-3">
                            <label for="endClassDate" class="form-label fw-semibold">Select End Date</label>
                            <input type="date" class="form-control shadow-none" id="endClassDate" required>
                        </div>

                        <p class="mb-0">
                            Are you sure you want to <strong>end</strong> this class?  
                            Once confirmed, the <strong>class result will be downloaded as a PDF</strong> and  
                            the <strong>class will be permanently removed from your account</strong>.  
                            This action cannot be undone.
                        </p>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-danger" id="confirmEndBtn">End Class</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Switch Class Modal -->
    <div class="modal fade" id="switchTeacherModal" tabindex="-1" aria-labelledby="transferClassModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border"> <!-- border + no shadow -->
                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="switchTeacherModal">Switch Instructor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="switchTeacherForm">
                    <div class="modal-body">
                        
                        <!-- Instructor ID -->
                        <div class="mb-3">
                            <label for="instructor_id_switch" class="">Instructor ID</label>
                            <!-- Error message -->
                            <div id="switchError" class="text-danger small mt-2 d-none"></div>
                            <input type="number" min="0" class="form-control shadow-none border" id="instructor_id_switch" name="instructor_id_switch" placeholder="Enter Instructor ID" required>
                        </div>

                        
                    </div>

                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary">
                            Transfer
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- QR Code Modal -->
    <div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-2 text-center py-4">
                <div class="text-center border-0">
                    <h5 class="modal-title mb-3" id="qrModalLabel">Scan to Register</h5>
                </div>
                <div class="text-center mx-auto mb-3">
                    <div id="qrcode" class=""></div>
                </div>
                <div class="text-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    $(document).ready(function(){

        let courses = [];
        let roadmaps = [];
        let buildings = [];
        let schedules = [];
        let allClasses = []; 

        function showLoadingOverlay() {
            $("#classes-container").html(`
                <div class="d-flex justify-content-center align-items-center w-100" style="min-height:200px;">
                    <div class="spinner-border text-primary" role="status" style="width: 4rem; height: 4rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `);
        }

        // --- Refresh button click ---
        $(document).on('click', '#refreshBtn', function() {
            showLoadingOverlay(); // Show spinner first
            // Optional: show loading animation or disable button
            const btn = $(this);
            btn.prop('disabled', true);

            // Re-fetch data
            fetchAllClasses();

            // Re-enable button after short delay or after fetch completes
            
            btn.prop('disabled', false);
            
        });

        function showAlert(message) {
            $('#successMessage').text(message);
            $('#successAlert').stop(true,true).fadeIn();
            setTimeout(() => $('#successAlert').fadeOut('slow'), 3000);
        }

        function toggleButtonLoading(button, isLoading, loadingText = 'Loading...') {
            const $btn = $(button);
            const $spinner = $btn.find('.spinner-border');
            const $text = $btn.find('.btn-text');

            if (isLoading) {    
                $btn.prop('disabled', true);
                $spinner.removeClass('d-none');
                $text.text(loadingText);
            } else {
                $btn.prop('disabled', false);
                $spinner.addClass('d-none');
                $text.text($btn.data('original-text') || 'Add Class');
            }
        }


        // --- Fetch Courses ---
        function getCourses(){
            let $select = $("#courseSelect");
            let $updateSelect = $('#updateCourseSelect');

            $select.prop('disabled', true).html('<option disabled selected>Loading courses...</option>');

            $.ajax({
                url: 'api.php?endpoint=course_getall',
                method: 'GET',
                dataType: 'json',
                success: function(res){
                    if(res.data){
                        courses = res.data;

                        $select.empty().append('<option value="" disabled selected>Select Course</option>');
                        $updateSelect.empty().append('<option value="" disabled selected>Select Course</option>');
                        $.each(courses, function(index, c){
                            $select.append(`<option value="${c.id}">${c.course}</option>`);
                            $updateSelect.append(`<option value="${c.id}">${c.course}</option>`);
                        });

                        $select.prop('disabled', false); // enable after loading
                        $updateSelect.prop('disabled', false); // enable after loading
                    } else {
                        $select.html('<option disabled selected>No courses found</option>');
                        $updateSelect.html('<option disabled selected>No courses found</option>');
                    }
                },
                error: function () {
                    $select.html('<option disabled selected>Failed to load courses</option>');
                    $updateSelect.html('<option disabled selected>Failed to load courses</option>');
                }
            });
        }
        getCourses()

        // --- Fetch Roadmaps (lessons) ---
        function getRoadmaps() {
            $.ajax({
                url: 'api.php?endpoint=roadmap_getall',
                method: 'GET',
                dataType: 'json',
                success: function(res) {
                    if(res.data && res.data.length > 0){
                        roadmaps = res.data; // store for filtering
                    }
                },
                error: function(){
                    console.error('Failed to load roadmaps');
                }
            });
        }
        getRoadmaps()

        // --- Filter Lessons by Course ---
        function filterLessonsByCourse(courseId, target = "add") {
            // Determine which select to update
            let $select = (target === "add") ? $("#lessonSelect") : $("#updateLessonSelect");

            $select.prop('disabled', true).empty().append('<option value="" disabled selected>Select Lesson</option>');

            if (!courseId) return;

            const filteredRoadmaps = roadmaps.filter(r => r.course_id == courseId);
            let hasLessons = false;

            if (filteredRoadmaps.length > 0) {
                $.each(filteredRoadmaps, function(index, roadmap) {
                    let lessons = roadmap.lessons;
                    if (typeof lessons === 'string') {
                        try { lessons = JSON.parse(lessons); } 
                        catch(e) { lessons = []; }
                    }

                    if (Array.isArray(lessons) && lessons.length > 0) {
                        hasLessons = true;
                        $.each(lessons, function(i, lesson) {
                            $select.append(`<option value="${lesson}">${lesson}</option>`);
                        });
                    }
                });
            }

            if (!hasLessons) {
                $select.prop('disabled', true)
                    .html('<option disabled selected>No lessons for this course</option>');
            } else {
                $select.prop('disabled', false);
            }
        }


        // For Add Modal
        $("#courseSelect").on("change", function() {
            const selectedCourseId = $(this).val();
            filterLessonsByCourse(selectedCourseId, "add");
        });

        // For Update Modal
        $("#updateCourseSelect").on("change", function() {
            const selectedCourseId = $(this).val();
            filterLessonsByCourse(selectedCourseId, "update");
        });

        
        // --- Fetch schedules (used by both Add + Update modals) ---
        function getSchedules() {
            let $statusSelect = $("#statusSelect");
            let $termSelect = $("#termSelect");
            let $timeSelect = $("#timeSelect");

            let $updateStatusSelect = $("#updateStatusSelect");
            let $updateTermSelect = $("#updateTermSelect");
            let $updateTimeSelect = $("#updateTimeSelect");

            // Disable and show loading state for both
            $statusSelect.prop('disabled', true).html('<option disabled selected>Loading status...</option>');
            $updateStatusSelect.prop('disabled', true).html('<option disabled selected>Loading status...</option>');
            $termSelect.prop('disabled', true).html('<option disabled selected>Select Term</option>');
            $timeSelect.prop('disabled', true).html('<option disabled selected>Select Time</option>');
            // $updateTermSelect.prop('disabled', true).html('<option disabled selected>Select Term</option>');
            // $updateTimeSelect.prop('disabled', true).html('<option disabled selected>Select Time</option>');

            $.ajax({
                url: 'api.php?endpoint=schedule_getall',
                method: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res.data && res.data.length > 0) {
                        schedules = res.data;
                        
                        // Get unique statuses
                        let uniqueStatuses = [...new Map(schedules.map(s => [s.class_type_id, s])).values()];

                        // Populate both add & update selects
                        $statusSelect.empty().append('<option value="" disabled selected>Select Status</option>');
                        $updateStatusSelect.empty().append('<option value="" disabled selected>Select Status</option>');

                        $.each(uniqueStatuses, function(i, s) {
                            $statusSelect.append(`<option value="${s.class_type_id}">${s.class_type_name}</option>`);
                            $updateStatusSelect.append(`<option value="${s.class_type_id}">${s.class_type_name}</option>`);
                        });

                        $statusSelect.prop('disabled', false);
                        $updateStatusSelect.prop('disabled', false);
                    } else {
                        $statusSelect.html('<option disabled selected>No schedules found</option>');
                        $updateStatusSelect.html('<option disabled selected>No schedules found</option>');
                    }
                },
                error: function() {
                    $statusSelect.html('<option disabled selected>Failed to load schedules</option>');
                    $updateStatusSelect.html('<option disabled selected>Failed to load schedules</option>');
                }
            });
        }

        // --- When Status changes ---
        $("#statusSelect").on("change", function(){
            const statusId = $(this).val();
            let $termSelect = $("#termSelect");
            let $timeSelect = $("#timeSelect");

            $termSelect.prop('disabled', true).empty().append('<option disabled selected>Select Term</option>');
            $timeSelect.prop('disabled', true).empty().append('<option disabled selected>Select Time</option>');

            if(!statusId) return;

            // Filter schedules by selected status
            const filtered = schedules.filter(s => s.class_type_id == statusId);

            if(filtered.length > 0){
                let uniqueTerms = [...new Map(filtered.map(f => [f.term_id, f])).values()];

                $.each(uniqueTerms, function(i, t){
                    $termSelect.append(`<option value="${t.term_id}">${t.term_name}</option>`);
                });

                $termSelect.prop('disabled', false);
            } else {
                $termSelect.html('<option disabled selected>No terms found</option>');
            }
        });

        // --- When Term changes ---
        $("#termSelect").on("change", function(){
            const termId = $(this).val();
            const statusId = $("#statusSelect").val();
            let $timeSelect = $("#timeSelect");

            $timeSelect.prop('disabled', true).empty().append('<option disabled selected>Select Time</option>');

            if(!termId || !statusId) return;

            // Filter schedules by status + term
            const filtered = schedules.filter(s => s.class_type_id == statusId && s.term_id == termId);

            if(filtered.length > 0){
                let timePairs = []; // array of {time, id}

                filtered.forEach(f => {
                    if(f.time_slots && f.time_id){
                        let slots = f.time_slots.split(",").map(t => t.trim());
                        let ids   = f.time_id.split(",").map(t => t.trim());

                        slots.forEach((slot, idx) => {
                            timePairs.push({time: slot, id: ids[idx] || slot});
                        });
                    }
                });

                // Remove duplicates based on time_id
                let uniqueTimes = Array.from(new Map(timePairs.map(tp => [tp.id, tp])).values());

                $.each(uniqueTimes, function(i, t){
                    $timeSelect.append(`<option value="${t.id}">${t.time}</option>`);
                });

                $timeSelect.prop('disabled', false);
            } else {
                $timeSelect.html('<option disabled selected>No times found</option>');
            }
        });


        // --- Initial load ---
        $("#termSelect, #timeSelect").prop('disabled', true);

        // --- When Update Status changes ---
        $("#updateStatusSelect").on("change", function(){
            const statusId = $(this).val();
            let $termSelect = $("#updateTermSelect");
            let $timeSelect = $("#updateTimeSelect");

            $termSelect.prop('disabled', true).empty().append('<option disabled selected>Select Term</option>');
            $timeSelect.prop('disabled', true).empty().append('<option disabled selected>Select Time</option>');

            if(!statusId) return;

            const filtered = schedules.filter(s => s.class_type_id == statusId);

            if(filtered.length > 0){
                let uniqueTerms = [...new Map(filtered.map(f => [f.term_id, f])).values()];

                $.each(uniqueTerms, function(i, t){
                    $termSelect.append(`<option value="${t.term_id}">${t.term_name}</option>`);
                });

                $termSelect.prop('disabled', false);
            } else {
                $termSelect.html('<option disabled selected>No terms found</option>');
            }
        });

        // --- When Update Term changes ---
        $("#updateTermSelect").on("change", function(){
            const termId = $(this).val();
            const statusId = $("#updateStatusSelect").val();
            let $timeSelect = $("#updateTimeSelect");

            $timeSelect.prop('disabled', true).empty().append('<option disabled selected>Select Time</option>');

            if(!termId || !statusId) return;

            // Filter schedules by status and term
            const filtered = schedules.filter(s => s.class_type_id == statusId && s.term_id == termId);

            if(filtered.length > 0){
                let timePairs = []; // will hold objects {time, time_id}

                filtered.forEach(f => {
                    if(f.time_slots && f.time_id){
                        let slots = f.time_slots.split(",").map(t => t.trim());
                        let ids   = f.time_id.split(",").map(t => t.trim());
                        
                        slots.forEach((slot, idx) => {
                            timePairs.push({time: slot, id: ids[idx] || slot});
                        });
                    }
                });

                // Remove duplicates based on time_id
                let uniqueTimes = Array.from(new Map(timePairs.map(tp => [tp.id, tp])).values());

                $.each(uniqueTimes, function(i, t){
                    $timeSelect.append(`<option value="${t.id}">${t.time}</option>`);
                });

                $timeSelect.prop('disabled', false);
            } else {
                $timeSelect.html('<option disabled selected>No times found</option>');
            }
        });

        getSchedules();

        // --- Handle Add Class Form Submit ---
        $("#addClassModal form").on("submit", function(e) {
            e.preventDefault();

            // Collect form values
            const lesson = $("#lessonSelect").val();
            const class_status = "progress";
            const course_id = parseInt($("#courseSelect").val());
            const instructor_id = 0; // optional, if you don't have instructor yet
            const building_id = parseInt($("#buildingSelect").val());
            const floor_id = parseInt($("#floorSelect").val());
            const room_id = parseInt($("#roomSelect").val());
            const class_type_id = parseInt($("#statusSelect").val()); // assuming statusSelect stores class_type_id
            const time_id = parseInt($("#timeSelect").val());
            const term_id = parseInt($("#termSelect").val());

            // Validation
            if (!class_status || !course_id || !building_id || !floor_id || !room_id || !class_type_id || !time_id || !term_id) {
                alert("Please fill all required fields!");
                return;
            }

            const $btn = $(this);
            $btn.data('original-text', $btn.find('.btn-text').text()); // store original text

            // Start spinner
            toggleButtonLoading($btn, true, 'Adding...');

            // Send AJAX request
            $.ajax({
                url: 'api.php?endpoint=class_create',
                method: 'POST',
                dataType: 'json',
                data: {
                    lesson,
                    class_status,
                    course_id,
                    instructor_id,
                    building_id,
                    floor_id,
                    room_id,
                    class_type_id,
                    time_id,
                    term_id
                },
                success: function(res) {
                    if(res.status){
                        // alert("Class created successfully!");
                        showAlert(res.message)
                        $("#addClassModal").modal('hide');
                        $("#addClassModal form")[0].reset();
                        fetchAllClasses()
                    } else {
                        alert("Failed to create class: " + res.message);
                    }
                },
                error: function(err) {
                    console.error(err);
                    alert("An error occurred while creating the class.");
                },
                complete: function () {
                    // Stop spinner
                    toggleButtonLoading($btn, false);
                }
            });

        });

        // --- Fetch Buildings ---
        function getBuildings() {
            let $select = $("#buildingSelect");
            let $updateSelect = $('#updateBuildingSelect');

            // Disable and show loading
            $select.prop('disabled', true).html('<option disabled selected>Loading buildings...</option>');

            $.ajax({
                url: 'api.php?endpoint=getAllBuildings',
                method: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res.status && res.data && res.data.length > 0) {
                        buildings = res.data; // store globally
                        
                        $select.empty().append('<option value="" disabled selected>Select Building</option>');
                        $updateSelect.empty().append('<option value="" disabled selected>Select Building</option>');

                        $.each(buildings, function(index, b) {
                            $select.append(`<option value="${b.building_id}">${b.building_name}</option>`);
                            $updateSelect.append(`<option value="${b.building_id}">${b.building_name}</option>`);
                        });

                        $select.prop('disabled', false);
                        $updateSelect.prop('disabled', false);
                    } else {
                        $select.html('<option disabled selected>No buildings found</option>');
                        $updateSelect.html('<option disabled selected>No buildings found</option>');
                    }
                },
                error: function() {
                    $select.html('<option disabled selected>Failed to load buildings</option>');
                    $updateSelect.html('<option disabled selected>Failed to load buildings</option>');
                }
            });
        }

        // --- Get Floors by Building ---
        function getFloors(buildingId) {
            let $select = $("#floorSelect");
            let $updateSelect = $('#updateFloorSelect');

            $select.prop("disabled", true).html('<option disabled selected>Loading floors...</option>');
            $updateSelect.prop("disabled", true).html('<option disabled selected>Select floors</option>');

            $.ajax({
                url: `api.php?endpoint=getFloors&building_id=${buildingId}`,
                method: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res.status && res.data.length > 0) {
                        $select.empty().append('<option value="" disabled selected>Select Floor</option>');
                        res.data.forEach(floor => {
                            $select.append(`<option value="${floor.id}">${floor.floor}</option>`);
                            $updateSelect.append(`<option value="${floor.id}">${floor.floor}</option>`);
                        });
                        $select.prop("disabled", false);
                        $updateSelect.prop("disabled", false);
                    } else {
                        $select.html('<option disabled selected>No floors found</option>');
                        $updateSelect.html('<option disabled selected>No floors found</option>');
                    }
                },
                error: function() {
                    $select.html('<option disabled selected>Failed to load floors</option>');
                    $updateSelect.html('<option disabled selected>Failed to load floors</option>');
                }
            });
        }

        function getRooms(buildingId, floorId, selectedRoomId = null) {
            let $select = $("#roomSelect");          // for add modal
            let $updateSelect = $("#updateRoomSelect"); // for update modal

            $select.prop("disabled", true).html('<option disabled selected>Loading rooms...</option>');
            $updateSelect.prop("disabled", true).html('<option disabled selected>Loading rooms...</option>');

            $.ajax({
                url: `api.php?endpoint=getRooms&building_id=${buildingId}&floor_id=${floorId}`,
                method: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res.status && res.data.length > 0) {
                        // ✅ Clear and populate both selects
                        $select.empty().append('<option value="" disabled selected>Select Room</option>');
                        $updateSelect.empty().append('<option value="" disabled selected>Select Room</option>');
                        
                        res.data.forEach(room => {
                            $select.append(`<option value="${room.room_id}">${room.room_name}</option>`);
                            $updateSelect.append(`<option value="${room.room_id}">${room.room_name}</option>`);
                        });

                        $select.prop("disabled", false);
                        $updateSelect.prop("disabled", false);

                        // ✅ Auto-select correct room in update modal
                        if (selectedRoomId) {
                            $updateSelect.val(selectedRoomId);

                            // fallback if not found
                            if (!$updateSelect.val()) {
                                const selectedRoom = res.data.find(r => r.room_id == selectedRoomId);
                                if (selectedRoom) {
                                    $updateSelect.append(`<option value="${selectedRoomId}" selected>${selectedRoom.room_name}</option>`);
                                }
                            }
                        }

                    } else {
                        $select.html('<option disabled selected>No rooms found</option>');
                        $updateSelect.html('<option disabled selected>No rooms found</option>');
                    }
                },
                error: function() {
                    $select.html('<option disabled selected>Failed to load rooms</option>');
                    $updateSelect.html('<option disabled selected>Failed to load rooms</option>');
                }
            });
        }


        // --- Fetch all classes once ---
        function fetchAllClasses() {
            $.ajax({
                url: 'api.php?endpoint=class_get_by_instructor',
                method: 'GET',
                dataType: 'json',
                success: function(res) {
                    if(res.status && res.data.length > 0) {
                        allClasses = res.data; // save data
                        renderClasses(allClasses); // render all initially
                    } else {
                        $('#classes-container').html(`
                            <div id="container" class="d-flex justify-content-center align-items-center" style="min-height: 200px;">
                                <div class="text-center">
                                    <!-- Optional: SVG or image illustration -->
                                    <img src="https://cdn-icons-png.flaticon.com/512/4076/4076549.png" alt="Empty" class="mb-3" style="width:80px; opacity:0.5;">
                                    <h5 class="text-muted">Looks like there are no classes yet</h5>
                                    <p class="text-muted">You can add a new class to get started.</p>
                                </div>
                            </div>
                        `);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    console.log('Response:', xhr.responseText);
                    // alert('Failed to fetch classes. Check console for details.');
                }
            });
        }

        // --- Render cards from array ---
        function renderClasses(classes) {
            let container = $('#classes-container');
            container.empty();
            
            if(classes.length === 0) {
                container.html(`
                <div id="container" class="d-flex justify-content-center align-items-center" style="min-height: 200px;">
                    <div class="text-center">
                        <!-- Optional: SVG or image illustration -->
                        <img src="https://cdn-icons-png.flaticon.com/512/4076/4076549.png" alt="Empty" class="mb-3" style="width:80px; opacity:0.5;">
                        <h5 class="text-muted">Looks like there are no classes yet</h5>
                        <p class="text-muted">You can add a new class to get started.</p>
                        <button class="btn btn-primary">Add New Class</button>
                    </div>
                </div>
            `);
                return;
            }
            
            classes.forEach(cls => {
                
                let cardBgClass = cls.class_status === "pre-end" ? "bg-secondary-subtle" : "bg-white";
                let border = cls.class_status === "pre-end" ? "border-light" : "";

                // Conditionally render the Transfer button
               let transferBtn = cls.course_id === 44
                    ? `<li>
                            <button class="btn btn-light border-bottom py-2 w-100 text-start rounded-0 transferclassbnt" 
                                data-id="${cls.id}" 
                                data-bs-toggle="modal" 
                                data-bs-target="#transferClassModal"
                                ${cls.isTransfer == 1 ? 'disabled title="Class already transferred"' : ''}>
                                <i class="bi bi-shuffle me-1"></i>
                                ${cls.isTransfer == 1 ? 'Transferred' : 'Transfer'}
                            </button>
                    </li>`
                    : '';

                let card = `
                <div class="col-md-4">
                    <div class="card shadow-sm border rounded h-100 ${cardBgClass}">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2 justify-content-between">
                                <div class="me-3 d-flex align-items-center fs-2">
                                    <i class="bi bi-house-fill text-etec-color me-2"></i>
                                        <h5 class="mb-0 fw-bold">${cls.course_name}</h5>
                                    </div>
                                    
                                    <div class="dropdown">
                                        <button class="btn btn-sm border-0" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical fs-5"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm py-0 overflow-hidden">
                                            <li>
                                                <button 
                                                    data-id="${cls.id}"
                                                    data-course_id = "${cls.course_id}"
                                                    data-course_name = "${cls.course_name}"
                                                    
                                                    data-lesson = "${cls.lesson}"
                                                    
                                                    data-building_id = "${cls.building_id}"
                                                    data-building_name = "${cls.building_name}"

                                                    data-floor_id = "${cls.floor_id}"
                                                    data-floor = "${cls.floor}"

                                                    data-room_id = "${cls.room_id}"
                                                    data-room = "${cls.room}"
                                                    
                                                    data-class_type_id = "${cls.class_type_id}"
                                                    data-class_type = "${cls.class_type}"

                                                    data-term_id = "${cls.term_id}"
                                                    data-term = "${cls.term}"

                                                    data-time_id = "${cls.time_id}"
                                                    data-time = "${cls.time}"


                                                    class="btn btn-light border-bottom rounded-0 py-2 w-100 text-start update-class-btn" data-bs-toggle="modal" data-bs-target="#updateClassModal">
                                                    <i class="bi bi-pencil me-1"></i> Edit Class
                                                </button>
                                            </li>
                                            <li>
                                                <button class="btn btn-light border-bottom rounded-0 py-2 w-100 text-start addStu "  data-course_id="${cls.course_id}"  data-id="${cls.id}" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                                                    <i class="bi bi-person-add me-1"></i> Add Student
                                                </button>
                                            </li>
                                            <li>
                                                <button class="btn btn-light border-bottom rounded-0 py-2 w-100 text-start qrBtn" data-instructor_id="<?= $instructor_id ?>" data-id="${cls.id}" data-bs-toggle="modal" data-bs-target="#qrModal">
                                                    <i class="bi bi-qr-code me-1"></i> QR Add
                                                </button>
                                            </li>
                                            <li>
                                                <button class="btn btn-light border-bottom py-2 w-100 text-start rounded-0 swtichTeacherbtn" data-id="${cls.id}" data-bs-toggle="modal" data-bs-target="#switchTeacherModal">
                                                    <i class="bi bi-arrow-left-right me-1"></i> Switch Teacher
                                                </button>
                                            </li>                    
                                            ${transferBtn}
                                            <li>
                                                <button class="btn btn-light border-bottom py-2 w-100 text-start rounded-0 btnPreEnd" data-id="${cls.id}" data-bs-toggle="modal" data-bs-target="#modalPreEnd"  ${cls.class_status === 'pre-end' ? 'disabled title="Class already pre-ended"' : ''}>
                                                ${cls.class_status === 'pre-end' ? 'Pre-Ended' : ' Pre-End'}
                                                </button>
                                            </li>
                                            <li>
                                                <button class="btn btn-danger  py-2 w-100 text-start rounded-0 btnEnd"  data-id="${cls.id}" data-bs-toggle="modal" data-bs-target="#modalEnd">
                                                    End
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                            </div>
                            <p class="mb-3">Class id: <span class="bg-etec-color text-light px-2 rounded">${cls.id}</span></p>
                            <div class="d-flex align-items-center mb-2 pb-2 border-bottom ${border}">
                                <div class="col-4"><strong>Class Lessons:</strong></div>
                                <div class="col-8">
                                    <span class="px-2 bg-secondary-subtle text-dark rounded"> 
                                        ${cls.lesson && cls.lesson.trim() !== "" ? cls.lesson : "No lesson"}
                                    </span>
                                </div>
                            </div>

                            <div class="d-flex align-items-center mb-2 pb-2 border-bottom ${border}">
                                <div class="col-4"><strong>Building:</strong></div>
                                <div class="col-8">
                                    <p class="m-0 fw-meduim">${cls.building_name}</p>
                                </div>
                            </div>

                            <div class="d-flex align-items-center mb-2 pb-2 border-bottom ${border}">
                                <div class="col-4"><strong>FLoor & Room:</strong></div>
                                <div class="col-8">${cls.floor_name} - <span class="text-etec-color fw-medium">Room: (${cls.room_name})</span></div>
                            </div>

                            <div class="d-flex align-items-center mb-2 pb-2 border-bottom ${border}">
                                <div class="col-4"><strong>Status:</strong></div>
                                <div class="col-8"><span class="text-etec-color fw-medium">${cls.class_type}</span></div>
                            </div>

                            <div class="d-flex align-items-center mb-2 pb-2 border-bottom ${border}">
                                <div class="col-4"><strong>Term:</strong></div>
                                <div class="col-8">${cls.term_name}</div>
                            </div>

                            <div class="d-flex align-items-center mb-2 pb-2 border-bottom ${border}">
                                <div class="col-4"><strong>Time:</strong></div>
                                <div class="col-8 text-success fw-medium">${cls.time}</div>
                            </div>

                            <div class="d-flex align-items-center mb-2 pb-2 border-bottom ${border}">
                                <div class="col-4"><strong>Total Stu:</strong></div>
                                <div class="col-8">${cls.total_stu}</div>
                            </div>

                            <a href="pages/frontend/students.php" 
                                class="view-class"
                                data-id="${cls.id}">
                                <button class="btn btn-light border w-100 mt-3 text-etec-color">
                                    View Class
                                </button>
                            </a>
                        </div>
                    </div>
                </div>
                `;
                container.append(card);
            });
        }

        // --- Filter on search input ---
        $('#search-class').on('input', function() {
            let keyword = $(this).val().toLowerCase();
            let filtered = allClasses.filter(cls => cls.lesson.toLowerCase().includes(keyword));
            renderClasses(filtered);
        });

        // When Update Building changes
        $("#updateBuildingSelect").on("change", function() {
            const buildingId = $(this).val();
            const $floorSelect = $("#updateFloorSelect");
            const $roomSelect = $("#updateRoomSelect");

            if (!buildingId) {
                $floorSelect.prop("disabled", true).html('<option disabled selected>Select Floor</option>');
                $roomSelect.prop("disabled", true).html('<option disabled selected>Select Room</option>');
                return;
            }

            // Fetch floors for selected building
            getFloors(buildingId);

            // Clear and disable room until floor is selected
            $roomSelect.prop("disabled", true).html('<option disabled selected>Select Room</option>');
        });

        // --- When Building changes ---
        $("#buildingSelect").on("change", function() {
            const buildingId = $(this).val();
            const $floorSelect = $("#floorSelect");
            if (!buildingId) {
                // If no building selected, disable the floor select
                $floorSelect.prop("disabled", true)
                            .html('<option disabled selected>Select Floor</option>');
                return;
            }
            getFloors(buildingId); // Load floors for selected building
        });

        // Example usage after selecting floor
        $("#floorSelect").on("change", function() {
            const buildingId = $("#buildingSelect").val();
            const floorId = $(this).val();

            const $roomSelect = $("#roomSelect");
            if (!buildingId && floorId) {
                // If no building selected, disable the floor select
                $roomSelect.prop("disabled", true)
                            .html('<option disabled selected>Select Room</option>');
                return;
            }

            getRooms(buildingId, floorId);
        });

        // When Update Floor changes
        $("#updateFloorSelect").on("change", function() {
            const floorId = $(this).val();
            const buildingId = $("#updateBuildingSelect").val();
            const $roomSelect = $("#updateRoomSelect");

            if (!floorId || !buildingId) {
                $roomSelect.prop("disabled", true).html('<option disabled selected>Select Room</option>');
                return;
            }

            // Fetch rooms for selected building + floor
            getRooms(buildingId, floorId);
        });


        $("#lessonSelect").prop("disabled", true)
                            .html('<option disabled selected>Select Lesson</option>');
        $("#roomSelect").prop("disabled", true)
                            .html('<option disabled selected>Select Room</option>');
        $("#floorSelect").prop("disabled", true)
                            .html('<option disabled selected>Select Floor</option>');
        getBuildings() 

        fetchAllClasses()

      
        $(document).on("click", ".update-class-btn", function() {
            // --- Get data from button ---
            const classId = $(this).data("id");
            const courseId = $(this).data("course_id");
            const courseName = $(this).data("course_name");
            const lesson = $(this).data("lesson");
            const buildingId = $(this).data("building_id");
            const building = $(this).data("building_name");
            const statusId = $(this).data("class_type_id");
            const termId = $(this).data("term_id");
            const termName = $(this).data("term");
            const timeId = $(this).data("time_id");
            const timeName = $(this).data("time");
            const floorId = $(this).data("floor_id");
            const floorName = $(this).data("floor_name");
            const roomId = $(this).data("room_id");
            const roomName = $(this).data("room_name");

            // --- Set class basic info ---
            $("#classid").val(classId);
            $("#updateCourseSelect").val(courseId);
            filterLessonsByCourse(courseId, "update"); // Populate lessons
            $("#updateLessonSelect").val(lesson);
            $("#updateBuildingSelect").val(buildingId);
            $("#updateStatusSelect").val(statusId);

            // --- Fetch and set floors ---
            getFloors(buildingId);
            setTimeout(() => {
                $("#updateFloorSelect").val(floorId);
                if (!$("#updateFloorSelect").val() && floorId) {
                    $("#updateFloorSelect").append(`<option value="${floorId}" selected>${floorName}</option>`);
                }
            }, 400); // Wait for AJAX to complete

            // --- Fetch and set rooms ---
            getRooms(buildingId, floorId, roomId);

            // --- Populate Term Select ---
            const $termSelect = $("#updateTermSelect");
            $termSelect.empty().append('<option disabled selected>Select Term</option>');

            const filteredTerms = schedules.filter(s => s.class_type_id == statusId);
            if (filteredTerms.length > 0) {
                // Remove duplicate terms by term_id
                const uniqueTerms = [...new Map(filteredTerms.map(t => [t.term_id, t])).values()];
                uniqueTerms.forEach(t => {
                    $termSelect.append(`<option value="${t.term_id}">${t.term_name}</option>`);
                });

                // Select current term
                $termSelect.val(termId);
                if (!$termSelect.val() && termId) {
                    $termSelect.append(`<option value="${termId}" selected>${termName}</option>`);
                }

                // --- Populate Time Select ---
                const $timeSelect = $("#updateTimeSelect");
                $timeSelect.empty().append('<option disabled selected>Select Time</option>');

                const timeFiltered = schedules.filter(s => s.class_type_id == statusId && s.term_id == termId);
                
                if (timeFiltered.length > 0) {
                    let times = [];

                    timeFiltered.forEach(f => {
                        // Split IDs and names if there are multiple
                        const ids = f.time_id ? f.time_id.split(",").map(id => id.trim()) : [];
                        const names = f.time_name
                            ? f.time_name.split(",").map(n => n.trim())
                            : f.time_slots
                            ? f.time_slots.split(",").map(t => t.trim())
                            : [];

                        // Pair each time_id with its matching time name
                        names.forEach((name, i) => {
                            const id = ids[i] || ids[0] || name; // fallback if mismatch
                            times.push({ id, name });
                        });
                    });

                    // Remove duplicates by id + name
                    const uniqueTimes = Array.from(new Map(times.map(t => [t.id + "_" + t.name, t])).values());

                    // Populate dropdown
                    uniqueTimes.forEach(t => {
                        $timeSelect.append(`<option value="${t.id}">${t.name}</option>`);
                    });

                    // Select current time
                    $timeSelect.val(timeId);
                    if (!$timeSelect.val() && timeId) {
                        $timeSelect.append(`<option value="${timeId}" selected>${timeName}</option>`);
                    }


                } else {
                    $timeSelect.html('<option disabled selected>No times available</option>');
                }
            } else {
                $termSelect.html('<option disabled selected>No terms available</option>');
                $("#updateTimeSelect").html('<option disabled selected>No times available</option>');
            }
        });


        $("#updateClassForm").on("submit", function(e){
            e.preventDefault();

            const data = {
                id: $("#classid").val(),
                lesson: $("#updateLessonSelect").val(),
                class_status:"progress",
                course_id: $("#updateCourseSelect").val(),
                building_id: $("#updateBuildingSelect").val(),
                floor_id: $("#updateFloorSelect").val(),
                room_id: $("#updateRoomSelect").val(),
                class_type_id: $("#updateStatusSelect").val(),
                time_id: $("#updateTimeSelect").val(),
                term_id: $("#updateTermSelect").val()
            };
            
            $.ajax({
                url: "api.php?endpoint=class_update",
                method: "POST",
                data: data,
                dataType: "json",
                success: function(res){
                    if(res.status){
                        // alert(res.message);
                        showAlert(res.message)
                        $("#updateClassModal").modal("hide");
                        // optionally refresh the class list
                        fetchAllClasses();
                    } else {
                        alert(res.message);
                    }
                },
                error: function(err){
                    console.error(err);
                    alert("Failed to update class");
                }
            });
        });

        $(document).on('click', '.addStu', function() {
            const classId = $(this).data('id'); // Get class ID from data-id
            const courseID = $(this).data('course_id'); // Get class ID from data-id

            $('#addStudentForm').data('class-id', classId); // Store it in form for later
            $('#course_ID').val(courseID); // Store it in form for later

            if(courseID == 44){
                $('#transferOption').removeClass('d-none')
            }else{
                $('#transferOption').addClass('d-none')
            }
        });

        $('#addStudentForm').on('submit', function(e) {
            e.preventDefault();

            const transferto = $('#transferto').val().trim();
            const classId = $(this).data('class-id');

            const formData = {
                fullname: $('#fullname').val(),
                gender: $('#gender').val(),
                tel: $('#tel').val(),
                instructor_id: $('#instructor').val(), // make sure to include this
                class_id: classId,
                transferTo: transferto || null // send null if empty
            };

            const $submitBtn = $(this).find('button[type="submit"]');
            toggleButtonLoading($submitBtn, true, 'Adding...');

            $.ajax({
                url: 'api.php?endpoint=create_stu',
                method: 'POST',
                data: formData,
                dataType: 'json',
                success: function(res) {
                    toggleButtonLoading($submitBtn, false);

                    // Clear old error state first
                    $('#transferto').removeClass('is-invalid border-danger').addClass('border-primary');
                    $('#transfertoError').text('');

                    if (res.status) {
                        fetchAllClasses();
                        showAlert(res.message);

                        const currentTransfer = $('#transferto').val();

                        $('#addStudentForm')[0].reset();

                        $('#transferto').val(currentTransfer);
                    } else {
                        // 🔥 If there's an error about class not existing
                        if (res.message.toLowerCase().includes('class id')) {
                            $('#transferto').removeClass('border-primary').addClass('is-invalid border-danger');
                            $('#transfertoError').text(res.message);
                        } else {
                            showAlert(res.message, 'danger');
                        }
                    }
                },
                error: function(err) {
                    toggleButtonLoading($submitBtn, false);
                    console.error(err);
                }
            });

        });


        $(document).on('click', '.transferclassbnt', function() {
            const classId = $(this).data('id');           // get class id from button
            $('#transferClassForm').data('class-id', classId); // store it in the form
        });

        $('#transferClassForm').on('submit', function(e) {
            e.preventDefault();

            const classId = $(this).data('class-id'); // get class ID
            const instructorId = $('#instructor_id').val();
            const $errorDiv = $('#transferClassError');

            const $submitBtn = $(this).find('button[type="submit"]');

            // Show spinner/loading
            toggleButtonLoading($submitBtn, true, 'Transferring...');

            $.ajax({
                url: 'api.php?endpoint=transferClass', // use endpoint instead of action
                method: 'POST',
                data: {
                    class_id: classId,
                    instructor_id: instructorId
                },
                dataType: 'json',
                success: function(res) {
                    if (res.status) {
                        $('#transferClassForm')[0].reset();
                        $('#transferClassModal').modal('hide');
                        // Optionally refresh class list
                    } else {
                        console.log(res.message);
                        $errorDiv.removeClass('d-none').text(res.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error(error);
                    console.log("Something went wrong!");
                },
                complete: function() {
                    toggleButtonLoading($submitBtn, false);
                }
            });
        });

        $(document).on("click", ".view-class", function(e) {
            e.preventDefault();

            const url = $(this).attr("href");
            const classId = parseInt($(this).data("id"));

            if (!classId || classId <= 0) {
                alert("Class ID missing!");
                return;
            }

            // Add timestamp to avoid caching
            const fullUrl = url.includes("?") 
                ? `${url}&class_id=${classId}&_=${new Date().getTime()}`
                : `${url}?class_id=${classId}&_=${new Date().getTime()}`;

            $("#content-area").html(`
                   <div id="loading-overlay" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-25 d-flex justify-content-center align-items-center text-center text-white" style="z-index: 2000; display: none;">
                        <div>
                            <div class="spinner-border text-light mb-3" role="status" style="width: 4rem; height: 4rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <h5 class="font-custom">Loading, please wait...</h5>
                        </div>
                    </div>
            `);
            
            // Load students.php into #content-area dynamically
            $("#content-area").load(fullUrl, function(response, status, xhr) {
                if (status === "error") {
                    console.error("Load failed:", xhr.status, xhr.statusText);
                }
            });
        });

        $(document).on('click', '.btnPreEnd', function() {
            const classId = $(this).data('id'); // Get class ID from data-id
            $('#classIdconfirm').val(classId)
        });
        

        $('#preEndForm').on('submit', function(e) {
            e.preventDefault(); // prevent default form submit

            const classId = $('#classIdconfirm').val(); // get class ID
            const classStatus = "pre-end"; // status to update

            $.ajax({
                url: 'api.php?endpoint=update_class_status', // replace with your backend PHP file
                type: 'POST',
                data: {
                    class_id: classId,
                    class_status: classStatus
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        showAlert(response.message); // show success message
                        $('#modalPreEnd').modal('hide'); // close modal
                        fetchAllClasses() 
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                }
            });
        });

        $(document).on('click', '.btnEnd', function() {
            const classId = $(this).data('id'); // Get class ID from data-id
            $('#classIdEnd').val(classId)
        });

        let selectedClassId = null;
            // 1️⃣ Capture which class button was clicked
        $(document).on('click','.swtichTeacherbtn', function() {
            selectedClassId = $(this).data("id");
            // alert(selectedClassId)
            $("#instructor_id_switch").val("");
            $("#switchError").addClass("d-none").text("");
            $("#switchTeacherForm button[type='submit'] .spinner-border").addClass("d-none");
            
        });

        // 2️⃣ Handle form submission via AJAX
        $("#switchTeacherForm").on("submit", function(e) {
            e.preventDefault();

            const instructorId = $("#instructor_id_switch").val().trim();
            const $errorDiv = $("#switchError");
            const $submitBtn = $("#switchTeacherForm");
            const $spinner = $submitBtn.find(".spinner-border");

            
            if (!selectedClassId || !instructorId) {
                $errorDiv.text("Please enter a valid Instructor ID.").removeClass("d-none");
                return;
            }

            // Show spinner and disable button
            $spinner.removeClass("d-none");
            $submitBtn.prop("disabled", true);
            $errorDiv.addClass("d-none").text("");

            $.ajax({
                url: "api.php?endpoint=switch_instrutor", // replace with your PHP backend
                type: "POST",
                data: {
                    class_id: selectedClassId,
                    instructor_id: instructorId
                },
                dataType: "json",
                success: function(data) {
                    if (data.status) {
                        showAlert(data.message);
                        $("#switchTeacherModal").modal("hide");
                        fetchAllClasses()   
                    } else {
                        $errorDiv.text(data.message || "Something went wrong.").removeClass("d-none");
                    }
                },
                error: function(xhr, status, error) {
                    $errorDiv.text("Network error: " + error).removeClass("d-none");
                },
                complete: function() {
                    $spinner.addClass("d-none");
                    $submitBtn.prop("disabled", false);
                }
            });
        });

        $('#endclassform').on('submit', function(e) {
            e.preventDefault();

            const classId = $('#classIdEnd').val();
            const endDate = $('#endClassDate').val();

            if (!endDate) {
                alert("Please select an end date!");
                return;
            }

            const classStatus = "end"; // set the new status

            // First, update the class status to 'end'
            $.ajax({
                url: 'api.php?endpoint=update_class_status', // same API endpoint
                type: 'POST',
                data: {
                    class_id: classId,
                    class_status: classStatus
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        showAlert(response.message); // show success message
                        $('#modalEnd').modal('hide'); // close modal (if you have one)
                        fetchAllClasses(); // refresh classes list

                        // After successful update, open the result page
                        const url = `result.php?class_id=${classId}&end_date=${endDate}`;
                        window.open(url, '_blank');
                    } else {
                        alert("Failed to update class status: " + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                    alert("Error updating class status. Check console for details.");
                }
            });
        });


        // When QR button is clicked
        $(document).on('click', '.qrBtn', function() {
            const classId = $(this).data('id');
            const instructor_id = $(this).data('instructor_id');
          
            generateQR(classId,instructor_id);
        });

       function generateQR(classId, instructor_id) {
			`https://qr-scann-register.42web.io/?class_id=${classId}&instructor_id=${instructor_id}`
            // Direct link to your form page using plain IDs
            // const qrLink = `https://etec-system.42web.io/formaddstudent.php?class_id=${classId}&instructor_id=${instructor_id}`;
            const qrLink = `https://qr-scann-register.42web.io?class_id=${classId}&instructor_id=${instructor_id}`;

            // Clear old QR first
            $('#qrcode').empty();

            // Generate QR
            new QRCode(document.getElementById("qrcode"), {
                text: qrLink,
                width: 350,
                height: 350,
            });
        }

    });
</script>