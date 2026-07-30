<div class="container mt-4">

    <div class="card shadow-sm border-0">
        <div class="card-header text-white" style="background-color: #2c6fad;">
            <h5 class="mb-0">📋 Attendance Tracking Report</h5>
        </div>

        <div class="card-body bg-white">

            <!-- Filter Row -->
            <div class="row mb-4 align-items-end">
                <div class="col-md-3">
                    <label for="trackingDate" class="form-label text-muted small fw-semibold">SELECT DATE</label>
                    <input type="date" id="trackingDate" class="form-control">
                </div>
                <div class="col-md-2">
                    <button id="trackingFilterBtn" class="btn w-100 text-white fw-semibold" style="background-color: #2c6fad;">
                        Filter
                    </button>
                </div>
                <div class="col-md-4 ms-auto text-md-end">
                    <span class="badge bg-success">Tracked <span id="trackedCount">0</span></span>
                    <span class="badge bg-warning text-dark">Not Tracked <span id="notTrackedCount">0</span></span>
                </div>
            </div>

            <hr class="opacity-25">

            <!-- Loading -->
            <div id="trackingLoading" class="text-center py-4 d-none">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted small">Loading classes...</p>
            </div>

            <!-- Result -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Class ID</th>
                            <th>Course</th>
                            <th>Instructor</th>
                            <th>Term</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="trackingTbody">
                        <tr><td colspan="6" class="text-center text-muted py-4">Select a date and click Filter</td></tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>

<script>
function loadAttendanceTrackingReport(date) {
    $("#trackingTbody").empty();
    $("#trackingLoading").removeClass("d-none");

    $.ajax({
        url: "api.php",
        type: "GET",
        data: { endpoint: "att_tracking_status", date: date },
        dataType: "json",

        success: function (response) {
            $("#trackingLoading").addClass("d-none");

            if (!response.status) {
                $("#trackingTbody").html(`<tr><td colspan="6" class="text-center text-danger py-4">${response.message}</td></tr>`);
                return;
            }

            let classes = response.data;

            if (!classes.length) {
                $("#trackingTbody").html(`<tr><td colspan="6" class="text-center text-muted py-4">No active classes found</td></tr>`);
                $("#trackedCount").text(0);
                $("#notTrackedCount").text(0);
                return;
            }

            let trackedCount = 0;
            let rows = classes.map(cls => {
                if (cls.tracked == 1) trackedCount++;
                let badge = cls.tracked == 1
                    ? `<span class="badge bg-success">Tracked</span>`
                    : `<span class="badge bg-warning text-dark">Not Tracked</span>`;

                return `
                    <tr>
                        <td>${cls.class_id}</td>
                        <td>${cls.course_name ?? '-'}</td>
                        <td>${cls.instructor_name ?? '-'}</td>
                        <td>${cls.term_name ?? '-'}</td>
                        <td>${cls.time ?? '-'}</td>
                        <td>${badge}</td>
                    </tr>
                `;
            }).join("");

            $("#trackingTbody").html(rows);
            $("#trackedCount").text(trackedCount);
            $("#notTrackedCount").text(classes.length - trackedCount);
        },

        error: function () {
            $("#trackingLoading").addClass("d-none");
            $("#trackingTbody").html(`<tr><td colspan="6" class="text-center text-danger py-4">Something went wrong</td></tr>`);
        }
    });
}

$("#trackingFilterBtn").click(function () {
    let date = $("#trackingDate").val();

    if (!date) {
        alert("Please select a date");
        return;
    }

    loadAttendanceTrackingReport(date);
});

// Default to today on first load
$(function () {
    let today = new Date().toISOString().split("T")[0];
    $("#trackingDate").val(today);
    loadAttendanceTrackingReport(today);
});
</script>
