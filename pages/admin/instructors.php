<!-- Instructor section -->
<section>
    <div class="">
        <h3 class="mb-0">Instructor Management</h3>
        <p class="text-secondary mb-0">Manage instructors for your school</p>
    </div>

    <div class="d-flex justify-content-between mt-2 align-items-center border-bottom pb-3">       
        <div class="d-flex col-12 align-items-center justify-content-between">
            <!-- Search Form -->
            <div class="col-3 me-2">
                <form class="d-flex border rounded bg-white" id="searchForm">
                    <input 
                        type="text" 
                        placeholder="Search Instructor..." 
                        class="form-control shadow-none border-0 bg-transparent" 
                        id="searchInput"
                    />
                    <button class="btn" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
            </div>

            <!-- Filter by Time -->
            <div class="d-flex align-items-center">

                <p class="m-0 me-2 text-secondary">Filter instructor who available -></p>

                <div class="col-auto">
                    <select class="form-select shadow-none border" id="timeFilter">
                        <option value="">All instructor</option>
                    </select>
                </div>

                <div class="col-auto ms-2">
                    <button class="btn btn-primary" id="btnFilter">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Alert -->
    <div id="successAlert" class="alert alert-success alert-dismissible fade show mt-3" style="display:none;" role="alert">
        <span id="successMessage"></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <!-- Instructor List -->
    <div class="p-0 mt-4">
        <div id="instructorList" class="row g-4">
            <?php require __DIR__ . '../../../utils/instructor_skelaton.php'; ?>
            <!-- Add this above your instructorList div -->
            <div id="filterResultTitle" class="mt-4 mb-2" style="display:none;">
                <h2 class="h4 text-primary">Available Instructors</h2>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <form id="formDeleteAcc">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger">Confirm Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="deleteAccId">
                        Are you sure you want to delete this instructor?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-danger" id="confirmDeleteBtn">Yes, Delete</button>
                    </div>
              </form>
            </div>
        </div>
    </div>
</section>

<script>
$(document).ready(function () {
    let users = [];

    function showAlert(message) {
        $('#successMessage').text(message);
        $('#successAlert').stop(true, true).fadeIn();
        setTimeout(() => $('#successAlert').fadeOut('slow'), 3000);
    }

    function showLoadingOverlay() {
        $("#instructorList").html(`
            <div class="d-flex justify-content-center align-items-center w-100" style="min-height:200px;">
                <div class="spinner-border text-primary" role="status" style="width: 4rem; height: 4rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);
    }

    function renderInstructors(list) {
        let html = "";
        list.forEach(inst => {
            html += `
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                        <div class="card-body">
                            <div class="d-flex align-items-center position-relative">
                                <img src="${inst.image || './assets/defaultuser.png'}" alt="Instructor"
                                    class="rounded-circle border shadow-sm object-fit-cover"
                                    width="120" height="120">
                                <div class="ms-3">
                                    <h5 class="mb-1 fw-semibold">${inst.name}</h5>
                                    <p class="text-primary small mb-1">${inst.email}</p>
                                    <p class="text-muted small mb-2">pw: ${inst.pass || '********'}</p>
                                    <span class="badge bg-primary-subtle text-primary px-3 py-1">Instructor</span>
                                    <span class="badge bg-secondary">ID: ${inst.id}</span>
                                </div>
                                <div class="position-absolute end-0 top-0">
                                    <div class="dropdown">
                                        <button class="btn border-0 shadow-none p-0" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots fs-5"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a href="pages/admin/instructorDetail.php?inst_id=${inst.id}"
                                                    class="dropdown-item view-instructor-detail"
                                                    data-id="${inst.id}">
                                                        <i class="bi bi-eye me-2"></i>View
                                                </a>
                                            </li>
                                            <li>
                                                <a data-bs-target="#deleteConfirmModal" data-bs-toggle="modal"
                                                class="btn dropdown-item text-danger delete-btn"
                                                data-id="${inst.id}" href="#">
                                                    Delete
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="border-top py-1 mb-0 text-center text-etec-color">
                            <p class="small mb-0">etec center</p>
                        </div>
                    </div>
                </div>`;
        });

        $("#instructorList").html(html || "<p class='text-muted'>No instructors found</p>");
    }

	  $(document).on("click", ".view-instructor-detail", function(e) {
            e.preventDefault();

            const url = $(this).attr("href");
            const instID = parseInt($(this).data("id"));

            if (!instID || instID <= 0) {
                alert("Student ID missing!");
                return;
            }



            // Add timestamp to avoid caching
            const fullUrl = url.includes("?") 
                ? `${url}&stu_id=${instID}&_=${new Date().getTime()}`
                : `${url}?stu_id=${instID}&_=${new Date().getTime()}`;

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

    function loadInstructors() {
        showLoadingOverlay();
        $("#filterResultTitle").hide(); // hide title when loading all
        $.ajax({
            url: "api.php?endpoint=instructor_getall",
            method: "GET",
            dataType: "json",
            success: function (res) {
                if (!res.status) {
                    $("#instructorList").html("<p class='text-danger'>Failed to load instructors</p>");
                    return;
                }
                users = res.data;
                renderInstructors(users);
            },
            error: function () {
                $("#instructorList").html("<p class='text-danger'>Error loading instructors</p>");
            }
        });
    }

    function filterAndRender() {
        let keyword = $('#searchInput').val().toLowerCase();
        let selectedTime = $('#timeFilter').val();

        showLoadingOverlay();

        if (selectedTime) {
            $("#filterResultTitle").show(); // show title when filter applied
            $.ajax({
                url: `api.php?endpoint=filterKruAvailable&time_id=${selectedTime}`,
                method: "GET",
                dataType: "json",
                success: function(res) {
                    let availableInstructors = [];

                    if (res.status && res.data.length > 0) {
                        availableInstructors = res.data;

                        if (keyword) {
                            availableInstructors = availableInstructors.filter(inst =>
                                inst.name.toLowerCase().includes(keyword) ||
                                inst.email.toLowerCase().includes(keyword) ||
                                String(inst.id).includes(keyword)
                            );
                        }
                    }

                    renderInstructors(availableInstructors);
                },
                error: function() {
                    $("#instructorList").html("<p class='text-danger'>Error fetching available instructors</p>");
                }
            });
        } else {
            $("#filterResultTitle").hide(); // hide title if no filter
            let filtered = users;
            if (keyword) {
                filtered = filtered.filter(inst =>
                    inst.name.toLowerCase().includes(keyword) ||
                    inst.email.toLowerCase().includes(keyword) ||
                    String(inst.id).includes(keyword)
                );
            }
            renderInstructors(filtered);
        }
    }

    $('#searchForm').on('submit', function(e){ e.preventDefault(); filterAndRender(); });
    $('#searchInput').on('keyup', filterAndRender);
    $('#btnFilter').on('click', filterAndRender);

    function loadTimes() {
        $.ajax({
            url: "api.php?endpoint=time_get_all",
            method: "GET",
            dataType: "json",
            success: function(res) {
                if (!res.status) return;
                let options = `<option value="">All instructor</option>`;
                res.data.forEach(time => {
                    options += `<option value="${time.id}">${time.time}</option>`;
                });
                $("#timeFilter").html(options);
            }
        });
    }

    loadTimes();

    $(document).on("click", ".delete-btn", function(){ $('#deleteAccId').val($(this).data("id")); });

    $('#formDeleteAcc').on('submit', function(e){
        e.preventDefault();
        let id = $('#deleteAccId').val();
        let btn = $('#confirmDeleteBtn');
        let originalText = btn.text();

        btn.prop('disabled', true)
           .html('<span class="spinner-border spinner-border-sm me-2"></span>Deleting...');

        $.ajax({
            url: "api.php?endpoint=instructor_delete",
            method: "POST",
            data: { id },
            dataType: "json",
            success: function(res){
                btn.prop('disabled', false).text(originalText);
                if (res.status) {
                    showAlert(res.message);
                    $('#deleteConfirmModal').modal('hide');
                    loadInstructors();
                } 
            },
            error: function(){
                btn.prop('disabled', false).text(originalText);
                alert("Error deleting instructor");
            }
        });
    });

    loadInstructors();
});
</script>

