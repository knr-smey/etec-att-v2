<div class="container-fluid p-0">
    <div class="card p-0 border-0">

        <!-- Header -->
        <div class="card-header bg-white border-0 p-0 d-flex align-items-center justify-content-between">
            <h4 class="fw-bold text-etec-color mb-0">
                Student Certificate Request
            </h4>

            <!-- Filter Form -->
            <div class="col-3">
                <select class="form-select" id="filterType">
                    <option value="">Filter by...</option>
                </select>
            </div>
        </div>

        <!-- Table -->
        <div class="card-body p-0 my-3">
            <table class="table table-bordered" id="requestTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th>Instructor</th>
                        <th>Course</th>
                        <th>Category</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

    </div>
</div>

<script>
$(document).ready(function () {

    const tbody = $("#requestTable tbody");
    let allData = [];

    // =========================
    // FORMAT DATE
    // =========================
    function formatDate(dateString) {
        let d = new Date(dateString);
        return d.toLocaleDateString();
    }

    // =========================
    // RENDER TABLE (GROUP BY INSTRUCTOR)
    // =========================
    function renderTable(data) {

        tbody.empty();

        if (data.length === 0) {
            tbody.html("<tr><td colspan='6'>No data</td></tr>");
            return;
        }

        // group by instructor
        let grouped = {};

        data.forEach(item => {
            if (!grouped[item.instructor_name]) {
                grouped[item.instructor_name] = [];
            }
            grouped[item.instructor_name].push(item);
        });

        let index = 1;

        $.each(grouped, function (instructor, students) {

            // 🔥 Instructor header row with Done button
            tbody.append(`
                <tr class="table-secondary">
                    <td colspan="6">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Instructor: <b>${instructor}</b></span>
                            <button class="btn btn-success btn-sm ms-2 done-instructor-btn">Done</button>
                        </div>
                    </td>
                </tr>
            `);

            // students
            students.forEach((item) => {
                let row = `
                    <tr>
                        <td>${index++}</td>
                        <td>${item.student_name.toUpperCase()}</td>
                        <td></td>
                        <td>${item.course_name}</td>
                        <td>${item.category_name}</td>
                        <td>${formatDate(item.request_date)}</td>
                    </tr>
                `;
                tbody.append(row);
            });

        });
    }

    // =========================
    // LOAD TABLE DATA
    // =========================
    function loadTable() {

        tbody.html("<tr><td colspan='6'>Loading...</td></tr>");

        $.ajax({
            url: "api.php?endpoint=get_requested_certificates",
            type: "GET",
            dataType: "json",

            success: function (res) {

                if (!res.status) {
                    alert(res.message);
                    return;
                }

                allData = res.data;
                renderTable(allData);
            },

            error: function () {
                tbody.html("<tr><td colspan='6'>Error loading data</td></tr>");
            }
        });
    }

    // =========================
    // LOAD CATEGORY DROPDOWN
    // =========================
    function loadCategories() {

        $.ajax({
            url: "api.php?endpoint=category_get_all",
            type: "GET",
            dataType: "json",

            success: function (res) {

                let select = $("#filterType");
                select.html('<option value="">Filter by...</option>');

                $.each(res.data, function (i, item) {
                    select.append(
                        `<option value="${item.id}">${item.category}</option>`
                    );
                });
            },

            error: function () {
                alert("Error loading categories");
            }
        });
    }

    // =========================
    // FILTER BY CATEGORY
    // =========================
    $("#filterType").on("change", function () {

        let categoryId = $(this).val();

        if (!categoryId) {
            renderTable(allData);
            return;
        }

        let filtered = allData.filter(item => item.category_id == categoryId);
        renderTable(filtered);
    });

    // =========================
    // INIT
    // =========================
    loadTable();
    loadCategories();

});
</script>