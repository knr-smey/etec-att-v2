<?php
    // Get instructor ID from the URL
    $inst_id = isset($_GET['inst_id']) ? intval($_GET['inst_id']) : 0;

    // Optionally: redirect if no valid ID
    if ($inst_id <= 0) {
        echo "<p class='text-danger text-center mt-5'>Invalid instructor ID!</p>";
        exit;
    }
    // echo $inst_id
?>
<div class="container p-0">

    <!-- Header -->
    <div class="mb-4 d-flex justify-content-between align-items-center  border-bottom ">
      <a href="pages/admin/instructors.php" class="back-to">
        <button class=" btn btn-secondary btn-sm">
            Back to Instructor
        </button>
      </a>    
      <h3 class="fw-bold text-etec-color pb-2 mb-0">
        <i class="bi bi-person-badge-fill me-2"></i> Instructor Details
      </h3>
    </div>

    <!-- Profile Section -->
    <div class="d-flex align-items-center p-4 mb-4 profile-card border-0 shadow-sm rounded">
        <div class="col-6 d-flex align-items-center gap-2">
            <div class="col-md-3 text-center rounded-circle border border-2 overflow-hidden" style="width: 200px;height: 200px;">
                <img src="assets/defaultuser.png" alt="Instructor" class="w-100 h-100 object-fit-cover">
            </div>
            <div class="col-md-9">
                <h4 class="fw-bold text-etec-color mb-1">John Doe</h4>
                <p class="mb-1"><strong>Gender:</strong> Male</p>
                <p class="mb-1"><strong>Email:</strong> example@gmail.com</p>
                <p class="mb-1"><strong>Password:</strong> *****</p>
                <p class="mb-1"><strong>Tel:</strong> 012 Free Wifi</p>
                <p class="mb-1"><strong>Skills:</strong> Web Development, JavaScript, React</p>
                <p class="mb-1"><strong>Work Status:</strong> Morning & Evening</p>
            </div>
        </div>

        <!-- Summary Section -->
        <div class="col-6">
            <div class="my-3">
                <div class="card p-3 summary-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0 text-secondary">Total Classes</h5>
                            <h3 class="fw-bold text-etec-color">12</h3>
                        </div>
                        <i class="bi bi-easel-fill fs-2 text-etec-color"></i>
                    </div>
                </div>
            </div >

            <div class="my-3">
                <div class="card p-3 summary-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                        <h5 class="mb-0 text-secondary">Total Students</h5>
                        <h3 class="fw-bold text-etec-color">320</h3>
                        </div>
                        <i class="bi bi-people-fill fs-2 text-etec-color"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    

    <!-- Table Section -->
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white border-0">
            <h5 class="fw-bold text-etec-color mb-0 ">
                <i class="bi bi-journal-text me-2"></i> Instructor’s Classes
            </h5>
        </div>

        <div class="card-body">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Course</th>
                        <th>Students</th>
                        <th>Term & Time</th>
                        <th>Building</th>
                        <th>Floor</th>
                        <th>Room</th>
                        <th>Status</th>
                        <th>Start Date</th>
                    </tr>
                </thead>
                <tbody>
                    
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
$(document).ready(function () {
    const inst_id = <?= $inst_id ?>;

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
        url: "api.php?endpoint=getsingleinstructor",
        type: "POST",
        data: { inst_id },
        dataType: "json",
        success: function (res) {
            if (res.status) {
                const data = res.data;

                // ✅ Update Profile Section
                $(".profile-card img").attr("src", data.image || "assets/defaultuser.png");
                $(".profile-card h4").text(data.name);
                $(".profile-card").find("p:contains('Gender')").html(`<strong>Gender:</strong> ${data.gender}`);
                $(".profile-card").find("p:contains('Email')").html(`<strong>Email:</strong> ${data.email}`);
                $(".profile-card").find("p:contains('Password')").html(`<strong>Password:</strong> ${data.pass}`);
                $(".profile-card").find("p:contains('Tel')").html(`<strong>Tel:</strong> ${data.tel}`);
                $(".profile-card").find("p:contains('Skills')").html(`<strong>Skills:</strong> ${data.skills || "N/A"}`);
                $(".profile-card").find("p:contains('Work Status')").html(`<strong>Work Status:</strong> ${data.work_status || "N/A"}`);

                // ✅ Summary counts
                $(".summary-card").eq(0).find("h3").text(data.total_class || 0);
                $(".summary-card").eq(1).find("h3").text(data.total_student || 0);

                // ✅ Populate class table
                const tbody = $("table tbody");
                tbody.empty();

                if (data.classes && data.classes.length > 0) {
                    console.log(data);
                    
                    data.classes.forEach((cls, i) => {
                        tbody.append(`
                            <tr>
                                <td>${i + 1}</td>
                                <td>${cls.course}</td>
                                <td>${cls.total_stu}</td>
                                <td>${cls.term} - <span class="text-success fw-bold">${cls.time}</span></td>
                                <td><span class="text-etec-color">${cls.building}</span></td>
                                <td>${cls.floor}</td>
                                <td>${cls.room}</td>
                                <td><span class="badge ${getStatusColor(cls.class_status)}">${cls.class_status}</span></td>
                                <td>${cls.created_at.split(" ")[0]}</td>
                            </tr>
                        `);
                    });
                } else {
                    tbody.append(`<tr><td colspan="9" class="text-center text-muted">No classes found</td></tr>`);
                }
            } else {
                $(".container").html(`<p class="text-danger text-center mt-5">${res.message}</p>`);
            }
        },
        error: function () {
            $(".container").html("<p class='text-danger text-center mt-5'>Failed to fetch instructor details.</p>");
        }
    });

    // Helper function for badge color
    function getStatusColor(status) {
        switch (status.toLowerCase()) {
            case "progress": return "bg-primary";
            case "pre-end": return "bg-secondary";
            case "end": return "bg-danger";
            default: return "bg-light text-dark";
        }
    }
});
</script>
