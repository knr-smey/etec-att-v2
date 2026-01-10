<div class="container-fluid p-0">
  <div class="card p-0 border-0">
    <!-- Header -->
    <div class="card-header bg-white border-0 p-0">
      <h4 class="fw-bold text-etec-color mb-0">
        <i class="bi bi-people-fill me-2 text-etec-color"></i> Student Management
      </h4>
    </div>

    <!-- Inline Filters & Search -->
    <div class="card-body pb-0 p-0 mt-3">
      <div class="row g-2 align-items-center">
        <div class="col-md-4">
          <input type="text" id="searchInput" class="form-control shadow-none" placeholder="Search by student, course, or instructor" />
        </div>
        <div class="col-md-3">
          <select id="courseFilter" class="form-select shadow-none">
            <option value="">All Courses</option>
          </select>
        </div>
        <div class="col-md-2">
          <select id="genderFilter" class="form-select shadow-none">
            <option value="">All Genders</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Alert -->
    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert" style="display:none;">
      <span id="successMessage"></span>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <!-- Scrollable Table -->
    <div class="table-responsive mt-3" style="max-height: 580px; overflow-y: auto;">
      <table class="table table-bordered align-middle mb-0" id="studentTable">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Student Name</th>
            <th>Gender</th>
            <th>Tel</th>
            <th>Course</th>
            <th>Instructor</th>
            <th>Class</th>
            <th>Created At</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php for ($i = 0; $i < 5; $i++) { ?>
          <tr>
            <td><span class="placeholder col-1 placeholder-glow">&nbsp;</span></td>
            <td><span class="placeholder col-3 placeholder-glow">&nbsp;</span></td>
            <td><span class="placeholder col-2 placeholder-glow">&nbsp;</span></td>
            <td><span class="placeholder col-3 placeholder-glow">&nbsp;</span></td>
            <td><span class="placeholder col-3 placeholder-glow">&nbsp;</span></td>
            <td><span class="placeholder col-3 placeholder-glow">&nbsp;</span></td>
            <td><span class="placeholder col-2 placeholder-glow">&nbsp;</span></td>
            <td><span class="placeholder col-3 placeholder-glow">&nbsp;</span></td>
            <td><span class="placeholder col-2 placeholder-glow">&nbsp;</span></td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>

    <!-- Delete Student Modal -->
    <div class="modal fade" id="deleteStudentModal" tabindex="-1" aria-labelledby="deleteStudentModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title" id="deleteStudentModalLabel">Delete Student</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            Are you sure you want to delete this student?
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger" id="confirmDeleteStudent">Delete</button>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<style>
/* Sticky table header */
.table-responsive thead tr {
  position: sticky;
  top: 0;
  background: #fff;
  z-index: 2;
}
</style>

<script>
$(document).ready(function() {
  let typingTimer;
  const typingDelay = 300;
  let studentToDelete = null;
  let classToDelete = null;

  // Fetch students
  function fetchStudents() {
    const search = $('#searchInput').val().trim();
    const course = $('#courseFilter').val();
    const gender = $('#genderFilter').val();

    $.ajax({
      url: 'api.php',
      method: 'GET',
      data: { endpoint: 'getAllStudents', search, course, gender },
      dataType: 'json',
      success: function(res) {
        if(res.status && res.data.length > 0){
          renderTable(res.data);
        } else {
          $('#studentTable tbody').html('<tr><td colspan="9" class="text-center">No students found</td></tr>');
        }
      },
      error: function(err){ console.error(err); }
    });
  }

  // Render table
  function renderTable(students){
    const tbody = $('#studentTable tbody');
    tbody.stop(true,true).fadeOut(150, function(){
      tbody.empty();
      students.forEach((stu, index) => {
        tbody.append(`
          <tr>
            <td>${index + 1}</td>
            <td class="fw-bold">${stu.full_name}</td>
            <td>${stu.gender}</td>
            <td>${stu.tel || '-'}</td>
            <td>${stu.course || '-'}</td>
            <td>${stu.instructor_name || 'N/A'}</td>
            <td>${stu.class_id ? '#' + stu.class_id : '-'}</td>
            <td>${new Date(stu.created_at).toLocaleDateString()}</td>
            <td class="text-center">
                <a href="pages/admin/studentDetails.php?stu_id=${stu.id}&class_id=${stu.class_id}" 
                    class="view-student-detail text-decoration-none"
                    data-id="${stu.id}">
                    <button class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye-fill"></i>
                    </button>
                </a>
                <button class="btn btn-outline-danger btn-sm btnDeleteStudent" 
                      data-id="${stu.id}" 
                      data-class-id="${stu.class_id}" 
                      data-bs-toggle="modal" 
                      data-bs-target="#deleteStudentModal">
                <i class="bi bi-trash"></i>
                </button>
            </td>
          </tr>
        `);
      });
      tbody.fadeIn(200);
    });
  }

  // Populate courses
  function fetchCourses() {
    $.ajax({
      url: 'api.php',
      method: 'GET',
      data: { endpoint: 'course_getall' },
      dataType: 'json',
      success: function(res) {
        if(res.status){
          const select = $('#courseFilter');
          res.data.forEach(c => { select.append(`<option value="${c.id}">${c.course}</option>`); });
        }
      }
    });
  }

  // Delete student
  $(document).on('click', '.btnDeleteStudent', function(){
    studentToDelete = $(this).data('id');
    classToDelete = $(this).data('class-id');
    console.log("Selected:", studentToDelete, classToDelete);
  });

    $('#confirmDeleteStudent').on('click', function(){
        if(!studentToDelete) return;

        $.ajax({
        url: 'api.php?endpoint=delete_student',
        method: 'POST',
        data: { studentId: studentToDelete, class_id: classToDelete },
        dataType: 'json',
        success: function(res){
            if(res.status){
            $('#deleteStudentModal').modal('hide');
            $('.alert').fadeIn().find('#successMessage').text(res.message);
            setTimeout(()=> $('.alert').fadeOut(), 3000);
            fetchStudents();
            } else console.error(res.message);
        }
        });
    });

    // Event listeners
    $('#searchInput').on('input', function(){
        clearTimeout(typingTimer);
        typingTimer = setTimeout(fetchStudents, typingDelay);
    });

    $('#courseFilter, #genderFilter').on('change', fetchStudents);

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

    // Initial load
    fetchCourses();
    fetchStudents();
});
</script>
