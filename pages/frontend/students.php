<?php
// Get class_id from URL
$class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
?>

<!-- Hidden input for class ID -->
<input type="hidden" name="class_id" id="class_id" value="<?= $class_id ?>" readonly>

<section>
    <div class="d-flex justify-content-between align-items-center">
        <!-- Back Button -->
        <a href="pages/frontend/classes.php" class="back-to-class btn btn-secondary btn-sm text-light">
            Back to Class
        </a>

        <!-- Notification Bell -->
        <div class="position-relative"> 
            <i class="bi bi-bell-fill fs-4 text-secondary" id="notificationBell" data-bs-toggle="modal"  data-bs-target="#notificationModal" style="cursor: pointer;"></i>
            <!-- Badge -->
            <span id="notificationBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">0</span>
        </div>

    </div>

    <div class="container-fluid p-0 my-3" id="class-details-container">
        <div class="container-fluid p-0 my-2">
            <div class="card p-0 border-0 rounded-4">
                <div class="card-body p-0">
                    
                    <!-- Header Skeleton -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="col-7">
                            <h4 class="fw-bold mb-1 placeholder-glow">
                                <span class="placeholder col-9 bg-secondary">&nbsp;</span>
                            </h4>
                            <p class="text-muted mb-0 placeholder-glow">
                                <span class="placeholder col-4 bg-secondary">&nbsp;</span>
                            </p>
                        </div>
                        <span class="badge fs-6 px-3 py-2 placeholder col-2 bg-secondary">&nbsp;</span>
                    </div>

                    <!-- Info Cards Skeleton -->
                    <div class="row g-3">
                        
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="p-3 border rounded-3 bg-light placeholder-glow">
                                <h6 class="text-muted mb-1"><span class="placeholder col-6"></span></h6>
                                <h5 class="fw-semibold text-dark mb-0"><span class="placeholder col-4"></span></h5>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="p-3 border rounded-3 bg-light placeholder-glow">
                                <h6 class="text-muted mb-1"><span class="placeholder col-6"></span></h6>
                                <h5 class="fw-semibold text-dark mb-0"><span class="placeholder col-4"></span></h5>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="p-3 border rounded-3 bg-light placeholder-glow">
                                <h6 class="text-muted mb-1"><span class="placeholder col-6"></span></h6>
                                <h5 class="fw-semibold text-dark mb-0"><span class="placeholder col-5"></span></h5>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center border-top mt-2 pt-2">
        <div>
            <h4 class="mb-0 text-etec-color">Track Attendence</h4>
            <p class="text-secondary mb-0">Tack your student attendence</p>
        </div>
        <div>
            <button class="btn btn-secondary" id="btnRefresh">
                Refresh Table
            </button>
             <button class="btn btn-info text-light" id="btnGroup" data-bs-toggle="modal" data-bs-target="#groupmodal">
                <i class="bi bi-people-fill me-1"></i> Group 
            </button>
            <button class="btn btn-warning text-light" id="btnRequestCertificate" data-class-id="<?php echo $class_id; ?>" data-bs-toggle="modal" data-bs-target="#requestCertificateModal">
                <i class="bi bi-file-earmark-text me-1"></i> Request Certficate
            </button>
            <button class="btn btn-success" id="saveAllScoresBtn">
                <i class="bi bi-save2 me-1"></i> Save Score
            </button>
            <button id="trackAttendanceBtn" class="btn btn-primary"> 
                <i class="bi bi-file-text me-1"></i> Track Attendence
            </button> 
        </div>               
    </div>

    <!-- Success Alert -->
    <div id="successAlert" class="alert alert-success alert-dismissible fade show mt-3" style="display:none;" role="alert">
        <span id="successMessage"></span>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <div class="container-fluid my-4 p-0">
        <div class="card p-0">
            <div class="card-header bg-etec-color text-white text-center">
                <h5 class="mb-0">Student Attendance & Score</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-primary">
                            <tr>
                                <th>N<sup>o</sup></th>
                                <th>Student</th>
                                <th>Gender</th>
                                <th>Tel</th>
                                <th style="width: 13%;">Attendance</th>
                                <th colspan="3" class="text-center">Score</th>
                                <th class="text-center">Action</th>
                            </tr>
                            <tr>
                                <th colspan="5"></th>
                                <th class="col-1">Attendance Score</th>
                                <th class="col-1">Activity Score</th>
                                <th class="col-1">Exam Score</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="students-tbody">
                            <?php require __DIR__.'../../../utils/tablestu_skelaton.php' ?>
                        </tbody>
                            
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Modal -->
    <div class="modal fade" id="attModal" tabindex="-1" data-bs-backdrop="static" aria-labelledby="attModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-etec-color text-white">
                    <h5 class="modal-title" id="attModalLabel">Track Attendance</h5>
                    <button type="button" style="filter: invert(1) grayscale(100%) brightness(200%);" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered align-middle">
                        <thead class="table-secondary">
                            <tr>
                                <th>Name</th>
                                <th>Gender</th>
                                <th>Attendance</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody id="modal-students-tbody">
                            
                        </tbody>
                    </table>
                    <p id="date" class="mt-2"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" id="saveAttendance">
                        Save Attendance
                        <span class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Student Modal -->
    <div class="modal fade" id="modalUpdateStudent" tabindex="-1" aria-labelledby="modalUpdateStudentLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-etec-color text-white">
                    <h5 class="modal-title" id="modalUpdateStudentLabel">Update Student</h5>
                    <button type="button" style="filter: invert(1) grayscale(100%) brightness(200%);"  class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="updateStudentForm">
                        <input type="hidden" id="update-stu-id" name="stu_id">

                        <div class="mb-3">
                            <label for="update-full-name" class="form-label">Full Name</label>
                            <input type="text" class="form-control shadow-none" id="update-full-name" name="full_name" placeholder="Enter full name" required>
                        </div>

                        <div class="mb-3">
                            <label for="update-gender" class="form-label">Gender</label>
                            <select class="form-select shadow-none" id="update-gender" name="gender" required>
                                <option value="">Select gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="update-tel" class="form-label">Phone Number</label>
                            <input type="text" class="form-control shadow-none" id="update-tel" name="tel" placeholder="Enter phone number">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button id="saveUpdateStudent" class="btn btn-primary">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
                   
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="modalDeleteStudent" tabindex="-1" aria-labelledby="modalDeleteStudentLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalDeleteStudentLabel">Confirm Delete</h5>
                    <button type="button" style="filter: invert(1) grayscale(100%) brightness(200%);" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="bi bi-exclamation-triangle-fill text-warning fs-1 mb-3"></i>
                    <p class="fw-bold">Are you sure you want to delete this student?</p>
                    <p class="text-secondary mb-0" id="delete-student-name"></p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="confirmDeleteStudent" class="btn btn-danger">
                        Delete
                        <span class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Transfer Student Modal -->
    <div class="modal fade" id="transferStuModal" tabindex="-1" aria-labelledby="transferClassModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border"> <!-- border + no shadow -->
                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="transferClassModalLabel">Transfer Class</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="transferStuForm">
                    <div class="modal-body">
                        <input type="hidden" id="studen_ID"> <!-- selected student ID -->
                        <label for="class_ID">Class ID - (Make sure it’s a basic IT class)</label>
                        <input type="number" class="form-control shadow-none border" id="class_ID" name="class_ID" placeholder="Enter Class ID" required>
                        
                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <!-- Trigger JS instead of data-bs-toggle -->
                        <button type="submit" class="btn btn-primary">
                            Transfer
                            <span class="spinner-border spinner-border-sm d-none"></span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- ✅ Confirm Transfer Modal -->
    <div class="modal fade" id="confirmTransfer" data-bs-backdrop="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border">
                <!-- Header -->
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-semibold" id="confirmTransferLabel">
                        Confirm Transfer
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Body -->
                <div class="modal-body text-center">
                    <i class="bi bi-exclamation-triangle text-warning fs-1 mb-3"></i>
                    <p class="fs-6 mb-2">
                        Are you sure you want to <strong>transfer this student</strong> to class 
                        <span class="text-primary fw-bold" id="targetClassId"></span>?
                    </p>
                    <p class="text-muted small mb-0">
                        You can choose whether to <strong>keep</strong> or <strong>remove</strong> the student from their old class.
                    </p>
                    <div id="transferClassError" class="text-danger small mt-2 d-none"></div>
                </div>

                <!-- Footer -->
                <div class="modal-footer border-top justify-content-center">
                    <button type="button" class="btn btn-primary px-4" id="confirmTransferKeepBtn">
                        Transfer tv tea nv tnak jas 😒
                        <span class="spinner-border spinner-border-sm d-none ms-2" role="status" aria-hidden="true"></span>
                    </button>
                    <!-- <button type="button" class="btn btn-danger px-4" id="confirmTransferRemoveBtn">
                        Yes, Transfer & Remove
                        <span class="spinner-border spinner-border-sm d-none ms-2" role="status" aria-hidden="true"></span>
                    </button> -->
                </div>
            </div>
        </div>
    </div>

    <!-- Request Certificate Modal -->
    <div class="modal fade" id="requestCertificateModal" tabindex="-1" aria-labelledby="requestCertificateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-etec-color text-white">
                    <h5 class="modal-title" id="requestCertificateModalLabel">Request Certficate</h5>
                    <button type="button" style="filter: invert(1) grayscale(100%) brightness(200%);" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>N<sup>o</sup></th>
                                        <th>Student Name</th>
                                        <th>Gender</th>
                                        <th>Action [EDIT | APPROVE]</th> 
                                    </tr>
                                </thead>
                                <tbody id="certificate-pass-tbody">
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No data yet</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Modal -->
    <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header justify-content-between align-items-center">
                    <h5 class="modal-title fw-bold" id="notificationModalLabel">Pending Students</h5>
                
                    <button class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1" id="reloadBtn">
                        <i class="bi bi-arrow-clockwise"></i> Reload
                    </button>
                

                </div>
                <div class="modal-body" id="notificationModalBody">
                    <p class="text-center text-muted">Loading...</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Group Modal -->
    <div class="modal fade" id="groupmodal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title">Group Management</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body d-flex justify-content-between" >


                <!-- ========== LAYOUT 1: GROUP LIST ========== -->
                <div id="layoutList" class="col-8 p-2 ">
                    <div class="d-flex justify-content-between mb-3">
                        <h5>Group List</h5>
                    </div>

                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Group Number</th>
                                <th>Member Name</th>
                                <th>Topic</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="groupTableBody">
                        <!-- Dynamic rows -->
                            <tr>
                                <td colspan="5" class="text-center">No Group</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- ========== LAYOUT 2: CREATE GROUP FORM ========== -->
                <div id="layoutForm" class="col-4 p-2">
                    <h5 class="mb-3">Create New Group</h5>

                    <form id="groupForm">

                        <!-- Group Name -->
                        <div class="mb-3">
                            <label class="form-label">Group Number</label>
                            <input type="text" placeholder="Group Name" class="form-control shadow-none border" id="groupName" required>
                        </div>

                        <!-- Topic -->
                        <div class="mb-3">
                            <label class="form-label">Topic</label>
                            <input type="text" placeholder="Topic" class="form-control shadow-none border" id="groupTopic" required>
                        </div>

                        <!-- Students -->
                        <div class="mb-3">
                            <label class="form-label">Select Students <span id="totalStu"></span></label>
                            <ul class="list-group overflow-auto border rounded" style="max-height: 200px;" id="studentList"></ul>
                            <small class="text-muted">Hold CTRL to select multiple students</small>
                        </div>

                        <div class="d-flex justify-content-end mt-3">
                            <button id="resetBtn" class="btn btn-secondary me-2">Reset</button>
                            <button type="submit" class="btn btn-success">Save Group</button>
                        </div>

                    </form>
                </div>

            </div>

            </div>
        </div>
    </div>

</section>

<script>
    $(document).ready(function(){
        
        const class_id = $("#class_id").val();
        const stuArr = []

        function loadClassDetails(classId) {
            if (!classId || classId <= 0) return;

            // Fetch actual data
            $.ajax({
                url: `api.php?endpoint=getClassById&class_id=${classId}`,
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (!res.status) {
                        console.error(res.message);
                        return;
                    }

                    const classData = res.data;

                    // Render actual HTML
                    const html = `
                        <div class="container-fluid p-0 my-2">
                            <div class="card p-0 border-0 rounded-4">
                                <div class="card-body p-0">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <h4 class="fw-bold text-etec-color mb-1">
                                                <i class="bi bi-book me-2"></i> ${classData.course_name}
                                            </h4>
                                            <p class="text-muted mb-0">Created Date: <span class="bg-secondary-subtle px-2 text-secondary rounded">${classData.created_at}</span></p>
                                        </div>
                                        <div class="text-end">
                                            <p class="mb-1 ${classData.isTransfer ? 'd-block' : 'd-none'}">
                                                Teach with: <span class="text-primary fw-bold">${classData.class_with_name}</span>
                                            </p>
                                            <span class="badge fs-6 px-3 py-2 ${classData.class_status === 'Online' ? 'bg-success' : 'bg-secondary'}">
                                                ${classData.class_status}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="p-3 border rounded-3 bg-light">
                                                <h6 class="text-muted mb-1">Total Students</h6>
                                                <h5 class="fw-semibold text-dark mb-0">${classData.total_stu} Students</h5>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="p-3 border rounded-3 bg-light">
                                                <h6 class="text-muted mb-1">Class Type</h6>
                                                <h5 class="fw-semibold text-dark mb-0">${classData.class_type}</h5>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="p-3 border rounded-3 bg-light">
                                                <h6 class="text-muted mb-1">Term & Time</h6>
                                                <h5 class="fw-semibold text-dark mb-0">${classData.term_name} (${classData.time})</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    $('#class-details-container').html(html);
                },
                error: function(err) {
                    console.error("Failed to fetch class details", err);
                }
            });
        }


        $(document).on("click", ".back-to-class", function(e) {
            e.preventDefault();

            const url = $(this).attr("href");

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
            $("#content-area").load(url, function(response, status, xhr) {
                if (status === "error") {
                    console.error("Load failed:", xhr.status, xhr.statusText);
                }
            });
        });

        $('#btnRefresh').on('click',function(){
            loadStudents(class_id)
        })

        $('#btnRequestCertificate').off('click').on('click', function() {

            const classId = $(this).data('class-id');
            $.ajax({
                url: `api.php?endpoint=get_student_for_certificate`,
                method: 'POST',
                dataType: 'json',
                data: { class_id: classId },

               success: function(res) {

                    const $tbody = $('#certificate-pass-tbody');
                    let html = '';

                    const students = res.data.students;
                    const reqCertificateId = res.data.req_certificate_id;
                    if(!res.status || students.length === 0){
                        $tbody.html(`
                            <tr>
                                <td colspan="4" class="text-center text-muted">No students found</td>
                            </tr>
                        `);
                        return;
                    }

                    students.forEach((student, index) => {

                        const blocked = student.attendance_status === 'blocked';
                        const approved = student.is_approved == 1;

                        html += `
                            <tr class="${blocked ? 'table-danger' : ''}">
                                <td>${index + 1}</td>

                                <td>
                                    <input 
                                        type="text" 
                                        class="form-control form-control-sm student-name" 
                                        value="${student.full_name.toUpperCase()}" 
                                        data-id="${student.id}"
                                    >
                                </td>

                                <td>${student.gender}</td>

                                <td>
                                    ${blocked 
                                        ? `<span class="badge bg-danger">Blocked</span>`
                                        : approved
                                            ? `<span class="badge bg-success">Approved</span>`
                                            : `
                                                <button class="btn btn-sm btn-primary btn-save-name" data-id="${student.id}">Save</button>
                                                <button 
                                                    class="btn btn-sm btn-success btn-approve-req"
                                                    data-id="${student.id}"
                                                    data-req-certificate-id="${reqCertificateId}">
                                                    Approve
                                                </button>
                                            `
                                    }
                                </td>
                            </tr>
                        `;
                    });
                    $tbody.html(html);
                },

                error: function(err) {
                    console.error("AJAX ERROR:", err);
                }

            });

        });

        $(document)
            .off('click', '.btn-save-name')
            .on('click', '.btn-save-name', function(e){

                e.preventDefault();

                const $btn = $(this); // current button
                const studentId = $btn.data('id');
                const newName = $btn.closest('tr').find('.student-name').val();

                // show loading
                $btn.prop('disabled', true);
                $btn.html(`
                    <span class="spinner-border spinner-border-sm me-1"></span>
                    Saving...
                `);

                $.ajax({
                    url: 'api.php?endpoint=update_student_name',
                    method: 'POST',
                    dataType: 'json',
                    data: { student_id: studentId, full_name: newName },

                    success: function(res) {

                        if(res.status){
                            showAlert("Student name updated successfully");

                            // success state
                            $btn.removeClass("btn-warning")
                                .addClass("btn-success")
                                .html("Saved ✓");

                            // return to normal after 1.5s
                            setTimeout(()=>{
                                $btn.removeClass("btn-success")
                                    .addClass("btn-primary")
                                    .html("Save")
                                    .prop("disabled", false);
                            },1500);

                        } else {
                            alert(res.message || "Failed to update name");

                            $btn.html("Save").prop("disabled", false);
                        }

                    },

                    error: function(err) {
                        console.error("AJAX ERROR:", err);
                        alert("An error occurred while updating name");

                        $btn.html("Save").prop("disabled", false);
                    }

                });

        });

        // Approve student request
        $(document)
        .off('click', '.btn-approve-req')
        .on('click', '.btn-approve-req', function(e){

            e.preventDefault();

            const $btn = $(this);

            const studentId = $btn.data('id');
            const reqCertificateId  = $btn.data('req-certificate-id');

            $btn.prop("disabled", true).text("Approving...");

            $.ajax({
                url: "api.php?endpoint=approve_student_request",
                method: "POST",
                dataType: "json",
                data: {
                    student_id: studentId,
                    req_certificate_id: reqCertificateId
                },

                success: function(res){
                    // console.log(res);
                    if(res.status){

                        $btn
                        .removeClass("btn-success")
                        .addClass("btn-secondary")
                        .text("Approved");

                    }else{

                        alert(res.message);
                        $btn.prop("disabled", false).text("Approve");

                    }

                }

            });

        });
        function toggleSpinner(button, show) {
            const $btn = $(button);
            const $spinner = $btn.find(".spinner-border");
            if(show){
                $spinner.removeClass("d-none");
                $btn.prop("disabled", true);
            } else {
                $spinner.addClass("d-none");
                $btn.prop("disabled", false);
            }
        }

        function showAlert(message){
            $('#successMessage').text(message);
            $('#successAlert').stop(true,true).fadeIn();
            setTimeout(() => $('#successAlert').fadeOut('slow'), 3000);
        }

        if(!class_id || class_id <= 0){
            console.error("Invalid Class ID");
            return;
        }

        // Load pending students into modal
        function loadPendingStudents(class_id) {
            const badge = $("#notificationBadge");
            const modalBody = $("#notificationModalBody");

            // // 🔥 Show loading spinner immediately
            // modalBody.html(`
            //     <div class="text-center my-4">
            //         <div class="spinner-border text-primary" role="status"></div>
            //         <p class="text-muted mt-2">Loading...</p>
            //     </div>
            // `);

            $.ajax({
                url: 'api.php?endpoint=get_students_attendance_summary&class_id=' + class_id,
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    modalBody.empty();

                    if (res.status && res.data.length > 0) {
                    
                        const pendingStudents = res.data.filter(s => s.approval === 'pending');
                        // console.log(pendingStudents);
                        

                        if (pendingStudents.length > 0) {
                            badge.text(pendingStudents.length).show();

                            pendingStudents.forEach(student => {
                                const studentCard = $(`
                                    <div class="card mb-2">
                                        <div class="card-body d-flex justify-content-between align-items-center">
                                            <div>
                                                <p class="mb-0 fw-bold">
                                                    ${student.full_name}
                                                </p>
                                                <small class="text-muted">Submitted: ${student.created_at || ''}</small>
                                            </div>
                                            <div class="d-flex">
                                                <button type="button" class="btn btn-sm btn-danger mx-1 btn-delete-stu" 
                                                    data-class-id="${student.class_id}" data-stu-id="${student.stu_id}">Reject</button>
                                                <button type="button" class="btn btn-sm btn-success approve-btn" 
                                                    data-class-id="${student.class_id}" data-stu-id="${student.stu_id}">Approve</button>
                                            </div>
                                        </div>
                                    </div>
                                `);
                                modalBody.append(studentCard);
                            });
                        } else {
                            badge.hide();
                            modalBody.append(`<p class="text-center text-muted">No pending students</p>`);
                        }
                    } else {
                        badge.hide();
                        modalBody.append(`<p class="text-center text-muted">No pending students</p>`);
                    }
                },
                error: function() {
                    modalBody.html(`<p class="text-center text-danger">Failed to fetch students</p>`);
                }
            });
        }

        // Load pending students when page loads
        loadPendingStudents(class_id);

        $('#reloadBtn').on('click',function(){
            loadPendingStudents(class_id);
        })
   
        // Approve student
        $(document).on('click', '.approve-btn', function(e) {
            e.preventDefault();
            const btn = $(this);
            const stuId = String(btn.data('stu-id'));
            const classID = String(btn.data('class-id'));

            btn.prop('disabled', true);
            btn.html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Approving...`);

            $.ajax({
                url: 'api.php?endpoint=approvedStudent',
                type: 'POST',
                data: { studentId: stuId, class_id: classID },
                dataType: 'json',
                success: function(res) {
                    if (res.status) {

                        // 1️⃣ Update localStorage
                        const key = `students_${classID}`;
                        let students = JSON.parse(localStorage.getItem(key)) || [];

                        students = students.map(s => {
                            if (String(s.stu_id) === stuId) {
                                return { ...s, approval: 'approved' };
                            }
                            return s;
                        });

                        localStorage.setItem(key, JSON.stringify(students));

                        // 2️⃣ Remove the card from modal
                        btn.closest('.card').fadeOut(300, function() { $(this).remove(); });

                        // 3️⃣ Refresh main table
                        loadPendingStudents(class_id);
                        loadStudents(classID);
                    } else {
                        alert(res.message || 'Failed to approve student');
                        btn.prop('disabled', false);
                        btn.html('Approve');
                    }
                },
                error: function() {
                    alert('AJAX error while approving student');
                    btn.prop('disabled', false);
                    btn.html('Approve');
                }
            });
        });

        // Approve / Reject
        $(document).on('click', '.btn-delete-stu', function(e) {
            e.preventDefault()
            const btn = $(this);
            const stuId = $(this).data('stu-id');
            const classID = $(this).data('class-id');
            const endpoint = 'delete_student';

            // console.log(stuId,'classID:',classID);
            btn.prop('disabled', true);
            btn.html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>Loading...`);
            

            $.ajax({
                url: 'api.php?endpoint=' + endpoint,
                type: 'POST',
                data: { studentId: stuId, class_id: classID },
                dataType: 'json',
                success: function(res) {
                    // console.log(res);
                    
                    if (res.status) {
                        loadPendingStudents(class_id); // Refresh modal
                        loadStudents(classID);        // Refresh main table
                    }
                },
                complete: function() {
                    // Restore button
                    btn.prop('disabled', false);
                    btn.html('Reject');
                }
            });
        });

        function loadStudents(classId) {
            const $tbody = $("#students-tbody");

            // Preserve current Activity & Exam scores
            const values = {};
            $tbody.find("tr").each(function () {
                const $row = $(this);
                const stuId = $row.data("stu-id");
                values[stuId] = {
                    act: $row.find('input[placeholder="Activity Score"]').val(),
                    exam: $row.find('input[placeholder="Exam Score"]').val()
                };
            });

            // Show skeleton (already in tbody)
            // No need for opacity animation
            $.ajax({
                url: 'api.php?endpoint=get_students_attendance_summary&class_id=' + classId,
                type: 'GET',
                dataType: 'json',
                success: function (res) {
                    if (!res.status) {
                        alert(res.message);
                        return;
                    }

                    stuArr.length = 0; // Clear global array
                    let html = "";

                    if (res.data.length > 0) {
                        // Filter only approved students
                        const approvedStudents = res.data.filter(student => student.approval === 'approved');

                        approvedStudents.forEach((student, index) => {
                            stuArr.push(student);

                            const present = parseInt(student.present, 10) || 0;
                            const permission = parseInt(student.permission, 10) || 0;
                            const absent = parseInt(student.absent, 10) || 0;
                            const totalAtt = present + permission + absent;
                            const totalChob = permission + absent;
                            const attendancePercent = totalAtt > 0 ? 100 - ((totalChob) / totalAtt * 100) : 100;

                            let tdClass = "";
                            if (attendancePercent <= 30) tdClass = "table-danger";
                            else if (attendancePercent < 50) tdClass = "table-warning";

                            html += `
                                <tr data-stu-id="${student.stu_id}">
                                    <td class="${tdClass}">${index + 1}</td>
                                    <td class="${tdClass}">
                                        <p class="fw-bold mb-0 fs-5">${student.full_name}</p>
                                        ID: <span class="bg-etec-color text-white px-2 rounded">${student.stu_id}</span>
                                    </td>
                                    <td class="${tdClass}">
                                        ${student.gender === 'Female' 
                                            ? '<span class="border border-danger text-danger px-1 rounded"><i class="bi bi-gender-female"></i> Female</span>' 
                                            : '<span class="border border-primary text-primary px-1 rounded"><i class="bi bi-gender-male"></i> Male</span>'}
                                    </td>
                                    <td class="${tdClass}">${student.tel}</td>
                                    <td class="text-start attendance-summary ${tdClass}">
                                        <div class="p-2 bg-success rounded text-white">
                                            <p class="mb-1"><strong>Total:</strong> ${totalAtt}</p>
                                            <p class="mb-1"><strong>Present:</strong> ${student.present}</p>
                                            <p class="mb-1"><strong>Permission:</strong> ${student.permission}</p>
                                            <p class="mb-1"><strong class="bg-danger-subtle text-danger px-2 rounded">Absent: ${student.absent}</strong></p>
                                        </div>
                                    </td>
                                    <td class="${tdClass}">
                                        <input type="text" disabled value="${student.att_score ?? 0}" class="form-control shadow-none border" placeholder="Attendance Score">
                                    </td>
                                    <td class="${tdClass}">
                                        <input type="number" min="0" max="30" value="${student.act_score ?? 0}" class="form-control shadow-none border" placeholder="Activity Score">
                                    </td>
                                    <td class="${tdClass}">
                                        <input type="number" min="0" max="30" value="${student.exam_score ?? 0}" class="form-control shadow-none border" placeholder="Exam Score">
                                    </td>
                                    <td class="text-center ${tdClass}">
                                        <a href="pages/frontend/studentDetails.php?stu_id=${student.stu_id}" 
                                            class="view-student-detail text-decoration-none ${totalAtt === 0 ? 'pe-none' : ''}"
                                            data-id="${student.stu_id}">
                                            <button class="btn btn-light" ${totalAtt === 0 ? 'disabled' : ''}>
                                                <i class="bi bi-eye-fill"></i>
                                            </button>
                                        </a>

                                        <button data-id="${student.stu_id}" class="btn btn-light btnTransferStu" data-bs-target="#transferStuModal" data-bs-toggle="modal">
                                            <i class="bi bi-arrow-left-right"></i>
                                        </button>
                                        <button class="btn btn-light shadow-none border edit-student-btn"
                                            data-stu_id="${student.stu_id}" 
                                            data-name="${student.full_name}"
                                            data-gender="${student.gender}"
                                            data-tel="${student.tel}"
                                            data-bs-target="#modalUpdateStudent" 
                                            data-bs-toggle="modal">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>
                                        <button disabled class="btn btn-danger delete-student-btn" 
                                            data-stu_id="${student.stu_id}" 
                                            data-bs-target="#modalDeleteStudent" 
                                            data-bs-toggle="modal">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    }
                    else {
                        html = `
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <img src="https://cdn-icons-png.flaticon.com/512/4076/4076549.png" 
                                        alt="No students" 
                                        style="width:70px; opacity:0.5;" 
                                        class="mb-2">
                                    <div>Student Not Yet Added..</div>
                                </td>
                            </tr>
                        `;
                    }

                    // Replace skeleton instantly with data
                    $tbody.html(html);

                    // Restore Activity & Exam scores
                    $tbody.find("tr").each(function () {
                        const $row = $(this);
                        const stuId = $row.data("stu-id");
                        if (values[stuId]) {
                            $row.find('input[placeholder="Activity Score"]').val(values[stuId].act);
                            $row.find('input[placeholder="Exam Score"]').val(values[stuId].exam);
                        }
                    });
                },
                error: function () {
                    $tbody.html(`
                        <tr>
                            <td colspan="9" class="text-center text-danger py-3">
                                Failed to load students
                            </td>
                        </tr>
                    `);
                }
            });
        }
        // Pass class_id here 


        $(document).off("click", "#trackAttendanceBtn").on("click", "#trackAttendanceBtn", function () {

            const date = new Date();
            const today =
                date.getFullYear() + '-' +
                String(date.getMonth() + 1).padStart(2, '0') + '-' +
                String(date.getDate()).padStart(2, '0');

            $.ajax({
                url: "api.php?endpoint=beforeTrackAttendance",
                method: "GET",
                data: { class_id: class_id, date: today },
                dataType: "json",
                success: function (res) {

                    if (!res.status) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Oops!',
                            text: res.message,
                            confirmButtonText: 'OK'
                        });
                        return;
                    }

                    const lockMap = res.data || {};

                    $("#attModal").modal("show");

                    const modalBody = $("#modal-students-tbody");
                    modalBody.empty();

                    // timestamp
                    let timestamp =
                        today + " " +
                        String(date.getHours()).padStart(2, '0') + ":" +
                        String(date.getMinutes()).padStart(2, '0') + ":" +
                        String(date.getSeconds()).padStart(2, '0');
                    $('#date').text(timestamp);

                    // =================================================
                    // RENDER STUDENTS
                    // =================================================
                    if (stuArr.length > 0) {

                        stuArr.forEach(student => {

                            const lockInfo = lockMap[student.stu_id] || {};

                            const autoAbsentByRule   = lockInfo.status === 'absent';
                            const autoPermission     = lockInfo.status === 'permission';
                            const isPermissionLocked = lockInfo.status === 'permission_locked';

                            const hardLocked = lockInfo.locked === true &&
                                    lockInfo.status !== 'permission_locked';
                            const defaultAbsent = !lockInfo.status || lockInfo.status === 'free';

                            const reasonText = lockInfo.reason || "";
                            const isHardLockAbsence = autoAbsentByRule &&
                                /hard\s*lock|black\s*list/i.test(reasonText);
                            // ===============================
                            // PM RENDER LOGIC
                            // ===============================
                            let pmHtml = '';

                            if (isPermissionLocked || autoPermission) {
                                pmHtml = `
                                    <button class="btn btn-warning" disabled>PM</button>
                                `;
                            } else {
                                pmHtml = `
                                    <button class="btn btn-warning permission-btn">PM</button>
                                `;
                            }
                            // 🚫 HARD ABSENCE BLOCK (TEXT ONLY ROW)
                            if (autoAbsentByRule) {
                                const row = `
                                    <tr data-id="${student.stu_id}"
                                        data-locked="1"
                                        data-auto-absent="1"
                                        class="table-danger">

                                        <td>${student.full_name}</td>
                                        <td>${student.gender}</td>

                                        <td colspan="2">
                                            <span class="fw-bold text-danger">
                                                ${isHardLockAbsence
                                                    ? '⛔ BLACKLIST (HARD LOCK)'
                                                    : '🚫 Attendance locked. Please meet admin for approval.'}
                                            </span>
                                        </td>
                                    </tr>
                                `;

                                modalBody.append(row);
                                return; // ⛔ STOP here, do NOT render buttons row
                            }

                            const row = `
                                <tr data-id="${student.stu_id}"
                                    data-locked="${hardLocked ? 1 : 0}"
                                    class="${hardLocked ? 'table-secondary' : ''}">

                                    <td>${student.full_name}</td>
                                    <td>${student.gender}</td>

                                    <td>
                                        <button class="btn btn-success present-btn"
                                            ${hardLocked ? 'disabled' : ''}>
                                            P
                                        </button>

                                        <button class="btn btn-danger absent-btn ${autoAbsentByRule ? 'active' : ''}"
                                            ${autoAbsentByRule ? 'disabled' : ''}>
                                            A
                                        </button>

                                        ${pmHtml}
                                    </td>

                                    <td class="col-4">
                                        <input type="text"
                                            class="form-control reason-input shadow-none border"
                                            placeholder="Reason..."
                                            value="${reasonText}"
                                            ${hardLocked || isPermissionLocked ? "disabled" : ""}>
                                        <p class="text-danger d-none mb-0 small errorAlert"></p>
                                    </td>
                                </tr>
                            `;

                            modalBody.append(row);
                            const $lastRow = modalBody.find('tr').last();

                            // ===== DEFAULT ABSENT =====
                           if (defaultAbsent && !hardLocked) {
                                $lastRow.find('.absent-btn')
                                    .addClass('active')
                                    .prop('disabled', true);

                                $lastRow.find('.present-btn, .permission-btn')
                                    .prop('disabled', false);
                            }

                            // ===== HARD LOCK: ABSENCE BLOCK =====
                            if (autoAbsentByRule) {
                                $lastRow
                                    .attr('data-locked', 1)
                                    .addClass('table-secondary');

                                $lastRow.find(
                                    '.present-btn, .absent-btn, .permission-btn, .reason-input'
                                ).prop('disabled', true);
                            }

                            // ===== HARD LOCK: PERMISSION APPROVED =====
                            if (autoPermission) {
                                $lastRow
                                    .attr('data-locked', 1)
                                    .find('.permission-btn')
                                    .prop('disabled', true);
                            }

                            // ===== PERMISSION LIMIT EXCEEDED =====
                            if (isPermissionLocked) {

                                // unlock row
                                $lastRow.attr('data-locked', 0);

                                // PM disabled
                                $lastRow.find('.permission-btn')
                                    .prop('disabled', true);

                                // Absent locked
                                $lastRow.find('.absent-btn')
                                    .addClass('active')
                                    .prop('disabled', true);

                                // Present ALWAYS clickable
                                $lastRow.find('.present-btn')
                                    .prop('disabled', false);
                            }


                        });

                    } else {
                        modalBody.html(`
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">
                                    <img src="https://cdn-icons-png.flaticon.com/512/4076/4076549.png"
                                        style="width:60px;opacity:0.6;" class="mb-2">
                                    <div>No students available.</div>
                                </td>
                            </tr>
                        `);
                    }
                },
                error: function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to check attendance. Please try again.'
                    });
                }
            });
        });


        $(document).on('click', '.present-btn, .absent-btn, .permission-btn', function () {
            const $btn = $(this);
            const $row = $btn.closest('tr');

            // 🔒 Only block HARD-LOCKED rows
            if ($row.data('locked') === 1) return;

            // Reset buttons
            $row.find('.present-btn, .absent-btn, .permission-btn')
                .removeClass('active')
                .prop('disabled', false);

            // Activate clicked button
            $btn.addClass('active').prop('disabled', true);

            const $reason = $row.find('.reason-input');

            if ($btn.hasClass('permission-btn')) {
                $reason.prop('disabled', false);
            } else {
                $reason.prop('disabled', true).val('');
            }
        });

        $(document).on('keyup', '.reason-input', function(){
            const $input = $(this);
            if($input.val().trim() !== '') $input.removeClass('border-danger').siblings('.errorAlert').addClass('d-none').text('');
        });

        // ✅ Prevent multiple bindings
        $(document).off("click", "#saveAttendance").on("click", "#saveAttendance", function() {
            let valid = true;
            let attendanceData = [];

            $("#modal-students-tbody tr").each(function () {
                const $row = $(this);
                const studentId = parseInt($row.data("id"));

                // 🚫 AUTO ABSENT (LOCKED BY RULE)
                if ($row.data('auto-absent') === 1) {
                    attendanceData.push({
                        stu_id: studentId,
                        present: 0,
                        absent: 1,
                        permission: 0,
                        reason: 'Exceeded absence limit (awaiting admin approval)',
                        pending_approval: 1
                    });
                    return; // ✅ skip normal logic
                }

                // NORMAL STUDENTS
                const isPresent    = $row.find('.present-btn').hasClass('active');
                const isAbsent     = $row.find('.absent-btn').hasClass('active');
                const isPermission = $row.find('.permission-btn').hasClass('active');
                const reason       = $row.find('.reason-input').val()?.trim() || '';

                if (isPermission && reason === '') {
                    valid = false;
                    return false;
                }

                attendanceData.push({
                    stu_id: studentId,
                    present: isPresent ? 1 : 0,
                    absent: isAbsent ? 1 : 0,
                    permission: isPermission ? 1 : 0,
                    reason,
                    pending_approval: 0
                });
            });

            if (!valid) return;

            toggleSpinner("#saveAttendance", true);

            $.ajax({
                url: 'api.php?endpoint=record_attendance',
                method: 'POST',
                data: {
                    students: JSON.stringify(attendanceData),
                    att_record_date: new Date().toISOString().split('T')[0],
                    class_id: class_id
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        showAlert(response.message);
                        $("#attModal").modal('hide');
                        loadStudents(class_id);
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                    }
                },
                error: function() {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to save attendance. Please try again.' });
                },
                complete: function() {
                    toggleSpinner("#saveAttendance", false);
                }
            });
        });

        
        $('#students-tbody').on('click', '.edit-student-btn', function() {
            const stuId = $(this).data('stu_id');
            const name = $(this).data('name');
            const gender = $(this).data('gender');
            const tel = $(this).data('tel');

            $('#update-stu-id').val(stuId);
            $('#update-full-name').val(name);
            $('#update-gender').val(gender);
            $('#update-tel').val(tel);
        });

        $('#updateStudentForm').on('submit', function(e) {
            e.preventDefault(); // prevent default form submission
            const $btn = $('#saveUpdateStudent'); // target the submit button

            // Show spinner in the button
            $btn.prop('disabled', true);
            $btn.html(`
                <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
                Loading...
            `);
            // Collect form data
            let formData = {
                stu_id: $('#update-stu-id').val(),
                full_name: $('#update-full-name').val(),
                gender: $('#update-gender').val(),
                tel: $('#update-tel').val()
            };

            // Send AJAX request
            $.ajax({
                url: 'api.php?endpoint=update_student', // replace with your PHP API file
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(res) {
                    if(res.status) {
                        // alert('Student updated successfully!');
                        $('#modalUpdateStudent').modal('hide'); // hide modal
                        // Optionally reload or update your student table here
                        loadStudents(class_id)
                    } else {
                        alert('Error: ' + res.message);
                    }
                },
                error: function(xhr, status, error) {
                    // console.error(error);
                    alert('Something went wrong.');
                },
                complete: function() {
                    // Restore button text and enable it
                    $btn.prop('disabled', false);
                    $btn.html('Save Changes');
                }
            });
        })

        // 🧩 Handle Delete Button Click
        $('#students-tbody').on('click', '.delete-student-btn', function() {
            const stuId = $(this).data('stu_id');
            const name = $(this).data('name');

            // Store the student ID in the confirm button for later use
            $('#confirmDeleteStudent').data('stu_id', stuId);

            // Display student name in modal
            $('#delete-student-name').text(name);

            // Show the modal
            $('#modalDeleteStudent').modal('show');
        });

      
        // Save All Scores Button (No validation)
        $('#saveAllScoresBtn').on('click', function() {
            let allScores = [];

            $('#students-tbody tr').each(function() {
                const $row = $(this);
                const stuId = $row.data('stu-id');

                const attScore = parseFloat($row.find('input[placeholder="Attendance Score"]').val()) || 0;
                const actScore = parseFloat($row.find('input[placeholder="Activity Score"]').val()) || 0;
                const examScore = parseFloat($row.find('input[placeholder="Exam Score"]').val()) || 0;

                allScores.push({
                    stu_id: stuId,
                    att_score: attScore,
                    act_score: actScore,
                    exam_score: examScore
                });
            });

            // Send AJAX
            $.ajax({
                url: 'api.php?endpoint=save_scores',
                type: 'POST',
                data: { scores: allScores },
                dataType: 'json',
                success: function(res) {
                    if(res.status) {
                        showAlert(res.message); // You already have this function
                    } else {
                        Swal.fire({ icon: 'error', title: 'Failed', text: res.message });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                    // console.log('Raw response:', xhr.responseText);
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to save scores. Please try again.' });
                }
            });
        });

        $(document).on('click', '.btnTransferStu', function() {
            const stuId = $(this).data('id');
            $('#studen_ID').val(stuId); // store for later use
        });

        // Open confirm modal when submitting the transfer form
        $("#transferStuForm").on("submit", function(e) {
            e.preventDefault();
            const transferTo = $("#class_ID").val().trim();
            const errorDiv = $("#transferClassError");

            if (!transferTo) {
                errorDiv.text("Please enter a valid Class ID").removeClass("d-none");
                return;
            }

            errorDiv.addClass("d-none").text("");
            $("#targetClassId").text(transferTo); // update confirm modal
            const confirmModal = new bootstrap.Modal(document.getElementById("confirmTransfer"));
            confirmModal.show();
        });

        // 🔹 Keep old student
        $(document).on("click", "#confirmTransferKeepBtn", function () {
            handleTransferAction("transferStudentNotRemove", $(this), false);
        });

        // 🔹 Remove old student
        $(document).on("click", "#confirmTransferRemoveBtn", function () {
            handleTransferAction("transferStudentAndRemove", $(this), true);
        });

        function handleTransferAction(endpoint, $btn, removeOld) {
            const stu_id = $("#studen_ID").val().trim();
            const transferTo = $("#class_ID").val().trim();
            const $spinner = $btn.find(".spinner-border");
            const errorDiv = $("#transferClassError");
            const currentClassId = class_id; 
            toggleSpinner($btn, true);

            $.ajax({
                url: `api.php?endpoint=${endpoint}`,
                type: "POST",
                data: { stu_id, transferTo },
                dataType: "json",
                success: function(res) {
                    if(res.status) {
                        $("#confirmTransfer").modal("hide");
                        $("#transferStuModal").modal("hide");

                        showAlert(res.message);

                        // ✅ Optional: reload full page if you prefer
                        // location.reload();
                        loadStudents(currentClassId);

                    } else {
                        errorDiv.text(res.message).removeClass("d-none");
                    }
                },
                error: function(xhr, status, error) {
                    errorDiv.text("Transfer failed. Please try again.").removeClass("d-none");
                    console.error("AJAX Error:", status, error);
                },
                complete: function() {
                    toggleSpinner($btn, false);
                }
            });
        }

        $(document).on("click", ".view-student-detail", function(   e) {
            e.preventDefault();

            const url = $(this).attr("href");
            const studendID = parseInt($(this).data("id"));

            if (!studendID || studendID <= 0) {
                alert("Student ID missing!");
                return;
            }



            // Add timestamp to avoid caching
            const fullUrl = url.includes("?") 
                ? `${url}&stu_id=${studendID}&_=${new Date().getTime()}`
                : `${url}?stu_id=${studendID}&_=${new Date().getTime()}`;

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

        // ---------- Render student list for Create mode ----------
        function renderStudentList(classId, forceRefresh = false) {
            let students = JSON.parse(localStorage.getItem('students_' + classId)) || null;

            if (students && !forceRefresh) {
                populateStudentList(students);
            } else {
                $.ajax({
                    url: 'api.php?endpoint=get_students_attendance_summary&class_id=' + classId,
                    type: 'POST',
                    data: { classId },
                    dataType: 'json',
                    success: function(res) {
                        if (!res.data) res.data = [];
                        localStorage.setItem('students_' + classId, JSON.stringify(res.data));
                        populateStudentList(res.data);
                    },
                    error: function(err) {
                        console.error('Failed to load students:', err);
                        $("#studentList").empty().append('<li class="list-group-item text-center text-danger">Error loading students</li>');
                    }
                });
            }
        }

        function renderStudentListForEdit(classId, currentStudents = []) {
            // console.log('current: ',currentStudents);
            
            // Ensure all IDs are strings and trimmed
            const currentIds = currentStudents
                    .filter(s => s && s.stu_id !== undefined && s.stu_id !== null)
                    .map(s => String(s.stu_id).trim());


            let students = JSON.parse(localStorage.getItem('students_' + classId)) || null;

            if (students) {
                populateStudentList(students, currentIds);
            } else {
                $.ajax({
                    url: 'api.php?endpoint=get_students_attendance_summary&class_id=' + classId,
                    type: 'POST',
                    data: { classId },
                    dataType: 'json',
                    success: function(res) {
                        if (!res.data) res.data = [];
                        localStorage.setItem('students_' + classId, JSON.stringify(res.data));
                        populateStudentList(res.data, currentIds);
                    },
                    error: function(err) {
                        console.error('Failed to load students (edit):', err);
                        $("#studentList").empty().append('<li class="list-group-item text-center text-danger">Error loading students</li>');
                    }
                });
            }
        }

        // ---------- Reset button ----------
        $(document).on('click', '#resetBtn', function(e) {
            e.preventDefault();

            const $form = $('#groupForm');

            // Reset all form fields
            $form[0].reset();

            // Remove edit mode
            $form.removeAttr('data-edit-id');

            // Change submit button text back to "Save Group"
            const $submitBtn = $form.find('button[type="submit"]');
            $submitBtn.text('Save Group').removeClass('btn-primary').addClass('btn-success');

            // Optionally, re-render student list for the class
            const classId = parseInt(class_id);
            renderStudentList(classId, true);
        });

        function populateStudentList(students, currentIds = []) {
            // console.log(currentIds);
            
            const $list = $("#studentList").empty();
            students.forEach(student => {
                const idStr = String(student.stu_id).trim();
                const checked = currentIds.includes(idStr) ? 'checked' : '';
                $list.append(`
                    <li class="list-group-item">
                        <input type="checkbox" value="${idStr}" ${checked}> ${student.full_name}
                    </li>
                `);
            });

            $('#totalStu').html(` (Total: ${students.length})`)
        }


        // ---------- Open modal for "Add Group" ----------
        $(document).on('click', '#btnGroup', function() {
            const classId = parseInt(class_id);

            $('#groupForm').removeAttr("data-edit-id");
            $("#groupForm button[type='submit']").text("Save Group").removeClass("btn-primary").addClass("btn-success");
            $('#groupForm')[0].reset();

            // Pass `true` to force fetch fresh data
            renderStudentList(classId, true);

            $('#groupmodal').modal('show');
        });

        // ---------- Submit handler (insert / update) ----------
        $('#groupForm').off('submit').on('submit', function(e) {
            e.preventDefault();

            const groupName = $("#groupName").val().trim();
            const groupTopic = $("#groupTopic").val().trim();

            const selectedStudents = [];
            $("#studentList input[type='checkbox']:checked").each(function() {
                selectedStudents.push(String($(this).val()));
            });

            if (!groupName || !groupTopic || selectedStudents.length === 0) {
                alert('Please fill all fields and select students');
                return;
            }

            const submitBtn = $(this).find('button[type="submit"]');
            const originalBtnText = submitBtn.html();

            submitBtn.html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...`).prop('disabled', true);

            const editId = $(this).attr("data-edit-id");
            const url = editId ? 'api.php?endpoint=update_group' : 'api.php?endpoint=insert_group';
            const postData = editId
                ? { id: editId, groupName, groupTopic, class_id: class_id, students: JSON.stringify(selectedStudents) }
                : { groupName, groupTopic, class_id: class_id, students: JSON.stringify(selectedStudents) };

            $.ajax({
                url: url,
                type: 'POST',
                data: postData,
                dataType: 'json',
                success: function(res) {
                    const data = typeof res === 'string' ? JSON.parse(res) : res;

                    if (!data || data.status === false) {
                        alert('Error: ' + (data && data.message ? data.message : 'Unknown'));
                        return;
                    }

                    fetchGroup(class_id);

                    $('#groupForm')[0].reset();
                    $('#groupForm').removeAttr("data-edit-id");
                    $("#groupForm button[type='submit']").text("Save Group").removeClass("btn-primary").addClass("btn-success");
                    renderStudentList(class_id);
                },
                error: function(err) {
                    console.error(err);
                    alert('AJAX Error');
                },
                complete: function() {
                    submitBtn.html(originalBtnText).prop('disabled', false);
                }
            });
        });

        // ---------- Fetch groups ----------
        function fetchGroup(classId = null) {
            let url = 'api.php?endpoint=getGroups';
            if (classId) url += '&class_id=' + classId;

            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    const groupTableBody = $('#groupTableBody');
                    groupTableBody.empty();

                    if (!res || res.status === false || !res.data || res.data.length === 0) {
                        groupTableBody.append(`<tr><td colspan="5" class="text-center">No Group</td></tr>`);
                        return;
                    }
                    // console.log(res.data);
                    
                    res.data.forEach((group, index) => {
                        let studentNames = "No students";

                        if (group.students && group.students.length > 0) {
                            studentNames = group.students.map(s => s.full_name).join("<br>");
                        }

                        groupTableBody.append(`
                            <tr class="align-middle">
                                <td>${index + 1}</td>
                                <td>${group.gr_name}</td>
                                <td class="text-primary fw-medium">${studentNames}</td>
                                <td>${group.topic}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary btn-edit" data-id="${group.id}">Edit</button>
                                    <button class="btn btn-sm btn-danger btn-delete" data-id="${group.id}">Delete</button>
                                </td>
                            </tr>
                        `);
                    });
                },
                error: function(err) {
                    console.error(err);
                    alert('AJAX error while fetching groups');
                }
            });
        }

        // ---------- Edit button ----------
        $(document).on('click', '.btn-edit', function() {
            const groupId = $(this).data('id');
            if (!groupId) return;

            $("#groupForm").attr("data-edit-id", groupId);
            $("#groupForm button[type='submit']").text("Update Group").removeClass("btn-success").addClass("btn-primary");

            fetchGroupById(groupId);
        });

        // ---------- Fetch group by ID ----------
        function fetchGroupById(groupId) {
            $.ajax({
                url: 'api.php?endpoint=getGroupById&id=' + groupId,
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    // console.log(res.data);
                    
                    if (!res || res.status === false) {
                        alert('Error: ' + (res && res.message ? res.message : 'Unknown'));
                        return;
                    }

                    const group = res.data;

                    $("#groupName").val(group.gr_name);
                    $("#groupTopic").val(group.topic);

                    renderStudentListForEdit(group.classid, group.students);

                    $("#groupmodal").modal("show");
                },
                error: function(err) {
                    console.error('Failed to fetch group:', err);
                    alert('Failed to fetch group data');
                }
            });
        }

        // ---------- Delete button ----------
        $(document).on('click', '.btn-delete', function() {
            const groupId = $(this).data('id');
            if (!groupId) return;

            const btn = $(this);
            btn.prop('disabled', true).text('Deleting...');

            $.ajax({
                url: 'api.php?endpoint=delete_group',
                type: 'POST',
                data: { id: groupId },
                dataType: 'json',
                success: function(res) {
                    if (!res || res.status === false) {
                        alert('Error: ' + (res && res.message ? res.message : 'Unknown'));
                        return;
                    }
                     $('#groupForm')[0].reset();
                    fetchGroup(class_id);
                    renderStudentList(class_id);
                },
                error: function(err) {
                    console.error(err);
                    alert('AJAX error while deleting group');
                },
                complete: function() {
                    btn.prop('disabled', false).text('Delete');
                }
            });
        });

        // ---------- Initial load ----------
        fetchGroup(class_id);
        if (typeof loadClassDetails === 'function') loadClassDetails(class_id);


        loadClassDetails(class_id);
        loadStudents(class_id);

    });
</script>