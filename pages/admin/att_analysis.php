<div class="container mt-4">

    <div class="card shadow-sm border-0">
        <div class="card-header text-white" style="background-color: #2c6fad;">
            <h5 class="mb-0">📊 Attendance Analysis</h5>
        </div>

        <div class="card-body bg-white">

            <!-- Filter Row -->
            <div class="row mb-4 align-items-end">
                <div class="col-md-3">
                    <label for="date" class="form-label text-muted small fw-semibold">SELECT DATE</label>
                    <input type="date" id="date" class="form-control">
                </div>
                <div class="col-md-2">
                    <button id="filterBtn" class="btn w-100 text-white fw-semibold" style="background-color: #2c6fad;">
                        Filter
                    </button>
                </div>
            </div>

            <hr class="opacity-25">

            <!-- Loading -->
            <div id="loading" class="text-center py-4 d-none">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted small">Loading attendance...</p>
            </div>

            <!-- Result -->
            <div id="result" class="row g-3 text-center"></div>

        </div>
    </div>

</div>

<script>
$("#filterBtn").click(function () {

    let date = $("#date").val();

    if (!date) {
        alert("Please select a date");
        return;
    }

    $("#result").html("");
    $("#loading").removeClass("d-none");

    $.ajax({
        url: "api.php",
        type: "GET",
        data: { endpoint: "att_filter", date: date },
        dataType: "json",

        success: function (response) {
            $("#loading").addClass("d-none");
            let r = response.data;

            $("#result").html(`

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #28a745 !important;">
                        <div class="card-body py-4">
                            <p class="text-muted small mb-1 text-uppercase fw-semibold">Present</p>
                            <h2 class="fw-bold mb-0" style="color: #28a745;">${r.total_present}</h2>
                            <p class="text-muted small mt-1 mb-0">Students checked in</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #dc3545 !important;">
                        <div class="card-body py-4">
                            <p class="text-muted small mb-1 text-uppercase fw-semibold">Absent</p>
                            <h2 class="fw-bold mb-0" style="color: #dc3545;">${r.total_absent}</h2>
                            <p class="text-muted small mt-1 mb-0">Students missing</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #fd7e14 !important;">
                        <div class="card-body py-4">
                            <p class="text-muted small mb-1 text-uppercase fw-semibold">Permission</p>
                            <h2 class="fw-bold mb-0" style="color: #fd7e14;">${r.total_permission}</h2>
                            <p class="text-muted small mt-1 mb-0">Approved absences</p>
                        </div>
                    </div>
                </div>

            `);
        },

        error: function () {
            $("#loading").addClass("d-none");
            alert("Something went wrong");
        }
    });
});
</script>