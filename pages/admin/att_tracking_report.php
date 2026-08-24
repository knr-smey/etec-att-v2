<div class="container mt-4">

    <div class="card shadow-sm border-0">
        <div class="card-header text-white" style="background-color: #2c6fad;">
            <h5 class="mb-0">📋 Attendance Tracking Report</h5>
        </div>

        <div class="card-body bg-white">

            <!-- Filter Row -->
            <div class="row mb-3 align-items-end">
                <div class="col-md-2">
                    <label for="trackingDate" class="form-label text-muted small fw-semibold">SELECT DATE</label>
                    <input type="date" id="trackingDate" class="form-control">
                </div>
                <div class="col-md-2">
                    <label for="dayGroupFilter" class="form-label text-muted small fw-semibold">CLASS DAYS</label>
                    <select id="dayGroupFilter" class="form-select">
                        <option value="">All Classes</option>
                        <option value="mon_thu">Mon - Thu</option>
                        <option value="sat_sun">Sat - Sun</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="classStatusFilter" class="form-label text-muted small fw-semibold">CLASS STATUS</label>
                    <select id="classStatusFilter" class="form-select">
                        <option value="">All Status</option>
                        <option value="progress">Progress</option>
                        <option value="pre-end">Pre-End</option>
                        <option value="end">End</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="timeFilter" class="form-label text-muted small fw-semibold">TIME</label>
                    <select id="timeFilter" class="form-select">
                        <option value="">All Times</option>
                    </select>   
                </div>
                <div class="col-md-2">
                    <button id="trackingFilterBtn" class="btn w-100 text-white fw-semibold" style="background-color: #2c6fad;">
                        Filter
                    </button>
                </div>
                <div class="col-md-2 ms-auto text-md-end">
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
                            <th>Class Status</th>
                            <th>Attendance</th>
                        </tr>
                    </thead>
                    <tbody id="trackingTbody">
                        <tr><td colspan="7" class="text-center text-muted py-4">Select a date and click Filter</td></tr>
                    </tbody>
                </table>
            </div>

            <ul class="pagination justify-content-end mt-3 mb-0" id="trackingPagination"></ul>

        </div>
    </div>

</div>

<script>
const trackingPageSize = 7;
let trackingClasses = [];
let trackingCurrentPage = 1;

function loadTrackingTimeOptions() {
    $.ajax({
        url: "api.php",
        type: "GET",
        data: { endpoint: "time_get_all" },
        dataType: "json",
        success: function (response) {
            if (!response.status) return;

            let options = (response.data || []).map(t => `<option value="${t.id}">${t.time}</option>`).join("");
            $("#timeFilter").append(options);
        }
    });
}

function loadAttendanceTrackingReport(date, dayGroup, classStatus, timeId) {
    $("#trackingTbody").empty();
    $("#trackingPagination").empty();
    $("#trackingLoading").removeClass("d-none");

    $.ajax({
        url: "api.php",
        type: "GET",
        data: { endpoint: "att_tracking_status", date: date, day_group: dayGroup, class_status: classStatus, time_id: timeId },
        dataType: "json",

        success: function (response) {
            $("#trackingLoading").addClass("d-none");

            if (!response.status) {
                trackingClasses = [];
                $("#trackingTbody").html(`<tr><td colspan="7" class="text-center text-danger py-4">${response.message}</td></tr>`);
                $("#trackingPagination").empty();
                return;
            }

            trackingClasses = response.data || [];

            if (!trackingClasses.length) {
                $("#trackingTbody").html(`<tr><td colspan="7" class="text-center text-muted py-4">No classes found</td></tr>`);
                $("#trackingPagination").empty();
                $("#trackedCount").text(0);
                $("#notTrackedCount").text(0);
                return;
            }

            renderTrackingTable(1);
        },

        error: function () {
            $("#trackingLoading").addClass("d-none");
            trackingClasses = [];
            $("#trackingTbody").html(`<tr><td colspan="7" class="text-center text-danger py-4">Something went wrong</td></tr>`);
            $("#trackingPagination").empty();
        }
    });
}

function renderTrackingTable(page) {
    trackingCurrentPage = page;

    let trackedCount = trackingClasses.filter(cls => cls.tracked == 1).length;
    $("#trackedCount").text(trackedCount);
    $("#notTrackedCount").text(trackingClasses.length - trackedCount);

    let totalPages = Math.max(1, Math.ceil(trackingClasses.length / trackingPageSize));
    if (trackingCurrentPage > totalPages) trackingCurrentPage = totalPages;

    let start = (trackingCurrentPage - 1) * trackingPageSize;
    let pageClasses = trackingClasses.slice(start, start + trackingPageSize);

    let rows = pageClasses.map(cls => {
        let badge = cls.tracked == 1
            ? `<span class="badge bg-success">Tracked</span>`
            : `<span class="badge bg-warning text-dark">Not Tracked</span>`;

        let statusLabel = cls.class_status === 'progress' ? 'Progress'
            : cls.class_status === 'pre-end' ? 'Pre-End'
            : cls.class_status === 'end' ? 'End'
            : (cls.class_status ?? '-');

        return `
            <tr>
                <td>${cls.class_id}</td>
                <td>${cls.course_name ?? '-'}</td>
                <td>${cls.instructor_name ?? '-'}</td>
                <td>${cls.term_name ?? '-'}</td>
                <td>${cls.time ?? '-'}</td>
                <td>${statusLabel}</td>
                <td>${badge}</td>
            </tr>
        `;
    }).join("");

    $("#trackingTbody").html(rows);
    renderTrackingPagination(totalPages);
}

function renderTrackingPagination(totalPages) {
    const ul = $("#trackingPagination");
    ul.empty();

    if (totalPages <= 1) return;

    const maxVisible = 5;
    let startPage = Math.max(1, trackingCurrentPage - Math.floor(maxVisible / 2));
    let endPage = startPage + maxVisible - 1;
    if (endPage > totalPages) {
        endPage = totalPages;
        startPage = Math.max(1, endPage - maxVisible + 1);
    }

    ul.append(`
        <li class="page-item ${trackingCurrentPage === 1 ? 'disabled' : ''}">
            <button class="page-link shadow-none" data-page="${trackingCurrentPage - 1}">Prev</button>
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
            <li class="page-item ${i === trackingCurrentPage ? 'active' : ''}">
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
        <li class="page-item ${trackingCurrentPage === totalPages ? 'disabled' : ''}">
            <button class="page-link shadow-none" data-page="${trackingCurrentPage + 1}">Next</button>
        </li>
    `);

    ul.find(".page-link").click(function () {
        const page = parseInt($(this).data("page"));
        if (page && page !== trackingCurrentPage) {
            renderTrackingTable(page);
        }
    });
}

$("#trackingFilterBtn").click(function () {
    let date = $("#trackingDate").val();
    let dayGroup = $("#dayGroupFilter").val();
    let classStatus = $("#classStatusFilter").val();
    let timeId = $("#timeFilter").val();

    if (!date) {
        alert("Please select a date");
        return;
    }

    loadAttendanceTrackingReport(date, dayGroup, classStatus, timeId);
});

// Default to today on first load
$(function () {
    let today = new Date().toISOString().split("T")[0];
    $("#trackingDate").val(today);
    loadTrackingTimeOptions();
    loadAttendanceTrackingReport(today, "", "", "");
});
</script>
