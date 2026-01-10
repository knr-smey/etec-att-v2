<?php
    session_start();

    // Check if user is logged in
    if (!isset($_SESSION['user'])) {
        // Redirect to login page
        header('Location: login.php');
        exit;
    }
?>
<!DOCTYPE html>
<html lang="en">

<!-- head -->
<?php include ('utils/head.php') ?>
<!-- head -->

<body class="container-fluid p-0 font-custom">
    <main class="m-0 d-none d-lg-flex">

        <!-- Sidebar -->
        <?php require_once (__DIR__.'/components/sidebar.php') ?>
        <!-- Sidebar -->

        <!-- Main content area -->
        <div id="content-area" class="flex-grow-1" style="padding:35px">
            <!-- Page content will be loaded here -->
        </div>
        
</main>

    <main class="d-flex align-items-center justify-content-center d-lg-none">
        <h1>Not Found</h1>
    </main>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Bootstrap Spinner on page load -->
    <script>
        $(document).ready(function() {

            // Collapse buttons
            $('#collapseBtn').on('click', function() {
                $('.content').toggleClass('d-none d-block');
                $(this).find('.arrow-icon').toggleClass('rotate');
            });

            $('#collapseBtnAdmin').on('click', function() {
                $('.content').toggleClass('d-none d-block');
                $(this).find('.arrow-icon').toggleClass('rotate');
            });

            // Function to load page dynamically
            function loadPage(url) {
                // Show spinner while loading
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

                // Load new page into content area
                $("#content-area").load(url, function(response, status, xhr) {
                    if (status === "error") {
                        $("#content-area").html(`
                            <div class="text-center text-danger py-5">
                                <h5>⚠️ Failed to load page</h5>
                                <p>${xhr.status} ${xhr.statusText}</p>
                            </div>
                        `);
                    }
                });
            }

            // Sidebar navigation (AJAX page load)
            $(document).on("click", ".nav-link-ajax", function(e) {
                e.preventDefault();
                const url = $(this).attr("href");
                loadPage(url);

                // Highlight active link
                $(".nav-link-ajax").removeClass("active");
                $(this).addClass("active");
            });

            // Optional: Load default page when first open
            // loadPage("pages/admin/buildings.php");
            loadPage("pages/frontend/homes.php");
        });
    </script>
</body>
</html>
