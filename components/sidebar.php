<?php
    // Assuming $_SESSION['user']['role'] is set as 'admin' or 'instructor'
    $role = $_SESSION['user']['role'] ?? '';
?>
<style>
    .nav-link-ajax.active{
        background-color: #191846d8 !important;  /* blue background */
        color: #fff !important;               /* white text */
        border-radius: 6px !important;
    }
</style>
<aside class="col-2 bg-etec-color d-flex flex-column sticky-top px-4" style="height: 100vh;">   
    <!-- system logo and name -->
    <div class="text-white border-bottom border-secondary d-flex py-3">
        <img src="assets/etec.png" alt="" class="border rounded-2 object-fit-cover" width="50px" height="50px">
        <div class="ms-2">
            <h6 class="mt-2 mb-0">ETEC CENTER</h6>
            <p class="text-white-50 mb-2 fw-normal">Build your IT</p>
        </div>
    </div>

    <!-- user profile -->
    <div class="text-white d-flex align-items-center py-3 border-bottom border-secondary">
        <img src="" alt="" class="object-fit-cover rounded-circle" width="50" height="50" id="profileImage">
        <div class="ms-2">
            <p class="mb-0 text-limit">Name: <span id="profileName"></span></p>
            <p class="mb-0">ID: <span class="text-info" id="profileId">14</span></p>
        </div>
    </div>

    <!-- list menu of side bar -->
    <div class="flex-grow-1 pt-2 overflow-y-auto overflow-x-hidden">
        <ul class="list-unstyled">
            <a href="pages/frontend/homes.php" class="btn text-white w-100 text-start my-1 ps-2 border-0 fs-6 nav-link-ajax">
                <li class="m-0">
                    <i class="bi bi-house-door-fill me-2"></i>
                    Home
                </li>
            </a>
            <?php if ($role === 'instructor'): ?>
                <!-- instructor -->
                <div class="border-bottom border-secondary">
                    <button class="btn text-white w-100 my-1 ps-2 border-0 fs-6 text-start d-flex justify-content-between align-items-center" 
                            type="button" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#collapseInstructor"
                            id="collapseBtn">

                        <div>
                            <i class="bi bi-person-workspace me-2"></i>
                            Instructor 
                        </div>
                        <i class="bi bi-caret-right-fill arrow-icon fs-6 text-end"></i>
                        
                    </button>
                    <div class="collapse" id="collapseInstructor">
                        <a href="pages/frontend/classes.php" class="btn text-white w-100 text-start my-1  border-0 fs-6 nav-link-ajax">
                            <li class="m-0">
                                <i class="bi bi-house-add-fill me-2"></i>
                                Class
                            </li>
                        </a>
                        <a href="pages/frontend/homes.php" class="btn text-white w-100 text-start my-1  border-0 fs-6 nav-link-ajax">
                            <li class="m-0">
                                <i class="bi bi-gear-fill me-2"></i>
                                Setting
                            </li>
                        </a>
                        
                            <a href="pages/frontend/report.php" class="btn text-white w-100 text-start my-1  border-0 fs-6 nav-link-ajax">
                                <li class="m-0">
                                    <i class="bi bi-file-text me-2"></i>
                                    Report
                                </li>
                            </a>
                    	
                    </div>
                </div>
                <!-- instructor -->
            <?php elseif ($role === 'admin'): ?>
                <!-- admin -->
                
                <div class="border-bottom border-secondary">
                    <button class="btn text-white w-100 my-1 px-2 border-0 fs-6 text-start d-flex justify-content-between align-items-center" 
                            type="button" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#collapseAdmin"
                            id="collapseBtnAdmin">

                        <div>
                            <i class="bi bi-person-workspace me-2"></i>
                            Admin 
                        </div>
                        <i class="bi bi-caret-right-fill arrow-icon fs-6 text-end"></i>
                    </button>
                    <div class="collapse" id="collapseAdmin">
                        <a href="pages/admin/notifications.php" class="btn text-white w-100 text-start my-1 border-0 fs-6 nav-link-ajax position-relative">
                            <li class="m-0">
                                <i class="bi bi-bell-fill me-2"></i>
                                Notification
                                <span id="notificationBadge" class="position-absolute top-50 translate-middle badge rounded-pill bg-danger d-none" style="right: 5px;">
                                    0
                                </span>
                            </li>
                        </a>
                        <a href="pages/admin/permissionRule.php" class="btn text-white w-100 text-start my-1 border-0 fs-6 nav-link-ajax position-relative">
                            <li class="m-0">
                                <i class="bi bi-file-earmark-ruled me-2"></i>
                                Permission Rule
                            </li>
                        </a>
                        <a href="pages/admin/discountRule.php" class="btn text-white w-100 text-start my-1 border-0 fs-6 nav-link-ajax position-relative">
                            <li class="m-0">
                                <i class="bi bi-tag-fill me-2"></i>
                                Discount Rule
                            </li>
                        </a>
                        <!-- <a href="pages/admin/time.php" class="btn text-white w-100 text-start my-1 border-0 fs-6 nav-link-ajax position-relative">
                            <li class="m-0">
                                <i class="bi bi-alarm me-2"></i>
                                Time Control
                            </li>
                        </a> -->
                        <a href="pages/admin/students.php" class="btn text-white w-100 text-start my-1  border-0 fs-6 nav-link-ajax">
                            <li class="m-0">
                                <i class="bi bi-people-fill me-2"></i>
                                Student
                            </li>
                        </a>
                        <a href="pages/admin/permissionNotify.php" class="btn text-white w-100 text-start my-1 border-0 fs-6 nav-link-ajax position-relative">
                            <li class="m-0">
                                <i class="bi bi-chat-quote-fill me-2"></i>
                                Permission Record
                            </li>
                        </a>
                        <a href="pages/admin/instructors.php" class="btn text-white w-100 text-start my-1 border-0 fs-6 nav-link-ajax">
                            <li class="m-0">
                                <i class="bi bi-person-fill me-2"></i>
                                Instructor
                            </li>
                        </a>
                        <a href="pages/admin/classes.php" class="btn text-white w-100 text-start my-1  border-0 fs-6 nav-link-ajax">
                            <li class="m-0">
                                <i class="bi bi-house-fill me-2"></i>
                                Class
                            </li>
                        </a>
                        <a href="pages/admin/classtypes.php" class="btn text-white w-100 text-start my-1  border-0 fs-6 nav-link-ajax">
                            <li class="m-0">
                                <i class="bi bi-house-exclamation me-2"></i>
                                Class Type
                            </li>
                        </a>
                        <a href="pages/admin/buildings.php" class="btn text-white w-100 text-start my-1  border-0 fs-6 nav-link-ajax">
                            <li class="m-0">
                                <i class="bi bi-building-fill me-2"></i>
                                Building
                            </li>
                        </a>
                        <a href="pages/admin/courses.php" class="btn text-white w-100 text-start my-1  border-0 fs-6 nav-link-ajax">
                            <li class="m-0">
                                <i class="bi bi-book-fill me-2"></i>
                                Courses
                            </li>
                        </a>
                        <a href="pages/admin/termandtime.php" class="btn text-white w-100 text-start my-1  border-0 fs-6 nav-link-ajax">
                            <li class="m-0">
                                <i class="bi bi-clock-fill me-2"></i>
                                Term & Time
                            </li>
                        </a>
                        <a href="pages/admin/schedules.php" class="btn text-white w-100 text-start my-1  border-0 fs-6 nav-link-ajax">
                            <li class="m-0">
                                <i class="bi bi-calendar-plus me-2"></i>
                                Schedule
                            </li>
                        </a>
                        <a href="" class="btn text-white w-100 text-start my-1  border-0 fs-6 nav-link-ajax">
                            <li class="m-0">
                                <i class="bi bi-book-half  me-2"></i>
                                Document
                            </li>
                        </a>
                        <a href="pages/admin/roadmaps.php" class="btn text-white w-100 text-start my-1 border-0 fs-6 nav-link-ajax">
                            <li class="m-0">
                                <i class="bi bi-map-fill  me-2"></i>
                                Roadmap
                            </li>
                        </a>
                        <a href="pages/admin/categories.php" class="btn text-white w-100 text-start my-1 border-0 fs-6 nav-link-ajax">
                            <li class="m-0">
                                <i class="bi bi-bookmark-x  me-2"></i>
                                Category
                            </li>
                        </a>
                    </div>
                </div>
                <!-- admin -->
            <?php endif; ?>
        
        </ul>
    </div>

    <!-- Logout Button fixed at bottom -->
    <div class="p-2" >
        <button class="btn btn-danger w-100" type="button" data-bs-toggle="modal" data-bs-target="#logoutModal">
            <i class="bi bi-box-arrow-left me-2"></i> Logout
        </button>
    </div>

    

</aside>

<div class="modal fade" id="logoutModal" >
    <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
            
            <div class="modal-header">
                <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                Are you sure you want to logout?
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger shadow-none" id="btnLogout">
                    Logout
                    <span class="spinner-border spinner-border-sm text-light ms-2 d-none" role="status" aria-hidden="true"></span>
                </button>
            </div>

            </div>
        </div>
    </div>  

<script>
$(document).ready(function(){

    $('#btnLogout').on('click', function(e) {
        e.preventDefault();
        const btn = $(this);
        const spinner = btn.find('.spinner-border');

        // Show spinner and disable button
        spinner.removeClass('d-none');
        btn.prop('disabled', true);
        $.ajax({
            url: 'api.php?endpoint=logout',  // Call your router endpoint
            method: 'POST',                  // Must be POST
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    // Redirect to login page after logout
                    window.location.href = 'login.php';
                } else {
                    alert('Logout failed!');
                    spinner.addClass('d-none');
                    btn.prop('disabled', false);
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
                spinner.addClass('d-none');
                btn.prop('disabled', false);
            }
        });
    });



    function loadProfile() {
        $.ajax({
            url: 'api.php?endpoint=profile',  // call your switch-case
            method: 'GET',                    // must be GET
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    const profile = res.data;
                    $('#profileId').text(profile.id);
                    $('#profileName').text(profile.name);
                    $('#profileImage').attr('src', profile.image || 'assets/defaultuser.png');
                } else {
                    console.log('Profile not found');
                }
            },
            error: function(err) {
                console.log('Failed to load profile', err);
            }
        });
    }  

    function updatePendingUsersBadge() {
        $.ajax({
            url: 'api.php?endpoint=getPendingUsers',
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    const count = res.data.length || 0; // assuming res.data is an array of pending users
                    const badge = $('#notificationBadge');

                    if (count > 0) {
                        badge.text(count);
                        badge.removeClass('d-none');
                    } else {
                        badge.addClass('d-none');
                    }
                }
            },
            error: function(err) {
                console.log('Error fetching pending users', err);
            }
        });
    }

    // Call once and update periodically
    // updatePendingUsersBadge();
    // setInterval(updatePendingUsersBadge, 5000); // every 5 seconds

    loadProfile();
});
</script>
