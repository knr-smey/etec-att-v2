<?php
// Get class_id and end_date from URL
$class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Optional: format the end date nicely
$formatted_date = $end_date ? date('d-m-Y', strtotime($end_date)) : '';
$discount_from = $end_date ? date('d-m-Y', strtotime($end_date)) : '';
$discount_to   = $end_date ? date('d-m-Y', strtotime($end_date . ' +14 days')) : '';

?>

<!DOCTYPE html>
<html lang="km">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Result</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Metal&display=swap" rel="stylesheet">
    <style>
        body { background: #f8f9fa; font-family: 'Khmer OS Siemreap', Arial, sans-serif; }
        table { border-collapse: collapse !important; width: 100%; }
        th, td { border: 1px solid #000 !important; vertical-align: middle !important; font-size: 13px; }
        th { background-color: #e9ecef; font-weight: bold; text-align: center; font-size: 14px; padding: 6px !important; }
        td { text-align: center; }
        #content { transform: scale(1); transform-origin: top left; }
        .kh-font{
            font-family: "Metal", serif !important;
        }
        .fail-row td {
          color: red;

          /* draw a horizontal red line inside each cell */
          background-image: linear-gradient(red, red);
          background-size: 100% 1px;       /* line thickness */
          background-position: 0 50%;      /* center vertically */
          background-repeat: no-repeat;
        }
    </style>
</head>
<body>

<div class="container-fluid" style="padding: 50px 200px;">
    <div class="mb-3 text-end">
        <button id="downloadBtn" class="btn btn-primary">Download PDF</button>
    </div>

    <div id="content" class="bg-white py-3 px-3 rounded">
        <!-- Header -->
        <div class="d-flex mb-2">
            <div class="col-2 text-center font-custom">
                <img src="./assets/etec.png" alt="ETEC Center Logo" style="width:70px;">
                <p class="mb-0 fw-bold text-uppercase font-custom">ETEC Center</p>
                <p class="mb-0 font-custom" style="font-size: 12px;">Build your IT</p>
            </div>
            <div class="col-9 text-center pt-3 fs-5">
                <p class="kh-font mb-2 fw-bold">លទ្ធផលនៃការប្រលងបញ្ចប់</p>
                <p class="mb-0 kh-font fw-bold">
                    វគ្គសិក្សា៖ <span class="fw-medium fs-5" id="course-name">Loading...</span>&nbsp;
                    ម៉ោងសិក្សា៖ <span class="fw-medium fs-5" id="class-time">Loading...</span>&nbsp;
                    ថ្ងៃទី៖ <span class="text-danger fs-5"><?php echo $formatted_date; ?></span>
                </p>
            </div>
        </div>  

        <!-- Table -->
        <div class="table-responsive mt-3">
            <table class="table text-center mb-1">
                <thead class="table-secondary">
                    <tr class="font-custom">
                        <th rowspan="2" class="fw-medium col-1">No</th>
                        <th rowspan="2" class="fw-medium col-2 text-start ps-3">Full Name</th>
                        <th rowspan="2" class="fw-medium col-1">Gender</th>
                        <th colspan="4" class="fw-medium col-3">Score</th>
                        
                        <th rowspan="2" class="fw-medium" style="width: 5%;">Result</th>
                        <th rowspan="2" class="fw-medium col-1">Other</th>
                    </tr>
                    <tr class="font-custom">
                        <th class="fw-medium" style="width: 5%;">ATT</th>
                        <th class="fw-medium" style="width: 5%;">ACT</th>
                        <th class="fw-medium" style="width: 5%;">EXAM</th>
                        <th class="fw-medium" style="width: 5%;">Total</th>
                    </tr>
                </thead>
                <tbody id="student-rows">
                    <!-- Students will be inserted dynamically -->
                </tbody>
            </table>
        </div>
        <!-- Note -->
        <div class="mt-2 mb-3">
          <p class="mb-0 text-danger fw-bold" style="font-size: 13px;">
            ចំណាំ៖ ការបញ្ចុះតម្លៃមានសុពលភាពចាប់ពីថ្ងៃទី <?= $discount_from ?> ដល់ថ្ងៃទី <?= $discount_to ?> (រយៈពេល ២ សប្តាហ៍)។
          </p>
        </div>

        <!-- Footer -->
        <div class="d-flex justify-content-between mt-2">
            <div class="stamp col-2 text-center kh-font">
                <p class="m-0" style="font-size: 13px;">បានឃើញ និង ឯកភាព</p>
                <p class="m-0 mb-3" style="font-size: 13px;">នាយកមជ្ឈមណ្ឌល</p>
                <img src="./assets/stemp.png" alt="Stamp" width="120px">
            </div>
            <div class="signature col-4">
                <div class="text-center kh-font" id="dynamic-date-signature" style="font-size: 13px;"></div>
                <p class="kh-font mt-3 pt-5 text-center" style="font-size: 13px;">គ្រូបង្រៀន៖ <span class="fs-6 px-2" id="teacher-name">Loading...</span></p>
            </div>
        </div>
    </div>
</div>

<!-- jQuery and html2pdf.js -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
$(document).ready(function() {
  const classId = <?php echo $class_id; ?>;

  // Khmer month names
  const khMonths = [
    "មករា", "កុម្ភៈ", "មីនា", "មេសា", "ឧសភា", "មិថុនា",
    "កក្កដា", "សីហា", "កញ្ញា", "តុលា", "វិច្ឆិកា", "ធ្នូ"
  ];

  function toKhmerDigits(number) {
    const khDigits = ['០','១','២','៣','៤','៥','៦','៧','៨','៩'];
    return number.toString().split('').map(d => khDigits[d] || d).join('');
  }

  function formatKhmerDate(date = new Date()) {
    const day = toKhmerDigits(date.getDate());
    const month = khMonths[date.getMonth()];
    const year = toKhmerDigits(date.getFullYear());
    return `ធ្វើនៅភ្នំពេញ, ថ្ងៃទី ${day} ខែ ${month} ឆ្នាំ ${year}`;
  }

  $('#dynamic-date-signature').html(`
    <p class="mb-1">${formatKhmerDate()}</p>
    <p>ហត្ថលេខានិងឈ្មោះគ្រូ</p>
  `);

  // PDF download (keep same)
  $('#downloadBtn').click(function() {
    const element = document.getElementById("content");
    const opt = {
      margin: 0.2,
      filename: 'class_result.pdf',
      image: { type: 'jpeg', quality: 1 },
      html2canvas: { scale: 3, useCORS: true },
      jsPDF: { unit: 'in', format: 'a4', orientation: 'landscape' }
    };
    html2pdf().set(opt).from(element).save();
  });

  // ---------------------------
  // DISCOUNT RULES
  // ---------------------------
  let discountRules = [];

  function getRuleByScore(score) {
    score = parseFloat(score || 0);
    for (const r of discountRules) {
      const min = parseFloat(r.min_score);
      const max = parseFloat(r.max_score);
      if (score >= min && score <= max) return r;
    }
    return null;
  }

  // ---------------------------
  // LOAD CLASS + STUDENTS (after rules loaded)
  // ---------------------------
  function loadClassStudents() {
    $.ajax({
      url: 'api.php',
      method: 'GET',
      data: { endpoint: 'getClassWithStudent', class_id: classId },
      dataType: 'json',
      success: function(res) {

        if(!res.status) return alert(res.message || "Failed");

        const classData = res.data.class;
        const students = res.data.students;

        $('#course-name').text(classData.course_name || 'N/A');
        $('#class-time').text(classData.time || 'N/A');
        $('#teacher-name').text(classData.instructor_name || 'N/A');

        const tbody = $('#student-rows');
        tbody.empty();

        if(!students || students.length === 0) {
          tbody.append(`<tr><td colspan="9">No students found</td></tr>`);
          return;
        }

        // PASS first, then highest total on top, FAIL at bottom
        students.sort((a, b) => {
          const totalA =
            parseFloat(a.att_score || 0) +
            parseFloat(a.act_score || 0) +
            parseFloat(a.exam_score || 0);

          const totalB =
            parseFloat(b.att_score || 0) +
            parseFloat(b.act_score || 0) +
            parseFloat(b.exam_score || 0);

          const failA = totalA < 50 ? 1 : 0; // 0=pass, 1=fail
          const failB = totalB < 50 ? 1 : 0;

          // 1) PASS first, FAIL last
          if (failA !== failB) return failA - failB;

          // 2) Same group (both pass or both fail) -> highest total first
          return totalB - totalA;
        });


        students.forEach((stu, index) => {
          const att  = parseFloat(stu.att_score || 0);
          const act  = parseFloat(stu.act_score || 0);
          const exam = parseFloat(stu.exam_score || 0);

          const total = att + act + exam;
          const pass = total >= 50;

          const rule = getRuleByScore(total);
          let otherText = '';
          if (rule) {
            const percent = Number(rule.discount_percent);
            otherText = `(${percent}% discount) 🎉`;
          }

          tbody.append(`
            <tr class="small font-custom ${pass ? '' : 'fail-row'}">
              <td>${index + 1}</td>
              <td class="fw-bold text-start ps-3">${stu.full_name}</td>
              <td>${stu.gender}</td>
              <td>${stu.att_score || '0'}</td>
              <td>${stu.act_score || '0'}</td>
              <td>${stu.exam_score || '0'}</td>
              <td>${total}</td>

              <td style="color:${pass ? 'green' : 'red'};">
                ${pass ? 'Pass' : 'Fail'}
              </td>

              <td>${otherText}</td>
            </tr>
          `);
        });



      },
      error: function(err) {
        console.error(err);
        alert("Failed to fetch class data.");
      }
    });
  }

  // ---------------------------
  // 1) Load discount rules first
  // 2) Then load class students
  // ---------------------------
  $.ajax({
    url: 'api.php',
    method: 'GET',
    data: { endpoint: 'get_discount_rules' },
    dataType: 'json',
    success: function(ruleRes) {
      discountRules = (ruleRes.status && Array.isArray(ruleRes.data)) ? ruleRes.data : [];
      loadClassStudents();
    },
    error: function() {
      // still show students even if rules fail
      loadClassStudents();
    }
  });

});
</script>


</body>
</html>
