
<link rel="stylesheet" href="assets/css/studentAdmin.css">

<div class="container-fluid p-0">
  <div class="main-container">
    <!-- Header -->
    <div class="page-header">
      <h4>
        <i class="bi bi-people-fill me-2"></i> Student Management
      </h4>
    </div>

    <!-- Filters -->
    <div class="filter-section">
      <div class="row g-3">
        <div class="col-md-5">
          <div class="search-icon">
            <input type="text" id="searchInput" class="form-control search-input" placeholder="Search by name, course, or instructor...">
          </div>
        </div>
        <div class="col-md-3">
          <select id="courseFilter" class="form-select">
            <option value="">All Courses</option>
          </select>
        </div>
        <div class="col-md-2">
          <select id="genderFilter" class="form-select">
            <option value="">All Genders</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="position-relative">
      <div id="tableLoader" class="loading-overlay d-none">
        <div class="spinner-border" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>

      <div class="table-container">
        <table class="table" id="studentTable">
          <thead>
            <tr>
              <th style="width: 50px;">#</th>
              <th>Name</th>
              <th style="width: 100px;">Gender</th>
              <th style="width: 130px;">Phone</th>
              <th>Course</th>
              <th>Instructor</th>
              <th style="width: 80px;">Class</th>
              <th style="width: 120px;">Created</th>
              <th style="width: 120px;">Permission</th>
              <th style="width: 120px;">Action</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <nav>
      <ul class="pagination justify-content-center" id="pagination"></ul>
    </nav>
  </div>
</div>

<!-- Permission Modal -->
<div class="modal fade" id="permissionModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form id="permissionForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bi bi-calendar-check me-2"></i> Student Permission Request
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" name="stu_id" id="perm_stu_id">
        <input type="hidden" name="class_id" id="perm_class_id">

        <div class="mb-3">
          <label class="form-label">Student Name</label>
          <input type="text" id="perm_student_name" class="form-control" readonly>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Start Date</label>
            <input type="date" name="start_date" class="form-control" required>
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label">End Date</label>
            <input type="date" name="end_date" class="form-control" required>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Reason</label>
          <textarea name="reason" class="form-control" rows="3" placeholder="Enter the reason for permission..."></textarea>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-check-circle me-1"></i> Submit Request
        </button>
      </div>
    </form>
  </div>
</div>

<script>
$(document).ready(function () {

  let currentPage = 1;
  const limit = 10;

  // =========================
  // SHOW/HIDE LOADING
  // =========================
  function showLoading() {
    $("#tableLoader").removeClass("d-none");
  }

  function hideLoading() {
    $("#tableLoader").addClass("d-none");
  }

  // =========================
  // FETCH STUDENTS
  // =========================
  function fetchStudents(page = 1) {
    currentPage = page;
    showLoading();

    $.ajax({
      url: "api.php",
      method: "GET",
      dataType: "json",
      data: {
        endpoint: "getAllStudents",
        page: page,
        limit: limit,
        search: $("#searchInput").val().trim(),
        course: $("#courseFilter").val(),
        gender: $("#genderFilter").val()
      },
      success: function (res) {
        hideLoading();
        
        if (!res.status) {
          $("#studentTable tbody").html(`
            <tr>
              <td colspan="9" class="no-data-message">
                <i class="bi bi-exclamation-circle d-block"></i>
                <div>Error loading data</div>
              </td>
            </tr>
          `);
          return;
        }

        renderTable(res.data.students);
        renderPagination(res.data);
      },
      error: function () {
        hideLoading();
        $("#studentTable tbody").html(`
          <tr>
            <td colspan="9" class="no-data-message">
              <i class="bi bi-wifi-off d-block"></i>
              <div>Failed to load data. Please try again.</div>
            </td>
          </tr>
        `);
      }
    });
  }

  // =========================
  // RENDER TABLE
  // =========================
  function renderTable(students) {
    const tbody = $("#studentTable tbody");
    tbody.empty();

    if (students.length === 0) {
      tbody.html(`
        <tr>
          <td colspan="9" class="no-data-message">
            <i class="bi bi-inbox d-block"></i>
            <div>No students found</div>
          </td>
        </tr>
      `);
      return;
    }

    const start = (currentPage - 1) * limit;

    students.forEach((stu, index) => {
      const genderBadge = stu.gender === 'Male' 
        ? '<span class="badge-gender badge-male">Male</span>' 
        : '<span class="badge-gender badge-female">Female</span>';

      tbody.append(`
        <tr>
          <td class="text-center">${start + index + 1}</td>
          <td class="student-name">${stu.full_name}</td>
          <td>${genderBadge}</td>
          <td>${stu.tel || '<span class="text-muted">—</span>'}</td>
          <td>${stu.course || '<span class="text-muted">—</span>'}</td>
          <td>${stu.instructor_name || '<span class="text-muted">—</span>'}</td>
          <td class="text-center">${stu.class_id ? '#' + stu.class_id : '<span class="text-muted">—</span>'}</td>
          <td>${new Date(stu.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</td>
          <td>
            <button 
              class="btn btn-sm btn-permission btnPermission"
              data-stu="${stu.id}"
              data-class="${stu.class_id}"
              data-name="${stu.full_name}"
            >
              <i class="bi bi-calendar-check me-1"></i> Permission
            </button>
          </td>
          <td>
            <button
              class="btn btn-sm btn-danger btnDeleteStudent"
              data-stu="${stu.id}"
              data-class="${stu.class_id}"
              data-name="${stu.full_name}"
            >
              <i class="bi bi-trash"></i>
            </button>
            <a href="pages/admin/studentDetails.php?stu_id=${stu.id}" 
                class="view-student-detail text-decoration-none"
                data-id="${stu.id}">
                <button class="btn btn-light">
                    <i class="bi bi-eye-fill"></i>
                </button>
            </a>
           
          </td>
        </tr>
      `);
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



  // =========================
  // DELETE STUDENT
  // =========================
  $(document).on("click", ".btnDeleteStudent", function () {
    const stuId = $(this).data("stu");
    const classId = $(this).data("class");
    const name = $(this).data("name");

    Swal.fire({
      title: 'Delete student?',
      html: `<strong>${name}</strong><br>This action cannot be undone.`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Yes, delete',
      cancelButtonText: 'Cancel'
    }).then((result) => {
      if (!result.isConfirmed) return;

      Swal.fire({
        title: 'Deleting...',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

     $.ajax({
        url: "api.php?endpoint=delete_student",
        method: "POST",
        dataType: "json",
        data: {
          studentId: stuId,
          class_id: classId
        },
        success: function (res) {
          if (!res.status) {
            Swal.fire({
              icon: 'error',
              title: 'Failed',
              text: res.message || 'Failed to delete student'
            });
            return;
          }

          Swal.fire({
            icon: 'success',
            title: 'Deleted!',
            text: 'Student deleted successfully',
            timer: 1500,
            showConfirmButton: false
          });

          fetchStudents(currentPage);
        },
        error: function () {
          Swal.fire({
            icon: 'error',
            title: 'Server Error',
            text: 'Please try again later'
          });
        }
      });

    });
  });

  function renderPagination(meta) {
    const ul = $("#pagination");
    ul.empty();

    const totalPages = meta.total_pages;
    const current = meta.page;

    if (totalPages <= 1) return;

    const maxVisible = 5;
    let start = Math.max(1, current - Math.floor(maxVisible / 2));
    let end = start + maxVisible - 1;

    if (end > totalPages) {
      end = totalPages;
      start = Math.max(1, end - maxVisible + 1);
    }

    // Prev
    ul.append(`
      <li class="page-item ${current === 1 ? 'disabled' : ''}">
        <a class="page-link" href="#" data-page="${current - 1}">
          <i class="bi bi-chevron-left"></i>
        </a>
      </li>
    `);

    // First + dots
    if (start > 1) {
      ul.append(`
        <li class="page-item">
          <a class="page-link" href="#" data-page="1">1</a>
        </li>
      `);
      if (start > 2) {
        ul.append(`<li class="page-item disabled"><span class="page-link">…</span></li>`);
      }
    }

    // Pages
    for (let i = start; i <= end; i++) {
      ul.append(`
        <li class="page-item ${i === current ? 'active' : ''}">
          <a class="page-link" href="#" data-page="${i}">${i}</a>
        </li>
      `);
    }

    // Last + dots
    if (end < totalPages) {
      if (end < totalPages - 1) {
        ul.append(`<li class="page-item disabled"><span class="page-link">…</span></li>`);
      }
      ul.append(`
        <li class="page-item">
          <a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a>
        </li>
      `);
    }

    // Next
    ul.append(`
      <li class="page-item ${current === totalPages ? 'disabled' : ''}">
        <a class="page-link" href="#" data-page="${current + 1}">
          <i class="bi bi-chevron-right"></i>
        </a>
      </li>
    `);
  }

  // Pagination click
  $(document).on("click", "#pagination .page-link", function (e) {
    e.preventDefault();
    
    const $parent = $(this).parent();
    if ($parent.hasClass("disabled") || $parent.hasClass("active")) {
      return;
    }

    const page = parseInt($(this).data("page"));
    if (page && page !== currentPage) {
      fetchStudents(page);
      // Smooth scroll to top of table
      $('html, body').animate({
        scrollTop: $(".main-container").offset().top - 20
      }, 300);
    }
  });
  
  // =========================
  // SUBMIT PERMISSION
  // =========================
  $("#permissionForm").on("submit", function (e) {
    e.preventDefault();

    const $submitBtn = $(this).find('button[type="submit"]');
    const originalText = $submitBtn.html();
    $submitBtn.html('<span class="spinner-border spinner-border-sm me-1 text-white"></span> Submitting...').prop('disabled', true);

    $.ajax({
      url: "api.php?endpoint=student_permission_create",
      method: "POST",
      dataType: "json",
      data: $(this).serialize(),
      success: function (res) {
        $submitBtn.html(originalText).prop('disabled', false);

        if (!res.status) {
          Swal.fire({
            icon: 'error',
            title: 'Failed',
            text: res.message || 'Failed to submit permission',
          });
          return;
        }

        Swal.fire({
          icon: 'success',
          title: 'Success!',
          text: 'Permission submitted successfully',
          timer: 2000,
          showConfirmButton: false
        }).then(() => {
          $("#permissionModal").modal("hide");
          $form[0].reset();
        });
      },

      error: function () {
        $submitBtn.html(originalText).prop('disabled', false);
        alert("Server error. Please try again.");
      }
    });
  });

  // =========================
  // FILTER EVENTS
  // =========================
  $("#searchInput").on("input", function () {
    fetchStudents(1);
  });

  $("#courseFilter, #genderFilter").on("change", function () {
    fetchStudents(1);
  });

  // =========================
  // LOAD COURSES
  // =========================
  function loadCourses() {
    $.ajax({
      url: "api.php",
      method: "GET",
      dataType: "json",
      data: { endpoint: "course_getall" },
      success: function (res) {
        if (!res.status) return;
        res.data.forEach(c => {
          $("#courseFilter").append(`<option value="${c.id}">${c.course}</option>`);
        });
      }
    });
  }

  // =========================
  // OPEN PERMISSION MODAL
  // =========================
  $(document).on("click", ".btnPermission", function () {
    $("#perm_stu_id").val($(this).data("stu"));
    $("#perm_class_id").val($(this).data("class"));
    $("#perm_student_name").val($(this).data("name"));

    $("#permissionModal").modal("show");
  });

  // INIT
  loadCourses();
  fetchStudents(1);
});
</script>