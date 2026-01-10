<?php
// Get class_id from URL
$class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
?>

<!-- Hidden input for class ID -->
<input type="hidden" name="class_id" id="class_id" value="<?= $class_id ?>" readonly>

<section>
    <a href="pages/admin/classes.php" class="back-to-class mb-3 btn btn-secondary btn-sm text-light"> Back to Class </a>

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
                        Yes, Transfer (Keep Old)
                        <span class="spinner-border spinner-border-sm d-none ms-2" role="status" aria-hidden="true"></span>
                    </button>
                    <button type="button" class="btn btn-danger px-4" id="confirmTransferRemoveBtn">
                        Yes, Transfer & Remove
                        <span class="spinner-border spinner-border-sm d-none ms-2" role="status" aria-hidden="true"></span>
                    </button>
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
                                        <span class="badge fs-6 px-3 py-2 ${classData.class_status === 'Online' ? 'bg-success' : 'bg-secondary'}">
                                            ${classData.class_status}
                                        </span>
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
                        res.data.forEach((student, index) => {
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

                                        <a href="pages/admin/studentsView.php?stu_id=${student.stu_id}&class_id=${classId}" 
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
                    } else {
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
                    console.error(error);
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

        // 🧩 Confirm Delete Student
        $('#confirmDeleteStudent').on('click', function() {
            const stuId = $(this).data('stu_id');
            const $btn = $(this);
            const $spinner = $btn.find('.spinner-border');

            // Show loading spinner
            $spinner.removeClass('d-none');
            $btn.prop('disabled', true);

            $.ajax({
                url: 'api.php?endpoint=delete_student',
                type: 'POST',
                data: { 
                    stu_id: stuId,
                    class_id:class_id 
                },
                dataType: 'json',
                success: function(res) {
                    if (res.status) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        });

                        $('#modalDeleteStudent').modal('hide');
                        loadStudents(class_id); // reload table
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: res.message });
                    }
                },
                error: function() {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to delete student.' });
                },
                complete: function() {
                    $spinner.addClass('d-none');
                    $btn.prop('disabled', false);
                }
            });
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
                    console.log('Raw response:', xhr.responseText);
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

        $(document).on("click", ".view-student-detail", function(e) {
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
        
        loadClassDetails(class_id)
        loadStudents(class_id);


    });
</script>


