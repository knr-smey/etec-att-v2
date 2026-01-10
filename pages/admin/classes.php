<div class="container-fluid p-0">
   

    <div class="card p-0 border-0">

       
        <!-- Header -->
        <div class="card-header bg-white border-0 p-0 d-flex align-items-center justify-content-between">
            <h4 class="fw-bold text-etec-color mb-0">
                <i class="bi bi-house-fill me-2 text-etec-color"></i> Class Management
            </h4>

            <div>
               <p id="totalClass" class="mb-0 fw-bold text-etec-color">Total Class: 0</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="card-body pb-0 p-0 my-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" id="searchInput" class="form-control border shadow-none"
                        placeholder="Search by class, instructor, or time">
                    </div>
                </div>

                <div class="col-md-2">
                    <select id="courseFilter" class="form-select shadow-none border">
                        <option value="">All Courses</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <select id="typeFilter" class="form-select shadow-none border">
                        <option value="">Type</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <select id="termFilter" class="form-select shadow-none border" >
                        <option value="">Term</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <select id="timeFilter" class="form-select shadow-none border" >
                        <option value="">Time</option>
                    </select>
                </div>

                <div class="col-md-1">
                    <select id="statusFilter" class="form-select shadow-none border">
                        <option value="">Status</option>
                        <option value="progress">Progress</option>
                        <option value="pre-end">Pre-End</option>
                        <option value="end">End</option>
                    </select>
                </div>

            </div>
        </div>

        <div id="successAlert" class="alert alert-success alert-dismissible fade show mt-3" style="display:none;" role="alert">
            <span id="successMessage"></span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <!-- Table -->
        <div class="table-responsive mt-3">
            <table class="table table-bordered align-middle mb-0" id="classTable">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Class ID</th>
                    <th>Teacher</th>
                    <th>Course / Lesson</th>
                    <th>Term & Time</th>
                    <th>Building Floor & Room</th>
                    <th class="text-center">Students</th>
                    <th>Status</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    // Number of skeleton rows to show
                    $skeletonRows = 5;

                    for ($i = 0; $i < $skeletonRows; $i++) { 
                        ?>
                        <tr>
                            <td><span class="placeholder col-1 placeholder-glow">&nbsp;</span></td>
                            <td><span class="placeholder col-2 placeholder-glow">&nbsp;</span></td>
                            <td><span class="placeholder col-3 placeholder-glow">&nbsp;</span></td>
                            <td>
                                <div class="placeholder-glow mb-1"><span class="placeholder col-6"></span></div>
                                <div class="placeholder-glow"><span class="placeholder col-4"></span></div>
                                <div class="placeholder-glow"><span class="placeholder col-3"></span></div>
                            </td>
                            <td>
                                <div class="placeholder-glow"><span class="placeholder col-3"></span></div>
                                <div class="placeholder-glow"><span class="placeholder col-2"></span></div>
                            </td>
                            <td>
                                <div class="placeholder-glow"><span class="placeholder col-3"></span></div>
                                <div class="placeholder-glow"><span class="placeholder col-4"></span></div>
                            </td>
                            <td class="text-center"><span class="placeholder col-1 placeholder-glow">&nbsp;</span></td>
                            <td><span class="placeholder col-2 placeholder-glow">&nbsp;</span></td>
                            <td class="text-center">
                                <span class="placeholder col-2 placeholder-glow">&nbsp;</span>
                            </td>
                        </tr>
                    <?php 
                    } 
                ?>
            </tbody>

            </table>
        </div> 

        <!-- Pagination -->
        <div class="card-footer bg-white border-0">
        <nav>
            <ul class="pagination justify-content-end mt-3 mb-0" id="pagination"></ul>
        </nav>
        </div>

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteClassModal" tabindex="-1" aria-labelledby="deleteClassModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-danger" id="deleteClassModalLabel">
                    <i class="bi bi-exclamation-triangle-fill me-2 text-danger"></i> Confirm Deletion
                    </h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body text-center">
                    <p class="fs-5 mb-3">Are you sure you want to delete this class?</p>
                    <p class="text-muted small mb-0">This action cannot be undone.</p>
                </div>

                <div class="modal-footer border-0 justify-content-center">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </button>
                    <button type="button" id="confirmDeleteClass" class="btn btn-danger px-4">
                        <i class="bi bi-trash3-fill me-1"></i> Yes, Delete
                    </button>
                </div>
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
                            <input type="hidden" id="instructorId">
                            <div class="d-flex mb-3 border-bottom pb-3">
                                <div class="col-4 pe-2">
                                    <label class="form-label text-etec-color">Course</label>
                                    <select required id="updateCourseSelect" class="form-select shadow-none border">
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
                                    <select required id="updateStatusSelect" class="form-select shadow-none border">
                                        <option value="" selected disabled>Select Status</option>
                                    </select>
                                </div>
                            </div>

                            <div class="d-flex mb-3">
                                <div class="col-4 pe-2">
                                    <label class="form-label text-etec-color">Building</label>
                                    <select required id="updateBuildingSelect" class="form-select shadow-none border">
                                        <option value="" selected disabled>Select Building</option>
                                    </select>
                                </div>
                                <div class="col-4 pe-2">
                                    <label class="form-label text-etec-color">Floor</label>
                                    <select required id="updateFloorSelect" class="form-select shadow-none border">
                                        <option value="" selected disabled>Select Floor</option>
                                    </select>
                                </div>
                                <div class="col-4">
                                    <label class="form-label text-etec-color">Room</label>
                                    <select required id="updateRoomSelect" class="form-select shadow-none border">
                                        <option value="" selected disabled>Select Room</option>
                                    </select>
                                </div>
                            </div>

                            <div class="d-flex mb-3">
                                <div class="col-6 pe-2">
                                    <label class="form-label text-etec-color">Term</label>
                                    <select required id="updateTermSelect" class="form-select shadow-none border">
                                        <option value="" selected disabled>Select Term</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label text-etec-color">Time</label>
                                    <select required id="updateTimeSelect" class="form-select shadow-none border">
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

    </div>
</div>

<!-- jQuery + Script -->
<script>
    $(document).ready(function() {
        const limit = 7; 
        let currentPage = 1;
        let typingTimer;
        const typingDelay = 300;
        let ajaxRequest = null;
        let classIdToDelete = null;
        let courses = [];
        let roadmaps = [];
        let buildings = [];
        let schedules = [];
        let allClasses = []; 

        function showAlert(message) {
            $('#successMessage').text(message);
            $('#successAlert').stop(true,true).fadeIn();
            setTimeout(() => $('#successAlert').fadeOut('slow'), 3000);
        }

        function fetchClasses(page = 1) {
            currentPage = page;
            const search = $('#searchInput').val().trim();
            const course = $('#courseFilter').val();
            const type   = $('#typeFilter').val();
            const term   = $('#termFilter').val();
            const time   = $('#timeFilter').val();
            const status = $('#statusFilter').val(); // <-- new

            $.ajax({
                url: 'api.php',
                method: 'GET',
                data: {
                    endpoint: 'getAllClasses',
                    page: page,
                    limit: limit,
                    search: search,
                    course: course,
                    type: type,
                    term: term,
                    time: time,
                    class_status: status // <-- send to backend
                },
                dataType: 'json',
                success: function(res) {
                    // console.table(res.data.classes)
                    if (res.status) {
                        renderTable(res.data.classes);
                        renderPagination(res.data.total_pages);
                    } else {
                        $('#classTable tbody').html('<tr><td colspan="9" class="text-center">No classes found</td></tr>');
                        $('#pagination').empty();
                    }
                },
                error: function(err) {
                    console.error(err);
                }
            });
        }

        // --- Fetch Courses ---
        function getCourses(){
            
            let $updateSelect = $('#updateCourseSelect');
            $.ajax({
                url: 'api.php?endpoint=course_getall',
                method: 'GET',
                dataType: 'json',
                success: function(res){
                    if(res.data){
                        courses = res.data;
                        $updateSelect.empty().append('<option value="" disabled selected>Select Course</option>');
                        $.each(courses, function(index, c){                
                            $updateSelect.append(`<option value="${c.id}">${c.course}</option>`);
                        });

                        $updateSelect.prop('disabled', false); // enable after loading
                    } else {                   
                        $updateSelect.html('<option disabled selected>No courses found</option>');
                    }
                },
                error: function () {              
                    $updateSelect.html('<option disabled selected>Failed to load courses</option>');
                }
            });
        }
        getCourses() // call function

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
        getRoadmaps() // call function

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

        // For Update Modal
        $("#updateCourseSelect").on("change", function() {
            const selectedCourseId = $(this).val();
            filterLessonsByCourse(selectedCourseId, "update");
        });

        // --- Fetch schedules (used by both Add + Update modals) ---
        function getSchedules() {

            let $updateStatusSelect = $("#updateStatusSelect");
            let $updateTermSelect = $("#updateTermSelect");
            let $updateTimeSelect = $("#updateTimeSelect");

            $updateStatusSelect.prop('disabled', true).html('<option disabled selected>Loading status...</option>');


            $.ajax({
                url: 'api.php?endpoint=schedule_getall',
                method: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res.data && res.data.length > 0) {
                        schedules = res.data;
                        
                        // Get unique statuses
                        let uniqueStatuses = [...new Map(schedules.map(s => [s.class_type_id, s])).values()];

                        $updateStatusSelect.empty().append('<option value="" disabled selected>Select Status</option>');

                        $.each(uniqueStatuses, function(i, s) {
                            $updateStatusSelect.append(`<option value="${s.class_type_id}">${s.class_type_name}</option>`);
                        });
                        $updateStatusSelect.prop('disabled', false);
                    } else {
                        $updateStatusSelect.html('<option disabled selected>No schedules found</option>');
                    }
                },
                error: function() {
                    $updateStatusSelect.html('<option disabled selected>Failed to load schedules</option>');
                }
            });
        }

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


        // --- Fetch Buildings ---
        function getBuildings() {
            
            let $updateSelect = $('#updateBuildingSelect');

        
            $.ajax({
                url: 'api.php?endpoint=getAllBuildings',
                method: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res.status && res.data && res.data.length > 0) {
                        buildings = res.data; // store globally
                        
                        $updateSelect.empty().append('<option value="" disabled selected>Select Building</option>');

                        $.each(buildings, function(index, b) {
                            $updateSelect.append(`<option value="${b.building_id}">${b.building_name}</option>`);
                        });

                        $updateSelect.prop('disabled', false);
                    } else {
                        $updateSelect.html('<option disabled selected>No buildings found</option>');
                    }
                },
                error: function() {
                    $updateSelect.html('<option disabled selected>Failed to load buildings</option>');
                }
            });
        }
        getBuildings() 

        function getFloors(buildingId) {
            let $updateSelect = $('#updateFloorSelect');

            // Clear old floors completely first
            $updateSelect.prop("disabled", true)
                .empty()
                .append('<option disabled selected>Select floors</option>');

            $.ajax({
                url: `api.php?endpoint=getFloors&building_id=${buildingId}`,
                method: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res.status && res.data.length > 0) {
                        // Append unique floors only
                        const uniqueFloors = [];
                        res.data.forEach(floor => {
                            if (!uniqueFloors.includes(floor.floor)) {
                                uniqueFloors.push(floor.floor);
                                $updateSelect.append(`<option value="${floor.id}">${floor.floor}</option>`);
                            }
                        });
                        $updateSelect.prop("disabled", false);
                    } else {
                        $updateSelect.html('<option disabled selected>No floors found</option>');
                    }
                },
                error: function() {
                    $updateSelect.html('<option disabled selected>Failed to load floors</option>');
                }
            });
        }



        function getRooms(buildingId, floorId, selectedRoomId = null) {
                    // for add modal
            let $updateSelect = $("#updateRoomSelect"); // for update modal

            $updateSelect.prop("disabled", true).html('<option disabled selected>Loading rooms...</option>');

            $.ajax({
                url: `api.php?endpoint=getRooms&building_id=${buildingId}&floor_id=${floorId}`,
                method: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res.status && res.data.length > 0) {
                        
                        $updateSelect.empty().append('<option value="" disabled selected>Select Room</option>');
                        
                        res.data.forEach(room => {
                            $updateSelect.append(`<option value="${room.room_id}">${room.room_name}</option>`);
                        });

                        
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
                        
                        $updateSelect.html('<option disabled selected>No rooms found</option>');
                    }
                },
                error: function() {
                    
                    $updateSelect.html('<option disabled selected>Failed to load rooms</option>');
                }
            });
        }

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
            const instructor_id = $(this).data("instructor_id")

            // --- Set class basic info ---
            $("#classid").val(classId);
            $("#updateCourseSelect").val(courseId);
            filterLessonsByCourse(courseId, "update"); // Populate lessons
            $("#updateLessonSelect").val(lesson);
            $("#updateBuildingSelect").val(buildingId);
            $("#updateStatusSelect").val(statusId);
            $('#instructorId').val(instructor_id);

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


        function renderTable(classes) {
            const tbody = $('#classTable tbody');
            tbody.stop(true, true).fadeOut(150, function() {
                tbody.empty();

                if (classes.length === 0) {
                    tbody.append('<tr><td colspan="9" class="text-center">No classes found</td></tr>');
                } else {
                    classes.forEach((cls, index) => {
                        const rowNumber = (currentPage - 1) * limit + index + 1;
                        tbody.append(`
                            <tr>
                                <td>${rowNumber}</td>
                                <td><span class="badge bg-etec-color">ID:&ensp;${cls.class_id}</span></td>
                                <td><i class="bi bi-person-circle me-1 text-primary"></i>${cls.instructor_name || 'N/A'}</td>
                                <td>
                                    <div class="fw-bold text-etec-color mb-1">${cls.course_name}</div>
                                    <div>Lesson: <span class="text-secondary bg-secondary-subtle px-2 rounded">${cls.lesson || 'No lesson'}</span></div>
                                    <div>Type: <span class="text-success">${cls.class_type}</span></div>
                                </td>
                                <td>
                                    <div class="fw-bold">${cls.class_time || '-'}</div>
                                    <div class="text-success">${cls.term_name || '-'}</div>
                                </td>
                                <td>
                                    <div>${cls.building_name}</div>
                                    <div>${cls.floor_name} - Room ${cls.room_name}</div>
                                </td>
                                <td class="text-center">${cls.total_stu}</td>
                                <td>
                                    <span class="badge ${cls.class_status === 'progress' ? 'bg-primary' : 'bg-secondary'}">
                                        ${cls.class_status}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button 
                                        data-id="${cls.class_id}" 
                                        data-instructor_id="${cls.instructor_id}" 

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
                                        
                                        data-bs-toggle="modal" data-bs-target="#updateClassModal" 
                                        class="btn btn-outline-warning btn-sm update-class-btn">

                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="pages/admin/classdetails.php" class="view-class text-decoration-none" data-id="${cls.class_id}">
                                        <button class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </a>
                                    <button id="btnDeleteClass" data-id="${cls.class_id}" data-bs-toggle="modal" data-bs-target="#deleteClassModal" class="btn btn-outline-danger btn-sm">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `);
                    });
                }

                tbody.fadeIn(200); // smooth transition
            });
        }

        $("#updateClassForm").on("submit", function(e){
            console.log($("#instructorId").val());
            
            e.preventDefault();

            const data = {
                id: $("#classid").val(),
                lesson: $("#updateLessonSelect").val(),
                class_status:"progress",
                course_id: $("#updateCourseSelect").val(),
                instructor_id: $("#instructorId").val(),
                building_id: $("#updateBuildingSelect").val(),
                floor_id: $("#updateFloorSelect").val(),
                room_id: $("#updateRoomSelect").val(),
                class_type_id: $("#updateStatusSelect").val(),
                time_id: $("#updateTimeSelect").val(),
                term_id: $("#updateTermSelect").val()
            };
            
            $.ajax({
                url: "api.php?endpoint=class_update_admin",
                method: "POST",
                data: data,
                dataType: "json",
                success: function(res){
                    if(res.status){
                        // alert(res.message);
                        showAlert(res.message)
                        $("#updateClassModal").modal("hide");
                        // optionally refresh the class list
                        
                        const page = parseInt($(this).data('page'));
                        if(page > 0){
                            fetchClasses(page);
                        }else{
                            fetchClasses(1);
                        }
                       
                    } else {
                        showAlert(res.message);
                    }
                },
                error: function(err){
                    // console.error(err);
                    // alert("Failed to update class");
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please fill all fields before submitting!',
                        confirmButtonColor: '#3085d6'
                    });
                }
            });
        });

        $('#statusFilter').on('change', function() {
            fetchClasses(1);
        });

        function renderPagination(totalPages) {
            const ul = $('#pagination');
            ul.empty();
            const maxVisible = 5;
            let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
            let endPage = startPage + maxVisible - 1;
            if (endPage > totalPages) {
                endPage = totalPages;
                startPage = Math.max(1, endPage - maxVisible + 1);
            }

            ul.append(`
                <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <button class="page-link shadow-none" data-page="${currentPage - 1}">Prev</button>
                </li>
            `);

            if (startPage > 1) {
                ul.append(`
                    <li class="page-item"><button class="page-link shadow-none" data-page="1">1</button></li>
                    <li class="page-item disabled"><span class="page-link shadow-none">...</span></li>
                `);
            }

            for (let i = startPage; i <= endPage; i++) {
                ul.append(`
                    <li class="page-item ${i === currentPage ? 'active' : ''}">
                        <button class="page-link shadow-none" data-page="${i}">${i}</button>
                    </li>
                `);
            }

            if (endPage < totalPages) {
                ul.append(`
                    <li class="page-item disabled"><span class="page-link shadow-none">...</span></li>
                    <li class="page-item"><button class="page-link shadow-none" data-page="${totalPages}">${totalPages}</button></li>
                `);
            }

            ul.append(`
                <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <button class="page-link shadow-none" data-page="${currentPage + 1}">Next</button>
                </li>
            `);

            $('.page-link').click(function() {
                const page = parseInt($(this).data('page'));
                if (page && page !== currentPage) {
                    fetchClasses(page);
                }
            });
        }

        // --- Fetch filters ---
        function fetchCourses() {
            $.ajax({
                url: 'api.php',
                method: 'GET',
                data: { endpoint: 'course_getall' },
                dataType: 'json',
                success: function(res) {
                    if (res.status) {
                        const courseSelect = $('#courseFilter');
                        res.data.forEach(course => {
                            courseSelect.append(`<option value="${course.id}">${course.course}</option>`);
                        });
                    }
                }
            });
        }

        // --- Event handlers for live search & filters ---
        $('#searchInput').on('input', function() {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => {
                fetchClasses(1);
            }, typingDelay);
        });


        $('#courseFilter, #termFilter, #timeFilter').on('change', function() {
            fetchClasses(1);
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

        $(document).on('click', '#btnDeleteClass', function() {
            classIdToDelete = $(this).data('id'); // save class_id for deletion
        });

        $('#confirmDeleteClass').click(function() {
            if (!classIdToDelete) return;

            const $btn = $(this);
            const originalHtml = $btn.html();

            // Show loading spinner inside button
            $btn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Deleting...');
            $btn.prop('disabled', true);

            $.ajax({
                url: 'api.php?endpoint=deleteClass',
                method: 'POST',
                data: {
                    class_id: classIdToDelete
                },
                dataType: 'json',
                success: function(res) {
                    if (res.status) {
                        $('#deleteClassModal').modal('hide'); // close modal
                        fetchClasses(currentPage);           // refresh table
                        showAlert(res.message);
                    } else {
                        alert(res.message || "Failed to delete class");
                    }
                },
                error: function(err) {
                    console.error("Delete failed", err);
                },
                complete: function() {
                    // Restore button state
                    $btn.html(originalHtml);
                    $btn.prop('disabled', false);
                }
            });
        });


        // --- Initial load ---
        fetchCourses();
        fetchClasses(currentPage);
        
        // --- Global schedules array to cache Type → Term → Time ---
        let classSchedules = [];

        // --- Fetch all schedules once ---
        function fetchClassSchedules() {
            $.ajax({
                url: 'api.php',
                method: 'GET',
                data: { endpoint: 'schedule_getall' },
                dataType: 'json',
                success: function(res) {
                    if(res.status && res.data.length) {
                        classSchedules = res.data;

                        // Populate Type dropdown
                        const typeSelect = $('#typeFilter');
                        typeSelect.empty().append('<option value="">Type</option>');

                        // Get unique types
                        const types = [...new Map(classSchedules.map(s => [s.class_type_id, s.class_type_name]))];
                        types.forEach(([id, name]) => {
                            typeSelect.append(`<option value="${id}">${name}</option>`);
                        });
                        typeSelect.prop('disabled', false);
                    }
                }
            });
        }

        // --- When Type changes, populate Terms ---
        $('#typeFilter').on('change', function() {
            const typeId = $(this).val();
            const termSelect = $('#termFilter');
            const timeSelect = $('#timeFilter');

            termSelect.empty().append('<option value="">Term</option>').prop('disabled', true);
            timeSelect.empty().append('<option value="">Time</option>').prop('disabled', true);

            if(!typeId) return fetchClasses(1);

            // Filter terms based on selected type
            const terms = classSchedules
                .filter(s => s.class_type_id === typeId)
                .map(s => ({ id: s.term_id, name: s.term_name }))
                .filter((v, i, a) => a.findIndex(t => t.id === v.id) === i); // unique terms

            terms.forEach(term => {
                termSelect.append(`<option value="${term.id}">${term.name}</option>`);
            });
            termSelect.prop('disabled', false);

            fetchClasses(1);
        });

        // --- When Term changes, populate Times ---
        $('#termFilter').on('change', function() {
            const typeId = $('#typeFilter').val();
            const termId = $(this).val();
            const timeSelect = $('#timeFilter');

            timeSelect.empty().append('<option value="">Time</option>').prop('disabled', true);

            if(!typeId || !termId) return fetchClasses(1);

            // Filter times based on selected type and term
            const times = classSchedules
                .filter(s => s.class_type_id === typeId && s.term_id === termId)
                .flatMap(s => {
                    const ids = s.time_id.split(',').map(id => id.trim());
                    const slots = s.time_slots.split(',').map(slot => slot.trim());
                    return ids.map((id, i) => ({ id, slot: slots[i] || 'N/A' }));
                })
                .filter((v, i, a) => a.findIndex(t => t.id === v.id) === i); // unique times

            times.forEach(time => {
                timeSelect.append(`<option value="${time.id}">${time.slot}</option>`);
            });
            timeSelect.prop('disabled', false);

            fetchClasses(1);
        });

        // --- When Time changes, reload classes ---
        $('#timeFilter').on('change', function() {
            fetchClasses(1);
        });

        function fetchTotalClass() {
            // Get selected filters
            const course_id = $('#courseFilter').val();
            const time_id = $('#timeFilter').val();

            // Make sure both are selected
            if (!course_id || !time_id) {
                $('#totalClass').text('Total Class: 0');
                return;
            }

            // Call API
            $.ajax({
                url: 'api.php?endpoint=countClass',
                type: 'GET',
                data: {
                    course_id: course_id,
                    time_id: time_id
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        $('#totalClass').text('Total Class: ' + response.data.total_class);
                    } else {
                        $('#totalClass').text('Total Class: 0');
                    }
                },
                error: function() {
                    $('#totalClass').text('Total Class: 0');
                }
            });
        }

        // Call when filters change
        $('#courseFilter, #timeFilter').on('change', function() {
            fetchTotalClass();
        });

        fetchTotalClass();

        // --- Initial load ---
        fetchClassSchedules();

    });
</script>
