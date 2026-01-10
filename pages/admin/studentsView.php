<?php
$stu_id = isset($_GET['stu_id']) ? intval($_GET['stu_id']) : 0;
$class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0; // <-- fix here
?>
<input type="hidden" id="stu_id" value="<?= $stu_id ?>">
<input type="hidden" id="class_id">

<section class="container-fluid p-0 font-custom">

    <a href="pages/admin/classdetails.php?class_id=<?= $class_id ?>" class="back-to mb-3 btn btn-secondary btn-sm text-light"> Back to Class </a>

    <div class="row g-4 mb-3 align-items-center">
        <!-- Student Info (Left Column) -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm overflow-hidden" id="student-info">
                
                <!-- Header -->
                <div class="card-header bg-etec-color text-white d-flex align-items-center">
                    <i class="bi bi-person-circle fs-4 me-2"></i>
                    <h5 class="mb-0 fw-semibold">Student Information</h5>
                </div>

                <!-- Body -->
                <div class="card-body bg-light">
                  <div class="row g-4">

                    <!-- Left Column: Personal Information -->
                    <div class="col-md-6 border-end">
                      <h6 class="fw-bold text-uppercase text-primary mb-3">
                        <i class="bi bi-person-lines-fill me-2"></i>Personal Information
                      </h6>

                      <div class="vstack gap-3">

                        <div class="d-flex align-items-center">
                          <i class="bi bi-person-fill text-primary me-2 fs-5"></i>
                          <h6 class="fw-bold mb-0 me-2">Name:</h6>
                          <span id="stu_name" class="placeholder-wave text-secondary text-etec-color fw-bolder">
                            <span class="placeholder col-6"></span>
                          </span>
                        </div>

                        <div class="d-flex align-items-center">
                          <i class="bi bi-hash text-success me-2 fs-5"></i>
                          <h6 class="fw-bold mb-0 me-2">Student ID:</h6>
                          <span id="stu_code" class="placeholder-wave text-light bg-etec-color rounded px-2">
                            <span class="placeholder col-4"></span>
                          </span>
                        </div>

                        <div class="d-flex align-items-center">
                          <i class="bi bi-gender-ambiguous text-warning me-2 fs-5"></i>
                          <h6 class="fw-bold mb-0 me-2">Gender:</h6>
                          <span id="stu_gender" class="placeholder-wave text-secondary">
                            <span class="placeholder col-3"></span>
                          </span>
                        </div>

                        <div class="d-flex align-items-center">
                          <i class="bi bi-telephone-fill text-danger me-2 fs-5"></i>
                          <h6 class="fw-bold mb-0 me-2">Tel:</h6>
                          <span id="stu_tel" class="placeholder-wave text-secondary">
                            <span class="placeholder col-5"></span>
                          </span>
                        </div>
                      </div>
                    </div>

                    <!-- Right Column: Academic Information -->
                    <div class="col-md-6">
                      <h6 class="fw-bold text-uppercase text-success mb-3">
                        <i class="bi bi-book-half me-2"></i>Academic Information
                      </h6>

                      <div class="vstack gap-3">
                        <div class="d-flex align-items-center">
                          <i class="bi bi-building text-primary me-2 fs-5"></i>
                          <h6 class="fw-bold mb-0 me-2">Class:</h6>
                          <span id="stu_class" class="placeholder-wave text-secondary text-etec-color fw-bolder">
                            <span class="placeholder col-6"></span>
                          </span>
                        </div>

                        <div class="d-flex align-items-center">
                          <i class="bi bi-journal-bookmark text-danger me-2 fs-5"></i>
                          <h6 class="fw-bold mb-0 me-2">Course:</h6>
                          <span id="stu_course" class="placeholder-wave text-secondary text-etec-color fw-bolder">
                            <span class="placeholder col-6"></span>
                          </span>
                        </div>

                        <div class="d-flex align-items-center">
                          <i class="bi bi-clock-history text-warning me-2 fs-5"></i>
                          <h6 class="fw-bold mb-0 me-2">Time:</h6>
                          <span id="stu_time" class="placeholder-wave text-secondary text-etec-color fw-bolder">
                            <span class="placeholder col-5"></span>
                          </span>
                        </div>
                      </div>
                    </div>

                  </div>
                </div>

            </div>
        </div>


        <!-- Summary Cards (Right Column) -->
        <div class="col-lg-6">
          <div class="row g-3 text-center" id="summary-cards">
              <?php
              $cards = [
                  ['text-success', 'bg-success-subtle', 'Total Present', 'total_present'],
                  ['text-danger', 'bg-danger-subtle', 'Total Absent', 'total_absent'],
                  ['text-warning', 'bg-warning-subtle', 'Total Permission', 'total_permission'],
                  ['text-info', 'bg-info-subtle', 'Total Attendance', 'total_att']
              ];

              foreach ($cards as [$text, $bg, $label, $id]) {
                  echo "
                  <div class='col-6'>
                      <div class='card border-0 shadow-sm $bg'>
                      <div class='card-body'>
                          <h6 class='fw-semibold $text'>$label</h6>
                          <h3 class='fw-bold $text mb-0 placeholder-wave'>
                              <span id='$id' class='placeholder col-6'></span>
                          </h3>
                      </div>
                      </div>
                  </div>
                  ";
              }
              ?>
          </div>
        </div>
    </div>
  <!-- Attendance Table -->
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-light fw-semibold">
      <i class="bi bi-calendar-check text-primary"></i> Attendance Records
    </div>
    <div class="card-body shadow-none p-0">
      <div class="table-responsive">
        <table class="table  table-bordered align-middle text-center mb-0">
          <thead class="">
            <tr>
              <th>No</th>
              <th>Created Date</th>
              <th>Attendance Date</th>
              <th class="text-success">Present</th>
              <th class="text-danger">Absent</th>
              <th class="text-warning">Permission</th>
              <th>Reason</th>
            </tr>
          </thead>
          <tbody id="student-data">
            <!-- Skeleton rows -->
            <?php for ($i = 0; $i < 3; $i++): ?>
              <tr>
                <?php for ($j = 0; $j < 7; $j++): ?>
                <td><span class="placeholder col-8"></span></td>
                <?php endfor; ?>
              </tr>
            <?php endfor; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>

<script>
$(document).ready(function() {
  const stuId = $('#stu_id').val();
  const tbody = $('#student-data');
  let classOldid;

  $(document).on("click", ".back-to", function(e) {
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

  $.ajax({
    url: 'api.php?endpoint=showStudentData',
    method: 'POST',
    data: { id: stuId },
    dataType: 'json',
    success: function(res) {
      setTimeout(() => { // short delay for smoother feel
        tbody.fadeOut(150, function() {
          tbody.empty();

          if (!res.status || !res.data || res.data.length === 0) {
            tbody.html('<tr><td colspan="7" class="text-muted py-3">No attendance records found</td></tr>');
          } else {
            const student = res.data[0];
            $('#stu_name').text(student.full_name);
            $('#stu_code').text(student.stu_id);
            $('#stu_gender').text(student.gender);
            $('#stu_tel').text(student.tel);
            $('#stu_course').text(student.course)
            $('#stu_time').text(student.class_time)
            $('#stu_class').text(student.class_id)
            $('#class_id').val(student.class_id)

            // classOldid = student.class_id

            // Counters
            let totalPresent = 0, totalAbsent = 0, totalPermission = 0;

            res.data.forEach((row, index) => {
              totalPresent += parseInt(row.present) || 0;
              totalAbsent += parseInt(row.absent) || 0;
              totalPermission += parseInt(row.permission) || 0;

              tbody.append(`
                <tr>
                  <td>${index + 1}</td>
                  <td>${row.created_at}</td>
                  <td><span class="bg-secondary-subtle px-2 rounded">${row.att_record_date}</span></td>
                  <td><span class="badge bg-success">${row.present}</span></td>
                  <td><span class="badge bg-danger">${row.absent}</span></td>
                  <td><span class="badge bg-warning text-dark">${row.permission}</span></td>
                  <td>${row.reason || '—'}</td>
                </tr>
              `);
            });

            // Update summary cards
            $('#total_present').text(totalPresent);
            $('#total_absent').text(totalAbsent);
            $('#total_permission').text(totalPermission);
            $('#total_att').text(totalPresent + totalAbsent + totalPermission);
          }

          // Remove skeleton placeholders
          $('.placeholder, .placeholder-wave').removeClass('placeholder placeholder-wave');
          tbody.fadeIn(200);
        });
      }, 200);
    },
    error: function() {
      tbody.html('<tr><td colspan="7" class="text-danger py-3">Error fetching data</td></tr>');
    }
  });

  

  $(document).on("click", ".back-to-student", function(e) {
      e.preventDefault();

      const url = $(this).attr("href");
      const classId = parseInt($('#class_id').val()); // get the value from hidden input

      if (!classId) {
          console.warn("Class ID is missing!");
          return;
      }

      // Add timestamp to avoid caching
      const fullUrl = url.includes("?") 
          ? `${url}&class_id=${classId}&_=${new Date().getTime()}`
          : `${url}?class_id=${classId}&_=${new Date().getTime()}`;

      // Optional: show loading overlay
      $("#content-area").html(`
          <div id="loading-overlay" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-25 d-flex justify-content-center align-items-center text-center text-white" style="z-index: 2000;">
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

});
</script>
